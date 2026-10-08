<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlujoNodo extends Model
{
    protected $table = 'flujo_nodos';

    protected $fillable = [
        'id_procedimiento',
        'nombre',
        'orden',
        'nivel',
        'id_nivel',
        'puede_finalizar',
        'puede_rechazar_definitivo',
    ];

    protected $casts = [
        'orden' => 'integer',
        'nivel' => 'integer',
        'id_nivel' => 'integer',
        'puede_finalizar' => 'boolean',
        'puede_rechazar_definitivo' => 'boolean',
    ];

    public function procedimiento(): BelongsTo
    {
        return $this->belongsTo(Procedimiento::class, 'id_procedimiento');
    }
}

