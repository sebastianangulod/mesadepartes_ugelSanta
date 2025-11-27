<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

/**
 * Helper para manejo de documentos y archivos del sistema
 * 
 * Esta clase proporciona métodos utilitarios para el manejo de archivos,
 * generación de códigos, validaciones de formato y otras operaciones
 * comunes relacionadas con documentos.
 * 
 * @package App\Helpers
 */
class DocumentHelper
{
    /**
     * Genera un código único de trámite
     * 
     * @param int $procedimientoId ID del procedimiento
     * @param int $anio Año del trámite
     * @return string Código único generado
     */
    public static function generarCodigoTramite(int $procedimientoId, int $anio): string
    {
        $siglasProcedimiento = self::obtenerSiglasProcedimiento($procedimientoId);
        $correlativo = self::obtenerCorrelativo($procedimientoId, $anio);
        
        return sprintf('%s-%04d-%d', $siglasProcedimiento, $correlativo, $anio);
    }

    /**
     * Genera un número de voucher único
     * 
     * @param int $tramiteId ID del trámite
     * @return string Número de voucher
     */
    public static function generarVoucher(int $tramiteId): string
    {
        $fecha = date('Ymd');
        $hora = date('His');
        
        return sprintf('V-%s-%s-%06d', $fecha, $hora, $tramiteId);
    }

    /**
     * Genera hash único para archivo
     * 
     * @param string $nombreArchivo Nombre original del archivo
     * @param int $tramiteId ID del trámite
     * @return string Hash único
     */
    public static function generarHashArchivo(string $nombreArchivo, int $tramiteId): string
    {
        $timestamp = microtime(true);
        $random = bin2hex(random_bytes(8));
        
        return hash('sha256', $nombreArchivo . $tramiteId . $timestamp . $random);
    }

    /**
     * Obtiene las siglas de un procedimiento
     * 
     * @param int $procedimientoId ID del procedimiento
     * @return string Siglas del procedimiento
     */
    private static function obtenerSiglasProcedimiento(int $procedimientoId): string
    {
        // Obtener siglas desde la base de datos o configuración
        // Por ahora, retornar siglas genéricas
        return 'MP'; // Mesa de Partes
    }

    /**
     * Obtiene el correlativo siguiente para un procedimiento en el año
     * 
     * @param int $procedimientoId ID del procedimiento
     * @param int $anio Año
     * @return int Correlativo siguiente
     */
    private static function obtenerCorrelativo(int $procedimientoId, int $anio): int
    {
        // Implementar lógica para obtener el siguiente correlativo
        // desde la base de datos
        return 1; // Placeholder
    }

    /**
     * Valida el formato de un archivo PDF
     * 
     * @param string $rutaArchivo Ruta completa del archivo
     * @return bool True si es un PDF válido
     */
    public static function validarPDF(string $rutaArchivo): bool
    {
        if (!file_exists($rutaArchivo)) {
            return false;
        }

        $contenido = file_get_contents($rutaArchivo, false, null, 0, 4);
        return $contenido === '%PDF';
    }

    /**
     * Obtiene el número de páginas de un PDF
     * 
     * @param string $rutaArchivo Ruta del archivo PDF
     * @return int Número de páginas
     */
    public static function obtenerNumeroPaginasPDF(string $rutaArchivo): int
    {
        if (!self::validarPDF($rutaArchivo)) {
            return 0;
        }

        $contenido = file_get_contents($rutaArchivo);
        $paginas = preg_match_all('/\/Type\s*\/Page\b/', $contenido);
        
        return $paginas;
    }

    /**
     * Comprime una imagen
     * 
     * @param string $rutaOriginal Ruta del archivo original
     * @param string $rutaDestino Ruta de destino
     * @param int $calidad Calidad de compresión (1-100)
     * @return bool True si la compresión fue exitosa
     */
    public static function comprimirImagen(string $rutaOriginal, string $rutaDestino, int $calidad = 85): bool
    {
        $info = getimagesize($rutaOriginal);
        
        if (!$info) {
            return false;
        }

        switch ($info['mime']) {
            case 'image/jpeg':
                $imagen = imagecreatefromjpeg($rutaOriginal);
                $resultado = imagejpeg($imagen, $rutaDestino, $calidad);
                break;
            
            case 'image/png':
                $imagen = imagecreatefrompng($rutaOriginal);
                imagepalettetotruecolor($imagen);
                imagealphablending($imagen, true);
                imagesavealpha($imagen, true);
                $resultado = imagepng($imagen, $rutaDestino, 9);
                break;
            
            default:
                return false;
        }

        imagedestroy($imagen);
        return $resultado;
    }

