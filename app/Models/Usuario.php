<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

    /**
     * Modelo Usuario
     * 
     * Este modelo representa a los usuarios del sistema de mesa de partes.
     * Está adaptado a la estructura existente de la tabla 'usuarios'.
     * 
     * @property int $id
     * @property string|null $ruc
     * @property string|null $razon_social
     * @property string $passw
     * @property string $email
     * @property string $nombres
     * @property string $apellido_paterno
     * @property string $apellido_materno
     * @property string $dni
     * @property string $direccion
     * @property int $distrito_id
     * @property string $telefono
     * @property int $tipo_user (1: externo, 2: interno)
     * @property int $tipo_persona (1: natural, 2: jurídica)
     * @property \Carbon\Carbon $fecha_registro
     * @property \Carbon\Carbon $created_at
     * @property \Carbon\Carbon $updated_at
     * 
     * @package App\Models
     */
class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * La tabla asociada al modelo
     * 
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * Obtener la contraseña para autenticación
     * Laravel busca 'password' por defecto, pero nuestra BD usa 'passw'
     * 
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->passw;
    }

    /**
     * Los atributos que se pueden asignar masivamente
     * 
     * @var array<string>
     */
    protected $fillable = [
        'ruc',
        'razon_social',
        'passw',
        'email',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'direccion',
        'distrito_id',
        'telefono',
        'tipo_user',
        'tipo_persona',
        'fecha_registro',
    ];

    /**
     * Los atributos que deben estar ocultos para la serialización
     * 
     * @var array<string>
     */
    protected $hidden = [
        'passw',
        'remember_token',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_registro' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'tipo_user' => 'integer',
        'tipo_persona' => 'integer',
    ];

    /**
     * Accessor para password (Laravel usa 'password' pero la BD tiene 'passw')
     * 
     * @return string|null
     */
    public function getPasswordAttribute()
    {
        return $this->passw;
    }

    /**
     * Mutator para password (Laravel usa 'password' pero la BD tiene 'passw')
     * 
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->passw = $value;
    }

    /**
     * Obtener el nombre completo del usuario
     * 
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    /**
     * Relación con trámites
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tramites()
    {
        return $this->hasMany(Tramite::class, 'id_usuario');
    }

    /**
     * Relación con trámites revisados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tramitesRevisados()
    {
        return $this->hasMany(Tramite::class, 'revisado_por');
    }

    /**
     * Verificar si el usuario es administrador
     * 
     * @return bool
     */
    public function esAdministrador(): bool
    {
        return $this->esAdmin();
    }

    /**
     * Verificar si el usuario es externo
     * 
     * @return bool
     */
    public function esExterno(): bool
    {
        return $this->tipo_user === 1;
    }

    /**
     * Verificar si el usuario es interno
     * 
     * @return bool
     */
    public function esInterno(): bool
    {
        return $this->tipo_user === 2;
    }

    /**
     * Verificar si es persona natural
     * 
     * @return bool
     */
    public function esPersonaNatural(): bool
    {
        return $this->tipo_persona === 1;
    }

    /**
     * Verificar si es persona jurídica
     * 
     * @return bool
     */
    public function esPersonaJuridica(): bool
    {
        return $this->tipo_persona === 2;
    }

    /**
     * Relación con distrito
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function distrito()
    {
        return $this->belongsTo(Distrito::class, 'distrito_id');
    }

    /**
     * Relación con trámites rechazados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tramitesRechazados()
    {
        return $this->hasMany(Tramite::class, 'rechazado_por');
    }

    /**
     * Relación con procedimientos creados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedimientosCreados()
    {
        return $this->hasMany(Procedimiento::class, 'created_by');
    }

    /**
     * Relación con procedimientos actualizados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function procedimientosActualizados()
    {
        return $this->hasMany(Procedimiento::class, 'updated_by');
    }

    /**
     * Relación con solicitudes de restablecimiento de contraseña
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resetPasswordRequests()
    {
        return $this->hasMany(ResetPasswordRequest::class, 'user_id');
    }

    /**
     * Relación con administrador
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function administrador()
    {
        return $this->hasOne(Administrador::class, 'id_usuario');
    }

    /**
     * Relación con procedimientos asignados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function procedimientosAsignados()
    {
        return $this->belongsToMany(Procedimiento::class, 'procedimiento_usuarios', 'id_usuario', 'id_procedimiento');
    }

    /**
     * Relación con niveles asignados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usuariosNiveles()
    {
        return $this->hasMany(UsuariosNiveles::class, 'id_usuario');
    }

    /**
     * Verifica si el usuario es administrador
     * 
     * @return bool
     */
    public function esAdmin(): bool
    {
        return $this->administrador()->exists();
    }

    /**
     * Accessor para es_administrador (compatibilidad con código existente)
     * 
     * @return bool
     */
    public function getEsAdministradorAttribute(): bool
    {
        return $this->esAdmin();
    }

    /**
     * Obtiene el nombre completo con DNI
     * 
     * @return string
     */
    public function getNombreCompletoConDniAttribute(): string
    {
        return "{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno} - {$this->dni}";
    }

    /**
     * Obtiene el tipo de usuario formateado
     * 
     * @return string
     */
    public function getTipoUsuarioFormateadoAttribute(): string
    {
        $tipos = [
            1 => 'Usuario Externo',
            2 => 'Usuario Interno'
        ];

        return $tipos[$this->tipo_user] ?? 'Desconocido';
    }

    /**
     * Obtiene el tipo de persona formateado
     * 
     * @return string
     */
    public function getTipoPersonaFormateadoAttribute(): string
    {
        $tipos = [
            1 => 'Persona Natural',
            2 => 'Persona Jurídica'
        ];

        return $tipos[$this->tipo_persona] ?? 'Desconocido';
    }
}