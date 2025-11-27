<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo AreasTipoGrupo
 * Representa la relación entre áreas y tipos de grupo
 * Relación con TipoGrupo (N:1) y Grupos (1:N)
 */
class AreasTipoGrupo extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'areas_tipogrupo';

    /**
     * Indica si la tabla usa timestamps
     */
    public $timestamps = false;

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'id',
        'nombre',
        'id_tipogrupo'
    ];

    /**
     * Relación con tipo grupo
     */
    public function tipogrupo()
    {
        return $this->belongsTo(Tipogrupo::class, 'id_tipogrupo');
    }

    /**
     * Relación con grupos
     */
    public function grupos()
    {
        return $this->hasMany(Grupo::class, 'id_area_tipogrupo');
    }
}