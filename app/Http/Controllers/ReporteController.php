<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tramite;
use App\Services\Tramites\TramiteBandejaInternaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reportes semanales y mensuales de trámites ingresados (solo lectura,
 * solo usuarios internos). Reutiliza el alcance de la bandeja interna:
 * los no administradores ven únicamente su nivel.
 */
class ReporteController extends Controller
{
    protected TramiteBandejaInternaService $bandeja;

    private const ESTADOS_NO_TERMINALES = ['PENDIENTE', 'EN_PROCESO', 'OBSERVADO', 'VERIFICADO'];

    public function __construct(TramiteBandejaInternaService $bandeja)
    {
        $this->bandeja = $bandeja;
    }

    /**
     * Vista del apartado de reportes.
     */
    public function index()
    {
        $usuario = auth()->user();
        abort_unless($usuario && $usuario->esInterno(), 403);

        return view('reportes.index', [
            'tramitesPendientes' => Tramite::where('estado', 'PENDIENTE')->count(),
        ]);
    }

    /**
     * Rango [desde, hasta] + etiqueta según tipo y desplazamiento.
     * Semana de lunes a domingo, mes calendario, en hora de Lima.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function rango(string $tipo, int $offset): array
    {
        $ahora = Carbon::now('America/Lima');

        if ($tipo === 'mes') {
            $base = $ahora->copy()->addMonthsNoOverflow($offset)->startOfMonth();
            $desde = $base->copy()->startOfDay();
            $hasta = $base->copy()->endOfMonth()->endOfDay();
            $etiqueta = ucfirst($base->locale('es')->translatedFormat('F \\d\\e Y'));
        } else {
            $base = $ahora->copy()->addWeeks($offset)->startOfWeek(Carbon::MONDAY);
            $desde = $base->copy()->startOfDay();
            $hasta = $base->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            $etiqueta = $desde->format('d/m/Y') . ' — ' . $hasta->format('d/m/Y');
        }

        return [$desde, $hasta, $etiqueta];
    }

    /**
     * Query base con el alcance de la bandeja del usuario.
     */
    private function baseQuery($usuario)
    {
        $query = Tramite::with(['procedimiento']);

        if ($usuario->tipo_user == 2 && !$usuario->esAdministrador()) {
            $this->bandeja->aplicarFiltroInternoNoAdmin($query, $usuario);
        }

        return $query;
    }

