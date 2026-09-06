<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlujoEquipo extends Model
{
    protected $table = 'flujo_equipos';

    protected $fillable = [
        'nodo_id',
        'nombre',
        'nombre_tecnico',
        'nombre_visible',
    ];

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(FlujoNodo::class, 'nodo_id');
    }

    public function miembros(): BelongsToMany
    {
        return $this->belongsToMany(Usuario::class, 'flujo_equipo_usuario', 'equipo_id', 'usuario_id')
            ->withTimestamps();
    }

    public function equipoUsuarioFilas(): HasMany
    {
        return $this->hasMany(FlujoEquipoUsuario::class, 'equipo_id');
    }

    /**
     * Nombre para mostrar en UI: prioriza nombre_visible, fallback a nombre_tecnico, luego nombre
     */
    public function getNombreDisplayAttribute(): string
    {
        return $this->nombre_visible ?? $this->nombre_tecnico ?? $this->nombre ?? '';
    }
}
