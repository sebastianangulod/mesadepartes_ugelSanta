<?php

/**
 * Funciones auxiliares para el sistema de mesa de partes
 * 
 * Este archivo contiene funciones globales útiles para:
 * - Formateo de datos
 * - Validaciones comunes
 * - Utilidades de fecha y hora
 * - Funciones de texto y strings
 * - Utilidades de archivos y documentos
 * - Funciones de estado y validación de trámites
 */

if (!function_exists('formatearDocumento')) {
    /**
     * Formatea el número de documento según su tipo
     * 
     * @param string $documento Número de documento
     * @param string $tipo Tipo de documento (dni, ruc, etc.)
     * @return string Documento formateado
     */
    function formatearDocumento(string $documento, string $tipo): string
    {
        switch (strtolower($tipo)) {
            case 'dni':
                return substr($documento, 0, 2) . '-' . substr($documento, 2, 3) . '-' . substr($documento, 5, 3);
            case 'ruc':
                return substr($documento, 0, 2) . '-' . substr($documento, 2, 3) . '-' . substr($documento, 5, 3) . '-' . substr($documento, 8, 1);
            default:
                return $documento;
        }
    }
}

if (!function_exists('validarDocumento')) {
    /**
     * Valida el formato del documento según su tipo
     * 
     * @param string $documento Número de documento
     * @param string $tipo Tipo de documento
     * @return bool True si es válido, false en caso contrario
     */
    function validarDocumento(string $documento, string $tipo): bool
    {
        switch (strtolower($tipo)) {
            case 'dni':
                return preg_match('/^\\d{8}$/', $documento);
            case 'ruc':
                return preg_match('/^\\d{11}$/', $documento) && validarRUC($documento);
            case 'carnet_extranjeria':
            case 'pasaporte':
                return preg_match('/^[A-Z0-9]{6,12}$/', strtoupper($documento));
            default:
                return false;
        }
    }
}

if (!function_exists('validarRUC')) {
    /**
     * Valida el dígito verificador del RUC
     * 
     * @param string $ruc Número de RUC
     * @return bool True si es válido, false en caso contrario
     */
    function validarRUC(string $ruc): bool
    {
        if (strlen($ruc) !== 11) {
            return false;
        }
        
        // Validar dígito verificador según algoritmo peruano
        $factor = '5432765432';
        $suma = 0;
        
        for ($i = 0; $i < 10; $i++) {
            $suma += intval($ruc[$i]) * intval($factor[$i]);
        }
        
        $residuo = $suma % 11;
        $verificador = 11 - $residuo;
        
        if ($verificador == 10) {
            $verificador = 0;
        } elseif ($verificador == 11) {
            $verificador = 1;
        }
        
        return $verificador === intval($ruc[10]);
    }
}

if (!function_exists('formatearFechaHora')) {
    /**
     * Formatea fecha y hora según el formato peruano
     * 
     * @param string|DateTime $fecha Fecha a formatear
     * @param bool $incluirHora Si debe incluir la hora
     * @return string Fecha formateada
     */
    function formatearFechaHora($fecha, bool $incluirHora = true): string
    {
        if (!$fecha) {
            return '';
        }
        
        $carbon = is_string($fecha) ? Carbon::parse($fecha) : $fecha;
        
        if ($incluirHora) {
            return $carbon->format('d/m/Y H:i:s');
        }
        
        return $carbon->format('d/m/Y');
    }
}

if (!function_exists('calcularDiasHabiles')) {
    /**
     * Calcula días hábiles entre dos fechas (excluye sábados y domingos)
     * 
     * @param string|DateTime $fechaInicio Fecha de inicio
     * @param string|DateTime $fechaFin Fecha de fin
     * @return int Número de días hábiles
     */
    function calcularDiasHabiles($fechaInicio, $fechaFin): int
    {
        $inicio = is_string($fechaInicio) ? Carbon::parse($fechaInicio) : $fechaInicio;
        $fin = is_string($fechaFin) ? Carbon::parse($fechaFin) : $fechaFin;
        
        $diasHabiles = 0;
        $fechaActual = $inicio->copy();
        
        while ($fechaActual <= $fin) {
            // Verificar que no sea sábado (6) ni domingo (0)
            if (!in_array($fechaActual->dayOfWeek, [0, 6])) {
                $diasHabiles++;
            }
            $fechaActual->addDay();
        }
        
        return $diasHabiles;
    }
}

if (!function_exists('obtenerEstadoTramite')) {
    /**
     * Obtiene el estado de un trámite con formato legible
     * 
     * @param string $estado Código de estado
     * @return array Información del estado [nombre, clase_css, icono]
     */
    function obtenerEstadoTramite(string $estado): array
    {
        $estados = [
            'pendiente' => [
                'nombre' => 'Pendiente',
                'clase_css' => 'badge-warning',
                'icono' => 'fa-clock',
                'color' => '#ffc107'
            ],
            'en_proceso' => [
                'nombre' => 'En Proceso',
                'clase_css' => 'badge-info',
                'icono' => 'fa-spinner',
                'color' => '#17a2b8'
            ],
            'atendido' => [
                'nombre' => 'Atendido',
                'clase_css' => 'badge-success',
                'icono' => 'fa-check',
                'color' => '#28a745'
            ],
            'rechazado' => [
                'nombre' => 'Rechazado',
                'clase_css' => 'badge-danger',
                'icono' => 'fa-times',
                'color' => '#dc3545'
            ],
            'subsanado' => [
                'nombre' => 'Subsanado',
                'clase_css' => 'badge-primary',
                'icono' => 'fa-redo',
                'color' => '#007bff'
            ],
            'observado' => [
                'nombre' => 'Observado',
                'clase_css' => 'badge-secondary',
                'icono' => 'fa-exclamation',
                'color' => '#6c757d'
            ]
        ];
        
        return $estados[$estado] ?? [
            'nombre' => 'Desconocido',
            'clase_css' => 'badge-dark',
            'icono' => 'fa-question',
            'color' => '#343a40'
        ];
    }
}

