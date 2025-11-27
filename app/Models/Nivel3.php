<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Nivel3
 * Representa el tercer nivel de la jerarquía organizacional
 * Relación con Nivel2 (N:1) y Nivel4 (1:N)
 */
class Nivel3 extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'nivel_3';

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
        'id_nivel2'
    ];

    /**
     * Relación con nivel 2
     */
    public function nivel2()
    {
        return $this->belongsTo(Nivel2::class, 'id_nivel2');
    }

    /**
     * Relación con nivel 4
     */
    public function nivel4()
    {
        return $this->hasMany(Nivel4::class, 'id_nivel3');
    }

    /**
     * Relación con procedimientos
     */
    public function procedimientosNivelFinal()
    {
        return $this->hasMany(Procedimiento::class, 'id_nivel_final')
                    ->where('nivel_final', '3');
    }
}