<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Procedimiento
 * 
 * Este modelo representa los procedimientos disponibles en el sistema.
 * Adaptado a la estructura de la tabla 'procedimientos' del SQL.
 * 
 * @property int $id
 * @property string $nombre
 * @property int|null $id_area
 * @property int|null $id_subarea
 * @property string $nivel_final ENUM('2','3','4')
 * @property int $id_nivel_final
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * 
 * @package App\Models
 */
class Procedimiento extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo
     * 
     * @var string
     */
    protected $table = 'procedimientos';

    /**
     * Los atributos que se pueden asignar masivamente
     * 
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'id_area',
        'id_subarea',
        'nivel_final',
        'id_nivel_final',
        'created_by',
        'updated_by',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con trámites
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tramites()
    {
        return $this->hasMany(Tramite::class, 'id_procedimiento');
    }

    /**
     * Relación con usuarios asignados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usuariosAsignados()
    {
        return $this->belongsToMany(Usuario::class, 'procedimiento_usuarios', 'id_procedimiento', 'id_usuario');
    }

    /**
     * Relación con nivel 1 (área principal)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Nivel1::class, 'id_area');
    }

    /**
     * Relación con nivel 2 (subárea)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subarea()
    {
        return $this->belongsTo(Nivel2::class, 'id_subarea');
    }

    /**
     * Relación con nivel 1 (alias de area)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel1()
    {
        return $this->belongsTo(Nivel1::class, 'id_area');
    }

    /**
     * Relación con nivel 2 (alias de subarea)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel2()
    {
        return $this->belongsTo(Nivel2::class, 'id_subarea');
    }

    /**
     * Relación con nivel 3 (cuando nivel_final = 3)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel3()
    {
        return $this->belongsTo(Nivel3::class, 'id_nivel_final')
                    ->where('nivel_final', '3');
    }

    /**
     * Relación con nivel 4 (cuando nivel_final = 4)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel4()
    {
        return $this->belongsTo(Nivel4::class, 'id_nivel_final')
                    ->where('nivel_final', '4');
    }

    /**
     * Relación con nivel final según el tipo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivelFinal()
    {
        switch ($this->nivel_final) {
            case '2':
                return $this->belongsTo(Nivel2::class, 'id_nivel_final');
            case '3':
                return $this->belongsTo(Nivel3::class, 'id_nivel_final');
            case '4':
                return $this->belongsTo(Nivel4::class, 'id_nivel_final');
            default:
                return null;
        }
    }

    /**
     * Relación con usuario creador
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    /**
     * Relación con usuario actualizador
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }

    /**
     * Verificar si el procedimiento está activo
     * 
     * @return bool
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    /**
     * Obtener nombre completo con código
     * 
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }

    /**
     * Obtener costo formateado
     * 
     * @return string
     */
    public function getCostoFormateadoAttribute(): string
    {
        return 'S/ ' . number_format($this->costo, 2);
    }

    /**
     * Obtener tiempo estimado formateado
     * 
     * @return string
     */
    public function getTiempoEstimadoFormateadoAttribute(): string
    {
        if ($this->tiempo_estimado_dias === 1) {
            return '1 día';
        }
        
        return "{$this->tiempo_estimado_dias} días";
    }

    /**
     * Scope para procedimientos activos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    /**
     * Scope para filtrar por área
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $areaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorArea($query, $areaId)
    {
        return $query->where('id_area', $areaId);
    }

    /**
     * Scope para filtrar por nivel
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nivelId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorNivel($query, $nivelId)
    {
        return $query->where('id_nivel', $nivelId);
    }

    /**
     * Scope para búsqueda
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $termino
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo', 'like', "%{$termino}%")
              ->orWhere('nombre', 'like', "%{$termino}%")
              ->orWhere('descripcion', 'like', "%{$termino}%");
        });
    }
}