<?php

namespace App\Domain\Tramites;

use App\Models\Tramite;
use App\Exceptions\InvalidStateTransitionException;

/**
 * Servicio de transiciones de estado de trámites
 * 
 * Este servicio contiene toda la lógica de negocio para las transiciones
 * de estado de los trámites, asegurando que solo se permitan cambios válidos.
 * 
 * @package App\Domain\Tramites
 */
class StateTransitionService
{
    /**
     * Mapa de transiciones permitidas
     * 
     * @var array<string, array<string>>
     */
    private const TRANSICIONES_PERMITIDAS = [
        EstadoTramite::PENDIENTE => [
            EstadoTramite::EN_PROCESO,
            EstadoTramite::OBSERVADO,
            EstadoTramite::RECHAZADO,
        ],
        EstadoTramite::EN_PROCESO => [
            EstadoTramite::VERIFICADO,
            EstadoTramite::OBSERVADO,
            EstadoTramite::RECHAZADO,
        ],
        EstadoTramite::OBSERVADO => [
            EstadoTramite::EN_PROCESO,
            EstadoTramite::RECHAZADO,
        ],
        EstadoTramite::VERIFICADO => [
            EstadoTramite::FINALIZADO,
            EstadoTramite::OBSERVADO,
            EstadoTramite::RECHAZADO,
        ],
        EstadoTramite::FINALIZADO => [], // Estado final, no permite transiciones
        EstadoTramite::RECHAZADO => [], // Estado final, no permite transiciones
    ];

    /**
     * Verificar si una transición de estado es válida
     * 
     * @param string $estadoActual
     * @param string $estadoNuevo
     * @return bool
     */
    public function puedeTransicionar(string $estadoActual, string $estadoNuevo): bool
    {
        // Validar que ambos estados existan
        if (!EstadoTramite::esValido($estadoActual) || !EstadoTramite::esValido($estadoNuevo)) {
            return false;
        }

        // No permitir transición al mismo estado
        if ($estadoActual === $estadoNuevo) {
            return false;
        }

        // Verificar si la transición está permitida
        $transicionesPermitidas = self::TRANSICIONES_PERMITIDAS[$estadoActual] ?? [];
        
        return in_array($estadoNuevo, $transicionesPermitidas);
    }

    /**
     * Ejecutar transición de estado
     * 
     * @param Tramite $tramite
     * @param string $estadoNuevo
     * @param array $datosAdicionales
     * @return void
     * @throws InvalidStateTransitionException
     */
    public function transicionar(Tramite $tramite, string $estadoNuevo, array $datosAdicionales = []): void
    {
        $estadoActual = $tramite->estado;

        if (!$this->puedeTransicionar($estadoActual, $estadoNuevo)) {
            throw new InvalidStateTransitionException(
                "Transición no permitida de '{$estadoActual}' a '{$estadoNuevo}'"
            );
        }

        // Ejecutar lógica específica de la transición
        $this->ejecutarLogicaTransicion($tramite, $estadoActual, $estadoNuevo, $datosAdicionales);

        // Actualizar el estado del trámite
        $tramite->estado = $estadoNuevo;
        $tramite->save();

        // Registrar la transición en el log
        $this->registrarTransicion($tramite, $estadoActual, $estadoNuevo, $datosAdicionales);
    }

    /**
     * Ejecutar lógica específica de la transición
     * 
     * @param Tramite $tramite
     * @param string $estadoActual
     * @param string $estadoNuevo
     * @param array $datosAdicionales
     * @return void
     */
    private function ejecutarLogicaTransicion(Tramite $tramite, string $estadoActual, string $estadoNuevo, array $datosAdicionales): void
    {
        // Lógica específica para cada tipo de transición
        switch ($estadoNuevo) {
            case EstadoTramite::EN_PROCESO:
                $this->alEntrarEnProceso($tramite, $datosAdicionales);
                break;
                
            case EstadoTramite::VERIFICADO:
                $this->alVerificar($tramite, $datosAdicionales);
                break;
                
            case EstadoTramite::OBSERVADO:
                $this->alObservar($tramite, $datosAdicionales);
                break;
                
            case EstadoTramite::FINALIZADO:
                $this->alFinalizar($tramite, $datosAdicionales);
                break;
                
            case EstadoTramite::RECHAZADO:
                $this->alRechazar($tramite, $datosAdicionales);
                break;
        }
    }

