<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlujoNodo;
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
                    'orden' => $n->orden,
                    'puede_finalizar' => (bool) $n->puede_finalizar,
                    'puede_rechazar_definitivo' => (bool) $n->puede_rechazar_definitivo,
                    // Miembros resueltos desde usuarios_niveles (sin equipos)
                    'miembros' => $this->miembrosDeNodo($n),
                ];
            }),
        ]);
    }

    /**
     * Usuarios internos asignados al nivel del nodo.
     */
    private function miembrosDeNodo(FlujoNodo $nodo): array
    {
        if (!$nodo->nivel || !$nodo->id_nivel) {
            return [];
        }

        return Usuario::query()
            ->where('tipo_user', 2)
            ->whereHas('usuariosNiveles', function ($q) use ($nodo) {
                $q->where('nivel', $nodo->nivel)->where('id_nivel', $nodo->id_nivel);
            })
            ->orderBy('id')
            ->get()
            ->map(static function (Usuario $u) {
                return [
                    'id' => $u->id,
                    'nombre_completo' => $u->nombre_completo,
                    'email' => $u->email,
                ];
            })
            ->all();
    }

    public function nodosGlobales(): JsonResponse
    {
        $nodos = FlujoNodo::query()
            ->with(['procedimiento:id,nombre'])
            ->orderBy('orden')
            ->orderBy('id')
            ->get(['id', 'nombre', 'orden', 'puede_finalizar', 'id_procedimiento']);

        $agrupados = [];
        for ($nivel = 1; $nivel <= 4; $nivel++) {
            $agrupados[$nivel] = $nodos
                ->filter(fn ($n) => $n->orden === $nivel)
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'nombre' => $n->nombre,
                    'orden' => $n->orden,
                    'puede_finalizar' => (bool) $n->puede_finalizar,
                    'procedimiento' => $n->procedimiento?->nombre ?? '—',
                ])
                ->values()
                ->toArray();
        }

        return response()->json([
            'niveles' => $agrupados,
        ]);
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

    public function regenerarDesdeJerarquia(Procedimiento $procedimiento): JsonResponse
    {
        try {
            $procedimiento->crearFlujoConfigurativo();
            return response()->json(['success' => true, 'message' => 'Flujo regenerado desde la jerarquía de niveles.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al regenerar: ' . $e->getMessage()], 500);
        }
    }
}
