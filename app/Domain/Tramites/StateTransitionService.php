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
        // TODO: Implementar lógica específica
        // - Asignar a usuario responsable
        // - Enviar notificaciones
        // - Actualizar fechas
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
        // TODO: Implementar lógica específica
        // - Validar documentación completa
        // - Generar reportes
        // - Preparar para finalización
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
        // TODO: Implementar lógica específica
        // - Registrar observaciones
        // - Notificar al solicitante
        // - Establecer plazos para corrección
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
        // TODO: Implementar lógica específica
        // - Generar certificado/número de trámite
        // - Archivar documentación
        // - Notificar finalización
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
        // TODO: Implementar lógica específica
        // - Registrar motivo de rechazo
        // - Notificar al solicitante
        // - Devolver documentación si aplica
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
        // TODO: Implementar logging de transiciones
        // - Crear registro en tabla de auditoría
        // - Registrar usuario que realizó el cambio
        // - Guardar timestamp y motivo si existe
    }
}