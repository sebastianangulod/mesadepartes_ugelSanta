<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlujoNodo extends Model
{
    protected $table = 'flujo_nodos';

    protected $fillable = [
        'id_procedimiento',
        'nombre',
        'slug',
        'orden',
        'puede_finalizar',
        'puede_rechazar_definitivo',
        'es_terminal',
    ];

    protected $casts = [
        'orden' => 'integer',
        'puede_finalizar' => 'boolean',
        'puede_rechazar_definitivo' => 'boolean',
        'es_terminal' => 'boolean',
    ];

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento');
    }

    public function transicionesDesde(): HasMany
    {
        return $this->hasMany(FlujoTransicion::class, 'desde_nodo_id');
    }

    public function transicionesHacia(): HasMany
    {
        return $this->hasMany(FlujoTransicion::class, 'hacia_nodo_id');
    }

    public function equipos(): HasMany
    {
        return $this->hasMany(FlujoEquipo::class, 'nodo_id');
    }
}

