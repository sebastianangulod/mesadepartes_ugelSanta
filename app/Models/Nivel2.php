<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Nivel2
 * Representa el segundo nivel de la jerarquía organizacional
 * Relación con Nivel1 (N:1) y Nivel3 (1:N)
 */
class Nivel2 extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'nivel_2';

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
        'id_nivel1',
        'orden'
    ];

    /**
     * Relación con nivel 1
     */
    public function nivel1()
    {
        return $this->belongsTo(Nivel1::class, 'id_nivel1');
    }

    /**
     * Relación con nivel 3
     */
    public function nivel3()
    {
        return $this->hasMany(Nivel3::class, 'id_nivel2');
    }

    /**
     * Relación con procedimientos
     */
    public function procedimientos()
    {
        return $this->hasMany(Procedimiento::class, 'id_subarea');
    }
}