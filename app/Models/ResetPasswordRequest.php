<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo ResetPasswordRequest
 * Representa las solicitudes de restablecimiento de contraseña
 * Relación con Usuario (N:1)
 */
class ResetPasswordRequest extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'reset_password_requests';

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'user_id',
        'token',
        'created_at'
    ];

    /**
     * Indica si la tabla usa timestamps
     */
    public $timestamps = false;

    /**
     * Cast de atributos a tipos nativos
     */
    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    /**
     * Verifica si el token ha expirado
     */
    public function hasExpired()
    {
        // Verifica si han pasado más de 24 horas desde la creación
        return $this->created_at->diffInHours(now()) > 24;
    }

    /**
     * Marca la solicitud como utilizada
     */
    public function markAsUsed()
    {
        // Elimina el registro para prevenir reutilización
        $this->delete();
    }
}