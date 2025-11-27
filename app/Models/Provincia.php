<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Provincia
 * Representa las provincias del Perú
 * Relación con Departamentos (N:1) y Distritos (1:N)
 */
class Provincia extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'provincias';

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
        'departamento_id'
    ];

    /**
     * Relación con departamento
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    /**
     * Relación con distritos
     */
    public function distritos()
    {
        return $this->hasMany(Distrito::class, 'provincia_id');
    }

    /**
     * Relación con usuarios a través de distritos
     */
    public function usuarios()
    {
        return $this->hasManyThrough(Usuario::class, Distrito::class, 'provincia_id', 'distrito_id');
    }
}