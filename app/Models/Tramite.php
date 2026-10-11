<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;

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
     * Dispara notificaciones de bandeja ante cambios de estado
     * o de responsable. Cubre ambos motores (clásico y por nodos)
     * sin tocar cada acción por separado.
     */
    protected static function booted(): void
    {
        static::updated(function (Tramite $tramite) {
            try {
                $servicio = app(\App\Services\NotificacionService::class);

                if ($tramite->wasChanged('estado')) {
                    $servicio->avisarCambioEstado($tramite, $tramite->getOriginal('estado'));
                }

                if ($tramite->wasChanged('id_usuario')) {
                    $servicio->avisarNuevoResponsable($tramite);
                }
            } catch (\Exception $e) {
                // Las notificaciones nunca deben romper la operación.
                \Illuminate\Support\Facades\Log::warning('Fallo al notificar cambio de trámite: ' . $e->getMessage());
            }
        });
    }

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
        'modo_manual_por_usuario',
        'ultimo_estado',
        'id_usuario',
        'creado_por',
        'id_titular',
        'titular_tipo_persona',
        'titular_dni',
        'titular_nombres',
        'titular_apellido_paterno',
        'titular_apellido_materno',
        'titular_ruc',
        'titular_razon_social',
        'tipo_tramite',
        'tramitescol',
        'observaciones',
        'limite_observacion',
        'voucher',
        'numero_voucher',
        'periodo',
        'oficio',
        'requiere_oficio',
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
        'requiere_oficio' => 'boolean',
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
     * Ciudadano titular del trámite presencial (puede no estar registrado).
     */
    public function titular()
    {
        return $this->belongsTo(Usuario::class, 'id_titular');
    }

    /**
     * Datos del solicitante para voucher/confirmación/hoja de ruta.
     *
     * Prioridad:
     * 1. Snapshot del titular (trámite presencial registrado por interno).
     * 2. Usuario titular vinculado (id_titular).
     * 3. Creador (trámite virtual de externo) o responsable legacy.
     *
     * @return array{nombres:string,apellido_paterno:string,apellido_materno:string,dni:string,ruc:?string,razon_social:?string,email:?string}
     */
    public function datosSolicitante(): array
    {
        if ($this->titular_dni || $this->titular_nombres || $this->titular_razon_social
            || $this->titular_tipo_persona || $this->id_titular) {
            if ($this->relationLoaded('titular') && $this->titular) {
                return [
                    'nombres' => $this->titular_nombres ?? $this->titular->nombres ?? '',
                    'apellido_paterno' => $this->titular_apellido_paterno ?? $this->titular->apellido_paterno ?? '',
                    'apellido_materno' => $this->titular_apellido_materno ?? $this->titular->apellido_materno ?? '',
                    'dni' => $this->titular_dni ?? $this->titular->dni ?? '',
                    'ruc' => $this->titular_ruc ?? $this->titular->ruc,
                    'razon_social' => $this->titular_razon_social ?? $this->titular->razon_social,
                    'email' => $this->titular->email ?? null,
                ];
            }

            return [
                'nombres' => $this->titular_nombres ?? '',
                'apellido_paterno' => $this->titular_apellido_paterno ?? '',
                'apellido_materno' => $this->titular_apellido_materno ?? '',
                'dni' => $this->titular_dni ?? '',
                'ruc' => $this->titular_ruc,
                'razon_social' => $this->titular_razon_social,
                'email' => null,
            ];
        }

        $base = $this->creador ?? $this->usuario;
        if ($base) {
            return [
                'nombres' => $base->nombres ?? '',
                'apellido_paterno' => $base->apellido_paterno ?? '',
                'apellido_materno' => $base->apellido_materno ?? '',
                'dni' => $base->dni ?? '',
                'ruc' => $base->ruc ?? null,
                'razon_social' => $base->razon_social ?? null,
                'email' => $base->email ?? null,
            ];
        }

        return [
            'nombres' => '', 'apellido_paterno' => '', 'apellido_materno' => '',
            'dni' => '', 'ruc' => null, 'razon_social' => null, 'email' => null,
        ];
    }

    /**
     * Nombre completo del solicitante (para vistas).
     */
    public function getNombreSolicitanteAttribute(): string
    {
        $d = $this->datosSolicitante();
        if (!empty($d['razon_social']) && ($this->titular_tipo_persona == 2 || $this->titular_tipo_persona == 3)) {
            return $d['razon_social'];
        }
        return trim("{$d['nombres']} {$d['apellido_paterno']} {$d['apellido_materno']}");
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
     * Verificar si el trámite requiere oficio del creador
     * 
     * @return bool
     */
    public function requiereOficio(): bool
    {
        return (bool) $this->requiere_oficio;
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
     * Verifica si el trámite está en modo manual (global)
     * 
     * @return bool
     */
    public function esModoManual(): bool
    {
        return $this->modo_derivacion === ModoDerivacion::MANUAL_FORZADO;
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
        return $this->fecha_creacion->diffInDays(now());
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