<?php

namespace App\DTOs;

/**
 * Data Transfer Object para Transición de Estado
 * 
 * @package App\DTOs
 */
class TransicionDTO
{
    public int $tramite_id;
    public string $estado_anterior;
    public string $estado_nuevo;
    public ?string $motivo;
    public ?string $observacion;
    public int $usuario_id;
    public string $fecha_transicion;
    public array $metadatos = [];

    /**
     * Constructor
     * 
     * @param array $datos
     */
    public function __construct(array $datos = [])
    {
        $this->tramite_id = $datos['tramite_id'] ?? 0;
        $this->estado_anterior = $datos['estado_anterior'] ?? '';
        $this->estado_nuevo = $datos['estado_nuevo'] ?? '';
        $this->motivo = $datos['motivo'] ?? null;
        $this->observacion = $datos['observacion'] ?? null;
        $this->usuario_id = $datos['usuario_id'] ?? 0;
        $this->fecha_transicion = $datos['fecha_transicion'] ?? now()->toDateTimeString();
        $this->metadatos = $datos['metadatos'] ?? [];
    }

    /**
     * Convertir a array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'tramite_id' => $this->tramite_id,
            'estado_anterior' => $this->estado_anterior,
            'estado_nuevo' => $this->estado_nuevo,
            'motivo' => $this->motivo,
            'observacion' => $this->observacion,
            'usuario_id' => $this->usuario_id,
            'fecha_transicion' => $this->fecha_transicion,
            'metadatos' => json_encode($this->metadatos),
        ];
    }

    /**
     * Crear desde array
     * 
     * @param array $datos
     * @return self
     */
    public static function fromArray(array $datos): self
    {
        return new self($datos);
    }

    /**
     * Validar transición
     * 
     * @return array
     */
    public function validar(): array
    {
        $errores = [];

        if ($this->tramite_id <= 0) {
            $errores[] = 'El ID del trámite es requerido';
        }

        if (empty($this->estado_anterior)) {
            $errores[] = 'El estado anterior es requerido';
        }

        if (empty($this->estado_nuevo)) {
            $errores[] = 'El estado nuevo es requerido';
        }

        if ($this->estado_anterior === $this->estado_nuevo) {
            $errores[] = 'El estado nuevo debe ser diferente al estado anterior';
        }

        if ($this->usuario_id <= 0) {
            $errores[] = 'El ID del usuario es requerido';
        }

        // Validar que el motivo exista para rechazos
        if ($this->estado_nuevo === 'RECHAZADO' && empty($this->motivo)) {
            $errores[] = 'El motivo es obligatorio para rechazos';
        }

        // Validar que la observación exista para observaciones
        if ($this->estado_nuevo === 'OBSERVADO' && empty($this->observacion)) {
            $errores[] = 'La observación es obligatoria para observaciones';
        }

        return $errores;
    }
}