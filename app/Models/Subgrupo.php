<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Subgrupo
 * Representa los subgrupos organizacionales
 * Relación con Grupo (N:1)
 */
class Subgrupo extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'subgrupos';

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
        'id_grupo'
    ];

    /**
     * Relación con grupo
     */
    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }
}