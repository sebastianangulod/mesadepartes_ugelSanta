<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use App\Models\UsuariosNiveles;
use App\Models\Procedimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para la gestión de la jerarquía de niveles administrativos
 * 
 * Este controlador maneja la estructura jerárquica de niveles 1-4 que conforman
 * la organización administrativa del sistema. Gestiona las relaciones padre-hijo
 * entre los diferentes niveles y proporciona endpoints AJAX para la carga dinámica.
 * 
 * Funcionalidades principales:
 * - Gestión completa de niveles 1, 2, 3 y 4
 * - Relaciones jerárquicas entre niveles
 * - Endpoints AJAX para selects dinámicos
 * - Validación de consistencia jerárquica
 * - Prevención de ciclos y relaciones inválidas
 * 
 * @package App\Http\Controllers\Admin
 */
class NivelesController extends Controller
{
    /**
     * Mostrar vista de gestión de niveles
     * Equivalente a adminNiveles.php del PHP puro
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $niveles1 = Nivel1::with(['nivel2.nivel3.nivel4'])->orderBy('nombre')->get();
        
        return view('admin.niveles.index', compact('niveles1'));
    }

    /**
     * Obtener todos los niveles 1 (raíz de la jerarquía)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerNivel1()
    {
        // Implementar: retornar JSON con niveles 1
        // Formato: [{id, nombre, descripcion, estado}]
        // Orden: por nombre ascendente
    }

    /**
     * Obtener niveles 2 por nivel 1 (AJAX)
     * 
     * @param int $nivel1Id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerNivel2($nivel1Id)
    {
        try {
            $niveles2 = Nivel2::where('id_nivel1', $nivel1Id)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => $niveles2
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles 2: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener niveles 3 por nivel 2 (AJAX)
     * 
     * @param int $nivel2Id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerNivel3($nivel2Id)
    {
        try {
            $niveles3 = Nivel3::where('id_nivel2', $nivel2Id)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => $niveles3
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles 3: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener niveles 4 por nivel 3 (AJAX)
     * 
     * @param int $nivel3Id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerNivel4($nivel3Id)
    {
        try {
            $niveles4 = Nivel4::where('id_nivel3', $nivel3Id)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json([
                'success' => true,
                'data' => $niveles4
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles 4: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo nivel (2, 3 o 4)
     * Equivalente a action=create en adminNiveles.php del PHP puro
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nivel' => 'required|string|in:nivel_2,nivel_3,nivel_4',
            'nombre' => 'required|string|max:55',
            'id_parent' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $nivel = $request->nivel;
            $nombre = strtoupper(trim($request->nombre));
            $idParent = $request->id_parent;

            // Validar que el padre existe
            $parentTable = str_replace('nivel_', 'nivel_', substr($nivel, 0, -1)) . (substr($nivel, -1) - 1);
            $parentModel = $this->getModelForTable($parentTable);
            
            if (!$parentModel::find($idParent)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El nivel padre no existe'
                ], 422);
            }

            // Crear el nivel según el tipo
            switch ($nivel) {
                case 'nivel_2':
                    $nuevoNivel = Nivel2::create([
                        'nombre' => $nombre,
                        'id_nivel1' => $idParent,
                    ]);
                    break;
                case 'nivel_3':
                    $nuevoNivel = Nivel3::create([
                        'nombre' => $nombre,
                        'id_nivel2' => $idParent,
                    ]);
                    break;
                case 'nivel_4':
                    $nuevoNivel = Nivel4::create([
                        'nombre' => $nombre,
                        'id_nivel3' => $idParent,
                    ]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Nivel creado exitosamente',
                'data' => $nuevoNivel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener modelo según tabla
     */
    private function getModelForTable($table)
    {
        $models = [
            'nivel_1' => Nivel1::class,
            'nivel_2' => Nivel2::class,
            'nivel_3' => Nivel3::class,
            'nivel_4' => Nivel4::class,
        ];

        return $models[$table] ?? null;
    }