    /**
     * Genera miniatura de imagen
     * 
     * @param string $rutaOriginal Ruta del archivo original
     * @param string $rutaDestino Ruta de destino
     * @param int $anchoMax Ancho máximo de la miniatura
     * @param int $altoMax Alto máximo de la miniatura
     * @return bool True si la generación fue exitosa
     */
    public static function generarMiniatura(string $rutaOriginal, string $rutaDestino, int $anchoMax = 150, int $altoMax = 150): bool
    {
        $info = getimagesize($rutaOriginal);
        
        if (!$info) {
            return false;
        }

        $anchoOriginal = $info[0];
        $altoOriginal = $info[1];

        // Calcular proporciones
        $proporcion = min($anchoMax / $anchoOriginal, $altoMax / $altoOriginal);
        $nuevoAncho = (int)($anchoOriginal * $proporcion);
        $nuevoAlto = (int)($altoOriginal * $proporcion);

        switch ($info['mime']) {
            case 'image/jpeg':
                $imagenOriginal = imagecreatefromjpeg($rutaOriginal);
                break;
            
            case 'image/png':
                $imagenOriginal = imagecreatefrompng($rutaOriginal);
                break;
            
            default:
                return false;
        }

        $miniatura = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        
        // Mantener transparencia para PNG
        if ($info['mime'] === 'image/png') {
            imagealphablending($miniatura, false);
            imagesavealpha($miniatura, true);
        }

        imagecopyresampled($miniatura, $imagenOriginal, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $anchoOriginal, $altoOriginal);
        
        $resultado = false;
        switch ($info['mime']) {
            case 'image/jpeg':
                $resultado = imagejpeg($miniatura, $rutaDestino, 85);
                break;
            
            case 'image/png':
                $resultado = imagepng($miniatura, $rutaDestino, 9);
                break;
        }

        imagedestroy($imagenOriginal);
        imagedestroy($miniatura);

        return $resultado;
    }

    /**
     * Convierte un documento Word a PDF
     * 
     * @param string $rutaWord Ruta del archivo Word
     * @param string $rutaPDF Ruta de destino PDF
     * @return bool True si la conversión fue exitosa
     */
    public static function convertirWordAPDF(string $rutaWord, string $rutaPDF): bool
    {
        // Esta función requeriría una librería externa como PHPWord o LibreOffice
        // Por ahora, retornar false indicando que no está implementado
        return false;
    }

    /**
     * Une múltiples PDFs en uno solo
     * 
     * @param array $rutasPDF Array con rutas de archivos PDF
     * @param string $rutaSalida Ruta del archivo resultante
     * @return bool True si la unión fue exitosa
     */
    public static function unirPDFs(array $rutasPDF, string $rutaSalida): bool
    {
        // Esta función requeriría una librería como FPDI o PDFMerger
        // Por ahora, retornar false indicando que no está implementado
        return false;
    }

    /**
     * Genera código QR para un trámite
     * 
     * @param string $texto Texto a codificar
     * @param string $rutaSalida Ruta de salida de la imagen QR
     * @param int $tamano Tamaño del QR en píxeles
     * @return bool True si la generación fue exitosa
     */
    public static function generarQR(string $texto, string $rutaSalida, int $tamano = 200): bool
    {
        // Esta función requeriría una librería QR como Endroid QR Code
        // Por ahora, retornar false indicando que no está implementado
        return false;
    }

    /**
     * Valida el formato de un correo electrónico institucional
     * 
     * @param string $correo Correo a validar
     * @return bool True si es un correo institucional válido
     */
    public static function validarCorreoInstitucional(string $correo): bool
    {
        // Validar formato básico de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Verificar dominios institucionales permitidos
        $dominiosPermitidos = [
            'ugel.edu.pe',
            'minedu.gob.pe',
            'gob.pe'
        ];

        $dominio = substr(strrchr($correo, "@"), 1);
        return in_array($dominio, $dominiosPermitidos);
    }

    /**
     * Sanitiza el nombre de un archivo
     * 
     * @param string $nombreArchivo Nombre original del archivo
     * @return string Nombre sanitizado
     */
    public static function sanitizarNombreArchivo(string $nombreArchivo): string
    {
        // Eliminar caracteres especiales y espacios
        $nombre = preg_replace('/[^A-Za-z0-9\-\_\.]/', '_', $nombreArchivo);
        
        // Limitar longitud
        $nombre = substr($nombre, 0, 100);
        
        // Convertir a minúsculas
        $nombre = strtolower($nombre);
        
        return $nombre;
    }

    /**
     * Calcula el hash SHA256 de un archivo
     * 
     * @param string $rutaArchivo Ruta del archivo
     * @return string|null Hash del archivo o null si no existe
     */
    public static function calcularHashArchivo(string $rutaArchivo): ?string
    {
        if (!file_exists($rutaArchivo)) {
            return null;
        }

        return hash_file('sha256', $rutaArchivo);
    }

    /**
     * Verifica la integridad de un archivo mediante hash
     * 
     * @param string $rutaArchivo Ruta del archivo
     * @param string $hashOriginal Hash original para comparar
     * @return bool True si el archivo es íntegro
     */
    public static function verificarIntegridadArchivo(string $rutaArchivo, string $hashOriginal): bool
    {
        $hashActual = self::calcularHashArchivo($rutaArchivo);
        return $hashActual === $hashOriginal;
    }

    /**
     * Obtiene el tamaño formateado de un archivo
     * 
     * @param int $bytes Tamaño en bytes
     * @param int $decimales Número de decimales
     * @return string Tamaño formateado
     */
    public static function formatearTamañoArchivo(int $bytes, int $decimales = 2): string
    {
        $unidades = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.{$decimales}f", $bytes / pow(1024, $factor)) . $unidades[$factor];
    }

    /**
     * Genera un nombre único para archivo
     * 
     * @param string $extension Extensión del archivo
     * @param string $prefijo Prefijo opcional
     * @return string Nombre único
     */
    public static function generarNombreUnico(string $extension, string $prefijo = ''): string
    {
        $timestamp = microtime(true);
        $random = bin2hex(random_bytes(8));
        $nombre = $prefijo ? $prefijo . '_' : '';
        
        return $nombre . $timestamp . '_' . $random . '.' . $extension;
    }
}