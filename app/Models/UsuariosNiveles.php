<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;

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
     * Acceso directo al modelo del nivel (no es una relación para evitar conflictos)
     */
    public function getNivelModeloAttribute()
    {
        $nivelNumero = $this->getAttribute('nivel');
        $idNivel = $this->getAttribute('id_nivel');
        
        if (!$idNivel) {
            return null;
        }
        
        switch ($nivelNumero) {
            case 1:
                return Nivel1::find($idNivel);
            case 2:
                return Nivel2::find($idNivel);
            case 3:
                return Nivel3::find($idNivel);
            case 4:
                return Nivel4::find($idNivel);
            default:
                return null;
        }
    }

    /**
     * Obtiene el nombre del nivel
     */
    public function getNivelNombreAttribute()
    {
        $nivelNumero = $this->getAttribute('nivel');
        $idNivel = $this->getAttribute('id_nivel');
        
        if (!$idNivel) {
            return 'N/A';
        }
        
        switch ($nivelNumero) {
            case 1:
                $modelo = Nivel1::find($idNivel);
                return $modelo ? $modelo->nombre : 'N/A';
            case 2:
                $modelo = Nivel2::find($idNivel);
                return $modelo ? $modelo->nombre : 'N/A';
            case 3:
                $modelo = Nivel3::find($idNivel);
                return $modelo ? $modelo->nombre : 'N/A';
            case 4:
                $modelo = Nivel4::find($idNivel);
                return $modelo ? $modelo->nombre : 'N/A';
            default:
                return 'N/A';
        }
    }
}