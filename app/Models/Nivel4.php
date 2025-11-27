<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Nivel4
 * Representa el cuarto nivel de la jerarquía organizacional
 * Relación con Nivel3 (N:1)
 */
class Nivel4 extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'nivel_4';

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
        'id_nivel3'
    ];

    /**
     * Relación con nivel 3
     */
    public function nivel3()
    {
        return $this->belongsTo(Nivel3::class, 'id_nivel3');
    }

    /**
     * Relación con procedimientos
     */
    public function procedimientosNivelFinal()
    {
        return $this->hasMany(Procedimiento::class, 'id_nivel_final')
                    ->where('nivel_final', '4');
    }
}