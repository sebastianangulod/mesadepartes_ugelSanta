<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Notificación de la bandeja del usuario.
 *
 * @property int $id
 * @property int $id_usuario
 * @property string $titulo
 * @property string|null $mensaje
 * @property string $tipo info|success|warning|error
 * @property string|null $url
 * @property bool $leida
 */
class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'id_usuario',
        'titulo',
        'mensaje',
        'tipo',
        'url',
        'leida',
    ];

    protected $casts = [
        'leida' => 'boolean',
    ];

    /**
     * Usuario dueño de la notificación.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Alcance: no leídas del usuario.
     */
    public function scopeNoLeidas($query, int $usuarioId)
    {
        return $query->where('id_usuario', $usuarioId)->where('leida', false);
    }
}
