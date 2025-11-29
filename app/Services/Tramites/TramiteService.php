<?php

namespace App\Services\Tramites;

use App\Models\Tramite;
use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\StateTransitionService;
use App\Repositories\TramiteRepository;
use App\DTOs\TramiteDTO;
use App\DTOs\TransicionDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de gestión del flujo de estados de trámites
 * 
 * Este servicio contiene la lógica de negocio principal para la gestión
 * del CICLO DE VIDA de los trámites (aceptar, rechazar, observar, etc.).
 * 
 * La creación/registro de trámites se maneja en
 * App\Services\Tramite\TramiteCreacionService.
 * 
 * @package App\Services\Tramites
 */
class TramiteWorkflowService
{
    private StateTransitionService $stateTransitionService;
    private TramiteRepository $tramiteRepository;

    /**
     * Constructor
     * 
     * @param StateTransitionService $stateTransitionService
     * @param TramiteRepository $tramiteRepository
     */
    public function __construct(
        StateTransitionService $stateTransitionService,
        TramiteRepository $tramiteRepository
    ) {
        $this->stateTransitionService = $stateTransitionService;
        $this->tramiteRepository = $tramiteRepository;
    }

    /**
     * Aceptar un trámite (pasar a EN_PROCESO)
     * 
     * @param int $tramiteId
     * @param array $datos
     * @return Tramite
     * @throws \Exception
     */
    public function aceptar(int $tramiteId, array $datos = []): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $datos) {
            $tramite = $this->tramiteRepository->obtenerPorId($tramiteId);
            
            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            $this->stateTransitionService->transicionar(
                $tramite,
                EstadoTramite::EN_PROCESO,
                $datos
            );

            Log::info("Trámite {$tramiteId} aceptado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'datos' => $datos
            ]);

            return $tramite;
        });
    }

    /**
     * Rechazar un trámite
     * 
     * @param int $tramiteId
     * @param string $motivo
     * @param array $datosAdicionales
     * @return Tramite
     * @throws \Exception
     */
    public function rechazar(int $tramiteId, string $motivo, array $datosAdicionales = []): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $motivo, $datosAdicionales) {
            $tramite = $this->tramiteRepository->obtenerPorId($tramiteId);
            
            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            $datos = array_merge($datosAdicionales, [
                'motivo_rechazo' => $motivo,
                'rechazado_por' => auth()->id(),
                'fecha_rechazo' => now()
            ]);

            $this->stateTransitionService->transicionar(
                $tramite,
                EstadoTramite::RECHAZADO,
                $datos
            );

            Log::info("Trámite {$tramiteId} rechazado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'motivo' => $motivo
            ]);

            return $tramite;
        });
    }

    /**
     * Observar un trámite
     * 
     * @param int $tramiteId
     * @param string $observacion
     * @param array $datosAdicionales
     * @return Tramite
     * @throws \Exception
     */
    public function observar(int $tramiteId, string $observacion, array $datosAdicionales = []): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $observacion, $datosAdicionales) {
            $tramite = $this->tramiteRepository->obtenerPorId($tramiteId);
            
            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            $datos = array_merge($datosAdicionales, [
                'observacion' => $observacion,
                'observado_por' => auth()->id(),
                'fecha_observacion' => now()
            ]);

            $this->stateTransitionService->transicionar(
                $tramite,
                EstadoTramite::OBSERVADO,
                $datos
            );

            Log::info("Trámite {$tramiteId} observado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'observacion' => $observacion
            ]);

            return $tramite;
        });
    }

    /**
     * Verificar un trámite
     * 
     * @param int $tramiteId
     * @param array $datos
     * @return Tramite
     * @throws \Exception
     */
    public function verificar(int $tramiteId, array $datos = []): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $datos) {
            $tramite = $this->tramiteRepository->obtenerPorId($tramiteId);
            
            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            $this->stateTransitionService->transicionar(
                $tramite,
                EstadoTramite::VERIFICADO,
                $datos
            );

            Log::info("Trámite {$tramiteId} verificado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'datos' => $datos
            ]);

            return $tramite;
        });
    }

    /**
     * Finalizar un trámite
     * 
     * @param int $tramiteId
     * @param array $datos
     * @return Tramite
     * @throws \Exception
     */
    public function finalizar(int $tramiteId, array $datos = []): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $datos) {
            $tramite = $this->tramiteRepository->obtenerPorId($tramiteId);
            
            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            $datos['finalizado_por'] = auth()->id();
            $datos['fecha_finalizacion'] = now();

            $this->stateTransitionService->transicionar(
                $tramite,
                EstadoTramite::FINALIZADO,
                $datos
            );

            Log::info("Trámite {$tramiteId} finalizado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'datos' => $datos
            ]);

            return $tramite;
        });
    }

    /**
     * Obtener trámite con relaciones
     * 
     * @param int $tramiteId
     * @return Tramite|null
     */
    public function obtenerTramiteCompleto(int $tramiteId): ?Tramite
    {
        return $this->tramiteRepository->obtenerConRelaciones($tramiteId);
    }

    /**
     * Listar trámites con filtros
     * 
     * @param array $filtros
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listarTramites(array $filtros = [])
    {
        return $this->tramiteRepository->listarConFiltros($filtros);
    }

    /**
     * Buscar trámites
     * 
     * @param string $termino
     * @param array $filtros
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function buscarTramites(string $termino, array $filtros = [])
    {
        return $this->tramiteRepository->buscar($termino, $filtros);
    }

    /**
     * Crear nuevo trámite
     * 
     * @param TramiteDTO $datos
     * @return Tramite
     * @throws \Exception
     */
    public function crear(TramiteDTO $datos): Tramite
    {
        return DB::transaction(function () use ($datos) {
            $tramite = $this->tramiteRepository->crear($datos);

            Log::info("Trámite creado", [
                'tramite_id' => $tramite->id,
                'usuario_id' => auth()->id(),
                'datos' => $datos->toArray()
            ]);

            return $tramite;
        });
    }

    /**
     * Actualizar trámite
     * 
     * @param int $tramiteId
     * @param TramiteDTO $datos
     * @return Tramite
     * @throws \Exception
     */
    public function actualizar(int $tramiteId, TramiteDTO $datos): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $datos) {
            $tramite = $this->tramiteRepository->actualizar($tramiteId, $datos);

            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            Log::info("Trámite {$tramiteId} actualizado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'datos' => $datos->toArray()
            ]);

            return $tramite;
        });
    }

    /**
     * Eliminar trámite (soft delete)
     * 
     * @param int $tramiteId
     * @return bool
     * @throws \Exception
     */
    public function eliminar(int $tramiteId): bool
    {
        return DB::transaction(function () use ($tramiteId) {
            $tramite = $this->tramiteRepository->obtenerPorId($tramiteId);
            
            if (!$tramite) {
                throw new \Exception("Trámite no encontrado");
            }

            // No permitir eliminar trámites en ciertos estados
            if (in_array($tramite->estado, [EstadoTramite::EN_PROCESO, EstadoTramite::VERIFICADO])) {
                throw new \Exception("No se puede eliminar un trámite en estado {$tramite->estado}");
            }

            $resultado = $this->tramiteRepository->eliminar($tramiteId);

            Log::info("Trámite {$tramiteId} eliminado", [
                'tramite_id' => $tramiteId,
                'usuario_id' => auth()->id(),
                'estado_anterior' => $tramite->estado
            ]);

            return $resultado;
        });
    }
}