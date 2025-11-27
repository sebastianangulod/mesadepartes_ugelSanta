<?php

namespace App\Validators;

use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use Illuminate\Support\Facades\DB;

/**
 * Validador para la jerarquía administrativa de niveles
 * 
 * Este validador proporciona métodos para validar la consistencia
 * de la jerarquía de niveles 1-4, verificar relaciones padre-hijo
 * y prevenir ciclos o relaciones inválidas.
 * 
 * Funcionalidades principales:
 * - Validar consistencia de relaciones padre-hijo
 * - Verificar existencia y estado de niveles
 * - Prevenir ciclos en la jerarquía
 * - Validar asignaciones de usuarios a niveles
 * - Verificar dependencias antes de eliminar
 * - Soporte para validaciones complejas de negocio
 * 
 * @package App\Validators
 */
class JerarquiaValidator
{
    /**
     * Validar que una relación padre-hijo sea válida
     * 
     * @param int $nivelHijo
     * @param int $padreId
     * @return array
     */
    public static function validarRelacionPadreHijo($nivelHijo, $padreId)
    {
        $resultado = [
            'valida' => false,
            'mensaje' => '',
            'detalles' => []
        ];

        try {
            // Validar que el nivel hijo sea válido (2, 3 o 4)
            if (!in_array($nivelHijo, [2, 3, 4])) {
                $resultado['mensaje'] = 'El nivel hijo debe ser 2, 3 o 4.';
                return $resultado;
            }

            // Validar que el padre existe y está activo
            $padreValido = self::validarNivelExiste($nivelHijo - 1, $padreId);
            if (!$padreValido['existe'] || !$padreValido['activo']) {
                $resultado['mensaje'] = 'El nivel padre no existe o está inactivo.';
                $resultado['detalles'] = $padreValido;
                return $resultado;
            }

            // Para nivel 3 y 4, verificar la cadena completa
            if ($nivelHijo >= 3) {
                $cadenaValida = self::validarCadenaJerarquica($nivelHijo, $padreId);
                if (!$cadenaValida['valida']) {
                    $resultado['mensaje'] = $cadenaValida['mensaje'];
                    $resultado['detalles'] = $cadenaValida['detalles'];
                    return $resultado;
                }
            }

            $resultado['valida'] = true;
            $resultado['mensaje'] = 'Relación válida.';

        } catch (\Exception $e) {
            $resultado['mensaje'] = 'Error al validar relación: ' . $e->getMessage();
        }

        return $resultado;
    }

    /**
     * Validar que un nivel existe y está activo
     * 
     * @param int $nivel
     * @param int $id
     * @return array
     */
    public static function validarNivelExiste($nivel, $id)
    {
        $resultado = [
            'existe' => false,
            'activo' => false,
            'datos' => null
        ];

        try {
            $modelo = self::obtenerModeloPorNivel($nivel);
            if (!$modelo) {
                return $resultado;
            }

            $registro = $modelo::find($id);
            
            if ($registro) {
                $resultado['existe'] = true;
                $resultado['activo'] = $registro->estado == 1;
                $resultado['datos'] = $registro->toArray();
            }

        } catch (\Exception $e) {
            // Loggear error pero no fallar la validación
            \Log::warning("Error validando nivel {$nivel} con ID {$id}: " . $e->getMessage());
        }

        return $resultado;
    }

