<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\FlujoNodo;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;

/**
 * Modelo Procedimiento
 * 
 * Este modelo representa los procedimientos disponibles en el sistema.
 * Adaptado a la estructura de la tabla 'procedimientos' del SQL.
 * 
 * @property int $id
 * @property string $nombre
 * @property int|null $id_area
 * @property int|null $id_subarea
 * @property string $nivel_final ENUM('2','3','4')
 * @property int $id_nivel_final
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * 
 * @package App\Models
 */
class Procedimiento extends Model
{
    use HasFactory;

    /**
     * La tabla asociada al modelo
     * 
     * @var string
     */
    protected $table = 'procedimientos';

    /**
     * Los atributos que se pueden asignar masivamente
     * 
     * @var array<string>
     */
    protected $fillable = [
        'nombre',
        'id_area',
        'id_subarea',
        'nivel_final',
        'id_nivel_final',
        'id_nodo_entrada',
        'created_by',
        'updated_by',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos
     * 
     * @var array<string, string>
     */
    protected $casts = [
        // No hay timestamps en la tabla real
    ];
    
    /**
     * Indica si la tabla usa timestamps
     * La tabla procedimientos NO tiene created_at ni updated_at en la base de datos real
     * 
     * @var bool
     */
    public $timestamps = false;

    /**
     * Relación con trámites
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tramites()
    {
        return $this->hasMany(Tramite::class, 'id_procedimiento');
    }

    /**
     * Nodos del flujo configurable asociados a este procedimiento.
     */
    public function flujoNodos()
    {
        return $this->hasMany(FlujoNodo::class, 'id_procedimiento');
    }

    /**
     * Primer nodo por el que debe ingresar un trámite nuevo (config. admin).
     */
    public function nodoEntrada()
    {
        return $this->belongsTo(FlujoNodo::class, 'id_nodo_entrada');
    }

    /**
     * Relación con usuarios asignados
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usuariosAsignados()
    {
        return $this->belongsToMany(Usuario::class, 'procedimiento_usuarios', 'id_procedimiento', 'id_usuario');
    }

    /**
     * Relación con nivel 1 (área principal)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Nivel1::class, 'id_area');
    }

    /**
     * Relación con nivel 2 (subárea)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subarea()
    {
        return $this->belongsTo(Nivel2::class, 'id_subarea');
    }

    /**
     * Relación con nivel 1 (alias de area)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel1()
    {
        return $this->belongsTo(Nivel1::class, 'id_area');
    }

    /**
     * Relación con nivel 2 (alias de subarea)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel2()
    {
        return $this->belongsTo(Nivel2::class, 'id_subarea');
    }

    /**
     * Relación con nivel 3 (cuando nivel_final = 3)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel3()
    {
        return $this->belongsTo(Nivel3::class, 'id_nivel_final')
                    ->where('nivel_final', '3');
    }

    /**
     * Relación con nivel 4 (cuando nivel_final = 4)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivel4()
    {
        return $this->belongsTo(Nivel4::class, 'id_nivel_final')
                    ->where('nivel_final', '4');
    }

    /**
     * Relación con nivel final según el tipo
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nivelFinal()
    {
        switch ($this->nivel_final) {
            case '2':
                return $this->belongsTo(Nivel2::class, 'id_nivel_final');
            case '3':
                return $this->belongsTo(Nivel3::class, 'id_nivel_final');
            case '4':
                return $this->belongsTo(Nivel4::class, 'id_nivel_final');
            default:
                return null;
        }
    }

    /**
     * Relación con usuario creador
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creadoPor()
    {
        return $this->belongsTo(Usuario::class, 'created_by');
    }

    /**
     * Relación con usuario actualizador
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function actualizadoPor()
    {
        return $this->belongsTo(Usuario::class, 'updated_by');
    }

    /**
     * Verificar si el procedimiento está activo
     * Como la tabla no tiene columna estado, todos los procedimientos se consideran activos
     * 
     * @return bool
     */
    public function estaActivo(): bool
    {
        return true; // Todos los procedimientos están activos por defecto
    }

    /**
     * Obtener nombre completo con código
     * 
     * @return string
     */
    public function getNombreCompletoAttribute(): string
    {
        // La tabla no tiene columna 'codigo', solo retornamos el nombre
        return $this->nombre ?? '';
    }

    /**
     * Obtener costo formateado
     * 
     * @return string
     */
    public function getCostoFormateadoAttribute(): string
    {
        // La tabla no tiene columna 'costo'
        return 'S/ 0.00';
    }

    /**
     * Obtener tiempo estimado formateado
     * 
     * @return string
     */
    public function getTiempoEstimadoFormateadoAttribute(): string
    {
        // La tabla no tiene columna 'tiempo_estimado_dias'
        return 'N/A';
    }

    /**
     * Scope para procedimientos activos
     * Como la tabla no tiene columna estado, retorna todos los procedimientos
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivos($query)
    {
        // La tabla no tiene columna 'estado', así que retornamos todos los procedimientos
        return $query;
    }

    /**
     * Scope para filtrar por área
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $areaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorArea($query, $areaId)
    {
        return $query->where('id_area', $areaId);
    }

    /**
     * Scope para filtrar por nivel
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $nivelId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorNivel($query, $nivelId)
    {
        return $query->where('id_nivel', $nivelId);
    }

/**
 * Scope para búsqueda
 * 
 * @param \Illuminate\Database\Eloquent\Builder $query
 * @param string $termino
 * @return \Illuminate\Database\Eloquent\Builder
 */
public function scopeBuscar($query, $termino)
{
    return $query->where(function ($q) use ($termino) {
        // Solo buscamos en 'nombre' ya que 'codigo' y 'descripcion' no existen en la tabla real
        $q->where('nombre', 'like', "%{$termino}%");
    });
}

/**
 * Boot: auto-crear nodos y equipos del flujo configurable
 */
protected static function booted(): void
{
    static::created(function (self $procedimiento) {
        $procedimiento->crearFlujoConfigurativo();
    });

    static::updated(function (self $procedimiento) {
        // Solo regenerar si cambiaron los niveles
        $dirty = $procedimiento->getDirty();
        if (isset($dirty['id_area']) || isset($dirty['id_subarea']) || 
            isset($dirty['nivel_final']) || isset($dirty['id_nivel_final'])) {
            $procedimiento->crearFlujoConfigurativo();
        }
    });
}

/**
 * Crear/actualizar nodos y equipos del flujo configurable basado en niveles del procedimiento
 * Genera: Nivel 1 → Nivel 2 → ... → Nivel Final
 * Cada nodo tiene 1 equipo con nombre técnico y visible
 */
public function crearFlujoConfigurativo(): void
{
    if (!$this->id_area) {
        return; // Sin área no hay flujo
    }

    $niveles = $this->obtenerNivelesDelProcedimiento();
    if (empty($niveles)) {
        return;
    }

    DB::transaction(function () use ($niveles) {
        $nodoEntradaId = null;

        foreach ($niveles as $index => $nivelData) {
            [$nivelNumero, $idNivel, $nombreNivel, $esFinal] = $nivelData;

            // Nombre del nodo: "Nivel X - NombreNivel"
            $nombreTecnico = "Nivel {$nivelNumero} - {$nombreNivel}";

            // Crear o actualizar nodo
            $nodo = FlujoNodo::updateOrCreate(
                [
                    'id_procedimiento' => $this->id,
                    'orden' => $index + 1,
                ],
                [
                    'nombre' => $nombreTecnico,
                    'nivel' => $nivelNumero,
                    'id_nivel' => $idNivel,
                    'puede_finalizar' => $esFinal,
                    'puede_rechazar_definitivo' => $esFinal,
                ]
            );

            // El primer nodo es el de entrada
            if ($index === 0) {
                $nodoEntradaId = $nodo->id;
            }
        }

// Actualizar nodo de entrada del procedimiento
            if ($nodoEntradaId) {
                $this->updateQuietly(['id_nodo_entrada' => $nodoEntradaId]);
            }
        });
}

/**
 * Obtener niveles que conforman el flujo del procedimiento
 * @return array<int, array<int|string|bool>>
 */
private function obtenerNivelesDelProcedimiento(): array
{
    $niveles = [];

    // Nivel 1 - siempre requerido
    if ($this->id_area) {
        $nivel1 = Nivel1::find($this->id_area);
        if ($nivel1) {
            $esFinal = ($this->nivel_final === '1'); // Poco común pero posible
            $niveles[] = [1, $nivel1->id, $nivel1->nombre, $esFinal];
        }
    }

    // Nivel 2
    if ($this->id_subarea) {
        $nivel2 = Nivel2::find($this->id_subarea);
        if ($nivel2) {
            $esFinal = ($this->nivel_final === '2' && $this->id_nivel_final == $nivel2->id);
            $niveles[] = [2, $nivel2->id, $nivel2->nombre, $esFinal];
        }
    }

    // Nivel 3
    if (in_array($this->nivel_final, ['3', '4'])) {
        $idNivel3 = null;
        if ($this->nivel_final === '3') {
            $idNivel3 = $this->id_nivel_final;
        } elseif ($this->nivel_final === '4' && $this->id_nivel_final) {
            $nivel4 = Nivel4::find($this->id_nivel_final);
            if ($nivel4 && $nivel4->id_nivel3) {
                $idNivel3 = $nivel4->id_nivel3;
            }
        }
        if ($idNivel3) {
            $nivel3 = Nivel3::find($idNivel3);
            if ($nivel3) {
                $esFinal = ($this->nivel_final === '3' && $this->id_nivel_final == $nivel3->id);
                $niveles[] = [3, $nivel3->id, $nivel3->nombre, $esFinal];
            }
        }
    }

    // Nivel 4
    if ($this->nivel_final === '4' && $this->id_nivel_final) {
        $nivel4 = Nivel4::find($this->id_nivel_final);
        if ($nivel4) {
            $niveles[] = [4, $nivel4->id, $nivel4->nombre, true];
        }
    }

    return $niveles;
}
}
