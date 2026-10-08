<?php

namespace App\Services\Tramites;

use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Models\FlujoNodo;
use App\Models\Procedimiento;
use App\Models\Tramite;
use App\Models\TramiteMovimiento;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Motor de flujo por nodos y modo auto/manual.
 * Cada nodo pertenece a un (nivel, id_nivel) de la jerarquía; los permisos y
 * el responsable se resuelven directo desde usuarios_niveles.
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

            // Validar que se puede reactivar AUTO
            if (!$this->puedeReactivarAuto($tramite)) {
                throw new Exception('No se puede reactivar la derivación automática: el trámite está fuera de su ruta original o el nodo actual no tiene derivación automática configurada.');
            }

            $tramite->modo_manual_por_usuario = null;
            $tramite->modo_derivacion = ModoDerivacion::AUTO;
            $tramite->save();

            $this->registrarMovimiento($tramite, (int) $actor->id, 'MODO_AUTO_RESTAURADO', $tramite->id_nodo_actual, $tramite->id_nodo_actual, []);

            return $tramite->fresh(['nodoActual']);
        });
    }

    /**
     * Verifica si se puede reactivar la derivación automática.
     * Solo permite si:
     * - El trámite está en modo manual
     * - El nodo actual pertenece al procedimiento original del trámite
     * - El nodo actual tiene un siguiente nodo en la cadena (no es final)
     */
    public function puedeReactivarAuto(Tramite $tramite): bool
    {
        if (!$tramite->esModoManual()) {
            return false;
        }

        if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
            return false;
        }

        // El nodo actual debe pertenecer al procedimiento original del trámite
        if ((int) $tramite->nodoActual->id_procedimiento !== (int) $tramite->id_procedimiento) {
            return false;
        }

        // El nodo debe tener un siguiente nodo en la cadena automática
        return $this->nodoTieneSiguiente($tramite->nodoActual);
    }

    public function usuarioInternoPuedeActuarEnNodo(Usuario $usuario, FlujoNodo $nodo): bool
    {
        if ((int) $usuario->tipo_user !== 2) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        // Sin equipos: el permiso sale directo de usuarios_niveles.
        // El nodo guarda su (nivel, id_nivel) de la jerarquía.
        if (!$nodo->nivel || !$nodo->id_nivel) {
            return false;
        }

        return UsuariosNiveles::query()
            ->where('id_usuario', $usuario->id)
            ->where('nivel', $nodo->nivel)
            ->where('id_nivel', $nodo->id_nivel)
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

    /**
     * Siguiente nodo de la cadena automática del mismo procedimiento
     * (el de menor orden mayor al actual). La derivación AUTO siempre
     * avanza al siguiente por orden; no hay aristas configurables.
     */
    public function siguienteNodo(FlujoNodo $nodo): ?FlujoNodo
    {
        return FlujoNodo::query()
            ->where('id_procedimiento', $nodo->id_procedimiento)
            ->where('orden', '>', $nodo->orden)
            ->orderBy('orden')
            ->orderBy('id')
            ->first();
    }

    public function nodoTieneSiguiente(FlujoNodo $nodo): bool
    {
        return $this->siguienteNodo($nodo) !== null;
    }

    /**
     * IDs de usuarios internos asignados al nivel del nodo (fuente: usuarios_niveles).
     *
     * @return list<int>
     */
    public function idsMiembrosDistintosEnNodo(FlujoNodo $nodo): array
    {
        if (!$nodo->nivel || !$nodo->id_nivel) {
            return [];
        }

        return UsuariosNiveles::query()
            ->where('nivel', $nodo->nivel)
            ->where('id_nivel', $nodo->id_nivel)
            ->whereHas('usuario', static function ($q) {
                $q->where('tipo_user', 2);
            })
            ->orderBy('id_usuario')
            ->pluck('id_usuario')
            ->map(static fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function contarMiembrosDistintosEnNodo(FlujoNodo $nodo): int
    {
        return count($this->idsMiembrosDistintosEnNodo($nodo));
    }

    /**
     * Avanza el trámite al siguiente nodo de la cadena en modo AUTO,
     * o a cualquier nodo indicado en modo MANUAL (hacia_nodo_id).
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

            // En modo manual el trámite puede derivarse a cualquier nodo del sistema
            // (cascada Nivel → Nodo global), incluso fuera de su procedimiento original.
            // Una vez fuera de su orden automático, no podrá reactivar AUTO
            // (puedeReactivarAuto lo bloquea) hasta volver a un nodo de su procedimiento.
            // Los nodos finales también pueden derivar en manual indicando destino.
            if ($modo === ModoDerivacion::MANUAL_FORZADO && $nodoDesde->puede_finalizar && $haciaNodoId === null) {
                throw new Exception(
                    'El nodo actual está autorizado a finalizar: indique el nodo destino para derivar en modo manual, o reactive la derivación automática.'
                );
            }

            $transicion = $this->resolverTransicionSaliente($nodoDesde, $modo, $haciaNodoId);
            $nodoHasta = $transicion->nodoHacia;
            if (!$nodoHasta) {
                $nodoHasta = FlujoNodo::query()->findOrFail($transicion->hacia_nodo_id);
            }

            // En AUTO el destino debe pertenecer al mismo procedimiento.
            // En MANUAL se permite cualquier nodo del sistema (derivación global).
            if ($modo !== ModoDerivacion::MANUAL_FORZADO && (int) $nodoHasta->id_procedimiento !== (int) $nodoDesde->id_procedimiento) {
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
        // sistema (derivación global Nivel → Nodo), incluso de otro procedimiento
        // o desde un nodo final sin siguiente.
        if ($modoDerivacion === ModoDerivacion::MANUAL_FORZADO && $haciaNodoId !== null) {
            $nodoHasta = FlujoNodo::query()
                ->where('id', $haciaNodoId)
                ->where('id', '!=', $desde->id) // No auto-derivar
                ->first();

            if (!$nodoHasta) {
                throw new Exception('El nodo destino no existe');
            }

            return $this->transicionVirtual($desde, $nodoHasta, false);
        }

        // En modo AUTO con destino explícito desde nodo final (puede_finalizar):
        // respetar el destino elegido dentro del mismo procedimiento.
        if ($modoDerivacion === ModoDerivacion::AUTO && (bool) $desde->puede_finalizar && $haciaNodoId !== null) {
            $nodoHasta = FlujoNodo::query()
                ->where('id', $haciaNodoId)
                ->where('id_procedimiento', $desde->id_procedimiento)
                ->where('id', '!=', $desde->id) // No auto-derivar
                ->first();

            if (!$nodoHasta) {
                throw new Exception('El nodo destino no existe o no pertenece a este procedimiento');
            }

            return $this->transicionVirtual($desde, $nodoHasta, false);
        }

        if ($modoDerivacion === ModoDerivacion::MANUAL_FORZADO) {
            // Si no se especifica destino en manual, lanzar error
            throw new Exception('En modo manual debe especificar el nodo destino');
        }

        // Modo AUTO: avanzar al siguiente nodo de la cadena por orden.
        $siguiente = $this->siguienteNodo($desde);
        if ($siguiente) {
            return $this->transicionVirtual($desde, $siguiente, true);
        }

        // Nodo final sin siguiente: en modo AUTO la derivación reinicia el flujo
        // enviando el trámite al nodo de entrada del procedimiento.
        if ((bool) $desde->puede_finalizar) {
            $nodoEntrada = $this->nodoEntradaDelProcedimiento((int) $desde->id_procedimiento);

            if ($nodoEntrada && (int) $nodoEntrada->id !== (int) $desde->id) {
                return $this->transicionVirtual($desde, $nodoEntrada, true);
            }

            throw new Exception('El trámite está en el nivel final y el procedimiento no tiene un nodo de entrada para reiniciar el flujo');
        }

        throw new Exception('No hay siguiente nodo configurado desde el nodo actual');
    }

    /**
     * Transición virtual para mantener compatibilidad con el registro de movimientos.
     */
    private function transicionVirtual(FlujoNodo $desde, FlujoNodo $hasta, bool $esAuto): object
    {
        return (object) [
            'id' => 0,
            'desde_nodo_id' => $desde->id,
            'hacia_nodo_id' => $hasta->id,
            'es_default_auto' => $esAuto,
            'nodoHacia' => $hasta,
        ];
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
     * En modo AUTO: el siguiente nodo de la cadena por orden (excepto nodos con
     * puede_finalizar, que pueden derivar a cualquier nodo del procedimiento).
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

        if ($esManual) {
            // Modo manual: TODOS los nodos del PROCEDIMIENTO ACTUAL excepto el actual
            return FlujoNodo::query()
                ->where('id', '!=', $tramite->nodoActual->id)
                ->where('id_procedimiento', $tramite->nodoActual->id_procedimiento)
                ->with('procedimiento:id,nombre')
                ->orderBy('orden')
                ->orderBy('id')
                ->get()
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

        if ($esNodoFinal) {
            // Nodo final en modo AUTO: TODOS los nodos del procedimiento excepto el actual
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

        // Modo AUTO (nodo no final): el siguiente nodo de la cadena por orden
        $siguiente = $this->siguienteNodo($tramite->nodoActual);
        if (!$siguiente) {
            return [];
        }

        return [[
            'id' => 0,
            'hacia_nodo_id' => (int) $siguiente->id,
            'nombre_destino' => $siguiente->nombre,
            'es_default_auto' => true,
        ]];
    }

    /**
     * Observación simple (sin oficio): usuarios del nivel en contexto.
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

        if ($archivo->getSize() > 25 * 1024 * 1024) {
            throw new Exception('El archivo no debe superar los 25MB');
        }
    }

    private function guardarPdfSubido(UploadedFile $archivo, string $directorio): string
    {
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'pdf');
        $base = \App\Support\DocumentStorage::nombreSeguro($nombreOriginal);
        $nombreFinal = time().'_'.$base.'.'.$extension;

        return \App\Support\DocumentStorage::guardar($archivo, $directorio, $nombreFinal);
    }
}
