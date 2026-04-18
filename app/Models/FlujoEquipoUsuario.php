<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlujoEquipoUsuario extends Model
{
    protected $table = 'flujo_equipo_usuario';

    protected $fillable = [
        'equipo_id',
        'usuario_id',
    ];

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(FlujoEquipo::class, 'equipo_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
