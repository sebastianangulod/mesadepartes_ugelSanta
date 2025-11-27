<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

/**
 * Clase auxiliar para el manejo de archivos y documentos
 * 
 * Proporciona métodos para:
 * - Subida de archivos con validación
 * - Organización de archivos por trámite
 * - Generación de nombres únicos
 * - Validación de tipos y tamaños
 * - Gestión de carpetas y estructura
 * 
 * @package App\Helpers
 */
class FileHelper
{
    /**
     * @var array Extensiones de archivo permitidas por defecto
     */
    protected $extensionesPermitidas = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'rar'
    ];

    /**
     * @var int Tamaño máximo de archivo por defecto (10MB)
     */
    protected $tamanoMaximo = 10 * 1024 * 1024; // 10MB

    /**
     * Sube un archivo al sistema con validaciones
     * 
     * @param UploadedFile $archivo Archivo a subir
     * @param string $carpeta Carpeta de destino
     * @param array $opciones Opciones adicionales [extensiones, tamano_maximo, nombre_personalizado]
     * @return array Información del archivo subido
     */
    public function subirArchivo(UploadedFile $archivo, string $carpeta, array $opciones = []): array
    {
        // Validar archivo antes de subir
        $this->validarArchivo($archivo, $opciones);
        
        // Generar nombre único para el archivo
        $nombreArchivo = $this->generarNombreUnico($archivo, $opciones['nombre_personalizado'] ?? null);
        
        // Determinar el disco de almacenamiento
        $disco = $opciones['disco'] ?? 'public';
        
        // Subir archivo
        $ruta = $archivo->storeAs($carpeta, $nombreArchivo, $disco);
        
        if (!$ruta) {
            throw new Exception('Error al subir el archivo');
        }
        
        return [
            'nombre_original' => $archivo->getClientOriginalName(),
            'nombre_sistema' => $nombreArchivo,
            'ruta' => $ruta,
            'tamaño' => $archivo->getSize(),
            'extension' => $archivo->getClientOriginalExtension(),
            'tipo_mime' => $archivo->getMimeType(),
            'url' => Storage::disk($disco)->url($ruta)
        ];
    }

    /**
     * Sube múltiples archivos a la vez
     * 
     * @param array $archivos Array de archivos UploadedFile
     * @param string $carpeta Carpeta de destino
     * @param array $opciones Opciones adicionales
     * @return array Array con información de cada archivo subido
     */
    public function subirMultiplesArchivos(array $archivos, string $carpeta, array $opciones = []): array
    {
        $resultados = [];
        
        foreach ($archivos as $indice => $archivo) {
            try {
                $resultados[] = $this->subirArchivo($archivo, $carpeta, $opciones);
            } catch (Exception $e) {
                // Agregar información del error
                $resultados[] = [
                    'error' => true,
                    'mensaje' => 'Error al subir archivo ' . ($indice + 1) . ': ' . $e->getMessage(),
                    'nombre_original' => $archivo->getClientOriginalName()
                ];
            }
        }
        
        return $resultados;
    }

    /**
     * Valida un archivo antes de subirlo
     * 
     * @param UploadedFile $archivo Archivo a validar
     * @param array $opciones Opciones de validación
     * @return void
     * @throws \\Exception Si el archivo no es válido
     */
    protected function validarArchivo(UploadedFile $archivo, array $opciones = []): void
    {
        // Validar que el archivo se haya subido correctamente
        if (!$archivo->isValid()) {
            throw new Exception('Error en la carga del archivo: ' . $archivo->getErrorMessage());
        }
        
        // Validar extensión
        $extensionesPermitidas = $opciones['extensiones'] ?? $this->extensionesPermitidas;
        $extension = strtolower($archivo->getClientOriginalExtension());
        
        if (!in_array($extension, $extensionesPermitidas)) {
            throw new Exception('Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $extensionesPermitidas));
        }
        
        // Validar tamaño
        $tamanoMaximo = $opciones['tamano_maximo'] ?? $this->tamanoMaximo;
        
        if ($archivo->getSize() > $tamanoMaximo) {
            $tamanoMaximoMB = round($tamanoMaximo / (1024 * 1024), 2);
            throw new Exception('El archivo excede el tamaño máximo permitido de ' . $tamanoMaximoMB . 'MB');
        }
        
        // Validar tipo MIME (seguridad adicional)
        $tiposMimePermitidos = $this->obtenerTiposMimePermitidos($extensionesPermitidas);
        $tipoMime = $archivo->getMimeType();
        
        if (!in_array($tipoMime, $tiposMimePermitidos)) {
            throw new Exception('Tipo de contenido no permitido para la extensión ' . $extension);
        }
    }

    /**
     * Genera un nombre único para el archivo
     * 
     * @param UploadedFile $archivo Archivo original
     * @param string|null $nombrePersonalizado Nombre personalizado (opcional)
     * @return string Nombre único para el archivo
     */
    protected function generarNombreUnico(UploadedFile $archivo, ?string $nombrePersonalizado = null): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension());
        
        if ($nombrePersonalizado) {
            // Sanitizar el nombre personalizado
            $nombrePersonalizado = $this->sanitizarNombre($nombrePersonalizado);
            return $nombrePersonalizado . '.' . $extension;
        }
        
        // Generar nombre único con timestamp y hash
        $timestamp = time();
        $hash = substr(md5(uniqid(rand(), true)), 0, 8);
        $nombreBase = $this->sanitizarNombre(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME));
        
        return sprintf('%s_%s_%s.%s', $nombreBase, $timestamp, $hash, $extension);
    }

    /**
     * Sanitiza un nombre de archivo
     * 
     * @param string $nombre Nombre a sanitizar
     * @return string Nombre sanitizado
     */
    protected function sanitizarNombre(string $nombre): string
    {
        // Convertir a minúsculas
        $nombre = strtolower($nombre);
        
        // Reemplazar espacios y caracteres especiales
        $nombre = preg_replace('/[^a-z0-9_-]/', '_', $nombre);
        
        // Eliminar múltiples guiones bajos
        $nombre = preg_replace('/_+/', '_', $nombre);
        
        // Eliminar guiones bajos al inicio y final
        $nombre = trim($nombre, '_');
        
        // Limitar longitud
        return substr($nombre, 0, 100);
    }

    /**
     * Obtiene los tipos MIME permitidos según las extensiones
     * 
     * @param array $extensiones Extensiones de archivo
     * @return array Tipos MIME correspondientes
     */
    protected function obtenerTiposMimePermitidos(array $extensiones): array
    {
        $mapaMime = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'jpg' => ['image/jpeg', 'image/jpg'],
            'jpeg' => ['image/jpeg', 'image/jpg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/x-rar-compressed', 'application/vnd.rar']
        ];
        
        $tiposPermitidos = [];
        
        foreach ($extensiones as $extension) {
            if (isset($mapaMime[$extension])) {
                $tiposPermitidos = array_merge($tiposPermitidos, $mapaMime[$extension]);
            }
        }
        
        return $tiposPermitidos;
    }

    /**
     * Crea la estructura de carpetas para un trámite
     * 
     * @param int $tramiteId ID del trámite
     * @return string Ruta de la carpeta creada
     */
    public function crearEstructuraCarpetasTramite(int $tramiteId): string
    {
        $año = date('Y');
        $mes = date('m');
        
        $rutaBase = sprintf('tramites/%d/%s/%s/%05d', $año, $mes, str_pad($tramiteId, 5, '0', STR_PAD_LEFT));
        
        // Crear subcarpetas
        $carpetas = [
            $rutaBase . '/documentos',
            $rutaBase . '/adjuntos',
            $rutaBase . '/vouchers',
            $rutaBase . '/respuestas'
        ];
        
        foreach ($carpetas as $carpeta) {
            if (!Storage::disk('public')->exists($carpeta)) {
                Storage::disk('public')->makeDirectory($carpeta);
            }
        }
        
        return $rutaBase;
    }

    /**
     * Elimina un archivo del sistema
     * 
     * @param string $ruta Ruta del archivo a eliminar
     * @param string $disco Disco de almacenamiento
     * @return bool True si se eliminó correctamente
     */
    public function eliminarArchivo(string $ruta, string $disco = 'public'): bool
    {
        if (Storage::disk($disco)->exists($ruta)) {
            return Storage::disk($disco)->delete($ruta);
        }
        
        return false;
    }

    /**
     * Genera una URL temporal para descarga segura de archivos
     * 
     * @param string $ruta Ruta del archivo
     * @param int $expiracionMinutos Minutos hasta que expire la URL
     * @return string URL temporal
     */
    public function generarUrlTemporal(string $ruta, int $expiracionMinutos = 60): string
    {
        return Storage::disk('public')->temporaryUrl(
            $ruta,
            now()->addMinutes($expiracionMinutos)
        );
    }

    /**
     * Obtiene información detallada de un archivo
     * 
     * @param string $ruta Ruta del archivo
     * @param string $disco Disco de almacenamiento
     * @return array|null Información del archivo o null si no existe
     */
    public function obtenerInformacionArchivo(string $ruta, string $disco = 'public'): ?array
    {
        if (!Storage::disk($disco)->exists($ruta)) {
            return null;
        }
        
        return [
            'ruta' => $ruta,
            'tamaño' => Storage::disk($disco)->size($ruta),
            'ultima_modificacion' => Storage::disk($disco)->lastModified($ruta),
            'mime_type' => Storage::disk($disco)->mimeType($ruta),
            'url' => Storage::disk($disco)->url($ruta)
        ];
    }
}