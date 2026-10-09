<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Tramite;
use App\Services\Tramites\TramiteFlujoService;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Política de Trámites
 * 
 * Define las reglas de autorización para las operaciones sobre trámites.
 * 
 * @package App\Policies
 */
class TramitePolicy
{
    use HandlesAuthorization;

    public function __construct(
        private readonly TramiteFlujoService $tramiteFlujoService
    ) {
    }

    /**
     * Determinar si el usuario puede ver un trámite específico
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function view(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        if ($usuario->esExterno()) {
            return (int) $tramite->creado_por === (int) $usuario->id
                || ((int) $tramite->id_usuario === (int) $usuario->id && $tramite->creado_por === null);
        }

        if ($usuario->esInterno()) {
            if ((int) $tramite->id_usuario === (int) $usuario->id
                || (int) $tramite->creado_por === (int) $usuario->id) {
                return true;
            }

            $tramite->loadMissing(['procedimiento.nodoEntrada', 'nodoActual']);
            $nodo = $tramite->nodoActual ?? $tramite->procedimiento?->nodoEntrada;
            if ($nodo && $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $nodo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determinar si el usuario puede aceptar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function aceptar(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo() || !$tramite->estaPendiente()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        $tramite->loadMissing('procedimiento.nodoEntrada');
        if ($tramite->id_nodo_actual === null && $tramite->procedimiento?->id_nodo_entrada) {
            $nodo = $tramite->procedimiento->nodoEntrada;
            if ($nodo && $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $nodo)) {
                return true;
            }
        }

        return $usuario->esPersonalMesaPartes();
    }

    /**
     * Determinar si el usuario puede activar la derivación manual
     *
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function modoManual(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo() || $tramite->esModoManual()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        // Usuario interno del mismo nodo actual puede forzar a MANUAL
        if ($usuario->esInterno()) {
            $tramite->loadMissing('nodoActual');
            if ($tramite->nodoActual && $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $tramite->nodoActual)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determinar si el usuario puede restaurar la derivación automática
     *
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function modoAuto(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo() || !$tramite->esModoManual()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        // Usuario interno del mismo nodo actual puede restaurar a AUTO
        if ($usuario->esInterno()) {
            $tramite->loadMissing('nodoActual');
            if ($tramite->nodoActual && $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $tramite->nodoActual)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determinar si el usuario puede rechazar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function rechazar(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        // En nodo de entrada (orden=1), no permitir RECHAZAR después de ACEPTAR
        if ($this->esNodoEntrada($tramite)) {
            return false;
        }

        $estadosOk = in_array($tramite->estado, [
            \App\Domain\Tramites\EstadoTramite::PENDIENTE,
            \App\Domain\Tramites\EstadoTramite::EN_PROCESO,
            \App\Domain\Tramites\EstadoTramite::OBSERVADO,
            \App\Domain\Tramites\EstadoTramite::VERIFICADO,
        ], true);

        if (!$estadosOk) {
            return false;
        }

        if ($this->internoPuedeActuarPorEquipoNodo($usuario, $tramite)) {
            return true;
        }

        return $usuario->esPersonalMesaPartes();
    }

    /**
     * Determinar si el usuario puede observar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function observar(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        // En nodo de entrada (orden=1), no permitir OBSERVAR después de ACEPTAR
        if ($this->esNodoEntrada($tramite)) {
            return false;
        }

        $estadosOk = in_array($tramite->estado, [
            \App\Domain\Tramites\EstadoTramite::PENDIENTE,
            \App\Domain\Tramites\EstadoTramite::EN_PROCESO,
            \App\Domain\Tramites\EstadoTramite::VERIFICADO,
        ], true);

        if (!$estadosOk) {
            return false;
        }

        if ($this->internoPuedeActuarPorEquipoNodo($usuario, $tramite)) {
            return true;
        }

        return $usuario->esPersonalMesaPartes();
    }

    /**
     * Determinar si el usuario puede verificar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function verificar(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        if ($tramite->estaEnProceso() || $tramite->estaObservado()) {
            if ($this->internoPuedeActuarPorEquipoNodo($usuario, $tramite)) {
                return true;
            }
        }

        return $usuario->esPersonalMesaPartes()
            && ($tramite->estaEnProceso() || $tramite->estaObservado());
    }

    /**
     * Determinar si el usuario puede finalizar un trámite (acción PROCESAR:
     * verifica, genera voucher y cierra). Se usa desde el endpoint procesar.
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function finalizar(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->estaActivo()) {
            return false;
        }

        if ($usuario->esAdministrador()) {
            return true;
        }

        $tramite->loadMissing('nodoActual');
        if ($tramite->id_nodo_actual && $tramite->nodoActual?->puede_finalizar) {
            return $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $tramite->nodoActual);
        }

        if (!$usuario->esPersonalMesaPartes()) {
            return false;
        }

        return $tramite->estaVerificado();
    }

    /**
     * Interno miembro del equipo del nodo actual o del nodo de entrada (misma regla que la bandeja).
     */
    private function internoPuedeActuarPorEquipoNodo(Usuario $usuario, Tramite $tramite): bool
    {
        if (!$usuario->esInterno()) {
            return false;
        }

        $tramite->loadMissing(['procedimiento.nodoEntrada', 'nodoActual']);
        $nodo = $tramite->nodoActual ?? $tramite->procedimiento?->nodoEntrada;

        return $nodo && $this->tramiteFlujoService->usuarioInternoPuedeActuarEnNodo($usuario, $nodo);
    }

    /**
     * Verifica si el trámite está en el nodo de entrada (orden = 1)
     * En el nodo de entrada, después de ACEPTAR, solo se permite DERIVAR
     */
    private function esNodoEntrada(Tramite $tramite): bool
    {
        $tramite->loadMissing('nodoActual');
        return $tramite->id_nodo_actual
            && $tramite->nodoActual
            && $tramite->nodoActual->orden === 1;
    }
}