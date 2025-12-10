<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Procedimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para la gestión de áreas administrativas
 * 
 * Este controlador maneja el CRUD completo de áreas y sus relaciones
 * con grupos, subgrupos y tipos de grupos. Proporciona endpoints AJAX
 * para ser consumidos por las vistas de administración.
 * 
 * Funcionalidades principales:
 * - Listar áreas con paginación y búsqueda
 * - Crear nuevas áreas con validación de jerarquía
 * - Actualizar información de áreas existentes
 * - Eliminar áreas (con verificación de dependencias)
 * - Obtener áreas por tipo de grupo
 * - Gestión de grupos, subgrupos y tipos de grupos
 * 
 * @package App\Http\Controllers\Admin
 */
class AreasController extends Controller
{
    /**
     * Mostrar lista de áreas (Nivel 1)
     * Equivalente a adminAreas.php del PHP puro
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $areas = Nivel1::orderBy('nombre')->get();
        
        return view('admin.areas.index', compact('areas'));
    }

    /**
     * Obtener todas las áreas (AJAX)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerAreas()
    {
        try {
            $areas = Nivel1::orderBy('nombre')->get(['id', 'nombre']);
            
            return response()->json([
                'success' => true,
                'data' => $areas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener áreas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener áreas por tipo de grupo
     * 
     * @param int $tipoGrupoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerPorTipoGrupo($tipoGrupoId)
    {
        // Implementar: retornar áreas filtradas por tipo_grupo_id
        // Usar en: select dinámicos de procedimientos
    }

    /**
     * Crear nueva área (Nivel 1)
     * Equivalente a action=create_nivel1 en adminAreas.php del PHP puro
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:55|unique:nivel_1,nombre',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $area = Nivel1::create([
                'nombre' => strtoupper(trim($request->nombre)),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Área creada exitosamente',
                'data' => $area
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el área: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar área existente (Nivel 1)
     * Equivalente a action=edit_nivel1 en adminAreas.php del PHP puro
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $area = Nivel1::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => ['required', 'string', 'max:55', \Illuminate\Validation\Rule::unique('nivel_1', 'nombre')->ignore($id)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $area->update([
                'nombre' => strtoupper(trim($request->nombre)),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Área actualizada exitosamente',
                'data' => $area
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el área: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar área (Nivel 1)
     * Equivalente a action=delete_nivel1 en adminAreas.php del PHP puro
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $area = Nivel1::findOrFail($id);

            // Verificar dependencias
            $nivel2Count = Nivel2::where('id_nivel1', $id)->count();
            $procedimientosCount = Procedimiento::where('id_area', $id)->count();

            if ($nivel2Count > 0 || $procedimientosCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el área porque tiene registros dependientes (Niveles 2: ' . $nivel2Count . ', Procedimientos: ' . $procedimientosCount . ')'
                ], 422);
            }

            $area->delete();

            return response()->json([
                'success' => true,
                'message' => 'Área eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el área: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener tipos de grupos
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerTiposGrupos()
    {
        // Implementar: retornar todos los tipos de grupos
        // Usar en: select de formularios
    }

    /**
     * Obtener grupos por tipo
     * 
     * @param int $tipoGrupoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerGrupos($tipoGrupoId)
    {
        // Implementar: retornar grupos filtrados por tipo_grupo_id
    }

    /**
     * Obtener subgrupos por grupo
     * 
     * @param int $grupoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerSubgrupos($grupoId)
    {
        // Implementar: retornar subgrupos filtrados por grupo_id
    }

    /**
     * Verificar dependencias antes de eliminar
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarDependencias($id)
    {
        try {
            $nivel2Count = Nivel2::where('id_nivel1', $id)->count();
            $procedimientosCount = Procedimiento::where('id_area', $id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'nivel2_count' => $nivel2Count,
                    'procedimientos_count' => $procedimientosCount,
                    'total' => $nivel2Count + $procedimientosCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar dependencias: ' . $e->getMessage()
            ], 500);
        }
    }
}