    /**
     * Lógica al pasar a EN_PROCESO
     * 
     * @param Tramite $tramite
     * @param array $datosAdicionales
     * @return void
     */
    private function alEntrarEnProceso(Tramite $tramite, array $datosAdicionales): void
    {
        // Guardar el estado anterior si no existe
        if (!$tramite->ultimo_estado) {
            $tramite->ultimo_estado = $tramite->estado;
        }

        // Si se proporciona un usuario responsable, asignarlo
        if (isset($datosAdicionales['usuario_responsable'])) {
            $tramite->id_usuario = $datosAdicionales['usuario_responsable'];
        }

        // Actualizar fecha de actualización
        $tramite->updated_at = now();
    }

    /**
     * Lógica al verificar
     * 
     * @param Tramite $tramite
     * @param array $datosAdicionales
     * @return void
     */
    private function alVerificar(Tramite $tramite, array $datosAdicionales): void
    {
        // Guardar el estado anterior
        if (!$tramite->ultimo_estado) {
            $tramite->ultimo_estado = $tramite->estado;
        }

        // Limpiar observaciones si se está verificando después de una corrección
        if ($tramite->estado === EstadoTramite::OBSERVADO) {
            $tramite->observaciones = null;
            $tramite->limite_observacion = null;
        }

        // Actualizar fecha de actualización
        $tramite->updated_at = now();
    }

    /**
     * Lógica al observar
     * 
     * @param Tramite $tramite
     * @param array $datosAdicionales
     * @return void
     */
    private function alObservar(Tramite $tramite, array $datosAdicionales): void
    {
        // Guardar el estado anterior
        $tramite->ultimo_estado = $tramite->estado;

        // Registrar observaciones si se proporcionan
        if (isset($datosAdicionales['observaciones'])) {
            $tramite->observaciones = $datosAdicionales['observaciones'];
        }

        // Establecer límite de observación (2 días por defecto)
        $diasLimite = $datosAdicionales['dias_limite'] ?? 2;
        $tramite->limite_observacion = now()->addDays($diasLimite);

        // Si se proporciona un oficio, guardarlo
        if (isset($datosAdicionales['oficio'])) {
            $tramite->oficio = $datosAdicionales['oficio'];
        }

        // Actualizar fecha de actualización
        $tramite->updated_at = now();
    }

    /**
     * Lógica al finalizar
     * 
     * @param Tramite $tramite
     * @param array $datosAdicionales
     * @return void
     */
    private function alFinalizar(Tramite $tramite, array $datosAdicionales): void
    {
        // Guardar el estado anterior
        if (!$tramite->ultimo_estado) {
            $tramite->ultimo_estado = $tramite->estado;
        }

        // Si se proporciona un número de voucher, guardarlo
        if (isset($datosAdicionales['numero_voucher'])) {
            $tramite->numero_voucher = $datosAdicionales['numero_voucher'];
        }

        // Si se proporciona una ruta de voucher, guardarla
        if (isset($datosAdicionales['voucher'])) {
            $tramite->voucher = $datosAdicionales['voucher'];
        }

        // Actualizar fecha de actualización
        $tramite->updated_at = now();
    }

    /**
     * Lógica al rechazar
     * 
     * @param Tramite $tramite
     * @param array $datosAdicionales
     * @return void
     */
    private function alRechazar(Tramite $tramite, array $datosAdicionales): void
    {
        // Guardar el estado anterior
        $tramite->ultimo_estado = $tramite->estado;

        // Registrar motivo de rechazo en observaciones
        if (isset($datosAdicionales['motivo'])) {
            $prefijo = isset($datosAdicionales['rechazo_definitivo']) && $datosAdicionales['rechazo_definitivo']
                ? 'RECHAZADO POR ESPECIALISTA: '
                : 'RECHAZADO: ';
            $tramite->observaciones = $prefijo . $datosAdicionales['motivo'];
        }

        // Si es rechazo definitivo, limpiar límite de observación
        if (isset($datosAdicionales['rechazo_definitivo']) && $datosAdicionales['rechazo_definitivo']) {
            $tramite->limite_observacion = null;
            $tramite->rechazado_por = $datosAdicionales['usuario_id'] ?? null;
            $tramite->fecha_rechazo = now();
        }

        // Actualizar fecha de actualización
        $tramite->updated_at = now();
    }

    /**
     * Registrar transición en el log
     * 
     * @param Tramite $tramite
     * @param string $estadoAnterior
     * @param string $estadoNuevo
     * @param array $datosAdicionales
     * @return void
     */
    private function registrarTransicion(Tramite $tramite, string $estadoAnterior, string $estadoNuevo, array $datosAdicionales): void
    {
        // Por ahora, usamos el log de Laravel
        // En el futuro, se puede crear una tabla de auditoría específica
        \Log::info('Transición de estado de trámite', [
            'tramite_id' => $tramite->id,
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'usuario_id' => $datosAdicionales['usuario_id'] ?? null,
            'motivo' => $datosAdicionales['motivo'] ?? $datosAdicionales['observaciones'] ?? null,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}