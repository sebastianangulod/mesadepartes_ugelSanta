<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo UsuariosNiveles
 * Representa la asignación de usuarios a niveles específicos
 * Usado para definir qué usuarios pertenecen a qué niveles
 */
class UsuariosNiveles extends Model
{
    /**
     * Tabla asociada al modelo
     */
    protected $table = 'usuarios_niveles';

    /**
     * Atributos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'id_usuario',
        'nivel',
        'id_nivel'
    ];

    /**
     * Indica si la tabla usa timestamps
     */
    public $timestamps = false;

    /**
     * Relación con usuario
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Obtiene el modelo del nivel correspondiente según el tipo
     */
    public function nivel()
    {
        switch ($this->nivel) {
            case 1:
                return $this->belongsTo(Nivel1::class, 'id_nivel');
            case 2:
                return $this->belongsTo(Nivel2::class, 'id_nivel');
            case 3:
                return $this->belongsTo(Nivel3::class, 'id_nivel');
            case 4:
                return $this->belongsTo(Nivel4::class, 'id_nivel');
            default:
                return null;
        }
    }

    /**
     * Obtiene el nombre del nivel
     */
    public function getNivelNombreAttribute()
    {
        switch ($this->nivel) {
            case 1:
                return $this->belongsTo(Nivel1::class, 'id_nivel')->first()->nombre ?? 'N/A';
            case 2:
                return $this->belongsTo(Nivel2::class, 'id_nivel')->first()->nombre ?? 'N/A';
            case 3:
                return $this->belongsTo(Nivel3::class, 'id_nivel')->first()->nombre ?? 'N/A';
            case 4:
                return $this->belongsTo(Nivel4::class, 'id_nivel')->first()->nombre ?? 'N/A';
            default:
                return 'N/A';
        }
    }
}