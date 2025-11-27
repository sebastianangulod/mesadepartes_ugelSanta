<?php

namespace App\Validators;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Validador para datos de autenticación y usuarios
 * 
 * Este validador proporciona métodos para validar datos comunes
 * en procesos de autenticación como DNI, email, RUC, etc.
 * Incluye validaciones de formato, unicidad y consultas a
 * servicios externos cuando estén disponibles.
 * 
 * Funcionalidades principales:
 * - Validación de formato de DNI peruano
 * - Validación de formato de RUC peruano
 * - Validación de email con formato RFC
 * - Verificación de unicidad en base de datos
 * - Consulta a servicios externos (Reniec, SUNAT)
 * - Caché de resultados para optimizar
 * - Validación de contraseñas según políticas
 * 
 * @package App\Validators
 */
class AuthValidator
{
    /**
     * Validar formato de DNI peruano
     * 
     * @param string $dni
     * @return bool
     */
    public static function validarDni($dni)
    {
        // Validar longitud (8 dígitos)
        if (strlen($dni) !== 8) {
            return false;
        }

        // Validar que sean solo números
        if (!preg_match('/^[0-9]{8}$/', $dni)) {
            return false;
        }

        // Validar que no sea una secuencia común
        $secuenciasInvalidas = [
            '00000000', '11111111', '22222222', '33333333',
            '44444444', '55555555', '66666666', '77777777',
            '88888888', '99999999', '12345678', '87654321'
        ];

        if (in_array($dni, $secuenciasInvalidas)) {
            return false;
        }

        return true;
    }

    /**
     * Validar formato de RUC peruano
     * 
     * @param string $ruc
     * @return bool
     */
    public static function validarRuc($ruc)
    {
        // Validar longitud (11 dígitos)
        if (strlen($ruc) !== 11) {
            return false;
        }

        // Validar que sean solo números
        if (!preg_match('/^[0-9]{11}$/', $ruc)) {
            return false;
        }

        // Validar dígito verificador usando algoritmo módulo 11
        if (!self::validarDigitoVerificadorRuc($ruc)) {
            return false;
        }

        // Validar que no sea una secuencia común
        $secuenciasInvalidas = [
            '00000000000', '11111111111', '22222222222', '33333333333',
            '44444444444', '55555555555', '66666666666', '77777777777',
            '88888888888', '99999999999', '12345678901', '10987654321'
        ];

        if (in_array($ruc, $secuenciasInvalidas)) {
            return false;
        }

        return true;
    }

    /**
     * Validar dígito verificador de RUC usando módulo 11
     * 
     * @param string $ruc
     * @return bool
     */
    public static function validarDigitoVerificadorRuc($ruc)
    {
        $digitos = str_split($ruc);
        $ultimoDigito = array_pop($digitos);
        
        $suma = 0;
        $pesos = [3, 2, 7, 6, 5, 4, 3, 2]; // Pesos para los primeros 8 dígitos
        
        // Calcular suma ponderada para los primeros 8 dígitos
        for ($i = 0; $i < 8; $i++) {
            $suma += $digitos[$i] * $pesos[$i];
        }
        
        // Calcular dígito verificador esperado
        $residuo = $suma % 11;
        $digitoVerificador = 11 - $residuo;
        
        // Si el residuo es 0 o 1, el dígito verificador es 0
        if ($residuo === 0 || $residuo === 1) {
            $digitoVerificador = 0;
        }
        
        return $digitoVerificador == $ultimoDigito;
    }

    /**
     * Validar formato de email según RFC 5322
     * 
     * @param string $email
     * @return bool
     */
    public static function validarEmail($email)
    {
        // Validar longitud total (máximo 254 caracteres)
        if (strlen($email) > 254) {
            return false;
        }

        // Validar formato básico con filter_var
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Validar partes del email
        list($local, $domain) = explode('@', $email);

        // Validar longitud de la parte local (máximo 64 caracteres)
        if (strlen($local) > 64) {
            return false;
        }

        // Validar longitud del dominio (máximo 255 caracteres)
        if (strlen($domain) > 255) {
            return false;
        }

        // Validar caracteres permitidos en la parte local
        if (!preg_match('/^[a-zA-Z0-9._%+-]+$/', $local)) {
            return false;
        }

        // Validar que el dominio tenga al menos un punto
        if (substr_count($domain, '.') < 1) {
            return false;
        }

        // Validar que no termine con punto
        if (substr($domain, -1) === '.') {
            return false;
        }

        return true;
    }

