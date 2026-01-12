<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procedimiento;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use App\Models\Tramite;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
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
        
        // Cargar procedimientos con relaciones básicas (sin nivelFinal que causa problemas)
        $procedimientos = Procedimiento::with(['area', 'subarea'])
            ->orderBy('nombre')
            ->get();

        // Agregar nombres de niveles finales manualmente
        foreach ($procedimientos as $proc) {
            if ($proc->nivel_final == '2') {
                // Si el nivel final es 2, usar la subarea (nivel2)
                $proc->nivel_final_nombre = $proc->subarea ? $proc->subarea->nombre : null;
            } elseif ($proc->nivel_final == '3') {
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
            'nivel_final' => 'required|in:3,4', // Solo nivel 3 o 4
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
                'nombre' => trim($request->nombre),
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
            'nivel_final' => 'required|in:3,4', // Solo nivel 3 o 4
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
                'nombre' => trim($request->nombre),
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
            // Cargar procedimiento sin la relación nivelFinal que causa problemas
            $procedimiento = Procedimiento::with(['area', 'subarea'])
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

    /**
     * Obtener usuarios del nivel final de un procedimiento (especialistas disponibles)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerEspecialistasDisponibles($id)
    {
        try {
            $procedimiento = Procedimiento::findOrFail($id);
            
            // Obtener usuarios internos del nivel final del procedimiento
            $nivelFinal = (int)$procedimiento->nivel_final;
            $idNivelFinal = $procedimiento->id_nivel_final;
            
            // Obtener usuarios que tienen asignado el nivel final del procedimiento
            $usuarios = Usuario::where('tipo_user', 2)
                ->whereHas('usuariosNiveles', function($query) use ($nivelFinal, $idNivelFinal) {
                    $query->where('nivel', $nivelFinal)
                          ->where('id_nivel', $idNivelFinal);
                })
                ->select('id', 'nombres', 'apellido_paterno', 'apellido_materno', 'email')
                ->orderBy('nombres')
                ->orderBy('apellido_paterno')
                ->get();
            
            // Obtener usuarios ya asignados a este procedimiento
            $usuariosAsignados = DB::table('procedimiento_usuarios')
                ->where('id_procedimiento', $id)
                ->pluck('id_usuario')
                ->toArray();
            
            return response()->json([
                'success' => true,
                'usuarios' => $usuarios,
                'usuariosAsignados' => $usuariosAsignados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especialistas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar especialistas a un procedimiento
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignarEspecialistas(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'usuarios' => 'required|array',
            'usuarios.*' => 'required|integer|exists:usuarios,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $procedimiento = Procedimiento::findOrFail($id);
            $usuarios = $request->usuarios;

            DB::beginTransaction();

            // Eliminar asignaciones existentes
            DB::table('procedimiento_usuarios')
                ->where('id_procedimiento', $id)
                ->delete();

            // Crear nuevas asignaciones
            foreach ($usuarios as $idUsuario) {
                // Verificar que el usuario sea interno
                $usuario = Usuario::find($idUsuario);
                if (!$usuario || $usuario->tipo_user != 2) {
                    continue; // Saltar usuarios que no son internos
                }

                // Verificar que el usuario tenga el nivel correcto
                $usuarioNivel = UsuariosNiveles::where('id_usuario', $idUsuario)
                    ->where('nivel', $procedimiento->nivel_final)
                    ->where('id_nivel', $procedimiento->id_nivel_final)
                    ->first();

                if (!$usuarioNivel) {
                    continue; // Saltar usuarios que no tienen el nivel correcto
                }

                DB::table('procedimiento_usuarios')->insert([
                    'id_procedimiento' => $id,
                    'id_usuario' => $idUsuario
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Especialistas asignados correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar especialistas: ' . $e->getMessage()
            ], 500);
        }
    }
}

