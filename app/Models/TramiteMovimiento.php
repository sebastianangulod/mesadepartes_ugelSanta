<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TramiteMovimiento extends Model
{
    public $timestamps = false;

    protected $table = 'tramite_movimientos';

    protected static function booted(): void
    {
        static::creating(function (TramiteMovimiento $movimiento): void {
            if ($movimiento->created_at === null) {
                $movimiento->created_at = now();
            }
        });
    }

    protected $fillable = [
        'tramite_id',
        'usuario_id',
        'accion',
        'nodo_desde_id',
        'nodo_hasta_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function tramite(): BelongsTo
    {
        return $this->belongsTo(Tramite::class, 'tramite_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function nodoDesde(): BelongsTo
    {
        return $this->belongsTo(FlujoNodo::class, 'nodo_desde_id');
    }

    public function nodoHacia(): BelongsTo
    {
        return $this->belongsTo(FlujoNodo::class, 'nodo_hasta_id');
    }
}
