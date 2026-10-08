<?php

namespace App\Services\Tramites;

use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Models\FlujoNodo;
use App\Models\Tramite;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
use Illuminate\Database\Eloquent\Builder;

/**
 * Visibilidad y acciones de bandeja interna por nivel asignado (usuarios_niveles).
 */
class TramiteBandejaInternaService
{
    public function __construct(
        private readonly TramiteFlujoService $tramiteFlujoService
    ) {
    }

    /**
     * Restringe la consulta a trámites que el interno puede ver (nivel del nodo
     * de entrada o del nodo actual según su asignación en usuarios_niveles).
     * No aplica a administradores ni a usuarios externos.
     */
    public function aplicarFiltroInternoNoAdmin(Builder $query, Usuario $usuario): void
    {
        if ((int) $usuario->tipo_user !== 2 || $usuario->esAdministrador()) {
            return;
        }

        $pares = UsuariosNiveles::query()
            ->where('id_usuario', $usuario->id)
            ->whereNotNull('nivel')
            ->whereNotNull('id_nivel')
            ->get(['nivel', 'id_nivel']);

        if ($pares->isEmpty()) {
            $query->whereRaw('1 = 0');
            return;
        }

        $coincideNivel = function (Builder $n) use ($pares) {
            $n->where(function (Builder $w) use ($pares) {
                foreach ($pares as $p) {
                    $w->orWhere(function (Builder $x) use ($p) {
                        $x->where('nivel', $p->nivel)->where('id_nivel', $p->id_nivel);
                    });
                }
            });
        };

        $query->where(function (Builder $q) use ($coincideNivel) {
            $q->where(function (Builder $q2) use ($coincideNivel) {
                $q2->where('estado', EstadoTramite::PENDIENTE)
                    ->whereNull('id_nodo_actual')
                    ->whereHas('procedimiento.nodoEntrada', $coincideNivel);
            })->orWhere(function (Builder $q2) use ($coincideNivel) {
                $q2->whereNotNull('id_nodo_actual')
                    ->whereHas('nodoActual', $coincideNivel);
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
                // Si el trámite requiere oficio (fue observado CON oficio por interno),
                // el externo debe subir su oficio de respuesta
                if ((bool) $tramite->requiere_oficio) {
                    $acciones[] = 'SUBIR_OFICIO';
                }
                // Si no requiere oficio pero tiene oficio adjunto, puede subir oficio de subsanación
                elseif (!is_null($tramite->oficio) && $tramite->oficio !== '') {
                    $acciones[] = 'SUBIR_OFICIO';
                }
                // Si no requiere oficio y no tiene oficio, es observación simple → corregir documento
                else {
                    $acciones[] = 'CORREGIR';
                }
            }

            return $acciones;
        }

        if ((int) $usuario->tipo_user !== 2) {
            return $acciones;
        }

        // Si el trámite requiere oficio del creador, deshabilitar todas las acciones del especialista
        if ($tramite->requiere_oficio) {
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
                        && $this->tramiteFlujoService->nodoTieneSiguiente($nodo)) {
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
                    // Derivar: si hay siguiente nodo en la cadena (flujo normal)
                    // o desde el nodo final (puede_finalizar). Desde el nodo final,
                    // en modo AUTO la derivación reinicia el flujo hacia el nodo de
                    // entrada; en modo MANUAL se elige cualquier destino.
                    $puedeDerivar = $tramite->id_nodo_actual && $nodo
                        && ($this->tramiteFlujoService->nodoTieneSiguiente($nodo) || $nodo->puede_finalizar);

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
                        // Siempre mostrar PROCESAR en nodo final (con o sin oficio)
                        $acciones[] = 'PROCESAR';
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
