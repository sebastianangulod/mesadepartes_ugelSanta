<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Reporte\ReporteService;
use App\Services\Tramite\TramiteService;

/**
 * Controlador para gestionar reportes y estadísticas
 * 
 * Este controlador maneja:
 * - Reportes de trámites por estado
 * - Reportes de trámites por período
 * - Reportes de usuarios y procedimientos
 * - Exportación de reportes en diferentes formatos
 * - Estadísticas del sistema
 */
class ReporteController extends Controller
{
    protected $reporteService;
    protected $tramiteService;

    public function __construct(ReporteService $reporteService, TramiteService $tramiteService)
    {
        $this->reporteService = $reporteService;
        $this->tramiteService = $tramiteService;
    }

    /**
     * Mostrar dashboard de reportes
     * 
     * @return \Illuminate\View\View Vista del dashboard de reportes
     */
    public function index()
    {
        // Obtener estadísticas generales
        $estadisticas = $this->reporteService->obtenerEstadisticasGenerales();
        
        return view('reportes.index', compact('estadisticas'));
    }

    /**
     * Reporte de trámites por estado
     * 
     * @param Request $request Datos de la petición
     * @return \Illuminate\Http\JsonResponse Reporte de trámites por estado
     */
    public function tramitesPorEstado(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $reporte = $this->reporteService->obtenerTramitesPorEstado($fechaInicio, $fechaFin);
        
        return response()->json($reporte);
    }

    /**
     * Reporte de trámites por período
     * 
     * @param Request $request Datos de la petición
     * @return \Illuminate\Http\JsonResponse Reporte de trámites por período
     */
    public function tramitesPorPeriodo(Request $request)
    {
        $periodo = $request->input('periodo', date('Y'));
        $tipo = $request->input('tipo', 'mensual'); // mensual, trimestral, semestral
        
        $reporte = $this->reporteService->obtenerTramitesPorPeriodo($periodo, $tipo);
        
        return response()->json($reporte);
    }

    /**
     * Reporte de trámites por usuario
     * 
     * @param Request $request Datos de la petición
     * @return \Illuminate\Http\JsonResponse Reporte de trámites por usuario
     */
    public function tramitesPorUsuario(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $reporte = $this->reporteService->obtenerTramitesPorUsuario($fechaInicio, $fechaFin);
        
        return response()->json($reporte);
    }

    /**
     * Reporte de trámites por procedimiento
     * 
     * @param Request $request Datos de la petición
     * @return \Illuminate\Http\JsonResponse Reporte de trámites por procedimiento
     */
    public function tramitesPorProcedimiento(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $reporte = $this->reporteService->obtenerTramitesPorProcedimiento($fechaInicio, $fechaFin);
        
        return response()->json($reporte);
    }

    /**
     * Reporte de usuarios registrados
     * 
     * @param Request $request Datos de la petición
     * @return \Illuminate\Http\JsonResponse Reporte de usuarios
     */
    public function usuariosRegistrados(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $tipo = $request->input('tipo'); // 1: externos, 2: internos
        
        $reporte = $this->reporteService->obtenerUsuariosRegistrados($fechaInicio, $fechaFin, $tipo);
        
        return response()->json($reporte);
    }

    /**
     * Reporte de tiempos de procesamiento
     * 
     * @param Request $request Datos de la petición
     * @return \Illuminate\Http\JsonResponse Reporte de tiempos
     */
    public function tiemposProcesamiento(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        $reporte = $this->reporteService->obtenerTiemposProcesamiento($fechaInicio, $fechaFin);
        
        return response()->json($reporte);
    }

    /**
     * Exportar reporte a Excel
     * 
     * @param Request $request Datos de la petición
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Archivo Excel
     */
    public function exportarExcel(Request $request)
    {
        $tipo = $request->input('tipo'); // tipo de reporte
        $parametros = $request->except('tipo');
        
        $archivo = $this->reporteService->exportarExcel($tipo, $parametros);
        
        return response()->download($archivo)->deleteFileAfterSend();
    }

    /**
     * Exportar reporte a PDF
     * 
     * @param Request $request Datos de la petición
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse Archivo PDF
     */
    public function exportarPDF(Request $request)
    {
        $tipo = $request->input('tipo'); // tipo de reporte
        $parametros = $request->except('tipo');
        
        $archivo = $this->reporteService->exportarPDF($tipo, $parametros);
        
        return response()->download($archivo)->deleteFileAfterSend();
    }

    /**
     * Vista de reporte de trámites observados
     * 
     * @return \Illuminate\View\View Vista del reporte
     */
    public function tramitesObservados()
    {
        $tramites = $this->reporteService->obtenerTramitesObservados();
        
        return view('reportes.tramites-observados', compact('tramites'));
    }

    /**
     * Vista de reporte de trámites rechazados
     * 
     * @return \Illuminate\View\View Vista del reporte
     */
    public function tramitesRechazados()
    {
        $tramites = $this->reporteService->obtenerTramitesRechazados();
        
        return view('reportes.tramites-rechazados', compact('tramites'));
    }

    /**
     * Vista de reporte de trámites finalizados
     * 
     * @return \Illuminate\View\View Vista del reporte
     */
    public function tramitesFinalizados()
    {
        $tramites = $this->reporteService->obtenerTramitesFinalizados();
        
        return view('reportes.tramites-finalizados', compact('tramites'));
    }

    /**
     * Dashboard de estadísticas
     * 
     * @return \Illuminate\View\View Vista del dashboard
     */
    public function dashboard()
    {
        $estadisticas = $this->reporteService->obtenerEstadisticasDashboard();
        
        return view('reportes.dashboard', compact('estadisticas'));
    }
}