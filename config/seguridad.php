<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad para Archivos
    |--------------------------------------------------------------------------
    |
    | Esta configuración define los parámetros de seguridad para el manejo
    | de archivos adjuntos en el sistema de trámites.
    |
    */

    'archivos' => [
        // Tamaño máximo de archivo en kilobytes (25MB = 25600KB)
        'max_size' => env('ARCHIVO_MAX_SIZE', 25600),
        
        // Tipos de archivo permitidos
        'tipos_permitidos' => [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ],
        
        // Directorio de almacenamiento
        'directorio' => env('ARCHIVO_DIRECTORIO', 'tramites_adjuntos'),
        
        // Nombre de archivo seguro
        'nombre_seguro' => true,
        
        // Prefijo para nombres de archivo
        'prefijo' => 'tramite_',
        
        // Subdirectorios por fecha
        'subdirectorios_fecha' => true,
        
        // Encriptar nombres de archivo
        'encriptar_nombre' => env('ARCHIVO_ENCRIPTAR_NOMBRE', false),
        
        // Validación de MIME type
        'validar_mime' => true,
        
        // Validación de extensión
        'validar_extension' => true,
        
        // Escaneo de virus (requiere configuración adicional)
        'escaneo_virus' => env('ARCHIVO_ESCANEO_VIRUS', false),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad General
    |--------------------------------------------------------------------------
    |
    | Parámetros generales de seguridad del sistema.
    |
    */
    
    'seguridad' => [
        // Tiempo de expiración de sesión en minutos
        'sesion_expiracion' => env('SESION_EXPIRACION', 120),
        
        // Intentos máximos de login fallidos
        'intentos_login' => env('INTENTOS_LOGIN', 5),
        
        // Tiempo de bloqueo por intentos fallidos (minutos)
        'tiempo_bloqueo' => env('TIEMPO_BLOQUEO', 15),
        
        // Longitud mínima de contraseña
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
        
        // Requerir mayúsculas en contraseña
        'password_mayusculas' => env('PASSWORD_MAYUSCULAS', true),
        
        // Requerir números en contraseña
        'password_numeros' => env('PASSWORD_NUMEROS', true),
        
        // Requerir caracteres especiales en contraseña
        'password_especiales' => env('PASSWORD_ESPECIALES', true),
        
        // Habilitar auditoría de acciones
        'auditoria_habilitada' => env('AUDITORIA_HABILITADA', true),
        
        // IP confiables (separadas por comas)
        'ips_confiables' => env('IPS_CONFIABLES', ''),
        
        // Habilitar rate limiting
        'rate_limiting' => env('RATE_LIMITING', true),
        
        // Límite de requests por minuto
        'rate_limit_requests' => env('RATE_LIMIT_REQUESTS', 60),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    |
    | Parámetros para validaciones del sistema.
    |
    */
    
    'validacion' => [
        // Longitud máxima de campos de texto
        'texto_max_length' => 1000,
        
        // Longitud máxima de campos de nombre
        'nombre_max_length' => 200,
        
        // Longitud máxima de campos de dirección
        'direccion_max_length' => 300,
        
        // Longitud máxima de campos de teléfono
        'telefono_max_length' => 20,
        
        // Longitud máxima de campos de correo
        'correo_max_length' => 100,
        
        // Longitud máxima de número de documento
        'documento_max_length' => 50,
        
        // Longitud máxima de número de expediente
        'expediente_max_length' => 50,
    ],
];