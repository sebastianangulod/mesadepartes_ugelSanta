<?php

namespace App\Repositories;

use App\Models\Tramite;
use App\DTOs\TramiteDTO;
use App\Domain\Tramites\EstadoTramite;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Repositorio de Trámites
 * 
 * Este repositorio maneja todas las operaciones de persistencia relacionadas
 * con los trámites, incluyendo consultas, filtros y paginación.
 * 
 * @package App\Repositories
 */
class TramiteRepository
{
    /**
     * Obtener trámite por ID
     * 
     * @param int $id
     * @return Tramite|null
     */
    public function obtenerPorId(int $id): ?Tramite
    {
        return Tramite::find($id);
    }

    /**
     * Obtener trámite con relaciones
     * 
     * @param int $id
     * @return Tramite|null
     */
    public function obtenerConRelaciones(int $id): ?Tramite
    {
        return Tramite::with(['usuario', 'procedimiento', 'revisor'])->find($id);
    }

    /**
     * Listar trámites con filtros
     * 
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function listarConFiltros(array $filtros = []): LengthAwarePaginator
    {
        $query = Tramite::with(['usuario', 'procedimiento']);

        // Aplicar filtros
        $this->aplicarFiltros($query, $filtros);

        // Ordenar por fecha de creación descendente por defecto
        $query->orderBy('fecha_creacion', 'desc');

        // Paginar resultados
        $perPage = $filtros['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Buscar trámites por término
     * 
     * @param string $termino
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function buscar(string $termino, array $filtros = []): LengthAwarePaginator
    {
        $query = Tramite::with(['usuario', 'procedimiento']);

        // Búsqueda por término en múltiples campos
        $query->where(function (Builder $q) use ($termino) {
            $q->where('numero_expediente', 'like', "%{$termino}%")
              ->orWhere('asunto', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%")
              ->orWhereHas('usuario', function (Builder $q) use ($termino) {
                  $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('apellido', 'like', "%{$termino}%")
                    ->orWhere('dni', 'like', "%{$termino}%");
              })
              ->orWhereHas('procedimiento', function (Builder $q) use ($termino) {
                  $q->where('nombre', 'like', "%{$termino}%")
                    ->orWhere('codigo', 'like', "%{$termino}%");
              });
        });

        // Aplicar filtros adicionales
        $this->aplicarFiltros($query, $filtros);

        // Ordenar por relevancia y fecha
        $query->orderByRaw("CASE 
            WHEN numero_expediente LIKE '{$termino}%' THEN 1
            WHEN asunto LIKE '{$termino}%' THEN 2
            ELSE 3
        END")
        ->orderBy('fecha_creacion', 'desc');

        // Paginar resultados
        $perPage = $filtros['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Crear nuevo trámite
     * 
     * @param TramiteDTO $datos
     * @return Tramite
     */
    public function crear(TramiteDTO $datos): Tramite
    {
        return DB::transaction(function () use ($datos) {
            $tramite = new Tramite();
            
            // Asignar datos del DTO al modelo
            $this->asignarDatosDTO($tramite, $datos);
            
            // Generar número de expediente si no existe
            if (empty($tramite->numero_expediente)) {
                $tramite->numero_expediente = TramiteDTO::generarNumeroExpediente();
            }
            
            // Estado inicial
            $tramite->estado = EstadoTramite::PENDIENTE;
            
            $tramite->save();
            
            return $tramite;
        });
    }

    /**
     * Actualizar trámite
     * 
     * @param int $id
     * @param TramiteDTO $datos
     * @return Tramite|null
     */
    public function actualizar(int $id, TramiteDTO $datos): ?Tramite
    {
        return DB::transaction(function () use ($id, $datos) {
            $tramite = $this->obtenerPorId($id);
            
            if (!$tramite) {
                return null;
            }
            
            // Asignar datos del DTO al modelo
            $this->asignarDatosDTO($tramite, $datos);
            
            $tramite->save();
            
            return $tramite;
        });
    }

