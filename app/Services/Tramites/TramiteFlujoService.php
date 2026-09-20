<?php

namespace App\Services\Tramites;

use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Models\FlujoNodo;
use App\Models\FlujoTransicion;
use App\Models\Procedimiento;
use App\Models\Tramite;
use App\Models\TramiteMovimiento;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\UploadedFile;
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

            if ($tramite->esModoManual()) {
                return $tramite;
            }

            if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
                throw new Exception('El trámite no tiene nodo actual asignado');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $tramite->nodoActual)) {
                throw new Exception('No tiene permiso para cambiar el modo de derivación en este nodo');
            }

            $tramite->modo_derivacion = ModoDerivacion::MANUAL_FORZADO;
            $tramite->modo_manual_por_usuario = null;
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'MODO_MANUAL_FORZADO', $tramite->id_nodo_actual, $tramite->id_nodo_actual, []);

            return $tramite->fresh(['nodoActual']);
        });
    }

    /**
     * Desactiva el modo manual forzado y vuelve a AUTO
     *
     * @param Tramite $tramite
     * @param Usuario|null $actor
     * @return Tramite
     * @throws Exception
     */
    public function desactivarModoDerivacionManual(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->load('nodoActual');

            if (!$tramite->esModoManual()) {
                return $tramite;
            }

            if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
                throw new Exception('El trámite no tiene nodo actual asignado');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $tramite->nodoActual)) {
                throw new Exception('No tiene permiso para cambiar el modo de derivación en este nodo');
            }

            $tramite->modo_manual_por_usuario = null;
            $tramite->modo_derivacion = ModoDerivacion::AUTO;
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'MODO_AUTO_RESTAURADO', $tramite->id_nodo_actual, $tramite->id_nodo_actual, []);

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

            $modo = $tramite->esModoManual() ? ModoDerivacion::MANUAL_FORZADO : ModoDerivacion::AUTO;

            // Los nodos autorizados a finalizar (puede_finalizar) no pueden derivar en
            // modo manual: al desactivar la derivación automática solo pueden reactivarla.
            if ($modo === ModoDerivacion::MANUAL_FORZADO && $nodoDesde->puede_finalizar) {
                throw new Exception(
                    'El nodo actual está autorizado a finalizar: no puede derivar en modo manual. Reactive la derivación automática para poder derivar.'
                );
            }

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
     * ¿Hay un paso previo registrado (derivación o ingreso) para volver desde el nodo actual?
     */
    public function puedeRetrocederEnGrafo(Tramite $tramite): bool
    {
        if ($tramite->estado !== EstadoTramite::EN_PROCESO || !$tramite->id_nodo_actual) {
            return false;
        }

        return $this->ultimoMovimientoEntradaAlNodoActual($tramite) !== null;
    }

    /**
     * Deshace el último avance hacia el nodo actual (DERIVAR_NODO o ACEPTAR_INGRESO).
     *
     * @throws Exception
     */
    public function retrocederEnFlujoConfigurable(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->load('nodoActual');

            if ($tramite->estado !== EstadoTramite::EN_PROCESO) {
                throw new Exception('Solo se puede retroceder en EN_PROCESO');
            }

            if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
                throw new Exception('El trámite no tiene nodo actual');
            }

            $nodoActual = $tramite->nodoActual;
            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodoActual)) {
                throw new Exception('No tiene permiso para retroceder el flujo en este nodo');
            }

            $mov = $this->ultimoMovimientoEntradaAlNodoActual($tramite);
            if (!$mov) {
                throw new Exception('No hay historial para retroceder desde este nodo');
            }

            $nodoDesdeIdActual = (int) $tramite->id_nodo_actual;

            if ($mov->accion === 'DERIVAR_NODO') {
                if (!$mov->nodo_desde_id) {
                    throw new Exception('Movimiento de derivación inconsistente');
                }

                $nodoAnterior = FlujoNodo::query()->findOrFail($mov->nodo_desde_id);
                $tramite->id_nodo_actual = (int) $nodoAnterior->id;
                $tramite->id_usuario = $this->primerUsuarioIdEnNodo($nodoAnterior);
                $tramite->save();

                $this->registrarMovimiento($tramite, (int) $actor->id, 'RETROCEDER_NODO', $nodoDesdeIdActual, (int) $nodoAnterior->id, [
                    'movimiento_revertido_id' => $mov->id,
                ]);

                return $tramite->fresh(['procedimiento', 'nodoActual']);
            }

            if ($mov->accion === 'ACEPTAR_INGRESO') {
                $tramite->id_nodo_actual = null;
                $tramite->estado = EstadoTramite::PENDIENTE;
                $tramite->save();

                $this->registrarMovimiento($tramite, (int) $actor->id, 'RETROCEDER_INGRESO', $nodoDesdeIdActual, null, [
                    'movimiento_revertido_id' => $mov->id,
                ]);

                return $tramite->fresh(['procedimiento', 'nodoActual']);
            }

            throw new Exception('Tipo de movimiento no reversible');
        });
    }

    private function ultimoMovimientoEntradaAlNodoActual(Tramite $tramite): ?TramiteMovimiento
    {
        return TramiteMovimiento::query()
            ->where('tramite_id', $tramite->id)
            ->whereIn('accion', ['DERIVAR_NODO', 'ACEPTAR_INGRESO'])
            ->where('nodo_hasta_id', $tramite->id_nodo_actual)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @throws Exception
     */
    private function resolverTransicionSaliente(FlujoNodo $desde, string $modoDerivacion, ?int $haciaNodoId): object
    {
        // En modo manual con destino explícito: permitir derivar a CUALQUIER nodo del
        // procedimiento, incluso desde un nodo terminal (nivel final) sin transiciones.
        // En modo AUTO con destino explícito desde nodo final (puede_finalizar): también permitir.
        if (($modoDerivacion === ModoDerivacion::MANUAL_FORZADO || ($modoDerivacion === ModoDerivacion::AUTO && (bool) $desde->puede_finalizar)) && $haciaNodoId !== null) {
            $nodoHasta = FlujoNodo::query()
                ->where('id', $haciaNodoId)
                ->where('id_procedimiento', $desde->id_procedimiento)
                ->where('id', '!=', $desde->id) // No auto-derivar
                ->first();

            if (!$nodoHasta) {
                throw new Exception('El nodo destino no existe o no pertenece a este procedimiento');
            }

            // Crear una transición virtual para mantener compatibilidad
            return (object) [
                'id' => 0,
                'desde_nodo_id' => $desde->id,
                'hacia_nodo_id' => $nodoHasta->id,
                'es_default_auto' => false,
                'nodoHacia' => $nodoHasta,
            ];
        }

        /** @var Collection<int, FlujoTransicion> $transiciones */
        $transiciones = FlujoTransicion::query()
            ->where('desde_nodo_id', $desde->id)
            ->with('nodoHacia')
            ->get();

        if ($transiciones->isEmpty()) {
            // Nodo terminal (nivel final) sin transiciones: en modo AUTO la derivación
            // reinicia el flujo enviando el trámite al nodo de entrada del procedimiento.
            // Así, cualquier usuario asignado al nivel final puede volver a derivar el
            // trámite cuando la derivación automática está activada.
            if ($modoDerivacion === ModoDerivacion::AUTO && (bool) $desde->es_terminal) {
                $nodoEntrada = $this->nodoEntradaDelProcedimiento((int) $desde->id_procedimiento);

                if ($nodoEntrada && (int) $nodoEntrada->id !== (int) $desde->id) {
                    return (object) [
                        'id' => 0,
                        'desde_nodo_id' => $desde->id,
                        'hacia_nodo_id' => $nodoEntrada->id,
                        'es_default_auto' => true,
                        'nodoHacia' => $nodoEntrada,
                    ];
                }

                throw new Exception('El trámite está en el nivel final y el procedimiento no tiene un nodo de entrada para reiniciar el flujo');
            }

            throw new Exception('No hay transiciones configuradas desde el nodo actual');
        }

        foreach ($transiciones as $t) {
            $hacia = $t->nodoHacia;
            if (!$hacia || (int) $hacia->id_procedimiento !== (int) $desde->id_procedimiento) {
                throw new Exception('Transición inconsistente con el procedimiento del nodo');
            }
        }

        if ($modoDerivacion === ModoDerivacion::MANUAL_FORZADO) {
            // Si no se especifica destino en manual, lanzar error
            throw new Exception('En modo manual debe especificar el nodo destino');
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

    /**
     * Nodo de entrada configurado del procedimiento (usado para reiniciar el flujo
     * cuando se deriva desde el nodo terminal / nivel final en modo AUTO).
     */
    private function nodoEntradaDelProcedimiento(int $procedimientoId): ?FlujoNodo
    {
        $entradaId = Procedimiento::query()
            ->where('id', $procedimientoId)
            ->value('id_nodo_entrada');

        if (!$entradaId) {
            return null;
        }

        return FlujoNodo::query()->find($entradaId);
    }

    /**
     * Nodo usado para permisos (actual o entrada), igual criterio que la bandeja interna.
     */
    public function nodoContextoPermisos(Tramite $tramite): ?FlujoNodo
    {
        $tramite->loadMissing(['procedimiento.nodoEntrada', 'nodoActual']);

        if ($tramite->id_nodo_actual && $tramite->nodoActual) {
            return $tramite->nodoActual;
        }

        return $tramite->procedimiento?->nodoEntrada;
    }

    /**
     * Obtiene las opciones de derivación para un trámite.
     * En modo AUTO: solo transiciones desde el nodo actual (excepto nodos con puede_finalizar, que pueden derivar a cualquier nodo).
     * En modo MANUAL_FORZADO: TODOS los nodos del procedimiento (excepto el actual).
     *
     * @return list<array{id:int, hacia_nodo_id:int, nombre_destino:string, es_default_auto:bool}>
     */
    public function listarOpcionesDerivacionDesdeTramite(Tramite $tramite): array
    {
        $tramite->loadMissing(['nodoActual', 'procedimiento']);
        if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
            return [];
        }

        $esManual = $tramite->esModoManual();
        $esNodoFinal = (bool) $tramite->nodoActual->puede_finalizar;

        if ($esManual || $esNodoFinal) {
            // En modo manual O en nodo final: TODOS los nodos del procedimiento excepto el actual
            return $tramite->procedimiento->flujoNodos
                ->where('id', '!=', $tramite->nodoActual->id)
                ->map(function (FlujoNodo $nodo) {
                    return [
                        'id' => (int) $nodo->id,
                        'hacia_nodo_id' => (int) $nodo->id,
                        'nombre_destino' => $nodo->nombre,
                        'es_default_auto' => false,
                    ];
                })
                ->values()
                ->all();
        }

        // Modo AUTO (nodo no final): solo transiciones definidas desde el nodo actual
        return FlujoTransicion::query()
            ->where('desde_nodo_id', $tramite->nodoActual->id)
            ->with('nodoHacia')
            ->orderByDesc('es_default_auto')
            ->orderBy('hacia_nodo_id')
            ->get()
            ->map(static function (FlujoTransicion $t) {
                return [
                    'id' => (int) $t->id,
                    'hacia_nodo_id' => (int) $t->hacia_nodo_id,
                    'nombre_destino' => $t->nodoHacia?->nombre ?? ('#'.$t->hacia_nodo_id),
                    'es_default_auto' => (bool) $t->es_default_auto,
                ];
            })
            ->all();
    }

    /**
     * Observación simple (sin oficio): miembros del nodo en contexto.
     *
     * @throws Exception
     */
    public function observarEnFlujoConfigurable(Tramite $tramite, string $observacion, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $observacion, $actor) {
            $nodo = $this->nodoContextoPermisos($tramite);
            if (!$nodo) {
                throw new Exception('No hay nodo de contexto para observar');
            }

            if (!in_array($tramite->estado, [EstadoTramite::PENDIENTE, EstadoTramite::EN_PROCESO], true)) {
                throw new Exception('Solo se puede observar en PENDIENTE o EN_PROCESO');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodo)) {
                throw new Exception('No tiene permiso para observar en este nodo');
            }

            $tramite->estado = EstadoTramite::OBSERVADO;
            $tramite->ultimo_estado = EstadoTramite::OBSERVADO;
            $tramite->observaciones = $observacion;
            $tramite->limite_observacion = now()->addDays(2);
            $tramite->save();

            $nid = (int) $nodo->id;
            $this->registrarMovimiento($tramite, (int) $actor->id, 'OBSERVAR', $nid, $nid, []);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    /**
     * Rechazo: definitivo solo si el nodo permite rechazo definitivo; si no, observación al ciudadano.
     *
     * @throws Exception
     */
    public function rechazarEnFlujoConfigurable(Tramite $tramite, string $motivo, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $motivo, $actor) {
            $nodo = $this->nodoContextoPermisos($tramite);
            if (!$nodo) {
                throw new Exception('No hay nodo de contexto para rechazar');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodo)) {
                throw new Exception('No tiene permiso para rechazar en este nodo');
            }

            if ($tramite->estado === EstadoTramite::OBSERVADO && !$this->validarLimiteObservacionInterno($tramite)) {
                throw new Exception('Se ha excedido el límite de tiempo para observaciones');
            }

            if ($nodo->puede_rechazar_definitivo) {
                $tramite->estado = EstadoTramite::RECHAZADO;
                $tramite->ultimo_estado = EstadoTramite::RECHAZADO;
                $tramite->observaciones = 'RECHAZO DEFINITIVO: '.$motivo;
                $tramite->limite_observacion = null;
                $tramite->rechazado_por = $actor->id;
                $tramite->fecha_rechazo = now();
            } else {
                $tramite->estado = EstadoTramite::OBSERVADO;
                $tramite->ultimo_estado = EstadoTramite::OBSERVADO;
                $tramite->observaciones = $motivo;
                $tramite->limite_observacion = now()->addDays(2);
            }

            $tramite->save();

            $nid = (int) $nodo->id;
            $this->registrarMovimiento($tramite, (int) $actor->id, 'RECHAZAR', $nid, $nid, [
                'definitivo' => $nodo->puede_rechazar_definitivo,
            ]);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    /**
     * Observar con oficio (nodo con puede_finalizar).
     * El oficio debe ser subido posteriormente por el creador del trámite.
     *
     * @throws Exception
     */
    public function observarConOficioEnFlujoConfigurable(
        Tramite $tramite,
        string $observacion,
        ?Usuario $actor = null
    ): Tramite {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $observacion, $actor) {
            $tramite->loadMissing('nodoActual');
            $nodo = $tramite->nodoActual;
            if (!$nodo || !$nodo->puede_finalizar) {
                throw new Exception('Solo un nodo autorizado a finalizar puede observar con oficio');
            }

            if ($tramite->estado !== EstadoTramite::EN_PROCESO) {
                throw new Exception('Solo se puede observar con oficio en EN_PROCESO');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodo)) {
                throw new Exception('No tiene permiso para esta acción en este nodo');
            }

            $tramite->estado = EstadoTramite::OBSERVADO;
            $tramite->observaciones = $observacion;
            $tramite->oficio = null;
            $tramite->requiere_oficio = true;
            $tramite->limite_observacion = now()->addDays(2);
            $tramite->save();

            $nid = (int) $nodo->id;
            $this->registrarMovimiento($tramite, (int) $actor->id, 'OBSERVAR_CON_OFICIO', $nid, $nid, []);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    /**
     * Verificar (tras observación o con oficio en proceso).
     *
     * @throws Exception
     */
    public function verificarEnFlujoConfigurable(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->loadMissing('nodoActual');
            $nodo = $tramite->nodoActual;
            if (!$nodo || !$nodo->puede_finalizar) {
                throw new Exception('Solo el nodo autorizado a finalizar puede verificar');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodo)) {
                throw new Exception('No tiene permiso para verificar en este nodo');
            }

            // No permitir verificar si requiere oficio del creador
            if ($tramite->requiere_oficio) {
                throw new Exception('El trámite requiere oficio del creador antes de poder verificarse');
            }

            $ok = $tramite->estado === EstadoTramite::OBSERVADO
                || ($tramite->estado === EstadoTramite::EN_PROCESO && !empty($tramite->oficio));
            if (!$ok) {
                throw new Exception('El trámite no puede ser verificado en su estado actual');
            }

            $tramite->estado = EstadoTramite::VERIFICADO;
            $tramite->save();

            $nid = (int) $nodo->id;
            $this->registrarMovimiento($tramite, (int) $actor->id, 'VERIFICAR', $nid, $nid, []);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    /**
     * Procesar y generar voucher (nodo con puede_finalizar).
     *
     * @throws Exception
     */
    public function procesarEnFlujoConfigurable(Tramite $tramite, ?Usuario $actor = null): Tramite
    {
        $actor = $actor ?? Auth::user();
        if (!$actor instanceof Usuario) {
            throw new Exception('Usuario no autenticado');
        }

        return DB::transaction(function () use ($tramite, $actor) {
            $tramite->loadMissing(['nodoActual', 'procedimiento', 'usuario']);
            $nodo = $tramite->nodoActual;
            if (!$nodo || !$nodo->puede_finalizar) {
                throw new Exception('Solo el nodo autorizado a finalizar puede procesar');
            }

            if (!$actor->esAdministrador() && !$this->usuarioInternoPuedeActuarEnNodo($actor, $nodo)) {
                throw new Exception('No tiene permiso para procesar en este nodo');
            }

            // No permitir procesar si requiere oficio del creador
            if ($tramite->requiere_oficio) {
                throw new Exception('El trámite requiere oficio del creador antes de poder procesarse');
            }

            $puede = false;
            if ($tramite->estado === EstadoTramite::VERIFICADO) {
                $puede = true;
            } elseif ($tramite->estado === EstadoTramite::EN_PROCESO && !$tramite->requiere_oficio) {
                $puede = true;
            }
            if (!$puede) {
                throw new Exception(
                    'El trámite solo puede procesarse si está VERIFICADO o en EN_PROCESO sin requerir oficio pendiente'
                );
            }

            $numeroVoucher = $this->voucherService->generarNumeroVoucher($tramite->tipo_tramite);
            $voucherPath = $this->voucherService->generarVoucher($tramite);

            $tramite->estado = EstadoTramite::FINALIZADO;
            $tramite->numero_voucher = $numeroVoucher;
            $tramite->voucher = $voucherPath;
            $tramite->save();

            $nid = (int) $nodo->id;
            $this->registrarMovimiento($tramite, (int) $actor->id, 'PROCESAR_FINALIZAR', $nid, $nid, [
                'numero_voucher' => $numeroVoucher,
            ]);

            return $tramite->fresh(['procedimiento', 'nodoActual']);
        });
    }

    private function validarLimiteObservacionInterno(Tramite $tramite): bool
    {
        if (!$tramite->limite_observacion) {
            return true;
        }

        return now() <= $tramite->limite_observacion;
    }

    private function validarArchivoPdfSubida(UploadedFile $archivo): void
    {
        if (!$archivo->isValid()) {
            throw new Exception('El archivo no es válido');
        }

        if (strtolower($archivo->getClientOriginalExtension()) !== 'pdf') {
            throw new Exception('El archivo debe ser un PDF');
        }

        if ($archivo->getSize() > 10 * 1024 * 1024) {
            throw new Exception('El archivo no debe superar los 10MB');
        }
    }

    private function guardarPdfSubido(UploadedFile $archivo, string $directorio): string
    {
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = $archivo->getClientOriginalExtension();
        $base = preg_replace('/[^a-zA-Z0-9._-]/', '', pathinfo($nombreOriginal, PATHINFO_FILENAME) ?? 'doc');
        $base = strtolower(substr(str_replace(' ', '_', $base), 0, 100));
        $nombreFinal = time().'_'.$base.'.'.$extension;
        $archivo->storeAs('uploads/'.$directorio, $nombreFinal);

        return $nombreFinal;
    }
}
