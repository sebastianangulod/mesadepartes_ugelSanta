<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Acceso centralizado al almacenamiento de documentos del sistema
 * (PDFs de trámites: adjuntos, oficios, vouchers, expedientes).
 *
 * - El disco se elige por configuración (ver config/filesystems.php
 *   'documentos_disk' y FILESYSTEM_DOCUMENTOS_* en .env). Por defecto
 *   es local y equivale a la carpeta actual: no requiere migración.
 * - Los archivos nuevos se guardan con subcarpeta de año
 *   (uploads/<tipo>/2026/archivo.pdf) para no acumular decenas de
 *   miles de archivos por carpeta. En BD se guarda la ruta relativa.
 * - localizar()/eliminar() aceptan tanto rutas relativas nuevas como
 *   nombres sueltos antiguos, y revisan el disco actual y después el
 *   disco 'local' (compatibilidad con archivos previos).
 */
class DocumentStorage
{
    public static function diskName(): string
    {
        return (string) config('filesystems.documentos_disk', 'documentos');
    }

    public static function disk()
    {
        return Storage::disk(self::diskName());
    }

    /**
     * Sanitiza la base del nombre de archivo (sin extensión).
     */
    public static function nombreSeguro(string $original): string
    {
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($original, PATHINFO_FILENAME) ?? 'doc');
        $base = strtolower(substr(str_replace(' ', '_', (string) $base), 0, 100));

        return $base !== '' ? $base : 'doc';
    }

    /**
     * Guarda un PDF subido y devuelve la ruta relativa para la BD.
     * $directorio: tramites|oficios|vouchers|expedientes
     */
    public static function guardar(UploadedFile $archivo, string $directorio, ?string $nombreFinal = null): string
    {
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'pdf');
        $nombreFinal ??= time() . '_' . self::nombreSeguro($archivo->getClientOriginalName()) . '.' . $extension;
        $rel = 'uploads/' . trim($directorio, '/') . '/' . date('Y');

        return $archivo->storeAs($rel, $nombreFinal, self::diskName());
    }

    /**
     * Resuelve la ruta absoluta de un archivo guardado.
     * $dirsLegacy: carpetas donde pudo quedar por nombre (sin año).
     */
    public static function localizar(?string $guardado, array $dirsLegacy = []): ?string
    {
        if (!$guardado) {
            return null;
        }

        $candidatos = [];
        if (str_contains($guardado, '/')) {
            $candidatos[] = $guardado;
        }
        $base = basename($guardado);
        foreach ($dirsLegacy as $d) {
            $candidatos[] = 'uploads/' . trim($d, '/') . '/' . $base;
        }

        $discos = array_values(array_unique([self::diskName(), 'local']));
        foreach ($discos as $disk) {
            foreach ($candidatos as $rel) {
                try {
                    if (Storage::disk($disk)->exists($rel)) {
                        return Storage::disk($disk)->path($rel);
                    }
                } catch (\Throwable) {
                    // Disco remoto u otro error: se ignora ese candidato.
                    // NOTA S3: con driver remoto, servir con temporaryUrl()
                    // en el controlador en vez de response()->file().
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Elimina el archivo en todas las ubicaciones candidatas.
     */
    public static function eliminar(?string $guardado, array $dirsLegacy = []): void
    {
        if (!$guardado) {
            return;
        }

        $candidatos = [];
        if (str_contains($guardado, '/')) {
            $candidatos[] = $guardado;
        }
        $base = basename($guardado);
        foreach ($dirsLegacy as $d) {
            $candidatos[] = 'uploads/' . trim($d, '/') . '/' . $base;
        }

        $discos = array_values(array_unique([self::diskName(), 'local']));
        foreach ($discos as $disk) {
            foreach ($candidatos as $rel) {
                try {
                    if (Storage::disk($disk)->exists($rel)) {
                        Storage::disk($disk)->delete($rel);
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }
    }
}
