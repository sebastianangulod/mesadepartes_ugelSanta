<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procedimiento;
use App\Models\ProcedimientoVersion;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use App\Models\Tramite;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
use App\Models\FlujoNodo;
use App\Models\FlujoEquipo;
use App\Models\FlujoTransicion;
use App\Models\FlujoEquipoUsuario;
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
            $idArea = $request->id_area;
            $idSubarea = $request->id_subarea;

            // Validar coherencia jerárquica del nivel_final
            $errorJerarquia = $this->validarCoherenciaNivelFinal($nivelFinal, $idNivelFinal, $idArea, $idSubarea);
            if ($errorJerarquia) {
                return response()->json([
                    'success' => false,
                    'message' => $errorJerarquia
                ], 422);
            }

            DB::beginTransaction();

            $procedimiento = Procedimiento::create([
                'nombre' => trim($request->nombre),
                'id_area' => $request->id_area,
                'id_subarea' => $request->id_subarea,
                'nivel_final' => $nivelFinal,
                'id_nivel_final' => $idNivelFinal,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            // Crear versión inicial
            $this->crearVersion($procedimiento, 'Creación inicial');

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
            $idArea = $request->id_area;
            $idSubarea = $request->id_subarea;

            // Validar coherencia jerárquica del nivel_final
            $errorJerarquia = $this->validarCoherenciaNivelFinal($nivelFinal, $idNivelFinal, $idArea, $idSubarea);
            if ($errorJerarquia) {
                return response()->json([
                    'success' => false,
                    'message' => $errorJerarquia
                ], 422);
            }

            DB::beginTransaction();

            // Guardar cambios para versión
            $cambios = [];
            if ($procedimiento->nombre != $request->nombre) $cambios[] = "Nombre: {$procedimiento->nombre} → {$request->nombre}";
            if ($procedimiento->id_area != $request->id_area) $cambios[] = "Área cambiada";
            if ($procedimiento->id_subarea != $request->id_subarea) $cambios[] = "Subárea cambiada";
            if ($procedimiento->nivel_final != $request->nivel_final) $cambios[] = "Nivel final: {$procedimiento->nivel_final} → {$request->nivel_final}";
            if ($procedimiento->id_nivel_final != $request->id_nivel_final) $cambios[] = "Nivel final ID: {$procedimiento->id_nivel_final} → {$request->id_nivel_final}";

            $procedimiento->update([
                'nombre' => trim($request->nombre),
                'id_area' => $request->id_area,
                'id_subarea' => $request->id_subarea,
                'nivel_final' => $nivelFinal,
                'id_nivel_final' => $idNivelFinal,
                'updated_by' => auth()->id(),
            ]);

            // Crear versión con los cambios
            if (!empty($cambios)) {
                $this->crearVersion($procedimiento, implode('; ', $cambios));
            }

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
     * Obtener información de elementos relacionados que se eliminarán en cascada
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDependencias($id)
    {
        try {
            $procedimiento = Procedimiento::findOrFail($id);
            
            $dependencias = [
                'tramites' => Tramite::where('id_procedimiento', $id)->count(),
                'usuarios_asignados' => $procedimiento->usuariosAsignados()->count(),
            ];
            
            $total = array_sum($dependencias);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'dependencias' => $dependencias,
                    'total' => $total,
                    'tiene_dependencias' => $total > 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dependencias: ' . $e->getMessage()
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
     * Usa los nodos del flujo con puede_finalizar=true para determinar niveles válidos
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerEspecialistasDisponibles($id)
    {
        try {
            $procedimiento = Procedimiento::findOrFail($id);
            
            // Obtener niveles de los nodos que pueden finalizar
            $nivelesFinales = $this->obtenerNivelesFinalesDelFlujo($procedimiento);
            
            if (empty($nivelesFinales)) {
                return response()->json([
                    'success' => true,
                    'usuarios' => [],
                    'usuariosAsignados' => []
                ]);
            }
            
            // Obtener usuarios internos que tengan CUALQUIERA de los niveles finales
            $usuarios = Usuario::where('tipo_user', 2)
                ->whereHas('usuariosNiveles', function($query) use ($nivelesFinales) {
                    $query->whereIn('nivel', array_keys($nivelesFinales))
                          ->where(function($q) use ($nivelesFinales) {
                              foreach ($nivelesFinales as $nivel => $ids) {
                                  $q->orWhere(function($sub) use ($nivel, $ids) {
                                      $sub->where('nivel', $nivel)->whereIn('id_nivel', $ids);
                                  });
                              }
                          });
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
     * Obtiene los niveles (1-4) y sus IDs que corresponden a nodos con puede_finalizar=true
     * 
     * @param Procedimiento $procedimiento
     * @return array<int, array<int>> [nivel => [id_nivel1, id_nivel2, ...]]
     */
    private function obtenerNivelesFinalesDelFlujo(Procedimiento $procedimiento): array
    {
        $nodosFinales = $procedimiento->flujoNodos()
            ->where('puede_finalizar', true)
            ->get(['nombre', 'nombre_tecnico']);
        
        $niveles = [];
        
        foreach ($nodosFinales as $nodo) {
            // El nombre técnico tiene formato: "Nivel X - NombreNivel"
            if (preg_match('/^Nivel (\d+) - (.+)$/', $nodo->nombre_tecnico ?? $nodo->nombre, $matches)) {
                $nivelNumero = (int) $matches[1];
                $nivelNombre = $matches[2];
                
                // Buscar el ID del nivel en la tabla correspondiente
                $idNivel = null;
                switch ($nivelNumero) {
                    case 1:
                        $idNivel = Nivel1::where('nombre', $nivelNombre)->value('id');
                        break;
                    case 2:
                        $idNivel = Nivel2::where('nombre', $nivelNombre)->value('id');
                        break;
                    case 3:
                        $idNivel = Nivel3::where('nombre', $nivelNombre)->value('id');
                        break;
                    case 4:
                        $idNivel = Nivel4::where('nombre', $nivelNombre)->value('id');
                        break;
                }
                
                if ($idNivel) {
                    $niveles[$nivelNumero][] = $idNivel;
                }
            }
        }
        
        // Eliminar duplicados
        foreach ($niveles as $nivel => $ids) {
            $niveles[$nivel] = array_unique($ids);
        }
        
        return $niveles;
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

                // Verificar que el usuario tenga al menos uno de los niveles finales del flujo
                $nivelesFinales = $this->obtenerNivelesFinalesDelFlujo($procedimiento);
                
                $tieneNivelValido = false;
                foreach ($nivelesFinales as $nivel => $ids) {
                    $existe = UsuariosNiveles::where('id_usuario', $idUsuario)
                        ->where('nivel', $nivel)
                        ->whereIn('id_nivel', $ids)
                        ->exists();
                    if ($existe) {
                        $tieneNivelValido = true;
                        break;
                    }
                }

                if (!$tieneNivelValido) {
                    continue; // Saltar usuarios que no tienen ningún nivel final válido
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

    /**
     * Clonar procedimiento (copiar estructura + flujo)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function clonar(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $original = Procedimiento::with([
                'flujoNodos.equipos',
                'flujoNodos.transicionesDesde',
            ])->findOrFail($id);

            // Crear nuevo procedimiento con los mismos datos pero nuevo nombre
            $nuevoProcedimiento = Procedimiento::create([
                'nombre' => $request->nombre,
                'descripcion' => $original->descripcion,
                'id_area' => $original->id_area,
                'id_subarea' => $original->id_subarea,
                'nivel_final' => $original->nivel_final,
                'id_nivel_final' => $original->id_nivel_final,
                'created_by' => auth()->id(),
            ]);

            // Mapear nodos originales a nuevos
            $mapaNodos = [];
            foreach ($original->flujoNodos as $nodoOriginal) {
                $nuevoNodo = FlujoNodo::create([
                    'id_procedimiento' => $nuevoProcedimiento->id,
                    'nombre' => $nodoOriginal->nombre,
                    'slug' => $nodoOriginal->slug,
                    'orden' => $nodoOriginal->orden,
                    'puede_finalizar' => $nodoOriginal->puede_finalizar,
                    'puede_rechazar_definitivo' => $nodoOriginal->puede_rechazar_definitivo,
                    'es_terminal' => $nodoOriginal->es_terminal,
                ]);
                $mapaNodos[$nodoOriginal->id] = $nuevoNodo->id;

                // Clonar equipos
                foreach ($nodoOriginal->equipos as $equipoOriginal) {
                    $nuevoEquipo = FlujoEquipo::create([
                        'nodo_id' => $nuevoNodo->id,
                        'nombre' => $equipoOriginal->nombre,
                        'nombre_tecnico' => $equipoOriginal->nombre_tecnico,
                        'nombre_visible' => $equipoOriginal->nombre_visible,
                    ]);

                    // Clonar miembros
                    foreach ($equipoOriginal->miembros as $miembro) {
                        FlujoEquipoUsuario::create([
                            'equipo_id' => $nuevoEquipo->id,
                            'usuario_id' => $miembro->pivot->usuario_id,
                        ]);
                    }
                }
            }

            // Clonar transiciones
            foreach ($original->flujoNodos as $nodoOriginal) {
                foreach ($nodoOriginal->transicionesDesde as $transicion) {
                    if (isset($mapaNodos[$transicion->hacia_nodo_id])) {
                        FlujoTransicion::create([
                            'desde_nodo_id' => $mapaNodos[$transicion->desde_nodo_id],
                            'hacia_nodo_id' => $mapaNodos[$transicion->hacia_nodo_id],
                            'es_default_auto' => $transicion->es_default_auto,
                        ]);
                    }
                }
            }

            // Actualizar nodo de entrada si existe
            if ($original->id_nodo_entrada && isset($mapaNodos[$original->id_nodo_entrada])) {
                $nuevoProcedimiento->update(['id_nodo_entrada' => $mapaNodos[$original->id_nodo_entrada]]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Procedimiento clonado exitosamente',
                'data' => $nuevoProcedimiento->load('flujoNodos.equipos')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al clonar procedimiento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener historial de versiones de un procedimiento
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function historialVersiones($id)
    {
        try {
            $versiones = ProcedimientoVersion::where('id_procedimiento', $id)
                ->with('creador:id,nombres,apellido_paterno,apellido_materno')
                ->orderByDesc('version')
                ->get(['id', 'id_procedimiento', 'version', 'nombre', 'cambios', 'created_by', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => $versiones->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'version' => $v->version,
                        'nombre' => $v->nombre,
                        'cambios' => $v->cambios,
                        'creador' => $v->creador ? $v->creador->nombres . ' ' . $v->creador->apellido_paterno : 'Sistema',
                        'fecha' => $v->created_at->format('d/m/Y H:i:s'),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener historial: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurar una versión anterior del procedimiento
     * 
     * @param Request $request
     * @param int $id
     * @param int $versionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function restaurarVersion(Request $request, $id, $versionId)
    {
        try {
            DB::beginTransaction();

            $version = ProcedimientoVersion::where('id_procedimiento', $id)
                ->where('id', $versionId)
                ->firstOrFail();

            $procedimiento = Procedimiento::findOrFail($id);

            // Crear versión actual antes de restaurar
            $this->crearVersion($procedimiento, 'Restauración automática antes de revertir a v' . $version->version);

            // Restaurar datos
            $procedimiento->update([
                'nombre' => $version->nombre,
                'descripcion' => $version->descripcion,
                'id_area' => $version->id_area,
                'id_subarea' => $version->id_subarea,
                'nivel_final' => $version->nivel_final,
                'id_nivel_final' => $version->id_nivel_final,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Versión restaurada exitosamente',
                'data' => $procedimiento->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar versión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear versión del procedimiento (interno)
     * 
     * @param Procedimiento $procedimiento
     * @param string $cambios
     * @return ProcedimientoVersion
     */
    private function crearVersion(Procedimiento $procedimiento, string $cambios = ''): ProcedimientoVersion
    {
        $ultimaVersion = ProcedimientoVersion::where('id_procedimiento', $procedimiento->id)
            ->max('version') ?? 0;

        // Obtener configuración del flujo
        $flujoConfig = null;
        $nodos = $procedimiento->flujoNodos()->with(['equipos.miembros', 'transicionesDesde'])->get();
        if ($nodos->isNotEmpty()) {
            $flujoConfig = [
                'nodos' => $nodos->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'nombre' => $n->nombre,
                        'slug' => $n->slug,
                        'orden' => $n->orden,
                        'puede_finalizar' => $n->puede_finalizar,
                        'puede_rechazar_definitivo' => $n->puede_rechazar_definitivo,
                        'es_terminal' => $n->es_terminal,
                        'equipos' => $n->equipos->map(function ($e) {
                            return [
                                'id' => $e->id,
                                'nombre' => $e->nombre,
                                'nombre_tecnico' => $e->nombre_tecnico,
                                'nombre_visible' => $e->nombre_visible,
                                'miembros' => $e->miembros->pluck('id')->toArray(),
                            ];
                        }),
                        'transiciones' => $n->transicionesDesde->map(function ($t) {
                            return [
                                'hacia_nodo_id' => $t->hacia_nodo_id,
                                'es_default_auto' => $t->es_default_auto,
                            ];
                        }),
                    ];
                }),
                'id_nodo_entrada' => $procedimiento->id_nodo_entrada,
            ];
        }

return ProcedimientoVersion::create([
            'id_procedimiento' => $procedimiento->id,
            'version' => $ultimaVersion + 1,
            'nombre' => $procedimiento->nombre,
            'descripcion' => $procedimiento->descripcion,
            'id_area' => $procedimiento->id_area,
            'id_subarea' => $procedimiento->id_subarea,
            'nivel_final' => $procedimiento->nivel_final,
            'id_nivel_final' => $procedimiento->id_nivel_final,
            'flujo_config' => $flujoConfig,
            'created_by' => auth()->id(),
            'cambios' => $cambios,
        ]);
    }

    /**
     * Validar coherencia jerárquica del nivel_final
     * Verifica que el id_nivel_final pertenezca a la jerarquía definida por id_area e id_subarea
     * 
     * @param string $nivelFinal
     * @param int $idNivelFinal
     * @param int $idArea
     * @param int $idSubarea
     * @return string|null Mensaje de error o null si es válido
     */
    private function validarCoherenciaNivelFinal(string $nivelFinal, int $idNivelFinal, int $idArea, int $idSubarea): ?string
    {
        if ($nivelFinal === '3') {
            $nivel3 = Nivel3::find($idNivelFinal);
            if (!$nivel3) {
                return 'El nivel 3 especificado no existe';
            }
            // Verificar que el nivel 3 pertenezca al nivel 2 (subárea) seleccionado
            if ($nivel3->id_nivel2 != $idSubarea) {
                return 'El nivel 3 seleccionado no pertenece a la subárea (Nivel 2) seleccionada';
            }
            // Verificar que el nivel 2 pertenezca al nivel 1 (área) seleccionado
            $nivel2 = Nivel2::find($idSubarea);
            if ($nivel2 && $nivel2->id_nivel1 != $idArea) {
                return 'La subárea (Nivel 2) seleccionada no pertenece al área (Nivel 1) seleccionada';
            }
        } elseif ($nivelFinal === '4') {
            $nivel4 = Nivel4::find($idNivelFinal);
            if (!$nivel4) {
                return 'El nivel 4 especificado no existe';
            }
            if (!$nivel4->id_nivel3) {
                return 'El nivel 4 debe tener un nivel 3 relacionado';
            }
            // Verificar que el nivel 4 pertenezca a la jerarquía correcta
            $nivel3 = Nivel3::find($nivel4->id_nivel3);
            if ($nivel3 && $nivel3->id_nivel2 != $idSubarea) {
                return 'El nivel 4 seleccionado no pertenece a la subárea (Nivel 2) seleccionada';
            }
            // Verificar que el nivel 2 pertenezca al nivel 1 (área) seleccionado
            $nivel2 = Nivel2::find($idSubarea);
            if ($nivel2 && $nivel2->id_nivel1 != $idArea) {
                return 'La subárea (Nivel 2) seleccionada no pertenece al área (Nivel 1) seleccionada';
            }
}
        return null;
    }
}