<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Tramites\EstadoTramite;

/**
 * Modelo Tramite
 * 
 * Este modelo representa los trámites del sistema de mesa de partes.
 * Adaptado a la estructura de la tabla 'tramites' del SQL.
 * 
 * @property int $id
 * @property string|null $archivo_adjunto
 * @property int|null $id_procedimiento
 * @property \Carbon\Carbon|null $fecha_creacion
 * @property string $estado ENUM('PENDIENTE','OBSERVADO','EN_PROCESO','FINALIZADO','VERIFICADO','RECHAZADO')
 * @property string|null $ultimo_estado
 * @property int $id_usuario
 * @property int|null $creado_por
 * @property string|null $tipo_tramite ENUM('PRESENCIAL','VIRTUAL')
 * @property string|null $tramitescol
 * @property string|null $observaciones
 * @property \Carbon\Carbon|null $limite_observacion
 * @property string|null $voucher
 * @property string|null $numero_voucher
 * @property string|null $periodo
 * @property string|null $oficio
 * @property int|null $rechazado_por
 * @property \Carbon\Carbon|null $fecha_rechazo
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @package App\Models
 */
class Tramite extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo
     * 
     * @var string
     */
    protected $table = 'tramites';

    /**
     * Los atributos que se pueden asignar masivamente
     * 
     * @var array<string>
     */
    protected $fillable = [
        'archivo_adjunto',
        'id_procedimiento',
        'fecha_creacion',
        'estado',
        'nivel_actual',
        'id_nodo_actual',
        'modo_derivacion',
        'ultimo_estado',
        'id_usuario',
        'creado_por',
        'tipo_tramite',
        'tramitescol',
        'observaciones',
        'limite_observacion',
        'voucher',
        'numero_voucher',
        'periodo',
        'oficio',
        'rechazado_por',
        'fecha_rechazo',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_creacion' => 'datetime',
        'limite_observacion' => 'date',
        'fecha_rechazo' => 'datetime',
    ];
    
    /**
     * Indica si la tabla usa timestamps
     * La tabla tramites NO tiene created_at ni updated_at
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * Relación con usuario (solicitante)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Relación con el usuario que creó el trámite (solicitante)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    /**
     * Relación con procedimiento
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function procedimiento()
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento');
    }

    /**
     * Nodo actual del flujo configurable (null = pendiente sin ingreso a área/nodo).
     */
    public function nodoActual()
    {
        return $this->belongsTo(FlujoNodo::class, 'id_nodo_actual');
    }

    /**
     * Historial de acciones y cambios de nodo.
     */
    public function movimientosFlujo()
    {
        return $this->hasMany(TramiteMovimiento::class, 'tramite_id')->orderByDesc('id');
    }

    /**
     * Relación con usuario revisor
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revisor()
    {
        return $this->belongsTo(Usuario::class, 'revisado_por');
    }

    /**
     * Verificar si el trámite está pendiente
     * 
     * @return bool
     */
    public function estaPendiente(): bool
    {
        return $this->estado === EstadoTramite::PENDIENTE;
    }

    /**
     * Verificar si el trámite está en proceso
     * 
     * @return bool
     */
    public function estaEnProceso(): bool
    {
        return $this->estado === EstadoTramite::EN_PROCESO;
    }

    /**
     * Verificar si el trámite está finalizado
     * 
     * @return bool
     */
    public function estaFinalizado(): bool
    {
        return $this->estado === EstadoTramite::FINALIZADO;
    }

    /**
     * Verificar si el trámite está rechazado
     * 
     * @return bool
     */
    public function estaRechazado(): bool
    {
        return $this->estado === EstadoTramite::RECHAZADO;
    }

    /**
     * Verificar si el trámite está observado
     * 
     * @return bool
     */
    public function estaObservado(): bool
    {
        return $this->estado === EstadoTramite::OBSERVADO;
    }

    /**
     * Verificar si el trámite está verificado
     * 
     * @return bool
     */
    public function estaVerificado(): bool
    {
        return $this->estado === EstadoTramite::VERIFICADO;
    }

    /**
     * Verificar si el trámite puede ser editado
     * 
     * @return bool
     */
    public function puedeSerEditado(): bool
    {
        return in_array($this->estado, [
            EstadoTramite::PENDIENTE,
            EstadoTramite::OBSERVADO,
        ]);
    }

    /**
     * Verificar si el trámite puede ser eliminado
     * 
     * @return bool
     */
    public function puedeSerEliminado(): bool
    {
        return !in_array($this->estado, [
            EstadoTramite::EN_PROCESO,
            EstadoTramite::VERIFICADO,
            EstadoTramite::FINALIZADO,
        ]);
    }

    /**
     * Obtener etiqueta del estado
     * 
     * @return string
     */
    public function getEstadoEtiquetaAttribute(): string
    {
        return EstadoTramite::obtenerEtiqueta($this->estado);
    }

    /**
     * Obtener clases CSS para el estado
     * 
     * @return string
     */
    public function getEstadoClasesCssAttribute(): string
    {
        return EstadoTramite::obtenerClasesCss($this->estado);
    }

    /**
     * Obtener el nombre del archivo adjunto sin ruta
     * 
     * @return string|null
     */
    public function getNombreArchivoAdjuntoAttribute(): ?string
    {
        if (!$this->archivo_adjunto) {
            return null;
        }

        return basename($this->archivo_adjunto);
    }

    /**
     * Obtener el nombre del voucher sin ruta
     * 
     * @return string|null
     */
    public function getNombreVoucherAttribute(): ?string
    {
        if (!$this->voucher) {
            return null;
        }

        return basename($this->voucher);
    }

    /**
     * Obtener la antigüedad del trámite en días
     * 
     * @return int
     */
    public function getAntiguedadDiasAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Verificar si el trámite está vencido (más de 30 días)
     * 
     * @return bool
     */
    public function estaVencido(): bool
    {
        if ($this->estaFinalizado() || $this->estaRechazado()) {
            return false;
        }

        return $this->antiguedad_dias > 30;
    }

    /**
     * Scope para trámites activos (no finalizados ni rechazados)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->whereNotIn('estado', [EstadoTramite::FINALIZADO, EstadoTramite::RECHAZADO]);
    }

    /**
     * Scope para trámites vencidos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $dias
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVencidos($query, $dias = 30)
    {
        $fechaLimite = now()->subDays($dias);
        
        return $query->activos()
            ->where('fecha_creacion', '<', $fechaLimite);
    }

    /**
     * Scope para filtrar por estado
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $estado
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorEstado($query, $estado)
    {
        if (is_array($estado)) {
            return $query->whereIn('estado', $estado);
        }
        
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar por usuario
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $usuarioId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorUsuario($query, $usuarioId)
    {
        return $query->where('id_usuario', $usuarioId);
    }

    /**
     * Scope para búsqueda general
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $termino
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('numero_expediente', 'like', "%{$termino}%")
              ->orWhere('asunto', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }
}