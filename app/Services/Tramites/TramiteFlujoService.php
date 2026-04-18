<?php

namespace App\Services\Tramites;

use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Models\FlujoNodo;
use App\Models\FlujoTransicion;
use App\Models\Tramite;
use App\Models\TramiteMovimiento;
use App\Models\Usuario;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Motor de flujo configurable por nodos, equipos y modo auto/manual.
 * Las acciones complejas (derivar, retroceder, observar, finalizar) se irán cableando aquí.
 */
class TramiteFlujoService
{
    public function __construct(
        private readonly VoucherService $voucherService
    ) {
    }

    /**
     * Trámite en PENDIENTE sin nodo: primer ingreso al nodo de entrada del procedimiento → EN_PROCESO.
     *
     * @throws Exception
     */
    public function aceptarIngresoDesdePendienteSinNodo(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->load('procedimiento.nodoEntrada');

            if ($tramite->estado !== EstadoTramite::PENDIENTE) {
                throw new Exception('Solo se puede ingresar al flujo desde estado PENDIENTE');
            }

            if ($tramite->id_nodo_actual !== null) {
                throw new Exception('El trámite ya tiene nodo asignado');
            }

            $proc = $tramite->procedimiento;
            if (!$proc || !$proc->id_nodo_entrada) {
                throw new Exception('El procedimiento no tiene nodo de entrada configurado');
            }

            /** @var FlujoNodo $nodoEntrada */
            $nodoEntrada = $proc->nodoEntrada;
            if (!$nodoEntrada || (int) $nodoEntrada->id_procedimiento !== (int) $proc->id) {
                throw new Exception('Nodo de entrada inválido para este procedimiento');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodoEntrada)) {
                throw new Exception('No tiene permiso para aceptar el ingreso en este nodo');
            }

            $nodoDesde = null;
            $nodoHasta = $nodoEntrada->id;

