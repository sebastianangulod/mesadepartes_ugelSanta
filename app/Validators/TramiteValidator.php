<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validador personalizado para trámites y documentos
 * 
 * Esta clase centraliza todas las validaciones específicas del sistema
 * de mesa de partes, incluyendo validaciones de documentos, usuarios,
 * y reglas de negocio complejas.
 * 
 * @package App\Validators
 */
class TramiteValidator
{
    /**
     * Valida los datos de un nuevo trámite
     * 
     * @param array $datos Datos del trámite a validar
     * @return array Datos validados
     * @throws ValidationException Si la validación falla
     */
    public static function validarNuevoTramite(array $datos): array
    {
        $reglas = [
            'procedimiento_id' => 'required|integer|exists:procedimientos,id',
            'dni' => 'required|string|size:8',
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:50',
            'apellido_materno' => 'required|string|max:50',
            'celular' => 'required|string|size:9',
            'correo' => 'required|email|max:100',
            'direccion' => 'required|string|max:200',
            'departamento_id' => 'required|integer|exists:departamentos,id',
            'provincia_id' => 'required|integer|exists:provincias,id',
            'distrito_id' => 'required|integer|exists:distritos,id',
            'asunto' => 'required|string|max:500',
            'descripcion' => 'required|string|max:2000',
            'documentos' => 'required|array|min:1',
            'documentos.*.tipo_documento_id' => 'required|integer|exists:tipo_documentos,id',
            'documentos.*.archivo' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            'modalidad' => 'required|in:presencial,virtual',
            'terminos_aceptados' => 'required|accepted'
        ];

        $mensajes = [
            'procedimiento_id.required' => 'Debe seleccionar un procedimiento',
            'dni.required' => 'El DNI es obligatorio',
            'dni.size' => 'El DNI debe tener 8 dígitos',
            'correo.email' => 'Debe ingresar un correo electrónico válido',
            'documentos.min' => 'Debe adjuntar al menos un documento',
            'documentos.*.archivo.max' => 'Cada documento no debe exceder 10MB',
            'documentos.*.archivo.mimes' => 'Los documentos deben ser PDF, Word o imágenes',
            'terminos_aceptados.accepted' => 'Debe aceptar los términos y condiciones'
        ];

        return Validator::make($datos, $reglas, $mensajes)->validate();
    }

    /**
     * Valida los datos de actualización de un trámite
     * 
     * @param array $datos Datos a actualizar
     * @return array Datos validados
     * @throws ValidationException Si la validación falla
     */
    public static function validarActualizacionTramite(array $datos): array
    {
        $reglas = [
            'asunto' => 'sometimes|string|max:500',
            'descripcion' => 'sometimes|string|max:2000',
            'estado' => 'sometimes|string|in:pendiente,en_proceso,observado,finalizado,anulado',
            'observaciones' => 'required_if:estado,observado|string|max:1000',
            'motivo_anulacion' => 'required_if:estado,anulado|string|max:500'
        ];

        $mensajes = [
            'estado.in' => 'Estado inválido. Estados permitidos: pendiente, en_proceso, observado, finalizado, anulado',
            'observaciones.required_if' => 'Las observaciones son obligatorias cuando el trámite es observado',
            'motivo_anulacion.required_if' => 'El motivo de anulación es obligatorio cuando se anula un trámite'
        ];

        return Validator::make($datos, $reglas, $mensajes)->validate();
    }

    /**
     * Valida documentos adjuntos
     * 
     * @param array $documentos Array de documentos a validar
     * @return array Documentos validados
     * @throws ValidationException Si la validación falla
     */
    public static function validarDocumentos(array $documentos): array
    {
        $reglas = [
            '*.tipo_documento_id' => 'required|integer|exists:tipo_documentos,id',
            '*.archivo' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
            '*.numero_folios' => 'required|integer|min:1|max:999',
            '*.descripcion' => 'nullable|string|max:200'
        ];

        return Validator::make($documentos, $reglas)->validate();
    }

    /**
     * Valida el formato y contenido del DNI
     * 
     * @param string $dni DNI a validar
     * @return bool True si es válido
     */
    public static function validarDNI(string $dni): bool
    {
        // Validar longitud
        if (strlen($dni) !== 8 || !ctype_digit($dni)) {
            return false;
        }

        // Validar que no sea una secuencia repetitiva
        if (preg_match('/^(\d)\1{7}$/', $dni)) {
            return false;
        }

        // Validar dígito verificador (método peruano)
        return self::validarDigitoVerificadorDNI($dni);
    }

    /**
     * Valida el dígito verificador del DNI peruano
     * 
     * @param string $dni DNI de 8 dígitos
     * @return bool True si el dígito verificador es correcto
     */
    private static function validarDigitoVerificadorDNI(string $dni): bool
    {
        // Implementar algoritmo de validación peruano
        // Por simplicidad, se asume válido si pasa las validaciones básicas
        return true;
    }

    /**
     * Valida el formato del número celular peruano
     * 
     * @param string $celular Número de celular
     * @return bool True si es válido
     */
    public static function validarCelularPeruano(string $celular): bool
    {
        // Validar formato peruano: 9 dígitos que comienzan con 9
        return preg_match('/^9\d{8}$/', $celular);
    }

    /**
     * Valida que el usuario tenga permisos para modificar el trámite
     * 
     * @param int $usuarioId ID del usuario
     * @param int $tramiteId ID del trámite
     * @return bool True si tiene permisos
     */
    public static function validarPermisoModificacion(int $usuarioId, int $tramiteId): bool
    {
        // Verificar si el usuario es administrador
        // Verificar si el usuario está asignado al trámite
        // Verificar el estado actual del trámite
        return true; // Implementar lógica específica
    }

