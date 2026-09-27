<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlujoEquipo;
use App\Models\FlujoNodo;
use App\Models\FlujoTransicion;
use App\Models\Procedimiento;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CRUD del flujo configurable (nodos, transiciones, equipos) por procedimiento.
 */
class FlujoProcedimientoController extends Controller
{
    public function edit(Procedimiento $procedimiento)
    {
        return view('admin.procedimientos.flujo', ['procedimiento' => $procedimiento]);
    }

    public function estructura(Procedimiento $procedimiento): JsonResponse
    {
        $nodos = FlujoNodo::query()
            ->where('id_procedimiento', $procedimiento->id)
            ->with([
                'transicionesDesde.nodoHacia',
                'equipos.miembros',
            ])
            ->orderBy('orden')
            ->orderBy('id')
            ->get();

        return response()->json([
            'procedimiento' => [
                'id' => $procedimiento->id,
                'nombre' => $procedimiento->nombre,
                'id_nodo_entrada' => $procedimiento->id_nodo_entrada,
            ],
            'nodos' => $nodos->map(function (FlujoNodo $n) {
                return [
                    'id' => $n->id,
                    'nombre' => $n->nombre,
                    'slug' => $n->slug,
                    'orden' => $n->orden,
                    'puede_finalizar' => (bool) $n->puede_finalizar,
                    'puede_rechazar_definitivo' => (bool) $n->puede_rechazar_definitivo,
                    'es_terminal' => (bool) $n->es_terminal,
                    'transiciones' => $n->transicionesDesde->map(static function (FlujoTransicion $t) {
                        return [
                            'id' => $t->id,
                            'hacia_nodo_id' => $t->hacia_nodo_id,
                            'nombre_destino' => $t->nodoHacia?->nombre,
                            'es_default_auto' => (bool) $t->es_default_auto,
                        ];
                    }),
                    'equipos' => $n->equipos->map(function (FlujoEquipo $eq) {
                        return [
                            'id' => $eq->id,
                            'nombre' => $eq->nombre,
                            'miembros' => $eq->miembros->map(static function (Usuario $u) {
                                return [
                                    'id' => $u->id,
                                    'nombre_completo' => $u->nombre_completo,
                                    'email' => $u->email,
                                ];
                            }),
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function usuariosInternos(): JsonResponse
    {
        $usuarios = Usuario::query()
            ->where('tipo_user', 2)
            ->orderBy('apellido_paterno')
            ->orderBy('nombres')
            ->limit(500)
            ->get(['id', 'nombres', 'apellido_paterno', 'apellido_materno', 'email']);

        return response()->json([
            'usuarios' => $usuarios->map(static function (Usuario $u) {
                return [
                    'id' => $u->id,
                    'nombre_completo' => $u->nombre_completo,
                    'email' => $u->email,
                ];
            }),
        ]);
    }

    public function storeNodo(Request $request, Procedimiento $procedimiento): JsonResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:150',
            'slug' => 'nullable|string|max:80',
            'puede_finalizar' => 'boolean',
            'puede_rechazar_definitivo' => 'boolean',
            'es_terminal' => 'boolean',
        ]);

        // Auto-assign orden: max existing + 1
        $maxOrden = FlujoNodo::where('id_procedimiento', $procedimiento->id)->max('orden') ?? 0;
        $orden = $maxOrden + 1;

        $nodo = FlujoNodo::create([
            'id_procedimiento' => $procedimiento->id,
            'nombre' => $data['nombre'],
            'slug' => $data['slug'] ?? null,
            'orden' => $orden,
            'puede_finalizar' => $data['puede_finalizar'] ?? false,
            'puede_rechazar_definitivo' => $data['puede_rechazar_definitivo'] ?? false,
            'es_terminal' => $data['es_terminal'] ?? false,
        ]);

        return response()->json(['success' => true, 'nodo' => $nodo]);
    }

    public function updateNodo(Request $request, Procedimiento $procedimiento, FlujoNodo $nodo): JsonResponse
    {
        $this->assertNodoDeProcedimiento($procedimiento, $nodo);

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:150',
            'slug' => 'nullable|string|max:80',
            'puede_finalizar' => 'boolean',
            'puede_rechazar_definitivo' => 'boolean',
            'es_terminal' => 'boolean',
        ]);

        $nodo->fill($data);
        $nodo->save();

        return response()->json(['success' => true, 'nodo' => $nodo->fresh()]);
    }

    public function destroyNodo(Procedimiento $procedimiento, FlujoNodo $nodo): JsonResponse
    {
        $this->assertNodoDeProcedimiento($procedimiento, $nodo);

        if ((int) $procedimiento->id_nodo_entrada === (int) $nodo->id) {
            $procedimiento->id_nodo_entrada = null;
            $procedimiento->save();
        }

        $nodo->delete();

        return response()->json(['success' => true]);
    }

    public function setNodoEntrada(Request $request, Procedimiento $procedimiento): JsonResponse
    {
        $data = $request->validate([
            'id_nodo_entrada' => 'nullable|integer|exists:flujo_nodos,id',
        ]);

        if (!empty($data['id_nodo_entrada'])) {
            $n = FlujoNodo::findOrFail($data['id_nodo_entrada']);
            if ((int) $n->id_procedimiento !== (int) $procedimiento->id) {
                return response()->json(['success' => false, 'message' => 'El nodo no pertenece a este procedimiento'], 422);
            }
        }

        $procedimiento->id_nodo_entrada = $data['id_nodo_entrada'] ?? null;
        $procedimiento->save();

        return response()->json(['success' => true, 'id_nodo_entrada' => $procedimiento->id_nodo_entrada]);
    }

    public function storeTransicion(Request $request, Procedimiento $procedimiento): JsonResponse
    {
        $data = $request->validate([
            'desde_nodo_id' => 'required|integer|exists:flujo_nodos,id',
            'hacia_nodo_id' => 'required|integer|exists:flujo_nodos,id',
            'es_default_auto' => 'boolean',
        ]);

        $desde = FlujoNodo::findOrFail($data['desde_nodo_id']);
        $hacia = FlujoNodo::findOrFail($data['hacia_nodo_id']);
        if ((int) $desde->id_procedimiento !== (int) $procedimiento->id
            || (int) $hacia->id_procedimiento !== (int) $procedimiento->id) {
            return response()->json(['success' => false, 'message' => 'Los nodos deben ser de este procedimiento'], 422);
        }

        if ((int) $data['desde_nodo_id'] === (int) $data['hacia_nodo_id']) {
            return response()->json(['success' => false, 'message' => 'El origen y destino no pueden ser el mismo nodo'], 422);
        }

        try {
            $t = FlujoTransicion::create([
                'desde_nodo_id' => $data['desde_nodo_id'],
                'hacia_nodo_id' => $data['hacia_nodo_id'],
                'es_default_auto' => $data['es_default_auto'] ?? false,
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Transición duplicada o inválida: '.$e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'transicion' => $t]);
    }

    public function updateTransicion(Request $request, Procedimiento $procedimiento, FlujoTransicion $transicion): JsonResponse
    {
        $this->assertTransicionDeProcedimiento($procedimiento, $transicion);

        $data = $request->validate([
            'es_default_auto' => 'required|boolean',
        ]);

        $transicion->es_default_auto = $data['es_default_auto'];
        $transicion->save();

        return response()->json(['success' => true, 'transicion' => $transicion]);
    }

    public function destroyTransicion(Procedimiento $procedimiento, FlujoTransicion $transicion): JsonResponse
    {
        $this->assertTransicionDeProcedimiento($procedimiento, $transicion);
        $transicion->delete();

        return response()->json(['success' => true]);
    }

    public function storeEquipo(Request $request, Procedimiento $procedimiento): JsonResponse
    {
        $data = $request->validate([
            'nodo_id' => 'required|integer|exists:flujo_nodos,id',
            'nombre' => 'required|string|max:120',
        ]);

        $nodo = FlujoNodo::findOrFail($data['nodo_id']);
        $this->assertNodoDeProcedimiento($procedimiento, $nodo);

        $eq = FlujoEquipo::create([
            'nodo_id' => $nodo->id,
            'nombre' => $data['nombre'],
        ]);

        return response()->json(['success' => true, 'equipo' => $eq]);
    }

    public function destroyEquipo(Procedimiento $procedimiento, FlujoEquipo $equipo): JsonResponse
    {
        $nodo = $equipo->nodo;
        if (!$nodo || (int) $nodo->id_procedimiento !== (int) $procedimiento->id) {
            abort(404);
        }
        $equipo->delete();

        return response()->json(['success' => true]);
    }

    public function attachMiembro(Request $request, Procedimiento $procedimiento, FlujoEquipo $equipo): JsonResponse
    {
        $nodo = $equipo->nodo;
        if (!$nodo || (int) $nodo->id_procedimiento !== (int) $procedimiento->id) {
            abort(404);
        }

        $data = $request->validate([
            'usuario_id' => 'required|integer|exists:usuarios,id',
        ]);

        $usuario = Usuario::findOrFail($data['usuario_id']);
        if ((int) $usuario->tipo_user !== 2) {
            return response()->json(['success' => false, 'message' => 'Solo usuarios internos pueden integrar equipos'], 422);
        }

        $equipo->miembros()->syncWithoutDetaching([(int) $data['usuario_id']]);

        return response()->json(['success' => true]);
    }

    public function detachMiembro(Procedimiento $procedimiento, FlujoEquipo $equipo, Usuario $usuario): JsonResponse
    {
        $nodo = $equipo->nodo;
        if (!$nodo || (int) $nodo->id_procedimiento !== (int) $procedimiento->id) {
            abort(404);
        }

        $equipo->miembros()->detach($usuario->id);

        return response()->json(['success' => true]);
    }

    private function assertNodoDeProcedimiento(Procedimiento $procedimiento, FlujoNodo $nodo): void
    {
        if ((int) $nodo->id_procedimiento !== (int) $procedimiento->id) {
            abort(404);
        }
    }

    private function assertTransicionDeProcedimiento(Procedimiento $procedimiento, FlujoTransicion $transicion): void
    {
        $desde = FlujoNodo::find($transicion->desde_nodo_id);
        if (!$desde || (int) $desde->id_procedimiento !== (int) $procedimiento->id) {
            abort(404);
        }
    }

    public function regenerarDesdeJerarquia(Procedimiento $procedimiento): JsonResponse
    {
        try {
            $procedimiento->crearFlujoConfigurativo();
            return response()->json(['success' => true, 'message' => 'Flujo regenerado desde la jerarquía de niveles.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al regenerar: ' . $e->getMessage()], 500);
        }
    }

    public function sincronizarUsuariosNiveles(Procedimiento $procedimiento): JsonResponse
    {
        try {
            $procedimiento->syncUsuariosNivelesToEquipos();
            return response()->json(['success' => true, 'message' => 'Usuarios sincronizados desde usuarios_niveles a equipos.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al sincronizar: ' . $e->getMessage()], 500);
        }
    }
}
