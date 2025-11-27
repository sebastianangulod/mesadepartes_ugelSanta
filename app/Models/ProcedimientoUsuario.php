<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ProcedimientoUsuario
 * Representa la relación muchos a muchos entre procedimientos y usuarios
 * Usado para asignar procedimientos específicos a usuarios
 */
class ProcedimientoUsuario extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'procedimiento_usuarios';

    /**
     * Indica si la tabla usa timestamps
     */
    public $timestamps = false;

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'id_procedimiento',
        'id_usuario'
    ];

    /**
     * Relación con procedimiento
     */
    public function procedimiento()
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento');
    }

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }
}