    /**
     * Actualizar nivel existente
     * Equivalente a action=update en adminNiveles.php del PHP puro
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nivel' => 'required|string|in:nivel_2,nivel_3,nivel_4',
            'nombre' => 'required|string|max:55',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $nivel = $request->nivel;
            $nombre = strtoupper(trim($request->nombre));

            // Obtener y actualizar el nivel según el tipo
            switch ($nivel) {
                case 'nivel_2':
                    $nivelModel = Nivel2::findOrFail($id);
                    break;
                case 'nivel_3':
                    $nivelModel = Nivel3::findOrFail($id);
                    break;
                case 'nivel_4':
                    $nivelModel = Nivel4::findOrFail($id);
                    break;
            }

            $nivelModel->update(['nombre' => $nombre]);

            return response()->json([
                'success' => true,
                'message' => 'Nivel actualizado exitosamente',
                'data' => $nivelModel
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar nivel
     * Equivalente a action=delete en adminNiveles.php del PHP puro
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nivel' => 'required|string|in:nivel_2,nivel_3,nivel_4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $nivel = $request->nivel;

            // Obtener el nivel según el tipo
            switch ($nivel) {
                case 'nivel_2':
                    $nivelModel = Nivel2::findOrFail($id);
                    // Verificar hijos (nivel 3)
                    $hijosCount = Nivel3::where('id_nivel2', $id)->count();
                    break;
                case 'nivel_3':
                    $nivelModel = Nivel3::findOrFail($id);
                    // Verificar hijos (nivel 4)
                    $hijosCount = Nivel4::where('id_nivel3', $id)->count();
                    break;
                case 'nivel_4':
                    $nivelModel = Nivel4::findOrFail($id);
                    $hijosCount = 0; // Nivel 4 no tiene hijos
                    break;
            }

            // Verificar usuarios asignados
            $usuariosCount = UsuariosNiveles::where('nivel', substr($nivel, -1))
                ->where('id_nivel', $id)
                ->count();

            // Verificar procedimientos (solo para niveles que se usan en procedimientos)
            $procedimientosCount = 0;
            if ($nivel === 'nivel_2') {
                $procedimientosCount = Procedimiento::where('id_subarea', $id)->count();
            }

            if ($hijosCount > 0 || $usuariosCount > 0 || $procedimientosCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar el nivel porque tiene registros dependientes (Hijos: {$hijosCount}, Usuarios: {$usuariosCount}, Procedimientos: {$procedimientosCount})"
                ], 422);
            }

            $nivelModel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Nivel eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el nivel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estructura jerárquica completa
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerEstructuraCompleta()
    {
        // Implementar: retornar árbol completo de niveles
        // Formato: nested JSON con todos los niveles
        // Usar en: visualización de jerarquía
    }

    /**
     * Validar consistencia jerárquica
     * 
     * @param int $nivel
     * @param int $padreId
     * @return \Illuminate\Http\JsonResponse
     */
    public function validarJerarquia($nivel, $padreId)
    {
        // Implementar: validar que la relación padre-hijo es válida
        // Verificar: existe cadena completa hasta nivel 1
        // Verificar: todos los ancestros están activos
        // Retornar: true/false con mensaje
    }

    /**
     * Obtener niveles para select dinámico
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerParaSelect(Request $request)
    {
        // Implementar: retornar niveles para selects dependientes
        // Parámetros: nivel_actual, padre_id
        // Formato: optimizado para select2 o similar
    }

    /**
     * Verificar dependencias antes de eliminar
     * 
     * @param int $id
     * @param int $nivel
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarDependencias($id, $nivel)
    {
        // Implementar: contar dependencias según nivel
        // Nivel 1: verificar niveles 2, usuarios, procedimientos
        // Nivel 2: verificar niveles 3, usuarios, procedimientos
        // Nivel 3: verificar niveles 4, usuarios, procedimientos
        // Nivel 4: verificar usuarios, procedimientos
    }
}