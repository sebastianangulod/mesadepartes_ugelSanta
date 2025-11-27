<?php

namespace App\Services\Procedimiento;

use App\Models\Procedimiento;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Servicio para gestionar la lógica de negocio de procedimientos
 * 
 * Este servicio maneja:
 * - CRUD de procedimientos
 * - Asignación de usuarios a procedimientos
 * - Validación de niveles y jerarquías
 * - Gestión de procedimientos por áreas y subáreas
 */
class ProcedimientoService
{
    /**
     * Obtener todos los procedimientos con sus relaciones
     * 
     * @return array Procedimientos con relaciones
     */
    public function obtenerTodos(): array
    {
        $procedimientos = Procedimiento::with([
            'area', 
            'subarea', 
            'nivelFinalModel',
            'usuariosAsignados'
        ])->orderBy('nombre')->get();

        return [
            'success' => true,
            'procedimientos' => $procedimientos
        ];
    }

    /**
     * Obtener procedimiento por ID
     * 
     * @param int $id ID del procedimiento
     * @return array Procedimiento con relaciones
     */
    public function obtenerPorId(int $id): array
    {
        try {
            $procedimiento = Procedimiento::with([
                'area', 
                'subarea', 
                'nivelFinalModel',
                'usuariosAsignados'
            ])->findOrFail($id);

            return [
                'success' => true,
                'procedimiento' => $procedimiento
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Procedimiento no encontrado'
            ];
        }
    }

