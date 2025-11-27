<?php

namespace App\Domain\Tramites;

/**
 * Enumeración de estados de trámite
 * 
 * Esta clase define todos los estados posibles que puede tener un trámite
 * en el sistema de mesa de partes.
 * 
 * @package App\Domain\Tramites
 */
class EstadoTramite
{
    // Estados base del sistema
    const PENDIENTE = 'PENDIENTE';
    const EN_PROCESO = 'EN_PROCESO';
    const OBSERVADO = 'OBSERVADO';
    const VERIFICADO = 'VERIFICADO';
    const FINALIZADO = 'FINALIZADO';
    const RECHAZADO = 'RECHAZADO';

    /**
     * Obtener todos los estados disponibles
     * 
     * @return array<string>
     */
    public static function todos(): array
    {
        return [
            self::PENDIENTE,
            self::EN_PROCESO,
            self::OBSERVADO,
            self::VERIFICADO,
            self::FINALIZADO,
            self::RECHAZADO,
        ];
    }

    /**
     * Obtener estados que permiten transiciones
     * 
     * @return array<string>
     */
    public static function estadosActivos(): array
    {
        return [
            self::PENDIENTE,
            self::EN_PROCESO,
            self::OBSERVADO,
            self::VERIFICADO,
        ];
    }

    /**
     * Obtener estados finales (sin transiciones)
     * 
     * @return array<string>
     */
    public static function estadosFinales(): array
    {
        return [
            self::FINALIZADO,
            self::RECHAZADO,
        ];
    }

    /**
     * Verificar si un estado es válido
     * 
     * @param string $estado
     * @return bool
     */
    public static function esValido(string $estado): bool
    {
        return in_array($estado, self::todos());
    }

    /**
     * Obtener etiqueta para UI
     * 
     * @param string $estado
     * @return string
     */
    public static function obtenerEtiqueta(string $estado): string
    {
        $etiquetas = [
            self::PENDIENTE => 'Pendiente',
            self::EN_PROCESO => 'En Proceso',
            self::OBSERVADO => 'Observado',
            self::VERIFICADO => 'Verificado',
            self::FINALIZADO => 'Finalizado',
            self::RECHAZADO => 'Rechazado',
        ];

        return $etiquetas[$estado] ?? 'Desconocido';
    }

    /**
     * Obtener clases CSS para UI
     * 
     * @param string $estado
     * @return string
     */
    public static function obtenerClasesCss(string $estado): string
    {
        $clases = [
            self::PENDIENTE => 'bg-yellow-100 text-yellow-800',
            self::EN_PROCESO => 'bg-blue-100 text-blue-800',
            self::OBSERVADO => 'bg-orange-100 text-orange-800',
            self::VERIFICADO => 'bg-green-100 text-green-800',
            self::FINALIZADO => 'bg-gray-100 text-gray-800',
            self::RECHAZADO => 'bg-red-100 text-red-800',
        ];

        return $clases[$estado] ?? 'bg-gray-100 text-gray-800';
    }
}