    /**
     * Validar cadena jerárquica completa
     * 
     * @param int $nivelHijo
     * @param int $padreId
     * @return array
     */
    public static function validarCadenaJerarquica($nivelHijo, $padreId)
    {
        $resultado = [
            'valida' => false,
            'mensaje' => '',
            'detalles' => []
        ];

        try {
            if ($nivelHijo == 3) {
                // Para nivel 3, verificar que el padre (nivel 2) tenga un nivel 1 activo
                $nivel2 = Nivel2::find($padreId);
                if (!$nivel2) {
                    $resultado['mensaje'] = 'El nivel 2 padre no existe.';
                    return $resultado;
                }

                $nivel1Valido = self::validarNivelExiste(1, $nivel2->nivel_1_id);
                if (!$nivel1Valido['existe'] || !$nivel1Valido['activo']) {
                    $resultado['mensaje'] = 'El nivel 1 asociado al nivel 2 no existe o está inactivo.';
                    $resultado['detalles']['nivel1'] = $nivel1Valido;
                    return $resultado;
                }

            } elseif ($nivelHijo == 4) {
                // Para nivel 4, verificar que el padre (nivel 3) tenga niveles 2 y 1 activos
                $nivel3 = Nivel3::find($padreId);
                if (!$nivel3) {
                    $resultado['mensaje'] = 'El nivel 3 padre no existe.';
                    return $resultado;
                }

                $nivel2Valido = self::validarNivelExiste(2, $nivel3->nivel_2_id);
                if (!$nivel2Valido['existe'] || !$nivel2Valido['activo']) {
                    $resultado['mensaje'] = 'El nivel 2 asociado al nivel 3 no existe o está inactivo.';
                    $resultado['detalles']['nivel2'] = $nivel2Valido;
                    return $resultado;
                }

                // Obtener nivel 1 a través del nivel 2
                $nivel2 = Nivel2::find($nivel3->nivel_2_id);
                $nivel1Valido = self::validarNivelExiste(1, $nivel2->nivel_1_id);
                if (!$nivel1Valido['existe'] || !$nivel1Valido['activo']) {
                    $resultado['mensaje'] = 'El nivel 1 asociado al nivel 2 no existe o está inactivo.';
                    $resultado['detalles']['nivel1'] = $nivel1Valido;
                    return $resultado;
                }
            }

            $resultado['valida'] = true;
            $resultado['mensaje'] = 'Cadena jerárquica válida.';

        } catch (\Exception $e) {
            $resultado['mensaje'] = 'Error al validar cadena jerárquica: ' . $e->getMessage();
        }

        return $resultado;
    }

    /**
     * Verificar si un nivel tiene hijos activos
     * 
     * @param int $nivel
     * @param int $id
     * @return bool
     */
    public static function tieneHijosActivos($nivel, $id)
    {
        try {
            switch ($nivel) {
                case 1:
                    return Nivel2::where('nivel_1_id', $id)
                        ->where('estado', 1)
                        ->exists();
                    
                case 2:
                    return Nivel3::where('nivel_2_id', $id)
                        ->where('estado', 1)
                        ->exists();
                    
                case 3:
                    return Nivel4::where('nivel_3_id', $id)
                        ->where('estado', 1)
                        ->exists();
                    
                case 4:
                    // El nivel 4 no puede tener hijos
                    return false;
                    
                default:
                    return false;
            }
        } catch (\Exception $e) {
            \Log::warning("Error verificando hijos activos para nivel {$nivel} ID {$id}: " . $e->getMessage());
            return true; // Asumir que tiene hijos por seguridad
        }
    }

    /**
     * Verificar dependencias antes de eliminar un nivel
     * 
     * @param int $nivel
     * @param int $id
     * @return array
     */
    public static function verificarDependencias($nivel, $id)
    {
        $dependencias = [
            'usuarios' => 0,
            'procedimientos' => 0,
            'tramites' => 0,
            'hijos' => 0,
            'total' => 0
        ];

        try {
            // Contar usuarios asignados a este nivel
            $dependencias['usuarios'] = DB::table('usuarios_niveles')
                ->where("nivel_{$nivel}_id", $id)
                ->where('estado', 1)
                ->count();

            // Contar procedimientos asociados
            $dependencias['procedimientos'] = DB::table('procedimientos')
                ->where("nivel_{$nivel}_id", $id)
                ->where('estado', 1)
                ->count();

            // Contar trámites (si aplica)
            if ($nivel >= 2) {
                $dependencias['tramites'] = DB::table('tramites')
                    ->where("nivel_{$nivel}_id", $id)
                    ->whereIn('estado', ['pendiente', 'en_proceso'])
                    ->count();
            }

            // Contar hijos activos
            $dependencias['hijos'] = self::contarHijosActivos($nivel, $id);

            $dependencias['total'] = array_sum($dependencias);

        } catch (\Exception $e) {
            \Log::warning("Error verificando dependencias para nivel {$nivel} ID {$id}: " . $e->getMessage());
        }

        return $dependencias;
    }