if (!function_exists('generarCodigoTramite')) {
    /**
     * Genera un código único para el trámite
     * 
     * @param int $procedimientoId ID del procedimiento
     * @param int $año Año del trámite
     * @return string Código único del trámite
     */
    function generarCodigoTramite(int $procedimientoId, int $año): string
    {
        $procedimiento = "\\App\\Models\\Procedimiento::find($procedimientoId)";
        $siglas = $procedimiento ? strtoupper(substr($procedimiento->codigo, 0, 3)) : 'TRM';
        
        //Obtener el último número de trámite del año
        $ultimoTramite = Tramite::whereYear('created_at', $año)
            ->where('procedimiento_id', $procedimientoId)
            ->orderBy('numero_correlativo', 'desc')
            ->first();
        
        $numero = $ultimoTramite ? $ultimoTramite->numero_correlativo + 1 : 1;
        
        return sprintf('%s-%04d-%d', $siglas, $numero, $año);
    }
}

if (!function_exists('validarExtensionArchivo')) {
    /**
     * Valida la extensión de un archivo según los tipos permitidos
     * 
     * @param string $nombreArchivo Nombre del archivo
     * @param array $extensionesPermitidas Extensiones permitidas
     * @return bool True si es válido, false en caso contrario
     */
    function validarExtensionArchivo(string $nombreArchivo, array $extensionesPermitidas): bool
    {
        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
        return in_array($extension, array_map('strtolower', $extensionesPermitidas));
    }
}

if (!function_exists('obtenerTamanoArchivoFormateado')) {
    /**
     * Obtiene el tamaño de archivo formateado en unidades legibles
     * 
     * @param int $bytes Tamaño en bytes
     * @return string Tamaño formateado (MB, KB, etc.)
     */
    function obtenerTamanoArchivoFormateado(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unidadActual = 0;
        
        while ($bytes >= 1024 && $unidadActual < count($unidades) - 1) {
            $bytes /= 1024;
            $unidadActual++;
        }
        
        return round($bytes, 2) . ' ' . $unidades[$unidadActual];
    }
}

if (!function_exists('sanitizarTexto')) {
    /**
     * Sanitiza texto para evitar inyección de código y normalizar
     * 
     * @param string $texto Texto a sanitizar
     * @return string Texto sanitizado
     */
    function sanitizarTexto(string $texto): string
    {
        // Eliminar espacios múltiples y saltos de línea extra
        $texto = preg_replace('/\\s+/', ' ', trim($texto));
        
        // Convertir caracteres especiales a entidades HTML
        $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        
        // Eliminar caracteres de control
        $texto = preg_replace('/[\\x00-\\x1F\\x7F]/', '', $texto);
        
        return $texto;
    }
}

if (!function_exists('obtenerIniciales')) {
    /**
     * Obtiene las iniciales de un nombre completo
     * 
     * @param string $nombre Nombre completo
     * @return string Iniciales
     */
    function obtenerIniciales(string $nombre): string
    {
        $palabras = explode(' ', trim($nombre));
        $iniciales = '';
        
        foreach ($palabras as $palabra) {
            if (!empty($palabra)) {
                $iniciales .= strtoupper(substr($palabra, 0, 1));
            }
        }
        
        return $iniciales;
    }
}

if (!function_exists('esDiaHabil')) {
    /**
     * Verifica si una fecha es día hábil (no sábado, domingo o feriado)
     * 
     * @param string|DateTime $fecha Fecha a verificar
     * @return bool True si es día hábil, false en caso contrario
     */
    function esDiaHabil($fecha): bool
    {
        $carbon = is_string($fecha) ? Carbon::parse($fecha) : $fecha;
        
        // Verificar que no sea sábado (6) ni domingo (0)
        if (in_array($carbon->dayOfWeek, [0, 6])) {
            return false;
        }
        
        // Aquí se podría agregar validación de feriados
        // Consultando una tabla de feriados o API externa
        
        return true;
    }
}

if (!function_exists('calcularFechaVencimiento')) {
    /**
     * Calcula la fecha de vencimiento sumando días hábiles
     * 
     * @param string|DateTime $fechaInicio Fecha de inicio
     * @param int $diasHabiles Días hábiles a sumar
     * @return \\Carbon\\Carbon Fecha de vencimiento
     */
    function calcularFechaVencimiento($fechaInicio, int $diasHabiles): Carbon
    {
        $fecha = is_string($fechaInicio) ? Carbon::parse($fechaInicio) : $fechaInicio->copy();
        $diasSumados = 0;
        
        while ($diasSumados < $diasHabiles) {
            $fecha->addDay();
            if (esDiaHabil($fecha)) {
                $diasSumados++;
            }
        }
        
        return $fecha;
    }
}