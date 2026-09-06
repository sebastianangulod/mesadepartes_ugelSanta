<?php

namespace App\Observers;

use App\Models\UsuariosNiveles;
use App\Models\FlujoEquipo;
use App\Models\FlujoEquipoUsuario;
use App\Models\FlujoNodo;
use Illuminate\Support\Facades\DB;

class UsuariosNivelesObserver
{
    /**
     * Handle the UsuariosNiveles "created" event.
     */
    public function created(UsuariosNiveles $usuariosNiveles): void
    {
        $this->syncEquipo($usuariosNiveles);
    }

    /**
     * Handle the UsuariosNiveles "updated" event.
     */
    public function updated(UsuariosNiveles $usuariosNiveles): void
    {
        $this->syncEquipo($usuariosNiveles);
    }

    /**
     * Handle the UsuariosNiveles "deleted" event.
     */
    public function deleted(UsuariosNiveles $usuariosNiveles): void
    {
        $this->removeFromEquipos($usuariosNiveles);
    }

    /**
     * Sincronizar usuario con los equipos correspondientes a su nivel
     */
    private function syncEquipo(UsuariosNiveles $un): void
    {
        if (!$un->id_usuario || !$un->nivel || !$un->id_nivel) {
            return;
        }

        // Solo usuarios internos (tipo_user = 2) participan en flujos
        $usuario = $un->usuario;
        if (!$usuario || (int) $usuario->tipo_user !== 2) {
            return;
        }

        // Buscar nodos que correspondan a este nivel
        // Los nodos tienen nombre_tecnico como "Nivel X - NombreNivel"
        $nivelNombre = $this->getNombreNivel($un->nivel, $un->id_nivel);
        if (!$nivelNombre) {
            return;
        }

        // Buscar equipos cuyo nodo tenga nombre_tecnico que coincida
        $equipos = FlujoEquipo::whereHas('nodo', function ($q) use ($un, $nivelNombre) {
            $q->where('nombre_tecnico', 'LIKE', "Nivel {$un->nivel} - {$nivelNombre}%");
        })->get();

        foreach ($equipos as $equipo) {
            FlujoEquipoUsuario::updateOrCreate(
                [
                    'equipo_id' => $equipo->id,
                    'usuario_id' => $un->id_usuario,
                ],
                []
            );
        }
    }

    /**
     * Remover usuario de todos los equipos de su nivel anterior
     */
    private function removeFromEquipos(UsuariosNiveles $un): void
    {
        $nivelNombre = $this->getNombreNivel($un->nivel, $un->id_nivel);
        if (!$nivelNombre) {
            return;
        }

        $equipoIds = FlujoEquipo::whereHas('nodo', function ($q) use ($un, $nivelNombre) {
            $q->where('nombre_tecnico', 'LIKE', "Nivel {$un->nivel} - {$nivelNombre}%");
        })->pluck('id');

        FlujoEquipoUsuario::where('usuario_id', $un->id_usuario)
            ->whereIn('equipo_id', $equipoIds)
            ->delete();
    }

    /**
     * Obtener nombre del nivel según número y ID
     */
    private function getNombreNivel(int $nivel, int $idNivel): ?string
    {
        return match ($nivel) {
            1 => \App\Models\Nivel1::where('id', $idNivel)->value('nombre'),
            2 => \App\Models\Nivel2::where('id', $idNivel)->value('nombre'),
            3 => \App\Models\Nivel3::where('id', $idNivel)->value('nombre'),
            4 => \App\Models\Nivel4::where('id', $idNivel)->value('nombre'),
            default => null,
        };
    }
}