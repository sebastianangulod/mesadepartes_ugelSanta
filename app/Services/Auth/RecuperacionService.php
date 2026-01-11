<?php

namespace App\Services\Auth;

use App\Models\Usuario;
use App\Models\ResetPasswordRequest;
use App\Mail\RecuperarPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Servicio para la gestión de recuperación de contraseñas
 * 
 * Este servicio maneja el proceso completo de recuperación de contraseñas
 * incluyendo la generación de tokens, envío de correos, validación de tokens
 * y actualización de contraseñas. Proporciona una capa de abstracción
 * sobre el sistema de recuperación de Laravel.
 * 
 * Funcionalidades principales:
 * - Generación de tokens seguros de recuperación
 * - Gestión de expiración de tokens
 * - Envío de correos de recuperación
 * - Validación de tokens
 * - Actualización de contraseñas
 * - Limpieza de tokens expirados
 * - Auditoría de intentos de recuperación
 * 
 * @package App\Services\Auth
 */
class RecuperacionService
{
    /**
     * Tiempo de expiración del token en minutos
     * 
     * @var int
     */
    protected $tiempoExpiracion;

    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        $this->tiempoExpiracion = config('auth.passwords.users.expire', 60);
    }

    /**
     * Crear token de recuperación para email
     * 
     * @param string $email
     * @param string $tipoUsuario (externo|interno)
     * @return string|null
     */
    public function crearTokenRecuperacion($email, $tipoUsuario = 'externo')
    {
        try {
            $usuario = $this->obtenerUsuarioPorEmail($email, $tipoUsuario);
            
            if (!$usuario) {
                return null;
            }

            // Eliminar tokens anteriores para el mismo usuario
            ResetPasswordRequest::where('user_id', $usuario->id)->delete();

            // Generar token único
            $token = $this->generarToken();

            // Guardar token en la base de datos
            ResetPasswordRequest::create([
                'user_id' => $usuario->id,
                'token' => $token,
                'created_at' => now(),
            ]);

            return $token;
        } catch (\Exception $e) {
            Log::error("Error al crear token de recuperación: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Validar token de recuperación
     * 
     * @param string $token
     * @param string|null $email
     * @return bool
     */
    public function validarToken($token, $email = null)
    {
        try {
            $query = ResetPasswordRequest::where('token', $token);
            
            if ($email) {
                $usuario = Usuario::where('email', $email)->first();
                if ($usuario) {
                    $query->where('user_id', $usuario->id);
                } else {
                    return false;
                }
            }
            
            $resetRequest = $query->first();
            
            if (!$resetRequest) {
                return false;
            }

            // Verificar que no haya expirado
            if ($resetRequest->hasExpired()) {
                $resetRequest->delete();
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error al validar token: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Redefinir contraseña usando token
     * 
     * @param string $email
     * @param string $token
     * @param string $nuevaContrasena
     * @param string $tipoUsuario (externo|interno)
     * @return bool
     */
    public function redefinirContrasena($email, $token, $nuevaContrasena, $tipoUsuario = 'externo')
    {
        try {
            // Validar token
            if (!$this->validarToken($token, $email)) {
                return false;
            }

            // Obtener usuario
            $usuario = $this->obtenerUsuarioPorEmail($email, $tipoUsuario);
            
            if (!$usuario) {
                return false;
            }

            // Actualizar contraseña
            if (!$this->actualizarContrasena($usuario, $nuevaContrasena)) {
                return false;
            }

            // Invalidar token
            $resetRequest = ResetPasswordRequest::where('token', $token)
                ->where('user_id', $usuario->id)
                ->first();
            
            if ($resetRequest) {
                $this->invalidarToken($token, $email);
            }

            Log::info("Contraseña redefinida exitosamente para: {$email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error al redefinir contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar correo de recuperación
     * 
     * @param string $email
     * @param string $token
     * @return bool
     */
    public function enviarCorreoRecuperacion($email, $token)
    {
        try {
            $usuario = Usuario::where('email', $email)->first();
            
            if (!$usuario) {
                return false;
            }

            $resetUrl = route('password.reset', ['token' => $token, 'email' => $email]);
            
            Mail::to($email)->send(new RecuperarPassword($usuario, $token, $resetUrl));
            
            Log::info("Correo de recuperación enviado a: {$email}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de recuperación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar token seguro
     * 
     * @return string
     */
    protected function generarToken()
    {
        // Generar token de 64 caracteres
        return hash('sha256', Str::random(40) . time());
    }

    /**
     * Obtener usuario por email y tipo
     * 
     * @param string $email
     * @param string $tipoUsuario (externo|interno)
     * @return \App\Models\Usuario|null
     */
    protected function obtenerUsuarioPorEmail($email, $tipoUsuario = 'externo')
    {
        $tipoUser = $tipoUsuario === 'externo' ? 1 : 2;
        
        return Usuario::where('email', $email)
            ->where('tipo_user', $tipoUser)
            ->first();
    }

    /**
     * Actualizar contraseña de usuario
     * 
     * @param \App\Models\Usuario $usuario
     * @param string $nuevaContrasena
     * @return bool
     */
    protected function actualizarContrasena($usuario, $nuevaContrasena)
    {
        try {
            $usuario->passw = Hash::make($nuevaContrasena);
            return $usuario->save();
        } catch (\Exception $e) {
            Log::error("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invalidar token después de uso
     * 
     * @param string $token
     * @param string $email
     * @return void
     */
    protected function invalidarToken($token, $email)
    {
        try {
            $usuario = Usuario::where('email', $email)->first();
            
            if ($usuario) {
                ResetPasswordRequest::where('token', $token)
                    ->where('user_id', $usuario->id)
                    ->delete();
            }
        } catch (\Exception $e) {
            Log::error("Error al invalidar token: " . $e->getMessage());
        }
    }

    /**
     * Limpiar tokens expirados
     * 
     * @return int
     */
    public function limpiarTokensExpirados()
    {
        try {
            $fechaLimite = now()->subMinutes($this->tiempoExpiracion);
            
            $eliminados = ResetPasswordRequest::where('created_at', '<', $fechaLimite)->delete();
            
            Log::info("Tokens expirados eliminados: {$eliminados}");
            return $eliminados;
        } catch (\Exception $e) {
            Log::error("Error al limpiar tokens expirados: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Verificar si email existe en el sistema
     * 
     * @param string $email
     * @param string $tipoUsuario (externo|interno)
     * @return bool
     */
    public function emailExiste($email, $tipoUsuario = 'externo')
    {
        return $this->obtenerUsuarioPorEmail($email, $tipoUsuario) !== null;
    }

    /**
     * Obtener tiempo restante de validez del token
     * 
     * @param string $token
     * @param string $email
     * @return int|null
     */
    public function obtenerTiempoRestanteToken($token, $email)
    {
        try {
            $usuario = Usuario::where('email', $email)->first();
            
            if (!$usuario) {
                return null;
            }

            $resetRequest = ResetPasswordRequest::where('token', $token)
                ->where('user_id', $usuario->id)
                ->first();
            
            if (!$resetRequest) {
                return null;
            }

            $minutosTranscurridos = $resetRequest->created_at->diffInMinutes(now());
            $minutosRestantes = $this->tiempoExpiracion - $minutosTranscurridos;
            
            return max(0, $minutosRestantes);
        } catch (\Exception $e) {
            Log::error("Error al obtener tiempo restante del token: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar intento de recuperación
     * 
     * @param string $email
     * @param string $ip
     * @param bool $exitoso
     * @return void
     */
    public function registrarIntento($email, $ip, $exitoso = false)
    {
        $cacheKey = "password_reset_attempt_{$ip}";
        $intentos = Cache::get($cacheKey, 0);
        
        if ($exitoso) {
            Cache::forget($cacheKey);
        } else {
            Cache::put($cacheKey, $intentos + 1, now()->addHours(1));
        }
        
        Log::info("Intento de recuperación de contraseña", [
            'email' => $email,
            'ip' => $ip,
            'exitoso' => $exitoso,
            'intentos' => $intentos + 1
        ]);
    }

    /**
     * Verificar límite de intentos por IP
     * 
     * @param string $ip
     * @return bool
     */
    public function verificarLimiteIntentos($ip)
    {
        $cacheKey = "password_reset_attempt_{$ip}";
        $intentos = Cache::get($cacheKey, 0);
        $limite = config('auth.password_reset.max_attempts', 5);
        
        return $intentos < $limite;
    }

    /**
     * Crear token de recuperación para DNI (como en PHP puro)
     * 
     * @param string $dni
     * @return string|null
     */
    public function crearTokenRecuperacionPorDni($dni)
    {
        try {
            $usuario = Usuario::where('dni', $dni)->first();
            
            if (!$usuario) {
                return null;
            }

            // Eliminar tokens anteriores para el mismo usuario
            ResetPasswordRequest::where('user_id', $usuario->id)->delete();

            // Generar token único
            $token = $this->generarToken();

            // Guardar token en la base de datos
            ResetPasswordRequest::create([
                'user_id' => $usuario->id,
                'token' => $token,
                'created_at' => now(),
            ]);

            return $token;
        } catch (\Exception $e) {
            Log::error("Error al crear token de recuperación por DNI: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener usuario por DNI
     * 
     * @param string $dni
     * @return \App\Models\Usuario|null
     */
    public function obtenerUsuarioPorDni($dni)
    {
        return Usuario::where('dni', $dni)->first();
    }

    /**
     * Verificar si DNI existe en el sistema
     * 
     * @param string $dni
     * @return bool
     */
    public function dniExiste($dni)
    {
        return $this->obtenerUsuarioPorDni($dni) !== null;
    }

    /**
     * Enviar correo de recuperación usando DNI
     * 
     * @param string $dni
     * @param string $token
     * @return bool
     */
    public function enviarCorreoRecuperacionPorDni($dni, $token)
    {
        try {
            $usuario = Usuario::where('dni', $dni)->first();
            
            if (!$usuario || !$usuario->email) {
                Log::warning("No se puede enviar correo de recuperación: Usuario no encontrado o sin email (DNI: {$dni})");
                return false;
            }

            // El Mailable RecuperarPassword genera la URL internamente
            // Ahora se envía inmediatamente (sin cola)
            Mail::to($usuario->email)->send(new RecuperarPassword($usuario, $token));
            
            Log::info("Correo de recuperación enviado exitosamente a: {$usuario->email} (DNI: {$dni})");
            return true;
        } catch (\Exception $e) {
            Log::error("Error al enviar correo de recuperación por DNI", [
                'dni' => $dni,
                'email' => $usuario->email ?? 'no disponible',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Redefinir contraseña usando DNI y token
     * 
     * @param string $dni
     * @param string $token
     * @param string $nuevaContrasena
     * @return bool
     */
    public function redefinirContrasenaPorDni($dni, $token, $nuevaContrasena)
    {
        try {
            $usuario = Usuario::where('dni', $dni)->first();
            
            if (!$usuario) {
                return false;
            }

            // Validar token
            $resetRequest = ResetPasswordRequest::where('token', $token)
                ->where('user_id', $usuario->id)
                ->first();
            
            if (!$resetRequest) {
                return false;
            }

            // Verificar que el token no haya expirado
            $minutosTranscurridos = $resetRequest->created_at->diffInMinutes(now());
            if ($minutosTranscurridos > $this->tiempoExpiracion) {
                return false;
            }

            // Actualizar contraseña
            if (!$this->actualizarContrasena($usuario, $nuevaContrasena)) {
                return false;
            }

            // Invalidar token
            $resetRequest->delete();

            Log::info("Contraseña redefinida exitosamente para DNI: {$dni}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error al redefinir contraseña por DNI: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validar token usando DNI
     * 
     * @param string $token
     * @param string $dni
     * @return bool
     */
    public function validarTokenPorDni($token, $dni)
    {
        try {
            $usuario = Usuario::where('dni', $dni)->first();
            
            if (!$usuario) {
                return false;
            }

            $resetRequest = ResetPasswordRequest::where('token', $token)
                ->where('user_id', $usuario->id)
                ->first();
            
            if (!$resetRequest) {
                return false;
            }

            // Verificar que el token no haya expirado
            $minutosTranscurridos = $resetRequest->created_at->diffInMinutes(now());
            return $minutosTranscurridos <= $this->tiempoExpiracion;
        } catch (\Exception $e) {
            Log::error("Error al validar token por DNI: " . $e->getMessage());
            return false;
        }
    }
}