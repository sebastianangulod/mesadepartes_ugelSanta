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
        'id_nodo_entrada',
        'created_by',
        'updated_by',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     * 
     * @var array<string, string>
     */
    protected $casts = [
        // No hay timestamps en la tabla real
    ];
    
    /**
     * Indica si la tabla usa timestamps
     * La tabla procedimientos NO tiene created_at ni updated_at en la base de datos real
     * 
     * @var bool
     */
    public $timestamps = false;

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
     * Nodos del flujo configurable asociados a este procedimiento.
     */
    public function flujoNodos()
    {
        return $this->hasMany(FlujoNodo::class, 'id_procedimiento');
    }

    /**
     * Primer nodo por el que debe ingresar un trámite nuevo (config. admin).
     */
    public function nodoEntrada()
    {
        return $this->belongsTo(FlujoNodo::class, 'id_nodo_entrada');
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
     * Como la tabla no tiene columna estado, todos los procedimientos se consideran activos
     * 
     * @return bool
     */
    public function estaActivo(): bool
    {
        return true; // Todos los procedimientos están activos por defecto
    }

    /**
     * Obtener nombre completo con código
     * 
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        // La tabla no tiene columna 'codigo', solo retornamos el nombre
        return $this->nombre ?? '';
    }

    /**
     * Obtener costo formateado
     * 
     * @return string
     */
    public function getCostoFormateadoAttribute(): string
    {
        // La tabla no tiene columna 'costo'
        return 'S/ 0.00';
    }

    /**
     * Obtener tiempo estimado formateado
     * 
     * @return string
     */
    public function getTiempoEstimadoFormateadoAttribute(): string
    {
        // La tabla no tiene columna 'tiempo_estimado_dias'
        return 'N/A';
    }

    /**
     * Scope para procedimientos activos
     * Como la tabla no tiene columna estado, retorna todos los procedimientos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        // La tabla no tiene columna 'estado', así que retornamos todos los procedimientos
        return $query;
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
            // Solo buscamos en 'nombre' ya que 'codigo' y 'descripcion' no existen en la tabla real
            $q->where('nombre', 'like', "%{$termino}%");
        });
    }
}