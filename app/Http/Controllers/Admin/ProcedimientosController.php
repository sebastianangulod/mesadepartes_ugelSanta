<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procedimiento;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use App\Models\Tramite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Controlador para la gestión de procedimientos
 * Equivalente a adminProcedimientos.php del PHP puro
 * 
 * @package App\Http\Controllers\Admin
 */
class ProcedimientosController extends Controller
{
    /**
     * Mostrar lista de procedimientos
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $nivel1 = Nivel1::orderBy('nombre')->get();
        
        $procedimientos = Procedimiento::with(['area', 'subarea', 'nivelFinal'])
            ->orderBy('nombre')
            ->get();

        // Agregar nombres de niveles finales
        foreach ($procedimientos as $proc) {
            if ($proc->nivel_final == '3') {
                $nivel3 = Nivel3::find($proc->id_nivel_final);
                $proc->nivel_final_nombre = $nivel3 ? $nivel3->nombre : null;
            } elseif ($proc->nivel_final == '4') {
                $nivel4 = Nivel4::find($proc->id_nivel_final);
                $proc->nivel_final_nombre = $nivel4 ? $nivel4->nombre : null;
            }
        }

        return view('admin.procedimientos.index', compact('procedimientos', 'nivel1'));
    }

    /**
     * Crear nuevo procedimiento
     * Equivalente a action=crear en adminProcedimientos.php del PHP puro
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'id_area' => 'required|exists:nivel_1,id',
            'id_subarea' => 'required|exists:nivel_2,id',
            'nivel_final' => 'required|in:2,3,4',
            'id_nivel_final' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Validar que el nivel final existe según el tipo
            $nivelFinal = $request->nivel_final;
            $idNivelFinal = $request->id_nivel_final;

            if ($nivelFinal == '3') {
                if (!Nivel3::find($idNivelFinal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nivel 3 especificado no existe'
                    ], 422);
                }
            } elseif ($nivelFinal == '4') {
                $nivel4 = Nivel4::find($idNivelFinal);
                if (!$nivel4) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nivel 4 especificado no existe'
                    ], 422);
                }
                // Validar que el nivel 4 tenga un nivel 3 relacionado
                if (!$nivel4->id_nivel3) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nivel 4 debe tener un nivel 3 relacionado'
                    ], 422);
                }
            }

            DB::beginTransaction();

            $procedimiento = Procedimiento::create([
                'nombre' => strtoupper(trim($request->nombre)),
                'id_area' => $request->id_area,
                'id_subarea' => $request->id_subarea,
                'nivel_final' => $nivelFinal,
                'id_nivel_final' => $idNivelFinal,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procedimiento creado correctamente',
                'data' => $procedimiento
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear procedimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar procedimiento existente
     * Equivalente a action=editar en adminProcedimientos.php del PHP puro
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $procedimiento = Procedimiento::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'id_area' => 'required|exists:nivel_1,id',
            'id_subarea' => 'required|exists:nivel_2,id',
            'nivel_final' => 'required|in:2,3,4',
            'id_nivel_final' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            // Validar que el nivel final existe según el tipo
            $nivelFinal = $request->nivel_final;
            $idNivelFinal = $request->id_nivel_final;

            if ($nivelFinal == '3') {
                if (!Nivel3::find($idNivelFinal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nivel 3 especificado no existe'
                    ], 422);
                }
            } elseif ($nivelFinal == '4') {
                if (!Nivel4::find($idNivelFinal)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El nivel 4 especificado no existe'
                    ], 422);
                }
            }

            DB::beginTransaction();

            $procedimiento->update([
                'nombre' => strtoupper(trim($request->nombre)),
                'id_area' => $request->id_area,
                'id_subarea' => $request->id_subarea,
                'nivel_final' => $nivelFinal,
                'id_nivel_final' => $idNivelFinal,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procedimiento actualizado correctamente',
                'data' => $procedimiento
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar procedimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar procedimiento
     * Equivalente a action=eliminar en adminProcedimientos.php del PHP puro
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $procedimiento = Procedimiento::findOrFail($id);

            // Verificar si hay trámites asociados
            $tramitesCount = Tramite::where('id_procedimiento', $id)->count();

            if ($tramitesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el procedimiento porque tiene ' . $tramitesCount . ' trámite(s) asociado(s)'
                ], 422);
            }

            $procedimiento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Procedimiento eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar procedimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener procedimiento por ID (AJAX)
     * Equivalente a action=obtener en adminProcedimientos.php del PHP puro
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtener($id)
    {
        try {
            $procedimiento = Procedimiento::with(['area', 'subarea', 'nivelFinal'])
                ->findOrFail($id);

            $data = $procedimiento->toArray();

            // Agregar información adicional según el nivel final
            if ($procedimiento->nivel_final == '3') {
                $nivel3 = Nivel3::find($procedimiento->id_nivel_final);
                if ($nivel3) {
                    $data['id_nivel3'] = $nivel3->id;
                    $data['nivel_final_nombre'] = $nivel3->nombre;
                }
            } elseif ($procedimiento->nivel_final == '4') {
                $nivel4 = Nivel4::with('nivel3')->find($procedimiento->id_nivel_final);
                if ($nivel4) {
                    $data['id_nivel4'] = $nivel4->id;
                    $data['nivel_final_nombre'] = $nivel4->nombre;
                    if ($nivel4->nivel3) {
                        $data['id_nivel3'] = $nivel4->nivel3->id;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener procedimiento: ' . $e->getMessage()
            ], 500);
        }
    }
}

