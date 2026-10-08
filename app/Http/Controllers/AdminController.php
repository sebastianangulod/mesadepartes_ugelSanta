<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Procedimiento;
use App\Models\Tramite;
use App\Domain\Tramites\EstadoTramite;

/**
 * Controlador de administración: dashboard con estadísticas generales.
 * La gestión (usuarios, niveles, procedimientos, flujo) vive en
 * App\Http\Controllers\Admin\*. Solo accesible para administradores.
 */
class AdminController extends Controller
{
    /**
     * Muestra el dashboard principal de administración
     */
    public function index()
    {
        // Estadísticas generales
        $totalUsuarios = Usuario::count();
        $totalTramites = Tramite::count();
        $tramitesActivos = Tramite::whereIn('estado', [
            EstadoTramite::PENDIENTE,
            EstadoTramite::EN_PROCESO,
            EstadoTramite::OBSERVADO,
            EstadoTramite::VERIFICADO
        ])->count();
        $tramitesPendientes = Tramite::where('estado', EstadoTramite::PENDIENTE)->count();
        $tramitesEnProceso = Tramite::where('estado', EstadoTramite::EN_PROCESO)->count();
        $tramitesRechazados = Tramite::where('estado', EstadoTramite::RECHAZADO)->count();
        $tramitesFinalizadosHoy = Tramite::where('estado', EstadoTramite::FINALIZADO)
            ->whereDate('fecha_creacion', today())
            ->count();
        
        // Trámites recientes (últimos 10)
        $tramitesRecientes = Tramite::with(['usuario', 'procedimiento'])
            ->orderByDesc('fecha_creacion')
            ->limit(10)
            ->get();
        
        // Estadísticas para gráficos
        $tramitesPorMes = Tramite::selectRaw('DATE_FORMAT(fecha_creacion, "%Y-%m") as mes, COUNT(*) as total')
            ->whereYear('fecha_creacion', date('Y'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        
        $tramitesPorEstado = Tramite::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
        
        return view('admin.index', compact(
            'totalUsuarios',
            'totalTramites',
            'tramitesActivos',
            'tramitesPendientes',
            'tramitesEnProceso',
            'tramitesRechazados',
            'tramitesFinalizadosHoy',
            'tramitesRecientes',
            'tramitesPorMes',
            'tramitesPorEstado'
        ));
    }
}