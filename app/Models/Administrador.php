<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Administrador
 * Representa los usuarios con privilegios administrativos
 * Relación con Usuario (1:1)
 */
class Administrador extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'administradores';

    /**
     * Indica si la tabla usa timestamps
     */
    public $timestamps = false;

    /**
     * La clave primaria no es autoincremental
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Indica si la clave primaria es entera
     */
    public $incrementing = false;

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'id_usuario',
        'fecha_asignacion'
    ];

    /**
     * Cast de atributos a tipos nativos
     */
    protected $casts = [
        'fecha_asignacion' => 'datetime',
    ];

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}