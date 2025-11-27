<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Grupo
 * Representa los grupos organizacionales
 * Relación con AreasTipoGrupo (N:1) y Subgrupos (1:N)
 */
class Grupo extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'grupos';

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
        'id_area_tipogrupo'
    ];

    /**
     * Relación con área tipo grupo
     */
    public function areaTipoGrupo()
    {
        return $this->belongsTo(AreasTipoGrupo::class, 'id_area_tipogrupo');
    }

    /**
     * Relación con subgrupos
     */
    public function subgrupos()
    {
        return $this->hasMany(Subgrupo::class, 'id_grupo');
    }
}