    /**
     * Contar hijos activos de un nivel
     * 
     * @param int $nivel
     * @param int $id
     * @return int
     */
    protected static function contarHijosActivos($nivel, $id)
    {
        try {
            switch ($nivel) {
                case 1:
                    return Nivel2::where('nivel_1_id', $id)
                        ->where('estado', 1)
                        ->count();
                    
                case 2:
                    return Nivel3::where('nivel_2_id', $id)
                        ->where('estado', 1)
                        ->count();
                    
                case 3:
                    return Nivel4::where('nivel_3_id', $id)
                        ->where('estado', 1)
                        ->count();
                    
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            \Log::warning("Error contando hijos activos para nivel {$nivel} ID {$id}: " . $e->getMessage());
            return 999; // Número alto para indicar error
        }
    }

    /**
     * Obtener el modelo correspondiente según el nivel
     * 
     * @param int $nivel
     * @return string|null
     */
    protected static function obtenerModeloPorNivel($nivel)
    {
        $modelos = [
            1 => Nivel1::class,
            2 => Nivel2::class,
            3 => Nivel3::class,
            4 => Nivel4::class
        ];

        return $modelos[$nivel] ?? null;
    }

    /**
     * Validar asignación de usuario a niveles
     * 
     * @param int $usuarioId
     * @param array $niveles
     * @return array
     */
    public static function validarAsignacionUsuario($usuarioId, $niveles)
    {
        $resultado = [
            'valida' => false,
            'mensaje' => '',
            'niveles_validos' => []
        ];

        try {
            // Validar que se proporcionen niveles
            if (empty($niveles)) {
                $resultado['mensaje'] = 'Debe proporcionar al menos un nivel.';
                return $resultado;
            }

            // Validar consistencia de la jerarquía
            if (isset($niveles['nivel_4_id']) && !isset($niveles['nivel_3_id'])) {
                $resultado['mensaje'] = 'Si asigna nivel 4, debe asignar también nivel 3.';
                return $resultado;
            }

            if (isset($niveles['nivel_3_id']) && !isset($niveles['nivel_2_id'])) {
                $resultado['mensaje'] = 'Si asigna nivel 3, debe asignar también nivel 2.';
                return $resultado;
            }

            if (isset($niveles['nivel_2_id']) && !isset($niveles['nivel_1_id'])) {
                $resultado['mensaje'] = 'Si asigna nivel 2, debe asignar también nivel 1.';
                return $resultado;
            }

            // Validar que todos los niveles existan y estén activos
            foreach ($niveles as $campo => $valor) {
                if (preg_match('/nivel_(\\d+)_id/', $campo, $matches)) {
                    $nivel = (int)$matches[1];
                    $validacion = self::validarNivelExiste($nivel, $valor);
                    
                    if (!$validacion['existe'] || !$validacion['activo']) {
                        $resultado['mensaje'] = "El nivel {$nivel} no existe o está inactivo.";
                        return $resultado;
                    }
                    
                    $resultado['niveles_validos'][$campo] = $validacion;
                }
            }

            $resultado['valida'] = true;
            $resultado['mensaje'] = 'Asignación válida.';

        } catch (\Exception $e) {
            $resultado['mensaje'] = 'Error al validar asignación: ' . $e->getMessage();
        }

        return $resultado;
    }

    /**
     * Obtener estructura jerárquica completa
     * 
     * @return array
     */
    public static function obtenerEstructuraCompleta()
    {
        try {
            return [
                'nivel1' => Nivel1::where('estado', 1)->orderBy('nombre')->get(),
                'nivel2' => Nivel2::where('estado', 1)->with('nivel1')->orderBy('nombre')->get(),
                'nivel3' => Nivel3::where('estado', 1)->with(['nivel2.nivel1'])->orderBy('nombre')->get(),
                'nivel4' => Nivel4::where('estado', 1)->with(['nivel3.nivel2.nivel1'])->orderBy('nombre')->get()
            ];
        } catch (\Exception $e) {
            \Log::error("Error obteniendo estructura jerárquica completa: " . $e->getMessage());
            return [
                'nivel1' => collect(),
                'nivel2' => collect(),
                'nivel3' => collect(),
                'nivel4' => collect()
            ];
        }
    }
}