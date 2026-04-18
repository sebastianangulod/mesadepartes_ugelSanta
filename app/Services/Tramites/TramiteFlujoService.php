<?php

namespace App\Services\Tramites;

use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Models\FlujoNodo;
use App\Models\Tramite;
use App\Models\TramiteMovimiento;
use App\Models\Usuario;
use Exception;
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
}