    /**
     * Datos del reporte en JSON.
     */
    public function datos(Request $request)
    {
        $usuario = auth()->user();
        abort_unless($usuario && $usuario->esInterno(), 403);

        $tipo = $request->input('tipo', 'semana') === 'mes' ? 'mes' : 'semana';
        $offset = (int) $request->input('offset', 0);
        $porPagina = min(max((int) $request->input('por_pagina', 10), 1), 100);
        $pagina = max((int) $request->input('pagina', 1), 1);

        [$desde, $hasta, $etiqueta] = $this->rango($tipo, $offset);

        $periodo = (clone $this->baseQuery($usuario))
            ->whereBetween('fecha_creacion', [$desde, $hasta]);

        $total = (clone $periodo)->count();

        $porEstado = (clone $periodo)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();

        $finalizados = (int) ($porEstado['FINALIZADO'] ?? 0);
        $porcentaje = $total > 0 ? round($finalizados * 100 / $total, 1) : 0;

        $porProcedimiento = (clone $periodo)
            ->selectRaw('id_procedimiento, COUNT(*) as total')
            ->groupBy('id_procedimiento')
            ->orderByDesc('total')
            ->limit(5)
            ->with('procedimiento:id,nombre')
            ->get()
            ->map(fn ($f) => [
                'nombre' => $f->procedimiento->nombre ?? 'Sin procedimiento',
                'total' => (int) $f->total,
            ])
            ->values();

        $porTipo = (clone $periodo)
            ->selectRaw('COALESCE(tipo_tramite, \'VIRTUAL\') as tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        $tiempoMedio = (clone $periodo)
            ->where('estado', 'FINALIZADO')
            ->whereNotNull('fecha_finalizacion')
            ->whereNotNull('fecha_creacion')
            ->selectRaw('AVG(DATEDIFF(fecha_finalizacion, fecha_creacion)) as promedio')
            ->value('promedio');
        $tiempoMedio = $tiempoMedio !== null ? round((float) $tiempoMedio, 1) : null;

        // Vencidos: en bandeja actual (sin filtro de periodo), más de 30 días sin terminar.
        $vencidos = (clone $this->baseQuery($usuario))
            ->whereIn('estado', self::ESTADOS_NO_TERMINALES)
            ->where('fecha_creacion', '<=', Carbon::now('America/Lima')->subDays(30))
            ->count();

        $detalleQuery = (clone $periodo)->orderByDesc('fecha_creacion');
        $totalDetalle = (clone $detalleQuery)->count();
        $items = $detalleQuery
            ->skip(($pagina - 1) * $porPagina)
            ->take($porPagina)
            ->get()
            ->map(function ($t) {
                $datos = $t->datosSolicitante();
                $nombre = trim($t->nombre_solicitante);
                $doc = $datos['dni'] ?? '';
                if (!$doc) {
                    $doc = $datos['ruc'] ?? '';
                }

                $dias = 0;
                if ($t->fecha_creacion) {
                    $dias = (int) Carbon::parse($t->fecha_creacion)->diffInDays(Carbon::now('America/Lima'), false);
                    if ($dias < 0) {
                        $dias = 0;
                    }
                }

                return [
                    'id' => $t->id,
                    'fecha' => $t->fecha_creacion ? Carbon::parse($t->fecha_creacion)->format('d/m/Y H:i') : '—',
                    'procedimiento' => $t->procedimiento->nombre ?? '—',
                    'tipo' => $t->tipo_tramite ?? '—',
                    'titular' => $nombre !== '' ? $nombre : '—',
                    'documento' => $doc !== '' ? $doc : '—',
                    'estado' => $t->estado,
                    'dias' => $dias,
                ];
            });

        return response()->json([
            'status' => 'success',
            'tipo' => $tipo,
            'offset' => $offset,
            'etiqueta' => $etiqueta,
            'kpis' => [
                'ingresados' => $total,
                'finalizados' => $finalizados,
                'porcentaje' => $porcentaje,
                'observados' => (int) ($porEstado['OBSERVADO'] ?? 0),
                'rechazados' => (int) ($porEstado['RECHAZADO'] ?? 0),
                'tiempo_medio' => $tiempoMedio,
                'vencidos' => $vencidos,
            ],
            'por_estado' => $porEstado,
            'por_procedimiento' => $porProcedimiento,
            'por_tipo' => $porTipo,
            'detalle' => [
                'items' => $items,
                'total' => $totalDetalle,
                'paginas' => (int) ceil($totalDetalle / $porPagina),
                'pagina_actual' => $pagina,
            ],
        ]);
    }

    /**
     * Descarga CSV del detalle del periodo.
     */
    public function exportar(Request $request): StreamedResponse
    {
        $usuario = auth()->user();
        abort_unless($usuario && $usuario->esInterno(), 403);

        $tipo = $request->input('tipo', 'semana') === 'mes' ? 'mes' : 'semana';
        $offset = (int) $request->input('offset', 0);

        [$desde, $hasta, $etiqueta] = $this->rango($tipo, $offset);

        $filas = (clone $this->baseQuery($usuario))
            ->whereBetween('fecha_creacion', [$desde, $hasta])
            ->orderByDesc('fecha_creacion')
            ->limit(5000)
            ->get();

        $nombreArchivo = 'reporte-' . $tipo . '-' . $desde->format('Ymd') . '-' . $hasta->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($filas) {
            $salida = fopen('php://output', 'w');
            // BOM para que Excel abra los tildes correctamente.
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['Exp.', 'Fecha', 'Procedimiento', 'Tipo', 'Titular', 'Documento', 'Estado', 'Días'], ';');

            foreach ($filas as $t) {
                $datos = $t->datosSolicitante();
                $doc = $datos['dni'] ?? '';
                if (!$doc) {
                    $doc = $datos['ruc'] ?? '';
                }
                $dias = 0;
                if ($t->fecha_creacion) {
                    $dias = (int) Carbon::parse($t->fecha_creacion)->diffInDays(Carbon::now('America/Lima'), false);
                    if ($dias < 0) {
                        $dias = 0;
                    }
                }

                fputcsv($salida, [
                    $t->id,
                    $t->fecha_creacion ? Carbon::parse($t->fecha_creacion)->format('d/m/Y H:i') : '',
                    $t->procedimiento->nombre ?? '',
                    $t->tipo_tramite ?? '',
                    trim($t->nombre_solicitante),
                    $doc,
                    $t->estado,
                    $dias,
                ], ';');
            }

            fclose($salida);
        }, $nombreArchivo, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