    /**
     * Crear nuevo procedimiento
     * 
     * @param array $datos Datos del procedimiento
     * @return array Resultado de la operación
     */
    public function crear(array $datos): array
    {
        DB::beginTransaction();
        
        try {
            // Validar datos
            $this->validarDatosProcedimiento($datos);

            // Validar que el nivel final exista
            $this->validarNivelFinal($datos['nivel_final'], $datos['id_nivel_final']);

            // Crear procedimiento
            $procedimiento = Procedimiento::create([
                'nombre' => $datos['nombre'],
                'id_area' => $datos['id_area'],
                'id_subarea' => $datos['id_subarea'],
                'nivel_final' => $datos['nivel_final'],
                'id_nivel_final' => $datos['id_nivel_final'],
                'created_by' => auth()->id()
            ]);

            // Asignar usuarios si se proporcionaron
            if (!empty($datos['usuarios_asignados'])) {
                $this->asignarUsuarios($procedimiento->id, $datos['usuarios_asignados']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Procedimiento creado exitosamente',
                'procedimiento' => $procedimiento->fresh(['area', 'subarea', 'nivelFinalModel'])
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar procedimiento
     * 
     * @param int $id ID del procedimiento
     * @param array $datos Datos a actualizar
     * @return array Resultado de la operación
     */
    public function actualizar(int $id, array $datos): array
    {
        DB::beginTransaction();
        
        try {
            $procedimiento = Procedimiento::findOrFail($id);

            // Validar datos
            $this->validarDatosProcedimiento($datos);

            // Validar que el nivel final exista
            if (isset($datos['nivel_final']) && isset($datos['id_nivel_final'])) {
                $this->validarNivelFinal($datos['nivel_final'], $datos['id_nivel_final']);
            }

            // Actualizar procedimiento
            $procedimiento->update([
                'nombre' => $datos['nombre'] ?? $procedimiento->nombre,
                'id_area' => $datos['id_area'] ?? $procedimiento->id_area,
                'id_subarea' => $datos['id_subarea'] ?? $procedimiento->id_subarea,
                'nivel_final' => $datos['nivel_final'] ?? $procedimiento->nivel_final,
                'id_nivel_final' => $datos['id_nivel_final'] ?? $procedimiento->id_nivel_final,
                'updated_by' => auth()->id()
            ]);

            // Actualizar usuarios asignados si se proporcionaron
            if (isset($datos['usuarios_asignados'])) {
                $this->asignarUsuarios($procedimiento->id, $datos['usuarios_asignados']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Procedimiento actualizado exitosamente',
                'procedimiento' => $procedimiento->fresh(['area', 'subarea', 'nivelFinalModel'])
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar procedimiento
     * 
     * @param int $id ID del procedimiento
     * @return array Resultado de la operación
     */
    public function eliminar(int $id): array
    {
        DB::beginTransaction();
        
        try {
            $procedimiento = Procedimiento::findOrFail($id);

            // Verificar si tiene trámites asociados
            if ($procedimiento->tramites()->exists()) {
                throw new Exception("No se puede eliminar un procedimiento que tiene trámites asociados");
            }

            // Eliminar relaciones con usuarios
            DB::table('procedimiento_usuarios')
                ->where('id_procedimiento', $id)
                ->delete();

            // Eliminar procedimiento
            $procedimiento->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Procedimiento eliminado exitosamente'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Asignar usuarios a un procedimiento
     * 
     * @param int $idProcedimiento ID del procedimiento
     * @param array $usuarios IDs de usuarios a asignar
     * @return array Resultado de la operación
     */
    public function asignarUsuarios(int $idProcedimiento, array $usuarios): array
    {
        DB::beginTransaction();
        
        try {
            // Eliminar asignaciones existentes
            DB::table('procedimiento_usuarios')
                ->where('id_procedimiento', $idProcedimiento)
                ->delete();

            // Crear nuevas asignaciones
            foreach ($usuarios as $idUsuario) {
                DB::table('procedimiento_usuarios')->insert([
                    'id_procedimiento' => $idProcedimiento,
                    'id_usuario' => $idUsuario
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuarios asignados exitosamente'
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener procedimientos por área
     * 
     * @param int $idArea ID del área
     * @return array Procedimientos del área
     */
    public function obtenerPorArea(int $idArea): array
    {
        $procedimientos = Procedimiento::with(['area', 'subarea', 'nivelFinalModel'])
            ->where('id_area', $idArea)
            ->orderBy('nombre')
            ->get();

        return [
            'success' => true,
            'procedimientos' => $procedimientos
        ];
    }

    /**
     * Obtener procedimientos por subárea
     * 
     * @param int $idSubarea ID de la subárea
     * @return array Procedimientos de la subárea
     */
    public function obtenerPorSubarea(int $idSubarea): array
    {
        $procedimientos = Procedimiento::with(['area', 'subarea', 'nivelFinalModel'])
            ->where('id_subarea', $idSubarea)
            ->orderBy('nombre')
            ->get();

        return [
            'success' => true,
            'procedimientos' => $procedimientos
        ];
    }

    /**
     * Obtener procedimientos por nivel final
     * 
     * @param int $nivel Nivel final
     * @param int $idNivel ID del nivel final
     * @return array Procedimientos del nivel
     */
    public function obtenerPorNivelFinal(int $nivel, int $idNivel): array
    {
        $procedimientos = Procedimiento::with(['area', 'subarea', 'nivelFinalModel'])
            ->where('nivel_final', $nivel)
            ->where('id_nivel_final', $idNivel)
            ->orderBy('nombre')
            ->get();

        return [
            'success' => true,
            'procedimientos' => $procedimientos
        ];
    }

    /**
     * Obtener procedimientos asignados a un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @return array Procedimientos asignados
     */
    public function obtenerPorUsuario(int $idUsuario): array
    {
        $procedimientos = Procedimiento::with(['area', 'subarea', 'nivelFinalModel'])
            ->join('procedimiento_usuarios', 'procedimientos.id', '=', 'procedimiento_usuarios.id_procedimiento')
            ->where('procedimiento_usuarios.id_usuario', $idUsuario)
            ->orderBy('procedimientos.nombre')
            ->get();

        return [
            'success' => true,
            'procedimientos' => $procedimientos
        ];
    }

    /**
     * Buscar procedimientos por nombre
     * 
     * @param string $termino Término de búsqueda
     * @return array Procedimientos encontrados
     */
    public function buscarPorNombre(string $termino): array
    {
        $procedimientos = Procedimiento::with(['area', 'subarea', 'nivelFinalModel'])
            ->where('nombre', 'like', '%' . $termino . '%')
            ->orderBy('nombre')
            ->limit(20)
            ->get();

        return [
            'success' => true,
            'procedimientos' => $procedimientos
        ];
    }

    /**
     * Obtener estructura jerárquica de procedimientos
     * 
     * @return array Estructura jerárquica
     */
    public function obtenerEstructuraJerarquica(): array
    {
        $estructura = [];

        // Obtener áreas
        $areas = Nivel1::with(['subareas', 'procedimientos'])->orderBy('nombre')->get();

        foreach ($areas as $area) {
            $areaData = [
                'id' => $area->id,
                'nombre' => $area->nombre,
                'subareas' => []
            ];

            // Obtener subáreas del área
            foreach ($area->subareas as $subarea) {
                $subareaData = [
                    'id' => $subarea->id,
                    'nombre' => $subarea->nombre,
                    'procedimientos' => []
                ];

                // Obtener procedimientos de la subárea
                $procedimientos = Procedimiento::with(['nivelFinalModel'])
                    ->where('id_area', $area->id)
                    ->where('id_subarea', $subarea->id)
                    ->orderBy('nombre')
                    ->get();

                $subareaData['procedimientos'] = $procedimientos;
                $areaData['subareas'][] = $subareaData;
            }

            $estructura[] = $areaData;
        }

        return [
            'success' => true,
            'estructura' => $estructura
        ];
    }

    /**
     * Validar datos del procedimiento
     * 
     * @param array $datos Datos a validar
     * @throws Exception Si hay datos inválidos
     */
    private function validarDatosProcedimiento(array $datos): void
    {
        if (empty($datos['nombre'])) {
            throw new Exception("El nombre del procedimiento es requerido");
        }

        if (strlen($datos['nombre']) > 255) {
            throw new Exception("El nombre del procedimiento no puede exceder 255 caracteres");
        }

        if (!isset($datos['id_area']) || !is_numeric($datos['id_area'])) {
            throw new Exception("El área del procedimiento es requerida");
        }

        if (!isset($datos['id_subarea']) || !is_numeric($datos['id_subarea'])) {
            throw new Exception("La subárea del procedimiento es requerida");
        }

        if (!isset($datos['nivel_final']) || !in_array($datos['nivel_final'], [2, 3, 4])) {
            throw new Exception("El nivel final debe ser 2, 3 o 4");
        }

        if (!isset($datos['id_nivel_final']) || !is_numeric($datos['id_nivel_final'])) {
            throw new Exception("El ID del nivel final es requerido");
        }
    }

    /**
     * Validar que el nivel final exista
     * 
     * @param int $nivel Nivel final
     * @param int $idNivel ID del nivel final
     * @throws Exception Si el nivel no existe
     */
    private function validarNivelFinal(int $nivel, int $idNivel): void
    {
        switch ($nivel) {
            case 2:
                // Nivel 2 no necesita validación adicional
                break;
            case 3:
                if (!Nivel3::where('id', $idNivel)->exists()) {
                    throw new Exception("El nivel 3 especificado no existe");
                }
                break;
            case 4:
                if (!Nivel4::where('id', $idNivel)->exists()) {
                    throw new Exception("El nivel 4 especificado no existe");
                }
                break;
            default:
                throw new Exception("Nivel final inválido");
        }
    }
}