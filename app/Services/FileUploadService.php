<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Directorio base para almacenamiento de archivos
     */
    protected string $directorioBase;
    
    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        $this->directorioBase = config('seguridad.archivos.directorio', 'tramites_adjuntos');
    }
    
    /**
     * Subir un archivo al sistema
     *
     * @param UploadedFile $archivo
     * @param string $subdirectorio
     * @return array<string, mixed>
     */
    public function subirArchivo(UploadedFile $archivo, string $subdirectorio = ''): array
    {
        try {
            // Validar el archivo
            $this->validarArchivo($archivo);
            
            // Generar nombre único para el archivo
            $nombreArchivo = $this->generarNombreArchivo($archivo);
            
            // Construir la ruta completa
            $rutaCompleta = $this->construirRuta($subdirectorio, $nombreArchivo);
            
            // Almacenar el archivo
            $rutaAlmacenada = $archivo->storeAs(
                $this->directorioBase . '/' . $subdirectorio,
                $nombreArchivo,
                'public'
            );
            
            // Verificar que se almacenó correctamente
            if (!$rutaAlmacenada) {
                throw new \Exception('Error al almacenar el archivo.');
            }
            
            return [
                'exito' => true,
                'ruta' => $rutaAlmacenada,
                'nombre_original' => $archivo->getClientOriginalName(),
                'nombre_almacenado' => $nombreArchivo,
                'tamaño' => $archivo->getSize(),
                'tipo_mime' => $archivo->getMimeType(),
                'extension' => $archivo->getClientOriginalExtension(),
            ];
            
        } catch (\Exception $e) {
            return [
                'exito' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Eliminar un archivo del sistema
     *
     * @param string $ruta
     * @return bool
     */
    public function eliminarArchivo(string $ruta): bool
    {
        try {
            if (Storage::disk('public')->exists($ruta)) {
                return Storage::disk('public')->delete($ruta);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener la URL pública de un archivo
     *
     * @param string $ruta
     * @return string|null
     */
    public function obtenerUrl(string $ruta): ?string
    {
        try {
            if (Storage::disk('public')->exists($ruta)) {
                return Storage::disk('public')->url($ruta);
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Validar que el archivo cumple con los requisitos
     *
     * @param UploadedFile $archivo
     * @throws \InvalidArgumentException
     */
    protected function validarArchivo(UploadedFile $archivo): void
    {
        // Validar tamaño máximo
        $tamanoMaximo = config('seguridad.archivos.max_size', 10240) * 1024; // Convertir a bytes
        if ($archivo->getSize() > $tamanoMaximo) {
            throw new \InvalidArgumentException('El archivo excede el tamaño máximo permitido.');
        }
        
        // Validar tipos de archivo permitidos
        $tiposPermitidos = config('seguridad.archivos.tipos_permitidos', []);
        $extension = strtolower($archivo->getClientOriginalExtension());
        
        if (!array_key_exists($extension, $tiposPermitidos)) {
            throw new \InvalidArgumentException('Tipo de archivo no permitido.');
        }
        
        // Validar MIME type
        if (config('seguridad.archivos.validar_mime', true)) {
            $mimeTypeEsperado = $tiposPermitidos[$extension];
            if ($archivo->getMimeType() !== $mimeTypeEsperado) {
                throw new \InvalidArgumentException('El tipo de archivo no coincide con su extensión.');
            }
        }
    }
    
    /**
     * Generar un nombre único y seguro para el archivo
     *
     * @param UploadedFile $archivo
     * @return string
     */
    protected function generarNombreArchivo(UploadedFile $archivo): string
    {
        $extension = $archivo->getClientOriginalExtension();
        
        if (config('seguridad.archivos.encriptar_nombre', false)) {
            // Generar nombre encriptado
            $nombre = Str::random(40) . '.' . $extension;
        } else {
            // Generar nombre con timestamp y prefijo
            $prefijo = config('seguridad.archivos.prefijo', 'tramite_');
            $timestamp = now()->format('YmdHis');
            $random = Str::random(8);
            $nombre = $prefijo . $timestamp . '_' . $random . '.' . $extension;
        }
        
        return $nombre;
    }
    
    /**
     * Construir la ruta completa del archivo
     *
     * @param string $subdirectorio
     * @param string $nombreArchivo
     * @return string
     */
    protected function construirRuta(string $subdirectorio, string $nombreArchivo): string
    {
        if (config('seguridad.archivos.subdirectorios_fecha', true)) {
            $fecha = now()->format('Y/m/d');
            $subdirectorio = trim($subdirectorio . '/' . $fecha, '/');
        }
        
        return $this->directorioBase . '/' . $subdirectorio . '/' . $nombreArchivo;
    }
    
    /**
     * Obtener estadísticas de almacenamiento
     *
     * @return array<string, mixed>
     */
    public function obtenerEstadisticas(): array
    {
        try {
            $archivos = Storage::disk('public')->allFiles($this->directorioBase);
            $totalArchivos = count($archivos);
            $tamanoTotal = 0;
            
            foreach ($archivos as $archivo) {
                $tamanoTotal += Storage::disk('public')->size($archivo);
            }
            
            return [
                'total_archivos' => $totalArchivos,
                'tamano_total_bytes' => $tamanoTotal,
                'tamano_total_formateado' => $this->formatearTamano($tamanoTotal),
                'directorio_base' => $this->directorioBase,
            ];
            
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Formatear tamaño en bytes a unidad legible
     *
     * @param int $bytes
     * @return string
     */
    protected function formatearTamano(int $bytes): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unidad = 0;
        
        while ($bytes >= 1024 && $unidad < count($unidades) - 1) {
            $bytes /= 1024;
            $unidad++;
        }
        
        return round($bytes, 2) . ' ' . $unidades[$unidad];
    }
}