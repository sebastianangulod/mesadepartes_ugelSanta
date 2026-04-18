<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlujoTransicion extends Model
{
    protected $table = 'flujo_transiciones';

    protected $fillable = [
        'desde_nodo_id',
        'hacia_nodo_id',
        'es_default_auto',
    ];

    protected $casts = [
        'es_default_auto' => 'boolean',
    ];

    public function nodoDesde(): BelongsTo
    {
        return $this->belongsTo(FlujoNodo::class, 'desde_nodo_id');
    }

    public function nodoHacia(): BelongsTo
    {
        return $this->belongsTo(FlujoNodo::class, 'hacia_nodo_id');
    }
}
