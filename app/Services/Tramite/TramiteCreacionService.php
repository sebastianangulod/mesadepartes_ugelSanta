<?php

namespace App\Services\Tramite;

use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            // Validar que el usuario esté autenticado
            if (!Auth::check()) {
                throw new Exception("Usuario no autenticado");
            }

            $usuario = Auth::user();
            
            // Determinar tipo de trámite según el tipo de usuario
            $tipoTramite = ($usuario->tipo_user == 2) ? 'PRESENCIAL' : 'VIRTUAL';
            
            // Validar y procesar archivo si existe
            $nombreArchivo = null;
            if ($archivo) {
                $this->validarArchivo($archivo);
                $nombreArchivo = $this->procesarArchivo($archivo, $usuario->id);
            }

            // Obtener el periodo actual
            $periodo = date('Y');

            // Contar páginas del PDF si existe
            $numeroPaginas = 1;
            if ($nombreArchivo) {
                $rutaArchivo = Storage::path($this->uploadPath . $nombreArchivo);
                $numeroPaginas = $this->contarPaginasPDF($rutaArchivo);
            }

            // Crear el trámite
            $tramite = Tramite::create([
                'archivo_adjunto' => $nombreArchivo,
                'id_procedimiento' => $datos['id_procedimiento'],
                'id_usuario' => $usuario->id,
                'estado' => 'PENDIENTE',
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
            
            // Limpiar archivo si se subió pero falló la transacción
            if (isset($nombreArchivo) && Storage::exists($this->uploadPath . $nombreArchivo)) {
                Storage::delete($this->uploadPath . $nombreArchivo);
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
        if (!$archivo->isValid()) {
            throw new Exception("El archivo no es válido");
        }

        if ($archivo->getClientOriginalExtension() !== 'pdf') {
            throw new Exception("El archivo debe ser un PDF");
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
        
        $archivo->storeAs($this->uploadPath, $nombreArchivo);

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
     * Contar páginas de un PDF usando FPDI
     * 
     * @param string $rutaArchivo
     * @return int
     */
    private function contarPaginasPDF(string $rutaArchivo): int
    {
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($rutaArchivo);
            return $pageCount;
        } catch (\Exception $e) {
            // Si falla, retornar 1 como valor por defecto
            return 1;
        }
    }
}


