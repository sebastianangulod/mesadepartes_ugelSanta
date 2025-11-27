<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Distrito
 * Representa los distritos del Perú
 * Relación con Provincias (N:1) y Usuarios (1:N)
 */
class Distrito extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'distritos';

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
        'provincia_id'
    ];

    /**
     * Relación con provincia
     */
    public function provincia()
    {
        return $this->belongsTo(Provincia::class, 'provincia_id');
    }

    /**
     * Relación con usuarios
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class, 'distrito_id');
    }

    /**
     * Relación con departamento a través de provincia
     */
    public function departamento()
    {
        return $this->belongsToThrough(Departamento::class, Provincia::class);
    }
}