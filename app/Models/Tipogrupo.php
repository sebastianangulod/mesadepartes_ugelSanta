<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo TipoGrupo
 * Representa los tipos de grupos organizacionales
 * Relación con Nivel1 (N:1) y AreasTipoGrupo (1:N)
 */
class Tipogrupo extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'tipogrupos';

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
        'id_nivel1'
    ];

    /**
     * Relación con nivel 1
     */
    public function nivel1()
    {
        return $this->belongsTo(Nivel1::class, 'id_nivel1');
    }

    /**
     * Relación con áreas de tipo grupo
     */
    public function areasTipoGrupo()
    {
        return $this->hasMany(AreasTipoGrupo::class, 'id_tipogrupo');
    }
}