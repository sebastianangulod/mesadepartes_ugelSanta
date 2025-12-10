<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Nivel1
 * Representa el primer nivel de la jerarquía organizacional
 * Relación con Nivel2 (1:N) y TipoGrupos (1:N)
 */
class Nivel1 extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'nivel_1';

    /**
     * Indica si la tabla usa timestamps
     */
    public $timestamps = false;

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'id',
        'nombre'
    ];

    /**
     * Relación con nivel 2
     */
    public function nivel2()
    {
        return $this->hasMany(Nivel2::class, 'id_nivel1');
    }
    
    /**
     * Relación con nivel 2 que incluye niveles 3 y 4
     */
    public function nivel2ConNiveles()
    {
        return $this->hasMany(Nivel2::class, 'id_nivel1')
                    ->with(['nivel3.nivel4']);
    }

    /**
     * Relación con tipos de grupos
     */
    public function tipogrupos()
    {
        return $this->hasMany(Tipogrupo::class, 'id_nivel1');
    }

    /**
     * Relación con procedimientos
     */
    public function procedimientos()
    {
        return $this->hasMany(Procedimiento::class, 'id_area');
    }
}