<?php

namespace App\Policies;

use App\Models\Usuario;
use App\Models\Tramite;
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

    /**
     * Determinar si el usuario puede ver cualquier trámite
     * 
     * @param Usuario $usuario
     * @return bool
     */
    public function viewAny(Usuario $usuario): bool
    {
        // Solo usuarios autenticados pueden ver la lista de trámites
        return true;
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
        // Cualquier usuario autenticado puede ver cualquier trámite
        return true;
    }

    /**
     * Verificar si un usuario está en la "hoja de ruta" del trámite
     * (pertenece al mismo nivel y área/subárea del procedimiento)
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    private function usuarioEnHojaDeRuta(Usuario $usuario, Tramite $tramite): bool
    {
        // Si no hay procedimiento, no se puede verificar
        if (!$tramite->procedimiento) {
            return false;
        }

        $procedimiento = $tramite->procedimiento;

        // Obtener niveles del usuario
        $nivelesUsuario = \App\Models\UsuariosNiveles::where('id_usuario', $usuario->id)->get();
        
        if ($nivelesUsuario->isEmpty()) {
            return false;
        }

        // Verificar si el usuario tiene algún nivel que coincida con la hoja de ruta
        foreach ($nivelesUsuario as $nivelUsuario) {
            $nivel = $nivelUsuario->nivel;
            $idNivel = $nivelUsuario->id_nivel;

            // Nivel 1: verificar que pertenece a la misma área
            if ($nivel == 1 && $procedimiento->id_area == $idNivel) {
                return true;
            }

            // Nivel 2: verificar que pertenece a la misma subárea
            if ($nivel == 2 && $procedimiento->id_subarea == $idNivel) {
                return true;
            }

            // Nivel 3: verificar que pertenece a la misma subárea (a través del nivel 3)
            if ($nivel == 3) {
                $nivel3Modelo = \App\Models\Nivel3::find($idNivel);
                if ($nivel3Modelo && $nivel3Modelo->id_nivel2 == $procedimiento->id_subarea) {
                    return true;
                }
            }

            // Nivel 4: verificar que pertenece al mismo nivel final
            if ($nivel == 4 && $procedimiento->nivel_final == '4' && $procedimiento->id_nivel_final == $idNivel) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determinar si el usuario puede crear trámites
     * 
     * @param Usuario $usuario
     * @return bool
     */
    public function create(Usuario $usuario): bool
    {
        // Cualquier usuario autenticado puede crear trámites
        return $usuario->estaActivo();
    }

    /**
     * Determinar si el usuario puede actualizar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function update(Usuario $usuario, Tramite $tramite): bool
    {
        // Los administradores pueden actualizar cualquier trámite
        if ($usuario->esAdministrador()) {
            return true;
        }

        // Los usuarios solo pueden actualizar sus propios trámites en ciertos estados
        if ($usuario->id === $tramite->id_usuario) {
            return $tramite->puedeSerEditado() && $usuario->estaActivo();
        }

        return false;
    }

    /**
     * Determinar si el usuario puede eliminar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function delete(Usuario $usuario, Tramite $tramite): bool
    {
        // Solo los administradores pueden eliminar trámites
        if (!$usuario->esAdministrador()) {
            return false;
        }

        // No se pueden eliminar trámites en ciertos estados
        return $tramite->puedeSerEliminado();
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
        // Solo el personal de mesa de partes puede aceptar trámites
        if (!$usuario->esPersonalMesaPartes()) {
            return false;
        }

        // Solo se pueden aceptar trámites pendientes
        return $tramite->estaPendiente() && $usuario->estaActivo();
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
        // Solo el personal de mesa de partes puede rechazar trámites
        if (!$usuario->esPersonalMesaPartes()) {
            return false;
        }

        // Solo se pueden rechazar trámites en ciertos estados
        return in_array($tramite->estado, [
            \App\Domain\Tramites\EstadoTramite::PENDIENTE,
            \App\Domain\Tramites\EstadoTramite::EN_PROCESO,
            \App\Domain\Tramites\EstadoTramite::OBSERVADO,
            \App\Domain\Tramites\EstadoTramite::VERIFICADO,
        ]) && $usuario->estaActivo();
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
        // Solo el personal de mesa de partes puede observar trámites
        if (!$usuario->esPersonalMesaPartes()) {
            return false;
        }

        // Solo se pueden observar trámites en ciertos estados
        return in_array($tramite->estado, [
            \App\Domain\Tramites\EstadoTramite::PENDIENTE,
            \App\Domain\Tramites\EstadoTramite::EN_PROCESO,
            \App\Domain\Tramites\EstadoTramite::VERIFICADO,
        ]) && $usuario->estaActivo();
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
        // Solo el personal de mesa de partes puede verificar trámites
        if (!$usuario->esPersonalMesaPartes()) {
            return false;
        }

        // Solo se pueden verificar trámites en proceso
        return $tramite->estaEnProceso() && $usuario->estaActivo();
    }

    /**
     * Determinar si el usuario puede finalizar un trámite
     * 
     * @param Usuario $usuario
     * @param Tramite $tramite
     * @return bool
     */
    public function finalizar(Usuario $usuario, Tramite $tramite): bool
    {
        // Solo el personal de mesa de partes puede finalizar trámites
        if (!$usuario->esPersonalMesaPartes()) {
            return false;
        }

        // Solo se pueden finalizar trámites verificados
        return $tramite->estaVerificado() && $usuario->estaActivo();
    }
}