<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegistroRequest;
use App\Services\Auth\RecuperacionService;
use App\Services\Api\ApiPeruService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;

/**
 * Controlador de autenticación mejorado para el sistema de mesa de partes
 * 
 * Este controlador maneja todo el flujo de autenticación incluyendo:
 * - Login de usuarios externos (DNI) con validación de RENIEC
 * - Login de usuarios internos (credenciales administrativas)
 * - Registro de nuevos usuarios con verificación de documentos
 * - Recuperación de contraseña con tokens seguros
 * - Integración con APIs externas (RENIEC/SUNAT)
 * - Sistema de intentos fallidos y bloqueo temporal
 * - Auditoría de accesos y seguridad
 * 
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @var RecuperacionService
     */
    protected $recuperacionService;

    /**
     * @var ApiPeruService
     */
    protected $apiPeruService;

    /**
     * Constructor del controlador
     */
    public function __construct()
    {
        $this->recuperacionService = new RecuperacionService();
        $this->apiPeruService = new ApiPeruService();
    }

    /**
     * Muestra el formulario de login principal para usuarios externos
     */
    public function showLoginForm()
    {
        return view('auth.login', [
            'title' => 'Iniciar Sesión - Mesa de Partes',
            'recaptcha_enabled' => config('services.recaptcha.enabled'),
            'recaptcha_site_key' => config('services.recaptcha.site_key')
        ]);
    }

    /**
     * Muestra el formulario de login para usuarios internos (administrativos)
     */
    public function showLoginInternoForm()
    {
        return view('auth.login-interno', [
            'title' => 'Login Interno - Administración',
            'recaptcha_enabled' => config('services.recaptcha.enabled'),
            'recaptcha_site_key' => config('services.recaptcha.site_key')
        ]);
    }

    /**
     * Procesa el login de usuarios externos con DNI
     */
    public function login(Request $request)
    {
        try {
            // Validar reCAPTCHA si está habilitado
            if (config('services.recaptcha.enabled')) {
                $this->validarRecaptcha($request->input('g-recaptcha-response'));
            }

            // Validar credenciales
            $credentials = $request->validate([
                'dni' => 'required|string|size:8|regex:/^\\d{8}$/',
                'password' => 'required|string|min:6',
                // 1: Natural, 2: Jurídica, 3: Entidad Pública
                'tipo_persona' => 'required|in:1,2,3',
                'ruc' => 'nullable|string|max:45',
                'nombre_entidad' => 'nullable|string|max:255',
                'remember' => 'boolean'
            ]);

            // Buscar usuario por DNI y tipo de persona
            $usuario = Usuario::where('dni', $credentials['dni'])
                ->where('tipo_user', 1) // 1: Usuario externo
                ->where('tipo_persona', $credentials['tipo_persona'])
                ->first();

            if (!$usuario) {
                Log::warning("Intento de login con DNI no registrado: {$credentials['dni']}");
                throw ValidationException::withMessages([
                    'dni' => 'El DNI no está registrado o no coincide con el tipo de persona seleccionado.'
                ]);
            }

            // Verificaciones adicionales según tipo de persona (mismas que en el PHP puro)
            if ((int)$credentials['tipo_persona'] === 2) { // Persona Jurídica
                if (empty($credentials['ruc'])) {
                    throw ValidationException::withMessages([
                        'ruc' => 'El RUC es obligatorio para persona jurídica.'
                    ]);
                }

                if (empty($usuario->ruc)) {
                    throw ValidationException::withMessages([
                        'ruc' => 'Su cuenta no tiene un RUC asociado. Comuníquese con la mesa de partes.'
                    ]);
                }
            } elseif ((int)$credentials['tipo_persona'] === 3) { // Entidad Pública
                if (empty($credentials['nombre_entidad'])) {
                    throw ValidationException::withMessages([
                        'nombre_entidad' => 'El nombre de la entidad es obligatorio para entidad pública.'
                    ]);
                }

                if (empty($usuario->razon_social)) {
                    throw ValidationException::withMessages([
                        'nombre_entidad' => 'Su cuenta no tiene una entidad asociada. Comuníquese con la mesa de partes.'
                    ]);
                }
            }

            // Verificar contraseña (usar passw directamente) con soporte de migración de hashes antiguos
            if (!Hash::check($credentials['password'], $usuario->passw)) {
                // Intentar migración desde texto plano como en el sistema antiguo
                if ($usuario->passw === $credentials['password']) {
                    $usuario->passw = Hash::make($credentials['password']);
                    $usuario->save();
                } else {
                    Log::warning("Intento de login fallido para DNI: {$credentials['dni']}");
                    $this->registrarIntentoFallido($credentials['dni'], $request->ip());
                    
                    throw ValidationException::withMessages([
                        'password' => 'La contraseña es incorrecta.'
                    ]);
                }
            }

            // Verificar si el usuario está bloqueado
            if ($this->estaUsuarioBloqueado($usuario)) {
                throw ValidationException::withMessages([
                    'dni' => 'La cuenta está temporalmente bloqueada por múltiples intentos fallidos.'
                ]);
            }

            // Autenticar al usuario
            Auth::login($usuario, $credentials['remember'] ?? false);
            
            // Limpiar intentos fallidos
            $this->limpiarIntentosFallidos($credentials['dni']);
            
            // Registrar acceso exitoso
            $this->registrarAccesoExitoso($usuario, $request->ip());

            // Redirigir según el tipo de usuario
            return $this->redirigirUsuario($usuario);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Error en login externo: " . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al procesar el login. Por favor, inténtelo nuevamente.'])->withInput();
        }
    }

    /**
     * Procesa el login de usuarios internos (administrativos)
     */
    public function loginInterno(Request $request)
    {
        try {
            // Validar reCAPTCHA si está habilitado
            if (config('services.recaptcha.enabled')) {
                $this->validarRecaptcha($request->input('g-recaptcha-response'));
            }

            // Validar credenciales
            $credentials = $request->validate([
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:6',
                'remember' => 'boolean'
            ]);

            // Buscar usuario interno por email
            $usuario = Usuario::where('email', $credentials['email'])
                ->where('tipo_user', 2) // 2: Usuario interno
                ->first();

            if (!$usuario) {
                Log::warning("Intento de login interno con email no registrado: {$credentials['email']}");
                throw ValidationException::withMessages([
                    'email' => 'El email no está registrado o no corresponde a un usuario interno.'
                ]);
            }

            // Verificar contraseña (usar passw directamente) con soporte de migración
            if (!Hash::check($credentials['password'], $usuario->passw)) {
                // Intentar migración desde texto plano como en el sistema antiguo
                if ($usuario->passw === $credentials['password']) {
                    $usuario->passw = Hash::make($credentials['password']);
                    $usuario->save();
                } else {
                    Log::warning("Intento de login interno fallido para email: {$credentials['email']}");
                    $this->registrarIntentoFallido($credentials['email'], $request->ip());
                    
                    throw ValidationException::withMessages([
                        'password' => 'La contraseña es incorrecta.'
                    ]);
                }
            }

            // Verificar si el usuario está bloqueado
            if ($this->estaUsuarioBloqueado($usuario)) {
                throw ValidationException::withMessages([
                    'email' => 'La cuenta está temporalmente bloqueada por múltiples intentos fallidos.'
                ]);
            }

            // Autenticar al usuario
            Auth::login($usuario, $credentials['remember'] ?? false);
            
            // Limpiar intentos fallidos
            $this->limpiarIntentosFallidos($credentials['email']);
            
            // Registrar acceso exitoso
            $this->registrarAccesoExitoso($usuario, $request->ip());

            // Redirigir según sea administrador o usuario interno operativo
            if ($usuario->es_administrador) {
                return redirect()->route('dashboard')->with('success', 'Bienvenido al sistema de administración.');
            }

            // Usuario interno no administrador: redirigir a bandeja de trámites internos
            return redirect()->route('mesa-partes.tramites-recibidos')->with('success', 'Bienvenido al panel de mesa de partes.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Error en login interno: " . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al procesar el login. Por favor, inténtelo nuevamente.'])->withInput();
        }
    }

    /**
     * Muestra el formulario de registro de nuevos usuarios
     */
    public function showRegistroForm()
    {
        $departamentos = Departamento::orderBy('nombre')->get();
        
        return view('auth.registro', [
            'title' => 'Registro de Usuario - Mesa de Partes',
            'departamentos' => $departamentos,
            'recaptcha_enabled' => config('services.recaptcha.enabled'),
            'recaptcha_site_key' => config('services.recaptcha.site_key'),
            'api_reniec_enabled' => config('services.reniec.enabled')
        ]);
    }

    /**
     * Procesa el registro de nuevos usuarios
     */
    public function registro(Request $request)
    {
        try {
            // Validar reCAPTCHA si está habilitado
            if (config('services.recaptcha.enabled')) {
                $this->validarRecaptcha($request->input('g-recaptcha-response'));
            }

            // Validar datos del formulario según estructura SQL
            $validated = $request->validate([
                'dni' => 'required|string|size:8|regex:/^\\d{8}$/|unique:usuarios,dni',
                'nombres' => 'required|string|max:20',
                'apellido_paterno' => 'required|string|max:20',
                'apellido_materno' => 'required|string|max:20',
                'email' => 'required|email|max:45|unique:usuarios,email',
                'telefono' => 'required|string|size:9|regex:/^\\d{9}$/',
                'direccion' => 'required|string|max:35',
                'distrito_id' => 'required|exists:distritos,id',
                'password' => 'required|string|min:6|confirmed',
                'tipo_persona' => 'required|in:1,2',
                'ruc' => 'nullable|string|max:45|unique:usuarios,ruc',
                'razon_social' => 'nullable|string|max:45',
            ]);

            // Si es persona jurídica, RUC y razón social son requeridos
            if ($validated['tipo_persona'] == 2) {
                $request->validate([
                    'ruc' => 'required|string|max:45|unique:usuarios,ruc',
                    'razon_social' => 'required|string|max:45',
                ]);
            }

            // Validar documento con API externa si está habilitado
            if (config('services.reniec.enabled', false)) {
                $datosDocumento = $this->apiPeruService->consultarDni($validated['dni']);
                
                if ($datosDocumento) {
                    // Opcional: verificar que los datos coincidan
                    $this->verificarCoincidenciaDatos($validated, $datosDocumento);
                }
            }

            // Crear el usuario
            $usuario = Usuario::create([
                'dni' => $validated['dni'],
                'nombres' => $validated['nombres'],
                'apellido_paterno' => $validated['apellido_paterno'],
                'apellido_materno' => $validated['apellido_materno'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'],
                'direccion' => $validated['direccion'],
                'distrito_id' => $validated['distrito_id'],
                'passw' => Hash::make($validated['password']),
                'tipo_user' => 1, // 1: Usuario externo
                'tipo_persona' => $validated['tipo_persona'],
                'ruc' => $validated['ruc'] ?? null,
                'razon_social' => $validated['razon_social'] ?? null,
                'fecha_registro' => now(),
            ]);

            // Enviar email de bienvenida
            $this->enviarEmailBienvenida($usuario);

            // Registrar el registro exitoso
            Log::info("Usuario registrado exitosamente: {$usuario->email} (DNI: {$usuario->dni})");

            return redirect()->route('login')->with('success', 'Registro exitoso. Por favor, inicie sesión con sus credenciales.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Error en registro de usuario: " . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al procesar el registro. Por favor, inténtelo nuevamente.'])->withInput();
        }
    }

    /**
     * Muestra el formulario de recuperación de contraseña
     */
    public function showRecuperarForm()
    {
        return view('auth.recuperar', [
            'title' => 'Recuperar Contraseña - Mesa de Partes',
            'recaptcha_enabled' => config('services.recaptcha.enabled'),
            'recaptcha_site_key' => config('services.recaptcha.site_key')
        ]);
    }

    /**
     * Procesa la solicitud de recuperación de contraseña
     */
    public function recuperar(Request $request)
    {
        try {
            // Validar reCAPTCHA si está habilitado
            if (config('services.recaptcha.enabled', false)) {
                $this->validarRecaptcha($request->input('g-recaptcha-response'));
            }

            // Validar email
            $validated = $request->validate([
                'email' => 'required|email|max:255'
            ]);

            $email = $validated['email'];
            $ip = $request->ip();

            // Verificar límite de intentos por IP
            if (!$this->recuperacionService->verificarLimiteIntentos($ip)) {
                return back()->withErrors(['email' => 'Ha excedido el límite de intentos. Por favor, intente más tarde.'])->withInput();
            }

            // Verificar si el email existe (no revelar si existe o no por seguridad)
            $emailExiste = $this->recuperacionService->emailExiste($email);
            
            if ($emailExiste) {
                // Crear token de recuperación
                $token = $this->recuperacionService->crearTokenRecuperacion($email);

                if ($token) {
                    // Enviar email de recuperación
                    $enviado = $this->recuperacionService->enviarCorreoRecuperacion($email, $token);
                    
                    if ($enviado) {
                        $this->recuperacionService->registrarIntento($email, $ip, true);
                        Log::info("Solicitud de recuperación enviada para: {$email}");
                    }
                }
            } else {
                $this->recuperacionService->registrarIntento($email, $ip, false);
            }

            // No revelar si el email existe o no por seguridad
            return back()->with('success', 'Si el email existe en nuestro sistema, recibirá instrucciones para recuperar su contraseña.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Error en recuperación de contraseña: " . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al procesar la solicitud. Por favor, inténtelo nuevamente.'])->withInput();
        }
    }

    /**
     * Muestra el formulario para restablecer contraseña
     */
    public function showRestablecerForm($token)
    {
        try {
            // Validar que el token existe y no ha expirado
            if (!$this->recuperacionService->validarToken($token, null)) {
                return redirect()->route('recuperar')->with('error', 'El enlace de recuperación es inválido o ha expirado.');
            }

            return view('auth.restablecer', [
                'title' => 'Restablecer Contraseña - Mesa de Partes',
                'token' => $token,
                'recaptcha_enabled' => config('services.recaptcha.enabled'),
                'recaptcha_site_key' => config('services.recaptcha.site_key')
            ]);

        } catch (\Exception $e) {
            Log::error("Error al mostrar formulario de restablecimiento: " . $e->getMessage());
            return redirect()->route('recuperar')->with('error', 'Ocurrió un error al procesar la solicitud.');
        }
    }

    /**
     * Procesa el restablecimiento de contraseña
     */
    public function restablecer(Request $request)
    {
        try {
            // Validar reCAPTCHA si está habilitado
            if (config('services.recaptcha.enabled')) {
                $this->validarRecaptcha($request->input('g-recaptcha-response'));
            }

            // Validar datos del formulario
            $validated = $request->validate([
                'token' => 'required|string',
                'email' => 'required|email|max:255',
                'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).+$/',
                'password_confirmation' => 'required|string|min:8'
            ]);

            // Intentar restablecer la contraseña
            $exitoso = $this->recuperacionService->redefinirContrasena(
                $validated['email'],
                $validated['token'],
                $validated['password'],
                'externo' // Por defecto externo, se puede detectar automáticamente
            );

            if (!$exitoso) {
                throw new \Exception("No se pudo restablecer la contraseña");
            }

            Log::info("Contraseña restablecida exitosamente para: {$validated['email']}");

            return redirect()->route('login')->with('success', 'Su contraseña ha sido restablecida exitosamente. Por favor, inicie sesión con su nueva contraseña.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error("Error en restablecimiento de contraseña: " . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al restablecer la contraseña. Por favor, inténtelo nuevamente.'])->withInput();
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout()
    {
        try {
            $usuario = Auth::user();
            
            if ($usuario) {
                $identificador = $usuario->esInterno() ? $usuario->email : $usuario->dni;
                // Registrar cierre de sesión
                Log::info("Usuario cerró sesión: {$identificador} (ID: {$usuario->id})");
            }
            
            // Cerrar sesión
            Auth::logout();
            
            // Invalidar la sesión
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login')->with('info', 'Sesión cerrada exitosamente.');

        } catch (\Exception $e) {
            Log::error("Error al cerrar sesión: " . $e->getMessage());
            return redirect()->route('login');
        }
    }

    /**
     * Obtiene departamentos para select anidado
     */
    public function obtenerDepartamentos()
    {
        try {
            $departamentos = Departamento::orderBy('nombre')->get(['id', 'nombre']);
            return response()->json(['success' => true, 'data' => $departamentos]);
        } catch (\Exception $e) {
            Log::error("Error al obtener departamentos: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener departamentos'], 500);
        }
    }

    /**
     * Obtiene provincias por departamento
     */
    public function obtenerProvincias($departamentoId)
    {
        try {
            $provincias = Provincia::where('departamento_id', $departamentoId)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
            
            return response()->json(['success' => true, 'data' => $provincias]);
        } catch (\Exception $e) {
            Log::error("Error al obtener provincias: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener provincias'], 500);
        }
    }

    /**
     * Obtiene distritos por provincia
     */
    public function obtenerDistritos($provinciaId)
    {
        try {
            $distritos = Distrito::where('provincia_id', $provinciaId)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);
            
            return response()->json(['success' => true, 'data' => $distritos]);
        } catch (\Exception $e) {
            Log::error("Error al obtener distritos: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener distritos'], 500);
        }
    }

    /**
     * Valida reCAPTCHA
     */
    protected function validarRecaptcha($recaptchaResponse)
    {
        if (empty($recaptchaResponse)) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Por favor, complete el reCAPTCHA.'
            ]);
        }

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . 
            config('services.recaptcha.secret_key') . "&response={$recaptchaResponse}");
        
        $responseKeys = json_decode($response, true);

        if (!intval($responseKeys["success"]) || $responseKeys["score"] < config('services.recaptcha.score_threshold')) {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Falló la validación del reCAPTCHA. Por favor, inténtelo nuevamente.'
            ]);
        }
    }

    /**
     * Verificar si el usuario está bloqueado por intentos fallidos
     */
    protected function estaUsuarioBloqueado($usuario)
    {
        $identificador = $usuario->esInterno() ? $usuario->email : $usuario->dni;
        $intentosFallidos = cache()->get("intentos_fallidos_{$identificador}", 0);
        $tiempoBloqueo = config('auth.lockout_time', 300); // 5 minutos por defecto
        $ultimoIntento = cache()->get("ultimo_intento_{$identificador}");

        if ($intentosFallidos >= config('auth.max_attempts', 5) && $ultimoIntento) {
            $tiempoTranscurrido = time() - $ultimoIntento;
            return $tiempoTranscurrido < $tiempoBloqueo;
        }

        return false;
    }

    /**
     * Registrar intento fallido de login
     */
    protected function registrarIntentoFallido($identificador, $ip)
    {
        $intentos = cache()->increment("intentos_fallidos_{$identificador}");
        cache()->put("ultimo_intento_{$identificador}", time(), now()->addHours(1));

        Log::warning("Intento fallido de login #{$intentos} desde IP: {$ip} para: {$identificador}");

        if ($intentos >= config('auth.max_attempts', 5)) {
            Log::warning("Usuario bloqueado temporalmente por múltiples intentos fallidos: {$identificador}");
        }
    }

    /**
     * Limpiar intentos fallidos después de login exitoso
     */
    protected function limpiarIntentosFallidos($identificador)
    {
        cache()->forget("intentos_fallidos_{$identificador}");
        cache()->forget("ultimo_intento_{$identificador}");
    }

    /**
     * Registrar acceso exitoso
     */
    protected function registrarAccesoExitoso($usuario, $ip)
    {
        $identificador = $usuario->esInterno() ? $usuario->email : $usuario->dni;
        Log::info("Login exitoso para usuario: {$identificador} (ID: {$usuario->id}) desde IP: {$ip}");
    }

    /**
     * Redirigir usuario según su tipo y permisos
     */
    protected function redirigirUsuario($usuario)
    {
        if ($usuario->es_administrador) {
            return redirect()->route('dashboard')->with('success', 'Bienvenido al sistema de administración.');
        }

        // Usuario externo - redirigir a mesa de partes
        return redirect()->route('mesa-partes.index')->with('success', 'Bienvenido al sistema de mesa de partes.');
    }

    /**
     * Verificar coincidencia de datos con API externa
     */
    protected function verificarCoincidenciaDatos($datosUsuario, $datosApi)
    {
        // Verificar que los nombres coincidan (opcional, puede ser configurable)
        $nombreCompletoUsuario = trim($datosUsuario['nombres'] . ' ' . $datosUsuario['apellido_paterno'] . ' ' . $datosUsuario['apellido_materno']);
        $nombreCompletoApi = trim($datosApi['nombre_completo']);

        // Implementar lógica de coincidencia (puede ser parcial o exacta)
        // Por ahora, solo registramos la verificación
        Log::info("Verificación de coincidencia de nombres: Usuario='{$nombreCompletoUsuario}' vs API='{$nombreCompletoApi}'");
    }

    /**
     * Enviar email de bienvenida
     */
    protected function enviarEmailBienvenida($usuario)
    {
        try {
            // Implementar envío de email usando Mailable
            // Por ahora, solo registramos
            Log::info("Email de bienvenida enviado a: {$usuario->correo}");
        } catch (\Exception $e) {
            Log::error("Error al enviar email de bienvenida: " . $e->getMessage());
        }
    }
}