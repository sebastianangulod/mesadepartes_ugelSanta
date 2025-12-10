<?php

namespace App\Services\Tramites;

use App\Models\Tramite;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Servicio para generar vouchers y expedientes de trámites
 */
class VoucherService
{
    private string $voucherPath = 'uploads/vouchers/';
    private string $expedientePath = 'uploads/expedientes/';

    /**
     * Generar número de voucher único
     * 
     * @param string $tipoTramite PRESENCIAL o VIRTUAL
     * @return string
     */
    public function generarNumeroVoucher(string $tipoTramite): string
    {
        // Determinar el prefijo según el tipo de trámite
        $prefijo = ($tipoTramite === 'PRESENCIAL') ? 'P' : 'V';

        // Obtener el último número de voucher de este tipo
        $ultimoNumero = Tramite::where('tipo_tramite', $tipoTramite)
            ->whereNotNull('numero_voucher')
            ->where('numero_voucher', 'like', $prefijo . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(numero_voucher, 2) AS UNSIGNED)) as ultimo_numero')
            ->value('ultimo_numero') ?? 0;

        // Calcular el nuevo número de voucher
        $nuevoNumero = $ultimoNumero + 1;
        
        return $prefijo . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT); // Formato P000001 o V000001
    }

    /**
     * Generar PDF del voucher
     * 
     * @param Tramite $tramite
     * @return string Nombre del archivo generado
     * @throws Exception
     */
    public function generarVoucher(Tramite $tramite): string
    {
        try {
            // Asegurar que el directorio existe
            $voucherDir = storage_path('app/' . $this->voucherPath);
            if (!is_dir($voucherDir)) {
                mkdir($voucherDir, 0777, true);
            }

            // Generar nombre único para el voucher
            $nombreVoucher = 'voucher_' . $tramite->id . '_' . time() . '.pdf';
            $rutaCompleta = $voucherDir . $nombreVoucher;

            // Cargar datos completos del trámite
            $tramite->load(['usuario', 'procedimiento']);
            $procedimiento = $tramite->procedimiento;

            // Obtener nombres de niveles jerárquicos
            $nivel1Nombre = null;
            $nivel2Nombre = null;
            $nivel3Nombre = null;
            $nivel4Nombre = null;

            if ($procedimiento->id_area) {
                $nivel1 = Nivel1::find($procedimiento->id_area);
                $nivel1Nombre = $nivel1 ? $nivel1->nombre : null;
            }

            if ($procedimiento->id_subarea) {
                $nivel2 = Nivel2::find($procedimiento->id_subarea);
                $nivel2Nombre = $nivel2 ? $nivel2->nombre : null;
            }

            if (in_array($procedimiento->nivel_final, ['3', '4'])) {
                if ($procedimiento->nivel_final == '3') {
                    $nivel3 = Nivel3::find($procedimiento->id_nivel_final);
                    $nivel3Nombre = $nivel3 ? $nivel3->nombre : null;
                } elseif ($procedimiento->nivel_final == '4') {
                    $nivel4 = Nivel4::find($procedimiento->id_nivel_final);
                    $nivel4Nombre = $nivel4 ? $nivel4->nombre : null;
                }
            }

            // Crear el PDF usando TCPDF
            $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Configurar el documento
            $pdf->SetCreator('Sistema Mesa de Partes');
            $pdf->SetAuthor('UGEL');
            $pdf->SetTitle('Voucher de Trámite #' . $tramite->numero_voucher);

            // Configurar márgenes
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);

            // Agregar página
            $pdf->AddPage();

            // Establecer fuente base
            $pdf->SetFont('helvetica', '', 10);

            // Encabezado
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->Cell(0, 10, 'VOUCHER DE TRÁMITE', 0, 1, 'C');
            $pdf->Ln(5);

            // Línea separadora
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // Información del trámite
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'INFORMACIÓN DEL TRÁMITE', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);

            // Número de voucher y fecha
            $pdf->Cell(40, 6, 'Número de Voucher:', 0);
            $pdf->Cell(60, 6, $tramite->numero_voucher, 0);
            $pdf->Cell(40, 6, 'Fecha de Registro:', 0);
            $pdf->Cell(0, 6, $tramite->fecha_creacion->format('d/m/Y H:i'), 0, 1);

            // Tipo y procedimiento
            $pdf->Cell(40, 6, 'Tipo de Trámite:', 0);
            $pdf->Cell(60, 6, $tramite->tipo_tramite, 0);
            $pdf->Cell(40, 6, 'Estado:', 0);
            $pdf->Cell(0, 6, $tramite->estado, 0, 1);

            // Número de oficio si existe
            if ($tramite->oficio) {
                $pdf->Cell(40, 6, 'Número de Oficio:', 0);
                $pdf->Cell(0, 6, $tramite->oficio, 0, 1);
            }

            // Procedimiento
            $pdf->Cell(40, 6, 'Procedimiento:', 0);
            $pdf->MultiCell(0, 6, $procedimiento->nombre);

            $pdf->Ln(3);

            // Línea separadora
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // Información del solicitante
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'INFORMACIÓN DEL SOLICITANTE', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);

            $pdf->Cell(40, 6, 'Razón Social:', 0);
            $pdf->Cell(60, 6, $tramite->usuario->razon_social ?? 'N/A', 0);
            $pdf->Cell(40, 6, 'DNI:', 0);
            $pdf->Cell(0, 6, $tramite->usuario->dni ?? 'N/A', 0, 1);

            $pdf->Ln(3);

            // Línea separadora
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // Ruta de procesamiento
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'RUTA DE PROCESAMIENTO', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);

            // Construir la ruta según el nivel final
            $ruta = [];
            if ($nivel1Nombre) $ruta[] = $nivel1Nombre;
            if ($nivel2Nombre) $ruta[] = $nivel2Nombre;
            if ($nivel3Nombre && $procedimiento->nivel_final >= '3') $ruta[] = $nivel3Nombre;
            if ($nivel4Nombre && $procedimiento->nivel_final == '4') $ruta[] = $nivel4Nombre;

            $pdf->Cell(0, 6, implode(' → ', $ruta), 0, 1);

            $pdf->Ln(3);

            // Línea separadora
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->Ln(5);

            // Historial de estados
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 8, 'HISTORIAL DE PROCESAMIENTO', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);

            // Estado inicial
            $pdf->Cell(40, 6, 'Estado Inicial:', 0);
            $pdf->Cell(60, 6, 'PENDIENTE', 0);
            $pdf->Cell(40, 6, 'Fecha:', 0);
            $pdf->Cell(0, 6, $tramite->fecha_creacion->format('d/m/Y H:i'), 0, 1);

            // Estado actual
            $pdf->Cell(40, 6, 'Estado Actual:', 0);
            $pdf->Cell(60, 6, $tramite->estado, 0);
            $pdf->Cell(40, 6, 'Fecha:', 0);
            $fechaActualizacion = $tramite->updated_at ?? $tramite->fecha_creacion;
            $pdf->Cell(0, 6, $fechaActualizacion->format('d/m/Y H:i'), 0, 1);

            // Si hay estado anterior (por observaciones)
            if ($tramite->ultimo_estado) {
                $pdf->Cell(40, 6, 'Estado Anterior:', 0);
                $pdf->Cell(60, 6, $tramite->ultimo_estado, 0);
                $pdf->Cell(40, 6, 'Fecha:', 0);
                $pdf->Cell(0, 6, $fechaActualizacion->format('d/m/Y H:i'), 0, 1);
            }

            // Si hay observaciones
            if ($tramite->observaciones) {
                $pdf->Ln(3);
                $pdf->SetFont('helvetica', 'B', 12);
                $pdf->Cell(0, 8, 'OBSERVACIONES', 0, 1, 'L');
                $pdf->SetFont('helvetica', '', 10);
                $pdf->MultiCell(0, 6, $tramite->observaciones);
            }

            // Pie de página
            $pdf->SetY(-20);
            $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->Cell(0, 5, 'Este documento es un comprobante oficial del trámite realizado en el Sistema de Mesa de Partes.', 0, 1, 'C');

            // Guardar el PDF
            $pdf->Output($rutaCompleta, 'F');

            return $nombreVoucher;

        } catch (\Exception $e) {
            throw new Exception("Error al generar voucher: " . $e->getMessage());
        }
    }

    /**
     * Generar expediente interno
     * 
     * @param Tramite $tramite
     * @return string Nombre del archivo generado
     * @throws Exception
     */
    public function generarExpedienteInterno(Tramite $tramite): string
    {
        // Similar a generarVoucher pero con contenido de expediente interno
        // Por ahora, reutilizamos la misma lógica
        return $this->generarVoucher($tramite);
    }
}

