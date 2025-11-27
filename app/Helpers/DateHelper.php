<?php

namespace App\Helpers;

use Carbon\Carbon;

/**
 * Helper para funciones de fecha y utilidades generales del sistema
 * 
 * Esta clase proporciona métodos utilitarios para el manejo de fechas,
 * cálculos de plazos, días hábiles y otras funciones generales
 * utilizadas en todo el sistema.
 * 
 * @package App\Helpers
 */
class DateHelper
{
    /**
     * Calcula días hábiles entre dos fechas (excluye sábados y domingos)
     * 
     * @param Carbon $fechaInicio Fecha de inicio
     * @param Carbon $fechaFin Fecha de fin
     * @return int Número de días hábiles
     */
    public static function calcularDiasHabiles(Carbon $fechaInicio, Carbon $fechaFin): int
    {
        $diasHabiles = 0;
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            // Excluir sábados (6) y domingos (0)
            if (!in_array($fechaActual->dayOfWeek, [0, 6])) {
                $diasHabiles++;
            }
            $fechaActual->addDay();
        }

        return $diasHabiles;
    }

    /**
     * Calcula la fecha límite considerando días hábiles
     * 
     * @param Carbon $fechaInicio Fecha de inicio
     * @param int $diasHabiles Número de días hábiles a agregar
     * @return Carbon Fecha límite calculada
     */
    public static function calcularFechaLimite(Carbon $fechaInicio, int $diasHabiles): Carbon
    {
        $fechaActual = $fechaInicio->copy();
        $diasAgregados = 0;

        while ($diasAgregados < $diasHabiles) {
            $fechaActual->addDay();
            // Solo contar días hábiles (lunes a viernes)
            if (!in_array($fechaActual->dayOfWeek, [0, 6])) {
                $diasAgregados++;
            }
        }

        return $fechaActual;
    }

    /**
     * Verifica si una fecha es día hábil
     * 
     * @param Carbon $fecha Fecha a verificar
     * @return bool True si es día hábil
     */
    public static function esDiaHabil(Carbon $fecha): bool
    {
        // Verificar que no sea sábado ni domingo
        if (in_array($fecha->dayOfWeek, [0, 6])) {
            return false;
        }

        // Aquí se podría agregar verificación de feriados
        return true;
    }

    /**
     * Obtiene el siguiente día hábil
     * 
     * @param Carbon $fecha Fecha de referencia
     * @return Carbon Siguiente día hábil
     */
    public static function obtenerSiguienteDiaHabil(Carbon $fecha): Carbon
    {
        $siguiente = $fecha->copy()->addDay();
        
        while (!self::esDiaHabil($siguiente)) {
            $siguiente->addDay();
        }

        return $siguiente;
    }

    /**
     * Calcula la antigüedad de un trámite en días hábiles
     * 
     * @param Carbon $fechaRegistro Fecha de registro del trámite
     * @return int Días hábiles transcurridos
     */
    public static function calcularAntiguedad(Carbon $fechaRegistro): int
    {
        $hoy = Carbon::now();
        return self::calcularDiasHabiles($fechaRegistro, $hoy);
    }

    /**
     * Formatea fecha en formato peruano
     * 
     * @param Carbon $fecha Fecha a formatear
     * @return string Fecha formateada
     */
    public static function formatearFechaPeruano(Carbon $fecha): string
    {
        return $fecha->format('d/m/Y');
    }

    /**
     * Formatea fecha y hora en formato peruano
     * 
     * @param Carbon $fecha Fecha a formatear
     * @return string Fecha y hora formateadas
     */
    public static function formatearFechaHoraPeruano(Carbon $fecha): string
    {
        return $fecha->format('d/m/Y H:i:s');
    }

    /**
     * Convierte fecha en formato peruano a objeto Carbon
     * 
     * @param string $fechaStr Fecha en formato d/m/Y
     * @return Carbon|null Objeto Carbon o null si es inválido
     */
    public static function parsearFechaPeruano(string $fechaStr): ?Carbon
    {
        try {
            return Carbon::createFromFormat('d/m/Y', $fechaStr);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene el nombre del mes en español
     * 
     * @param int $mes Número del mes (1-12)
     * @return string Nombre del mes
     */
    public static function obtenerNombreMes(int $mes): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $meses[$mes] ?? '';
    }

    /**
     * Obtiene el nombre del día de la semana en español
     * 
     * @param int $dia Número del día (0 = Domingo, 6 = Sábado)
     * @return string Nombre del día
     */
    public static function obtenerNombreDia(int $dia): string
    {
        $dias = [
            0 => 'Domingo', 1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
            4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado'
        ];

        return $dias[$dia] ?? '';
    }

    /**
     * Calcula la diferencia en formato humano
     * 
     * @param Carbon $fecha Fecha a comparar
     * @return string Diferencia en formato humano
     */
    public static function diferenciaHumana(Carbon $fecha): string
    {
        return $fecha->diffForHumans();
    }

    /**
     * Verifica si un trámite está dentro del plazo establecido
     * 
     * @param Carbon $fechaRegistro Fecha de registro
     * @param int $plazoDias Plazo en días hábiles
     * @return array Resultado con estado y días restantes
     */
    public static function verificarPlazoTramite(Carbon $fechaRegistro, int $plazoDias): array
    {
        $fechaLimite = self::calcularFechaLimite($fechaRegistro, $plazoDias);
        $hoy = Carbon::now();
        
        $diasRestantes = self::calcularDiasHabiles($hoy, $fechaLimite);
        $estado = $diasRestantes >= 0 ? 'dentro_plazo' : 'vencido';

        return [
            'estado' => $estado,
            'dias_restantes' => abs($diasRestantes),
            'fecha_limite' => $fechaLimite,
            'vencido' => $diasRestantes < 0
        ];
    }

    /**
     * Obtiene el primer día hábil del mes
     * 
     * @param int $anio Año
     * @param int $mes Mes
     * @return Carbon Primer día hábil
     */
    public static function obtenerPrimerDiaHabilMes(int $anio, int $mes): Carbon
    {
        $fecha = Carbon::create($anio, $mes, 1);
        
        if (!self::esDiaHabil($fecha)) {
            return self::obtenerSiguienteDiaHabil($fecha);
        }

        return $fecha;
    }

    /**
     * Obtiene el último día hábil del mes
     * 
     * @param int $anio Año
     * @param int $mes Mes
     * @return Carbon Último día hábil
     */
    public static function obtenerUltimoDiaHabilMes(int $anio, int $mes): Carbon
    {
        $fecha = Carbon::create($anio, $mes, 1)->endOfMonth();
        
        if (!self::esDiaHabil($fecha)) {
            while (!self::esDiaHabil($fecha)) {
                $fecha->subDay();
            }
        }

        return $fecha;
    }

    /**
     * Calcula días hábiles entre dos fechas excluyendo feriados
     * 
     * @param Carbon $fechaInicio Fecha de inicio
     * @param Carbon $fechaFin Fecha de fin
     * @param array $feriados Array de fechas de feriados
     * @return int Número de días hábiles
     */
    public static function calcularDiasHabilesConFeriados(Carbon $fechaInicio, Carbon $fechaFin, array $feriados = []): int
    {
        $diasHabiles = 0;
        $fechaActual = $fechaInicio->copy();

        while ($fechaActual <= $fechaFin) {
            // Excluir sábados, domingos y feriados
            if (!in_array($fechaActual->dayOfWeek, [0, 6]) && 
                !in_array($fechaActual->format('Y-m-d'), $feriados)) {
                $diasHabiles++;
            }
            $fechaActual->addDay();
        }

        return $diasHabiles;
    }

    /**
     * Genera rango de fechas en formato período
     * 
     * @param Carbon $fechaInicio Fecha de inicio
     * @param Carbon $fechaFin Fecha de fin
     * @return string Rango formateado
     */
    public static function formatearRangoFechas(Carbon $fechaInicio, Carbon $fechaFin): string
    {
        if ($fechaInicio->format('Y') === $fechaFin->format('Y')) {
            if ($fechaInicio->format('m') === $fechaFin->format('m')) {
                return sprintf('%s al %s de %s de %s',
                    $fechaInicio->format('d'),
                    $fechaFin->format('d'),
                    self::obtenerNombreMes((int)$fechaInicio->format('m')),
                    $fechaInicio->format('Y')
                );
            } else {
                return sprintf('%s de %s al %s de %s de %s',
                    $fechaInicio->format('d'),
                    self::obtenerNombreMes((int)$fechaInicio->format('m')),
                    $fechaFin->format('d'),
                    self::obtenerNombreMes((int)$fechaFin->format('m')),
                    $fechaInicio->format('Y')
                );
            }
        } else {
            return sprintf('%s al %s',
                self::formatearFechaPeruano($fechaInicio),
                self::formatearFechaPeruano($fechaFin)
            );
        }
    }

    /**
     * Obtiene trimestre de una fecha
     * 
     * @param Carbon $fecha Fecha a analizar
     * @return int Número de trimestre (1-4)
     */
    public static function obtenerTrimestre(Carbon $fecha): int
    {
        return (int)ceil($fecha->month / 3);
    }

    /**
     * Obtiene semestre de una fecha
     * 
     * @param Carbon $fecha Fecha a analizar
     * @return int Número de semestre (1-2)
     */
    public static function obtenerSemestre(Carbon $fecha): int
    {
        return $fecha->month <= 6 ? 1 : 2;
    }

    /**
     * Genera array de meses para selectores
     * 
     * @return array Array con meses
     */
    public static function obtenerMesesParaSelector(): array
    {
        $meses = [];
        for ($i = 1; $i <= 12; $i++) {
            $meses[$i] = self::obtenerNombreMes($i);
        }
        return $meses;
    }

    /**
     * Genera array de años para selectores
     * 
     * @param int $añosAtras Número de años hacia atrás
     * @param int $añosAdelante Número de años hacia adelante
     * @return array Array con años
     */
    public static function obtenerAñosParaSelector(int $añosAtras = 5, int $añosAdelante = 1): array
    {
        $añoActual = (int)date('Y');
        $años = [];
        
        for ($i = $añoActual - $añosAtras; $i <= $añoActual + $añosAdelante; $i++) {
            $años[$i] = $i;
        }
        
        return $años;
    }
}