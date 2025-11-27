<?php

namespace App\DTOs;

/**
 * Data Transfer Object para Trámite
 * 
 * @package App\DTOs
 */
class TramiteDTO
{
    public ?int $id;
    public string $numero_expediente;
    public ?string $asunto;
    public ?string $descripcion;
    public string $estado;
    public int $id_usuario;
    public ?int $id_procedimiento;
    public ?string $archivo_expediente;
    public ?string $archivo_voucher;
    public ?string $fecha_presentacion;
    public ?string $fecha_finalizacion;
    public ?string $observaciones;
    public ?string $motivo_rechazo;
    public ?int $revisado_por;
    public ?string $fecha_revision;
    public array $datos_adicionales = [];

    /**
     * Constructor
     * 
     * @param array $datos
     */
    public function __construct(array $datos = [])
    {
        $this->id = $datos['id'] ?? null;
        $this->numero_expediente = $datos['numero_expediente'] ?? '';
        $this->asunto = $datos['asunto'] ?? null;
        $this->descripcion = $datos['descripcion'] ?? null;
        $this->estado = $datos['estado'] ?? 'PENDIENTE';
        $this->id_usuario = $datos['id_usuario'] ?? 0;
        $this->id_procedimiento = $datos['id_procedimiento'] ?? null;
        $this->archivo_expediente = $datos['archivo_expediente'] ?? null;
        $this->archivo_voucher = $datos['archivo_voucher'] ?? null;
        $this->fecha_presentacion = $datos['fecha_presentacion'] ?? null;
        $this->fecha_finalizacion = $datos['fecha_finalizacion'] ?? null;
        $this->observaciones = $datos['observaciones'] ?? null;
        $this->motivo_rechazo = $datos['motivo_rechazo'] ?? null;
        $this->revisado_por = $datos['revisado_por'] ?? null;
        $this->fecha_revision = $datos['fecha_revision'] ?? null;
        $this->datos_adicionales = $datos['datos_adicionales'] ?? [];
    }

    /**
     * Convertir a array
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'numero_expediente' => $this->numero_expediente,
            'asunto' => $this->asunto,
            'descripcion' => $this->descripcion,
            'estado' => $this->estado,
            'id_usuario' => $this->id_usuario,
            'id_procedimiento' => $this->id_procedimiento,
            'archivo_expediente' => $this->archivo_expediente,
            'archivo_voucher' => $this->archivo_voucher,
            'fecha_presentacion' => $this->fecha_presentacion,
            'fecha_finalizacion' => $this->fecha_finalizacion,
            'observaciones' => $this->observaciones,
            'motivo_rechazo' => $this->motivo_rechazo,
            'revisado_por' => $this->revisado_por,
            'fecha_revision' => $this->fecha_revision,
            'datos_adicionales' => $this->datos_adicionales,
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
     * Validar datos básicos
     * 
     * @return array
     */
    public function validar(): array
    {
        $errores = [];

        if (empty($this->numero_expediente)) {
            $errores[] = 'El número de expediente es requerido';
        }

        if ($this->id_usuario <= 0) {
            $errores[] = 'El ID de usuario debe ser válido';
        }

        // Validar formato de expediente (ej: EXP-2025-000001)
        if (!preg_match('/^EXP-\d{4}-\d{6}$/', $this->numero_expediente)) {
            $errores[] = 'El formato del número de expediente debe ser EXP-AAAA-NNNNNN';
        }

        return $errores;
    }

    /**
     * Generar número de expediente
     * 
     * @return string
     */
    public static function generarNumeroExpediente(): string
    {
        $anio = date('Y');
        $numero = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        return "EXP-{$anio}-{$numero}";
    }
}