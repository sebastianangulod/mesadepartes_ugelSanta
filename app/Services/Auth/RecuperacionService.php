<?php

namespace App\Services\Auth;

use App\Models\Usuario;
use App\Models\ResetPasswordRequest;
use App\Mail\RecuperarPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

}