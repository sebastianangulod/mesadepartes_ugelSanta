<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Area
 * 
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 * @property string $estado
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @package App\Models
 */
class Area extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo
     * 
     * @var string
     */
    protected $table = 'areas';

    /**
     * Los atributos que se pueden asignar masivamente
     * 
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
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
     * Relación con procedimientos
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedimientos()
    {
        return $this->hasMany(Procedimiento::class, 'id_area');
    }

    /**
     * Verificar si el área está activa
     * 
     * @return bool
     */
    public function estaActiva(): bool
    {
        return $this->estado === 'ACTIVO';
    }

    /**
     * Scope para áreas activas
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVO');
    }
}