    /**
     * Eliminar trámite (soft delete)
     * 
     * @param int $id
     * @return bool
     */
    public function eliminar(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $tramite = $this->obtenerPorId($id);
            
            if (!$tramite) {
                return false;
            }
            
            return $tramite->delete();
        });
    }

    /**
     * Obtener estadísticas de trámites por estado
     * 
     * @return array
     */
    public function obtenerEstadisticasPorEstado(): array
    {
        $estadisticas = Tramite::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        // Asegurar que todos los estados aparezcan (incluso si tienen 0)
        $todosEstados = [];
        foreach (EstadoTramite::todos() as $estado) {
            $todosEstados[$estado] = $estadisticas[$estado] ?? 0;
        }

        return $todosEstados;
    }

    /**
     * Obtener trámites por usuario
     * 
     * @param int $usuarioId
     * @param array $filtros
     * @return LengthAwarePaginator
     */
    public function obtenerPorUsuario(int $usuarioId, array $filtros = []): LengthAwarePaginator
    {
        $query = Tramite::with(['procedimiento'])
            ->where('id_usuario', $usuarioId);

        // Aplicar filtros adicionales
        $this->aplicarFiltros($query, $filtros);

        // Ordenar por fecha de creación descendente
        $query->orderBy('fecha_creacion', 'desc');

        // Paginar resultados
        $perPage = $filtros['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * Obtener trámites vencidos
     * 
     * @param int $diasVencimiento
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function obtenerVencidos(int $diasVencimiento = 30)
    {
        $fechaVencimiento = now()->subDays($diasVencimiento);

        return Tramite::whereIn('estado', [EstadoTramite::PENDIENTE, EstadoTramite::EN_PROCESO])
            ->where('fecha_creacion', '<', $fechaVencimiento)
            ->with(['usuario', 'procedimiento'])
            ->get();
    }

    /**
     * Aplicar filtros comunes a la query
     * 
     * @param Builder $query
     * @param array $filtros
     * @return void
     */
    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        // Filtrar por estado
        if (!empty($filtros['estado'])) {
            if (is_array($filtros['estado'])) {
                $query->whereIn('estado', $filtros['estado']);
            } else {
                $query->where('estado', $filtros['estado']);
            }
        }

        // Filtrar por usuario
        if (!empty($filtros['usuario_id'])) {
            $query->where('id_usuario', $filtros['usuario_id']);
        }

        // Filtrar por procedimiento
        if (!empty($filtros['procedimiento_id'])) {
            $query->where('id_procedimiento', $filtros['procedimiento_id']);
        }

        // Filtrar por rango de fechas
        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_creacion', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_creacion', '<=', $filtros['fecha_hasta']);
        }

        // Filtrar por número de expediente
        if (!empty($filtros['numero_expediente'])) {
            $query->where('numero_expediente', 'like', "%{$filtros['numero_expediente']}%");
        }

        // Filtrar por asunto
        if (!empty($filtros['asunto'])) {
            $query->where('asunto', 'like', "%{$filtros['asunto']}%");
        }
    }

    /**
     * Asignar datos del DTO al modelo
     * 
     * @param Tramite $tramite
     * @param TramiteDTO $datos
     * @return void
     */
    private function asignarDatosDTO(Tramite $tramite, TramiteDTO $datos): void
    {
        $tramite->numero_expediente = $datos->numero_expediente;
        $tramite->asunto = $datos->asunto;
        $tramite->descripcion = $datos->descripcion;
        $tramite->estado = $datos->estado;
        $tramite->id_usuario = $datos->id_usuario;
        $tramite->id_procedimiento = $datos->id_procedimiento;
        $tramite->archivo_expediente = $datos->archivo_expediente;
        $tramite->archivo_voucher = $datos->archivo_voucher;
        $tramite->fecha_presentacion = $datos->fecha_presentacion;
        $tramite->fecha_finalizacion = $datos->fecha_finalizacion;
        $tramite->observaciones = $datos->observaciones;
        $tramite->motivo_rechazo = $datos->motivo_rechazo;
        $tramite->revisado_por = $datos->revisado_por;
        $tramite->fecha_revision = $datos->fecha_revision;
    }
}