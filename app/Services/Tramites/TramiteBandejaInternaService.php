<?php

namespace App\Services\Tramites;

use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Models\FlujoNodo;
use App\Models\Tramite;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

/**
 * Visibilidad y acciones de bandeja interna por equipos de nodo (flujo configurable).
 */
class TramiteBandejaInternaService
{
    public function __construct(
        private readonly TramiteFlujoService $tramiteFlujoService
    ) {
    }

    /**
     * Restringe la consulta a trámites que el interno puede ver (equipo del nodo de entrada o del nodo actual).
     * No aplica a administradores ni a usuarios externos.
     */
    public function aplicarFiltroInternoNoAdmin(Builder $query, Usuario $usuario): void
    {
        if ((int) $usuario->tipo_user !== 2 || $usuario->esAdministrador()) {
            return;
        }

        $uid = $usuario->id;

        $query->where(function (Builder $q) use ($uid) {
            $q->where(function (Builder $q2) use ($uid) {
                $q2->where('estado', EstadoTramite::PENDIENTE)
                    ->whereNull('id_nodo_actual')
                    ->whereHas('procedimiento.nodoEntrada.equipos.miembros', function (Builder $m) use ($uid) {
                        $m->where('usuarios.id', $uid);
                    });
            })->orWhere(function (Builder $q2) use ($uid) {
                $q2->whereNotNull('id_nodo_actual')
                    ->whereHas('nodoActual.equipos.miembros', function (Builder $m) use ($uid) {
                        $m->where('usuarios.id', $uid);
                    });
            });
        });
    }

    /**
     * Nodo usado para permisos de acción: nodo actual si existe; si no, nodo de entrada del procedimiento.
     */
    public function nodoContextoParaAcciones(Tramite $tramite): ?FlujoNodo
    {
        $tramite->loadMissing(['procedimiento.nodoEntrada', 'nodoActual']);

        if ($tramite->id_nodo_actual && $tramite->nodoActual) {
            return $tramite->nodoActual;
        }

        return $tramite->procedimiento?->nodoEntrada;
    }

    public function puedeActuarEnContexto(Usuario $usuario, Tramite $tramite): bool
    {
        if ((int) $usuario->tipo_user !== 2) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        $nodo = $this->nodoContextoParaAcciones($tramite);

        return $nodo && $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $nodo);
    }

    /**
     * @return list<string>
     */
    public function accionesPermitidas(Tramite $tramite, Usuario $usuario): array
    {
        $acciones = [];

        if ((int) $usuario->tipo_user === 1) {
            if ($tramite->estado === EstadoTramite::OBSERVADO
                && (int) $tramite->creado_por === (int) $usuario->id) {
                $acciones[] = 'CORREGIR';
            }

            return $acciones;
        }

        if ((int) $usuario->tipo_user !== 2) {
            return $acciones;
        }

        $nodo = $this->nodoContextoParaAcciones($tramite);
        $puede = $this->puedeActuarEnContexto($usuario, $tramite);
        $tieneOficio = !empty($tramite->oficio);

        if (!$puede) {
            return $acciones;
        }

        switch ($tramite->estado) {
            case EstadoTramite::PENDIENTE:
                if ($tramite->id_nodo_actual === null) {
                    $acciones[] = 'ACEPTAR';
                    $acciones[] = 'OBSERVAR';
                    if ($nodo && $nodo->puede_rechazar_definitivo) {
                        $acciones[] = 'RECHAZAR';
                    }
                }
                break;

            case EstadoTramite::EN_PROCESO:
                // Determinar si es nodo de entrada (orden = 1)
                $esNodoEntrada = $nodo && $nodo->orden === 1;

                if ($esNodoEntrada) {
                    // En nodo de entrada (Level 1): solo DERIVAR hacia adelante
                    if ($tramite->id_nodo_actual && $nodo
                        && $this->tramiteFlujoService->nodoTieneTransicionesSalientes($nodo)) {
                        $acciones[] = 'DERIVAR';
                    }
                } else {
                    // Nodos intermedios y finales: acciones completas
                    $acciones[] = 'OBSERVAR';
                    if ($tramite->esModoManual()) {
                        $acciones[] = 'MODO_AUTO';
                    } else {
                        $acciones[] = 'MODO_MANUAL';
                    }
                    // Derivar: con transiciones salientes (flujo normal) o desde el nodo
                    // terminal (nivel final). Desde el nodo terminal, en modo AUTO la
                    // derivación reinicia el flujo hacia el nodo de entrada.
                    // Excepción: los nodos autorizados a finalizar (puede_finalizar), al
                    // desactivar la derivación automática (modo manual), no pueden derivar;
                    // solo pueden reactivar la derivación automática (MODO_AUTO).
                    $puedeDerivar = $tramite->id_nodo_actual && $nodo
                        && ($this->tramiteFlujoService->nodoTieneTransicionesSalientes($nodo) || $nodo->es_terminal);

                    if ($nodo && $nodo->puede_finalizar && $tramite->esModoManual()) {
                        $puedeDerivar = false;
                    }

                    if ($puedeDerivar) {
                        $acciones[] = 'DERIVAR';
                    }
                    if ($tramite->id_nodo_actual && $nodo
                        && $this->tramiteFlujoService->contarMiembrosDistintosEnNodo($nodo) >= 2) {
                        $acciones[] = 'DERIVAR_ESPECIALISTA';
                    }
                    if (!$tramite->id_nodo_actual) {
                        $acciones[] = 'DERIVAR';
                    }
                    if ($tramite->id_nodo_actual && $this->tramiteFlujoService->puedeRetrocederEnGrafo($tramite)) {
                        $acciones[] = 'RETROCEDER';
                    }
                    if ($nodo && $nodo->puede_finalizar) {
                        $acciones[] = 'OBSERVAR_CON_OFICIO';
                        if (!$tieneOficio) {
                            $acciones[] = 'PROCESAR';
                        } else {
                            $acciones[] = 'VERIFICAR';
                        }
                    }
                    if ($nodo && $nodo->puede_rechazar_definitivo) {
                        $acciones[] = 'RECHAZAR';
                    }
                }
                break;

            case EstadoTramite::OBSERVADO:
                $acciones[] = 'VERIFICAR';
                $acciones[] = 'OBSERVAR';
                break;

            case EstadoTramite::VERIFICADO:
                if ($nodo && $nodo->puede_finalizar) {
                    $acciones[] = 'PROCESAR';
                    if ($nodo->puede_rechazar_definitivo) {
                        $acciones[] = 'RECHAZAR';
                    }
                }
                break;
        }

        return array_values(array_unique($acciones));
    }
}
