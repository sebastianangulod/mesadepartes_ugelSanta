<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Departamento
 * Representa los departamentos del Perú
 * Relación con Provincias (1:N)
 */
class Departamento extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'departamentos';

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
     * Relación con provincias
     */
    public function provincias()
    {
        return $this->hasMany(Provincia::class, 'departamento_id');
    }

    /**
     * Relación con usuarios a través de distritos
     */
    public function usuarios()
    {
        return $this->hasManyThrough(Usuario::class, Distrito::class, 'departamento_id', 'distrito_id');
    }
}