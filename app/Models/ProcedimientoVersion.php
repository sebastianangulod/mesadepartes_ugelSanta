<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ProcedimientoVersion
 * Representa el historial de versiones de un procedimiento
 */
class ProcedimientoVersion extends Model
{
    /**
     * La tabla asociada al modelo
     */
    protected $table = 'procedimiento_versiones';

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'id_procedimiento',
        'version',
        'nombre',
        'descripcion',
        'id_area',
        'id_subarea',
        'nivel_final',
        'id_nivel_final',
        'flujo_config',
        'created_by',
        'cambios',
    ];

    /**
     * Atributos que deben ser convertidos a tipos nativos
     */
    protected $casts = [
        'flujo_config' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Relación con procedimiento
     */
    public function procedimiento()
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento');
    }

    /**
     * Relación con usuario creador
     */
    public function creador()
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    /**
     * Relación con nivel 1 (área)
     */
    public function area()
    {
        return $this->belongsTo(Nivel1::class, 'id_area');
    }

    /**
     * Relación con nivel 2 (subárea)
     */
    public function subarea()
    {
        return $this->belongsTo(Nivel2::class, 'id_subarea');
    }

    /**
     * Relación con nivel final
     */
    public function nivelFinal()
    {
        return $this->belongsTo(Nivel3::class, 'id_nivel_final');
    }
}