            $tramite->id_nodo_actual = $nodoHasta;
            $tramite->estado = EstadoTramite::EN_PROCESO;
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'ACEPTAR_INGRESO', $nodoDesde, $nodoHasta, [
                'procedimiento_id' => $proc->id,
            ]);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    /**
     * Una vez manual, no vuelve a AUTO (cualquier actor con acceso al nodo actual).
     *
     * @throws Exception
     */
    public function forzarModoDerivacionManual(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->load('nodoActual');

            if ($tramite->modo_derivacion === ModoDerivacion::MANUAL_FORZADO) {
                return $tramite;
            }

            if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
                throw new Exception('El trámite no tiene nodo actual asignado');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $tramite->nodoActual)) {
                throw new Exception('No tiene permiso para cambiar el modo de derivación en este nodo');
            }

            $tramite->modo_derivacion = ModoDerivacion::MANUAL_FORZADO;
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'MODO_MANUAL_FORZADO', $tramite->id_nodo_actual, $tramite->id_nodo_actual, []);

            return $tramite->fresh(['nodoActual']);
        });
    }

    public function usuarioInternoPuedeActuarEnNodo(Usuario $usuario, FlujoNodo $nodo): bool
    {
        if ((int) $usuario->tipo_user !== 2) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        return $nodo->equipos()
            ->whereHas('miembros', static function ($q) use ($usuario) {
                $q->where('usuarios.id', $usuario->id);
            })
            ->exists();
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function registrarMovimiento(
        Tramite $tramite,
        ?int $usuarioId,
        string $accion,
        ?int $nodoDesdeId,
        ?int $nodoHastaId,
        ?array $metadata = null
    ): TramiteMovimiento {
        return TramiteMovimiento::create([
            'tramite_id' => $tramite->id,
            'usuario_id' => $usuarioId,
            'accion' => $accion,
            'nodo_desde_id' => $nodoDesdeId,
            'nodo_hasta_id' => $nodoHastaId,
            'metadata' => $metadata,
        ]);
    }

    public function nodoTieneTransicionesSalientes(FlujoNodo $nodo): bool
    {
        return FlujoTransicion::query()->where('desde_nodo_id', $nodo->id)->exists();
    }

    /**
     * @return list<int>
     */
    public function idsMiembrosDistintosEnNodo(FlujoNodo $nodo): array
    {
        $nodo->loadMissing('equipos.miembros');
        $map = [];
        foreach ($nodo->equipos->sortBy('id') as $equipo) {
            foreach ($equipo->miembros->sortBy('id') as $u) {
                $map[(int) $u->id] = true;
            }
        }

        $ids = array_map('intval', array_keys($map));
        sort($ids, SORT_NUMERIC);

        return $ids;
    }

    public function contarMiembrosDistintosEnNodo(FlujoNodo $nodo): int
    {
        return count($this->idsMiembrosDistintosEnNodo($nodo));
    }

    /**
     * Avanza el trámite al siguiente nodo según modo AUTO (arista predeterminada o única) o MANUAL (hacia_nodo_id).
     *
     * @throws Exception
     */
    public function derivarPorGrafo(Tramite $tramite, ?Usuario $actor = null, ?int $haciaNodoId = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor, $haciaNodoId) {
            $tramite->load(['nodoActual', 'procedimiento']);

            if ($tramite->estado !== EstadoTramite::EN_PROCESO) {
                throw new Exception('Solo se puede derivar por flujo cuando el trámite está EN_PROCESO');
            }

            if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
                throw new Exception('El trámite no está en un nodo del flujo configurable');
            }

            $nodoDesde = $tramite->nodoActual;
            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodoDesde)) {
                throw new Exception('No tiene permiso para derivar en este nodo');
            }

            $modo = $tramite->modo_derivacion ?? ModoDerivacion::AUTO;
            $transicion = $this->resolverTransicionSaliente($nodoDesde, $modo, $haciaNodoId);
            $nodoHasta = $transicion->nodoHacia;
            if (!$nodoHasta) {
                $nodoHasta = FlujoNodo::query()->findOrFail($transicion->hacia_nodo_id);
            }

            if ((int) $nodoHasta->id_procedimiento !== (int) $nodoDesde->id_procedimiento) {
                throw new Exception('El nodo destino no pertenece al mismo procedimiento');
            }

            $nodoDesdeId = (int) $nodoDesde->id;
            $nodoHastaId = (int) $nodoHasta->id;

            $tramite->id_nodo_actual = $nodoHastaId;
            $tramite->id_usuario = $this->primerUsuarioIdEnNodo($nodoHasta);
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'DERIVAR_NODO', $nodoDesdeId, $nodoHastaId, [
                'transicion_id' => $transicion->id,
                'modo_derivacion' => $modo,
            ]);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    /**
     * Reasigna id_usuario a otro miembro del mismo nodo (misma área/equipo).
     *
     * @throws Exception
     */
    public function derivarAColegaMismoNodo(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->load('nodoActual');

            if ($tramite->estado !== EstadoTramite::EN_PROCESO) {
                throw new Exception('Solo se puede reasignar en EN_PROCESO');
            }

            if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
                throw new Exception('El trámite no está en un nodo del flujo configurable');
            }

            $nodo = $tramite->nodoActual;
            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodo)) {
                throw new Exception('No tiene permiso para derivar a otro usuario en este nodo');
            }

            $ids = $this->idsMiembrosDistintosEnNodo($nodo);
            $actual = (int) $tramite->id_usuario;
            $otros = array_values(array_filter($ids, static fn (int $uid) => $uid !== $actual));

            if ($otros === []) {
                throw new Exception('No hay otro usuario en el equipo de este nodo para reasignar');
            }

            $tramite->id_usuario = $otros[0];
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'DERIVAR_COLEGA', (int) $nodo->id, (int) $nodo->id, [
                'id_usuario_nuevo' => $tramite->id_usuario,
            ]);

            return $tramite->fresh(['nodoActual']);
        });
    }

    /**
     * @throws Exception
     */
    private function resolverTransicionSaliente(FlujoNodo $desde, string $modoDerivacion, ?int $haciaNodoId): FlujoTransicion
    {
        /** @var Collection<int, FlujoTransicion> $transiciones */
        $transiciones = FlujoTransicion::query()
            ->where('desde_nodo_id', $desde->id)
            ->with('nodoHacia')
            ->get();

        if ($transiciones->isEmpty()) {
            throw new Exception('No hay transiciones configuradas desde el nodo actual');
        }

        foreach ($transiciones as $t) {
            $hacia = $t->nodoHacia;
            if (!$hacia || (int) $hacia->id_procedimiento !== (int) $desde->id_procedimiento) {
                throw new Exception('Transición inconsistente con el procedimiento del nodo');
            }
        }

        if ($modoDerivacion === ModoDerivacion::MANUAL_FORZADO) {
            if ($haciaNodoId !== null) {
                $elegida = $transiciones->firstWhere('hacia_nodo_id', $haciaNodoId);
                if (!$elegida) {
                    throw new Exception('El nodo destino no es una transición válida desde el nodo actual');
                }

                return $elegida;
            }

            if ($transiciones->count() === 1) {
                return $transiciones->first();
            }

            throw new Exception('En modo manual debe enviar hacia_nodo_id cuando hay más de una transición posible');
        }

        $defaultes = $transiciones->where('es_default_auto', true)->values();
        if ($defaultes->count() === 1) {
            return $defaultes->first();
        }

        if ($defaultes->count() > 1) {
            return $defaultes->sort(function (FlujoTransicion $a, FlujoTransicion $b) {
                $oa = $a->nodoHacia->orden;
                $ob = $b->nodoHacia->orden;
                if ($oa !== $ob) {
                    return $oa <=> $ob;
                }

                return $a->nodoHacia->id <=> $b->nodoHacia->id;
            })->first();
        }

        if ($transiciones->count() === 1) {
            return $transiciones->first();
        }

        throw new Exception(
            'Hay varias transiciones desde este nodo: marque una como predeterminada para modo AUTO o active modo manual e indique hacia_nodo_id'
        );
    }

    /**
     * @throws Exception
     */
    private function primerUsuarioIdEnNodo(FlujoNodo $nodo): int
    {
        $ids = $this->idsMiembrosDistintosEnNodo($nodo);
        if ($ids === []) {
            throw new Exception('El nodo destino no tiene usuarios asignados en equipos');
        }

        return $ids[0];
    }
}
