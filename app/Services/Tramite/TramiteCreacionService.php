<?php

namespace App\Services\Tramite;

use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para la creación y registro de trámites
 * 
 * Responsabilidades principales:
 * - Crear nuevos trámites con validación de archivos
 * - Gestionar subida de PDF y generación de nombres de archivo
 * - Calcular periodo y tipo de trámite (PRESENCIAL/VIRTUAL)
 * 
 * Nota: Este servicio está enfocado en el ALTA de trámites.
 * El flujo de estados (aceptar, rechazar, observar, etc.) se maneja
 * en App\Services\Tramites\TramiteWorkflowService.
 */
class TramiteCreacionService
{
    private $uploadPath = 'uploads/tramites/';
    private $voucherPath = 'uploads/vouchers/';
    private $expedientePath = 'uploads/expedientes/';

    /**
     * Crear un nuevo trámite con archivo adjunto
     * 
     * @param array $datos Datos del trámite
     * @param mixed $archivo Archivo PDF a subir
     * @return array Resultado de la operación
     */
    public function crearTramite(array $datos, $archivo = null): array
    {
        DB::beginTransaction();
        
        try {
            Log::info('Iniciando creación de trámite', [
                'datos' => $datos,
                'has_archivo' => $archivo !== null,
                'usuario_id' => Auth::id(),
            ]);

            // Validar que el usuario esté autenticado
            if (!Auth::check()) {
                Log::error('Usuario no autenticado al intentar crear trámite');
                throw new Exception("Usuario no autenticado");
            }

            $usuario = Auth::user();
            Log::info('Usuario autenticado', [
                'usuario_id' => $usuario->id,
                'tipo_user' => $usuario->tipo_user,
            ]);
            
            // Determinar tipo de trámite según el tipo de usuario
            $tipoTramite = ($usuario->tipo_user == 2) ? 'PRESENCIAL' : 'VIRTUAL';
            Log::info('Tipo de trámite determinado', ['tipo' => $tipoTramite]);
            
            // Validar que se proporcione un procedimiento
            if (empty($datos['id_procedimiento'])) {
                Log::error('No se proporcionó id_procedimiento');
                throw new Exception("Debe seleccionar un procedimiento");
            }

            // Validar que el procedimiento exista
            $procedimiento = Procedimiento::find($datos['id_procedimiento']);
            if (!$procedimiento) {
                Log::error('Procedimiento no encontrado', ['id_procedimiento' => $datos['id_procedimiento']]);
                throw new Exception("El procedimiento seleccionado no existe");
            }
            
            // Validar y procesar archivo si existe
            $nombreArchivo = null;
            if ($archivo) {
                Log::info('Validando archivo', [
                    'nombre' => $archivo->getClientOriginalName(),
                    'tamaño' => $archivo->getSize(),
                    'mime' => $archivo->getMimeType(),
                ]);
                
                $this->validarArchivo($archivo);
                $nombreArchivo = $this->procesarArchivo($archivo, $usuario->id);
                Log::info('Archivo procesado exitosamente', ['nombre_archivo' => $nombreArchivo]);
            } else {
                Log::error('No se proporcionó archivo');
                throw new Exception("Debe adjuntar un archivo PDF");
            }

            // Obtener el periodo actual
            $periodo = date('Y');

            // Contar páginas del PDF si existe
            $numeroPaginas = 1;
            if ($nombreArchivo) {
                $rutaArchivo = Storage::path($this->uploadPath . $nombreArchivo);
                $numeroPaginas = $this->contarPaginasPDF($rutaArchivo);
            }

            // IMPORTANTE: Al crear un trámite, debe asignarse automáticamente a un usuario de nivel 1
            // Buscar usuario de nivel 1 según el área del procedimiento
            $usuarioNivel1 = $this->buscarUsuarioNivel1($procedimiento);
            
            if (!$usuarioNivel1) {
                Log::error('No se encontró usuario de nivel 1 para el procedimiento', [
                    'id_procedimiento' => $procedimiento->id,
                    'id_area' => $procedimiento->id_area
                ]);
                throw new Exception(
                    'No se encontró usuario de nivel 1 para iniciar el trámite. ' .
                    'Por favor, verifique que haya un usuario de nivel 1 vinculado al área correspondiente.'
                );
            }

            Log::info('Usuario de nivel 1 asignado al trámite', [
                'usuario_nivel_1_id' => $usuarioNivel1,
                'id_procedimiento' => $procedimiento->id,
                'id_area' => $procedimiento->id_area
            ]);

            // Crear el trámite asignado automáticamente al nivel 1
            $tramite = Tramite::create([
                'archivo_adjunto' => $nombreArchivo,
                'id_procedimiento' => $datos['id_procedimiento'],
                'id_usuario' => $usuarioNivel1, // Usuario de nivel 1 asignado automáticamente
                'estado' => 'PENDIENTE', // PENDIENTE hasta que el usuario de nivel 1 lo acepte
                'nivel_actual' => 1, // Asignado automáticamente al nivel 1
                'tipo_tramite' => $tipoTramite,
                'tramitescol' => $numeroPaginas,
                'periodo' => $periodo,
                'fecha_creacion' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Trámite creado exitosamente',
                'id_tramite' => $tramite->id,
                'numero_tramite' => $tramite->id . '-' . $periodo
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear trámite', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos,
            ]);
            
            // Limpiar archivo si se subió pero falló la transacción
            if (isset($nombreArchivo) && Storage::exists($this->uploadPath . $nombreArchivo)) {
                Storage::delete($this->uploadPath . $nombreArchivo);
                Log::info('Archivo eliminado tras error', ['nombre_archivo' => $nombreArchivo]);
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Validar archivo PDF
     * 
     * @param mixed $archivo
     * @throws Exception
     */
    protected function validarArchivo($archivo): void
    {
        if (!$archivo) {
            Log::error('Archivo es null o no existe');
            throw new Exception("Debe adjuntar un archivo PDF");
        }

        if (!$archivo->isValid()) {
            Log::error('Archivo no es válido', [
                'error' => $archivo->getError(),
                'nombre' => $archivo->getClientOriginalName(),
            ]);
            throw new Exception("El archivo no es válido. Error: " . $archivo->getError());
        }

        $extension = strtolower($archivo->getClientOriginalExtension());
        $mimeType = $archivo->getMimeType();
        
        Log::info('Validando tipo de archivo', [
            'extension' => $extension,
            'mime_type' => $mimeType,
            'nombre' => $archivo->getClientOriginalName(),
        ]);

        if ($extension !== 'pdf' && $mimeType !== 'application/pdf') {
            Log::error('Archivo no es PDF', [
                'extension' => $extension,
                'mime_type' => $mimeType,
            ]);
            throw new Exception("El archivo debe ser un PDF. Tipo recibido: " . $mimeType);
        }

        // Validar tamaño (10MB = 10485760 bytes)
        $tamañoMaximo = 10485760;
        if ($archivo->getSize() > $tamañoMaximo) {
            Log::error('Archivo excede tamaño máximo', [
                'tamaño' => $archivo->getSize(),
                'maximo' => $tamañoMaximo,
            ]);
            throw new Exception("El archivo no debe exceder los 10MB");
        }
    }

    /**
     * Procesar archivo y guardarlo en el sistema de archivos
     * 
     * @param mixed $archivo
     * @param int $usuarioId
     * @return string Nombre del archivo guardado
     */
    protected function procesarArchivo($archivo, int $usuarioId): string
    {
        // Sanitizar nombre del archivo
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = $archivo->getClientOriginalExtension();
        $nombreSanitizado = $this->sanitizarNombreArchivo(
            pathinfo($nombreOriginal, PATHINFO_FILENAME)
        );
        $nombreArchivo = time() . '_' . $nombreSanitizado . '.' . $extension;
        
        $archivo->storeAs($this->uploadPath, $nombreArchivo, 'public');

        return $nombreArchivo;
    }

    /**
     * Sanitizar nombre de archivo
     * 
     * @param string $nombre
     * @return string
     */
    private function sanitizarNombreArchivo(string $nombre): string
    {
        // Eliminar caracteres especiales y espacios
        $nombre = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre);
        // Reemplazar espacios por guiones bajos
        $nombre = str_replace(' ', '_', $nombre);
        // Convertir a minúsculas
        $nombre = strtolower($nombre);
        // Limitar longitud
        $nombre = substr($nombre, 0, 100);
        
        return $nombre;
    }

    /**
     * Contar páginas de un PDF
     * 
     * @param string $rutaArchivo
     * @return int
     */
    private function contarPaginasPDF(string $rutaArchivo): int
    {
        try {
            // Método alternativo: leer el archivo y buscar el patrón de páginas
            // Esto es más simple y no requiere dependencias adicionales
            $content = @file_get_contents($rutaArchivo);
            if ($content === false) {
                Log::warning('No se pudo leer el archivo PDF para contar páginas', ['ruta' => $rutaArchivo]);
                return 1;
            }
            
            // Buscar el patrón /Count seguido de un número (número de páginas)
            // Este es un método simple que funciona para la mayoría de PDFs
            preg_match_all('/\/Count\s+(\d+)/', $content, $matches);
            
            if (!empty($matches[1])) {
                // Tomar el último valor encontrado (generalmente el más grande)
                $pageCount = max(array_map('intval', $matches[1]));
                if ($pageCount > 0) {
                    Log::info('Páginas contadas usando método /Count', ['paginas' => $pageCount]);
                    return $pageCount;
                }
            }
            
            // Método alternativo: buscar el patrón /Type\s*\/Page[^s]
            preg_match_all('/\/Type\s*\/Page[^s]/', $content, $pageMatches);
            if (!empty($pageMatches[0])) {
                $pageCount = count($pageMatches[0]);
                if ($pageCount > 0) {
                    Log::info('Páginas contadas usando método /Type/Page', ['paginas' => $pageCount]);
            return $pageCount;
                }
            }
            
            // Si no se puede determinar, retornar 1
            Log::info('No se pudo determinar el número de páginas del PDF, usando valor por defecto', ['ruta' => $rutaArchivo]);
            return 1;
            
        } catch (\Exception $e) {
            Log::warning('Error al contar páginas del PDF', [
                'ruta' => $rutaArchivo,
                'error' => $e->getMessage()
            ]);
            // Si falla, retornar 1 como valor por defecto
            return 1;
        }
    }

    /**
     * Buscar usuario de nivel 1 según el área del procedimiento
     * 
     * @param Procedimiento $procedimiento
     * @return int|null ID del usuario de nivel 1
     */
    private function buscarUsuarioNivel1(Procedimiento $procedimiento): ?int
    {
        if (!$procedimiento->id_area) {
            Log::warning('Procedimiento no tiene área asignada', [
                'id_procedimiento' => $procedimiento->id
            ]);
            return null;
        }

        // Buscar usuario de nivel 1 vinculado al área del procedimiento
        $usuario = DB::table('usuarios as u')
            ->join('usuarios_niveles as un', 'u.id', '=', 'un.id_usuario')
            ->where('un.nivel', 1)
            ->where('un.id_nivel', $procedimiento->id_area)
            ->where('u.tipo_user', 2) // Solo usuarios internos
            ->select('u.id')
            ->first();

        return $usuario ? $usuario->id : null;
    }
}