    /**
     * Verificar si un DNI ya existe en la base de datos
     * 
     * @param string $dni
     * @param int|null $excludeId
     * @param string $tipoUsuario (externo|interno)
     * @return bool
     */
    public static function dniExiste($dni, $excludeId = null, $tipoUsuario = 'externo')
    {
        $tabla = $tipoUsuario === 'externo' ? 'usuarios' : 'usuarios_internos';
        
        $query = DB::table($tabla)->where('dni', $dni);
        
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Verificar si un email ya existe en la base de datos
     * 
     * @param string $email
     * @param int|null $excludeId
     * @param string $tipoUsuario (externo|interno)
     * @return bool
     */
    public static function emailExiste($email, $excludeId = null, $tipoUsuario = 'externo')
    {
        $tabla = $tipoUsuario === 'externo' ? 'usuarios' : 'usuarios_internos';
        
        $query = DB::table($tabla)->where('email', $email);
        
        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Validar contraseña según políticas de seguridad
     * 
     * @param string $password
     * @return array
     */
    public static function validarContrasena($password)
    {
        $resultado = [
            'valida' => true,
            'errores' => []
        ];

        // Longitud mínima
        if (strlen($password) < 8) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'Debe tener al menos 8 caracteres.';
        }

        // Máxima longitud
        if (strlen($password) > 128) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'No puede exceder 128 caracteres.';
        }

        // Al menos una letra mayúscula
        if (!preg_match('/[A-Z]/', $password)) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'Debe contener al menos una letra mayúscula.';
        }

        // Al menos una letra minúscula
        if (!preg_match('/[a-z]/', $password)) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'Debe contener al menos una letra minúscula.';
        }

        // Al menos un número
        if (!preg_match('/[0-9]/', $password)) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'Debe contener al menos un número.';
        }

        // No debe ser una contraseña común
        $contrasenasComunes = [
            'password', '12345678', '123456789', '1234567890',
            'qwerty', 'qwerty123', 'abc123', 'password123',
            'admin123', 'user123', '123123123', '11111111',
            '00000000', 'aaaaaaaa', 'password1'
        ];

        if (in_array(strtolower($password), $contrasenasComunes)) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'No puede ser una contraseña común.';
        }

        // No debe contener espacios
        if (preg_match('/\\s/', $password)) {
            $resultado['valida'] = false;
            $resultado['errores'][] = 'No puede contener espacios.';
        }

        return $resultado;
    }

    /**
     * Consultar DNI en servicio externo (Reniec)
     * 
     * @param string $dni
     * @return array|null
     */
    public static function consultarReniec($dni)
    {
        // Verificar caché primero
        $cacheKey = "reniec_dni_{$dni}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Implementar consulta a API de Reniec (si está configurada)
            // Esto es un ejemplo, la implementación real dependerá del proveedor
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.reniec.token'),
                'Accept' => 'application/json',
            ])->get(config('services.reniec.url') . '/dni/' . $dni);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cachear resultado por 24 horas
                Cache::put($cacheKey, $data, now()->addHours(24));
                
                return $data;
            }
        } catch (\Exception $e) {
            // Loggear error pero no fallar el proceso
            \Log::warning("Error consultando Reniec para DNI {$dni}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Consultar RUC en servicio externo (SUNAT)
     * 
     * @param string $ruc
     * @return array|null
     */
    public static function consultarSunat($ruc)
    {
        // Verificar caché primero
        $cacheKey = "sunat_ruc_{$ruc}";
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Implementar consulta a API de SUNAT (si está configurada)
            // Esto es un ejemplo, la implementación real dependerá del proveedor
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.sunat.token'),
                'Accept' => 'application/json',
            ])->get(config('services.sunat.url') . '/ruc/' . $ruc);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cachear resultado por 24 horas
                Cache::put($cacheKey, $data, now()->addHours(24));
                
                return $data;
            }
        } catch (\Exception $e) {
            // Loggear error pero no fallar el proceso
            \Log::warning("Error consultando SUNAT para RUC {$ruc}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Validar si un número de teléfono peruano es válido
     * 
     * @param string $telefono
     * @return bool
     */
    public static function validarTelefonoPeruano($telefono)
    {
        // Validar longitud (9 dígitos)
        if (strlen($telefono) !== 9) {
            return false;
        }

        // Validar que sean solo números
        if (!preg_match('/^[0-9]{9}$/', $telefono)) {
            return false;
        }

        // Validar prefijos válidos en Perú
        $prefijosValidos = [
            '9', // Móviles
            '6', '7', // Teléfonos fijos Lima
            '1', '2', '3', '4', '5', '8' // Teléfonos fijos provincias
        ];

        $prefijo = substr($telefono, 0, 1);
        if (!in_array($prefijo, $prefijosValidos)) {
            return false;
        }

        return true;
    }

    /**
     * Validar si un usuario está activo
     * 
     * @param string $email
     * @param string $tipoUsuario (externo|interno)
     * @return bool
     */
    public static function usuarioEstaActivo($email, $tipoUsuario = 'externo')
    {
        $tabla = $tipoUsuario === 'externo' ? 'usuarios' : 'usuarios_internos';
        
        return DB::table($tabla)
            ->where('email', $email)
            ->where('estado', 'activo')
            ->exists();
    }
}