    /**
     * Valida transiciones de estado de trámite
     * 
     * @param string $estadoActual Estado actual del trámite
     * @param string $nuevoEstado Nuevo estado solicitado
     * @return bool True si la transición es válida
     */
    public static function validarTransicionEstado(string $estadoActual, string $nuevoEstado): bool
    {
        $transicionesValidas = [
            'pendiente' => ['en_proceso', 'anulado'],
            'en_proceso' => ['observado', 'finalizado', 'anulado'],
            'observado' => ['en_proceso', 'anulado'],
            'finalizado' => [], // Estado final, no permite transiciones
            'anulado' => [] // Estado final, no permite transiciones
        ];

        return in_array($nuevoEstado, $transicionesValidas[$estadoActual] ?? []);
    }

    /**
     * Valida que el procedimiento esté activo y disponible
     * 
     * @param int $procedimientoId ID del procedimiento
     * @return bool True si está disponible
     */
    public static function validarProcedimientoActivo(int $procedimientoId): bool
    {
        // Verificar que el procedimiento exista y esté activo
        // Verificar que tenga usuarios asignados
        // Verificar restricciones de horario si aplica
        return true; // Implementar lógica específica
    }

    /**
     * Valida el tamaño y tipo de archivos en lote
     * 
     * @param array $archivos Archivos a validar
     * @param int $tamanoMaximo Tamaño máximo por archivo en KB
     * @return array Resultado de validación
     */
    public static function validarArchivosLote(array $archivos, int $tamanoMaximo = 10240): array
    {
        $resultado = ['valido' => true, 'errores' => []];
        $tamanoTotal = 0;

        foreach ($archivos as $indice => $archivo) {
            if (!isset($archivo['tmp_name']) || !file_exists($archivo['tmp_name'])) {
                $resultado['errores'][] = "Archivo {$indice}: No se pudo acceder al archivo";
                $resultado['valido'] = false;
                continue;
            }

            $tamanoArchivo = filesize($archivo['tmp_name']);
            $tamanoTotal += $tamanoArchivo;

            // Validar tamaño individual
            if ($tamanoArchivo > $tamanoMaximo * 1024) {
                $resultado['errores'][] = "Archivo {$indice}: Excede el tamaño máximo de " . ($tamanoMaximo / 1024) . "MB";
                $resultado['valido'] = false;
            }

            // Validar tipo MIME
            $tiposPermitidos = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            $tipoArchivo = mime_content_type($archivo['tmp_name']);
            
            if (!in_array($tipoArchivo, $tiposPermitidos)) {
                $resultado['errores'][] = "Archivo {$indice}: Tipo de archivo no permitido";
                $resultado['valido'] = false;
            }
        }

        // Validar tamaño total del lote (50MB máximo)
        if ($tamanoTotal > 50 * 1024 * 1024) {
            $resultado['errores'][] = "El tamaño total de los archivos excede los 50MB";
            $resultado['valido'] = false;
        }

        return $resultado;
    }

    /**
     * Valida que la fecha de expedición de un documento sea válida
     * 
     * @param string $fecha Fecha en formato Y-m-d
     * @param int $diasMaximos Días máximos de antigüedad permitidos
     * @return bool True si la fecha es válida
     */
    public static function validarFechaExpedicion(string $fecha, int $diasMaximos = 90): bool
    {
        $fechaExpedicion = \DateTime::createFromFormat('Y-m-d', $fecha);
        $fechaActual = new \DateTime();
        
        if (!$fechaExpedicion || $fechaExpedicion > $fechaActual) {
            return false;
        }

        $diferencia = $fechaActual->diff($fechaExpedicion);
        return $diferencia->days <= $diasMaximos;
    }

    /**
     * Valida que el número de folios sea coherente con el archivo
     * 
     * @param int $numeroFolios Número de folios declarados
     * @param string $rutaArchivo Ruta al archivo PDF
     * @return bool True si es coherente
     */
    public static function validarNumeroFolios(int $numeroFolios, string $rutaArchivo): bool
    {
        // Para archivos PDF, podría contar páginas
        // Por ahora, validar que sea un número razonable (1-999)
        return $numeroFolios >= 1 && $numeroFolios <= 999;
    }

    /**
     * Valida que no exista un trámite duplicado en proceso
     * 
     * @param array $datos Datos del trámite a verificar
     * @return bool True si no hay duplicados
     */
    public static function validarTramiteDuplicado(array $datos): bool
    {
        // Verificar si existe un trámite similar en proceso para la misma persona
        // Considerar: DNI, procedimiento, asunto, fecha reciente
        return true; // Implementar lógica específica
    }

    /**
     * Valida el horario de atención para trámites presenciales
     * 
     * @param \DateTime $fechaHora Fecha y hora solicitada
     * @return array Resultado con mensaje de validación
     */
    public static function validarHorarioAtencion(\DateTime $fechaHora): array
    {
        $diaSemana = (int)$fechaHora->format('N'); // 1 = Lunes, 7 = Domingo
        $hora = (int)$fechaHora->format('H');
        
        // Validar días hábiles (lunes a viernes)
        if ($diaSemana >= 6) {
            return ['valido' => false, 'mensaje' => 'Solo se atiende de lunes a viernes'];
        }
        
        // Validar horario de atención (8:00 - 16:00)
        if ($hora < 8 || $hora >= 16) {
            return ['valido' => false, 'mensaje' => 'El horario de atención es de 8:00 AM a 4:00 PM'];
        }
        
        return ['valido' => true, 'mensaje' => 'Horario válido'];
    }
}