<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Nivel
 * 
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property int $orden
 * @property string $estado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @package App\Models
 */
class Nivel extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo
     * 
     * @var string
     */
    protected $table = 'niveles';

    /**
     * Los atributos que se pueden asignar masivamente
     * 
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
        'estado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con procedimientos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedimientos()
    {
        return $this->hasMany(Procedimiento::class, 'id_nivel');
    }

    /**
     * Verificar si el nivel está activo
     * 
     * @return bool
     */
    public function estaActivo(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    /**
     * Scope para niveles activos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }

    /**
     * Scope para ordenar por orden
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('orden');
    }
}