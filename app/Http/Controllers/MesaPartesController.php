<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use App\Repositories\TramiteRepository;
use App\Services\Tramites\TramiteBandejaInternaService;

/**
 * Controlador de Mesa de Partes para gestionar:
 * - Creación de nuevos trámites
 * - Consulta de trámites enviados
 * - Recepción de trámites
 * - Seguimiento de trámites
 * - Generación de vouchers
 * - Hojas de ruta
 */
class MesaPartesController extends Controller
{
    /**
     * @var TramiteRepository
     */
    protected TramiteRepository $tramiteRepository;

    protected TramiteBandejaInternaService $bandejaInterna;

    public function __construct(
        TramiteRepository $tramiteRepository,
        TramiteBandejaInternaService $bandejaInterna
    ) {
        $this->tramiteRepository = $tramiteRepository;
        $this->bandejaInterna = $bandejaInterna;
    }

    /**
     * Muestra el dashboard de mesa de partes
     */
    public function index()
    {
        $usuario = auth()->user();

        // Estadísticas básicas de los trámites del usuario autenticado
        // - Usuarios externos: trámites que ellos crearon (creado_por), o legacy por id_usuario
        // - Usuarios internos: trámites asignados a ellos (id_usuario) O que ellos crearon (creado_por)
        if ($usuario->tipo_user == 1) {
            $queryBase = Tramite::where(function ($q) use ($usuario) {
                $q->where('creado_por', $usuario->id)
                  ->orWhere(function ($q2) use ($usuario) {
                      $q2->whereNull('creado_por')->where('id_usuario', $usuario->id);
                  });
            });
        } else {
            $queryBase = Tramite::where(function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id)
                  ->orWhere('creado_por', $usuario->id);
            });
        }

        $estadisticas = [
            'tramites_realizados' => (clone $queryBase)->count(),
            'tramites_completados' => (clone $queryBase)->where('estado', 'FINALIZADO')->count(),
            'tramites_proceso' => (clone $queryBase)->where('estado', 'EN_PROCESO')->count(),
            'tramites_observados' => (clone $queryBase)->where('estado', 'OBSERVADO')->count(),
        ];

        // Trámites recientes del usuario (máx. 5)
        $tramitesRecientes = (clone $queryBase)
            ->orderByDesc('fecha_creacion')
            ->limit(5)
            ->get();

        // Cantidad de trámites pendientes para mostrar en badge del área interna (solo para usuarios internos)
        $tramitesPendientes = 0;
        if ($usuario->tipo_user == 2) {
            $tramitesPendientes = Tramite::where('estado', 'PENDIENTE')->count();
        }

        return view('mesa-partes.index', [
            'estadisticas' => $estadisticas,
            'tramitesRecientes' => $tramitesRecientes,
            'tramitesPendientes' => $tramitesPendientes,
        ]);
    }

    /**
     * Muestra el formulario para crear nuevo trámite
     */
    public function create()
    {
        // Retorna vista: resources/views/mesa-partes/create.blade.php
        // Incluye selector de procedimientos
        // Formulario con validaciones
    }

    /**
     * Almacena un nuevo trámite
     */
    public function store(Request $request)
    {
        // Valida datos del trámite
        // Procesa archivo adjunto si existe
        // Genera número de trámite
        // Crea registro en base de datos
        // Genera voucher
        // Redirige con mensaje de éxito
    }

    /**
     * Muestra trámites enviados por el usuario
     * Equivalente a documentosEnviados.php del PHP puro
     */
    public function documentosEnviados()
    {
        $usuario = auth()->user();

        // Obtener documentos enviados por el usuario
        // - Usuarios externos: trámites que ellos crearon (creado_por), o legacy por id_usuario
        // - Usuarios internos: trámites asignados a ellos (id_usuario) O que ellos crearon (creado_por)
        $query = Tramite::with('procedimiento');

        if ($usuario->tipo_user == 1) {
            $query->where(function ($q) use ($usuario) {
                $q->where('creado_por', $usuario->id)
                  ->orWhere(function ($q2) use ($usuario) {
                      $q2->whereNull('creado_por')->where('id_usuario', $usuario->id);
                  });
            });
        } else {
            $query->where(function ($q) use ($usuario) {
                $q->where('id_usuario', $usuario->id)
                  ->orWhere('creado_por', $usuario->id);
            });
        }

        $documentos = $query
            ->orderByDesc('fecha_creacion')
            ->get();

        // Para el badge de pendientes en el sidebar (solo para usuarios internos)
        $tramitesPendientes = 0;
        if ($usuario->tipo_user == 2) {
            $tramitesPendientes = Tramite::where('estado', 'PENDIENTE')->count();
        }

        return view('mesa-partes.documentos-enviados', [
            'documentos' => $documentos,
            'tramitesPendientes' => $tramitesPendientes,
        ]);
    }

    /**
     * Muestra trámites recibidos para procesar
     * Equivalente a tramitesRecibidos.php del PHP puro
     */
    public function tramitesRecibidos(Request $request)
    {
        $usuario = auth()->user();

        // Obtener filtros de la solicitud
        $filtros = $request->only([
            'numero_tramite',
            'estado',
            'fecha_desde',
            'fecha_hasta',
            'remitente',
        ]);

        $query = Tramite::with([
            'procedimiento',
            'usuario',
            'procedimiento.nodoEntrada',
            'nodoActual',
        ]);

        if ($usuario->tipo_user == 2) {
            if (!$usuario->esAdministrador()) {
                $this->bandejaInterna->aplicarFiltroInternoNoAdmin($query, $usuario);
            }
        } else {
            $query->where(function ($q) use ($usuario) {
                $q->where('creado_por', $usuario->id)
                    ->orWhere(function ($q2) use ($usuario) {
                        $q2->whereNull('creado_por')->where('id_usuario', $usuario->id);
                    });
            });
        }

        // Aplicar filtros
        if (!empty($filtros['numero_tramite'])) {
            $query->where('id', 'like', '%' . $filtros['numero_tramite'] . '%');
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_creacion', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_creacion', '<=', $filtros['fecha_hasta']);
        }

        if (!empty($filtros['remitente'])) {
            $query->whereHas('usuario', function($q) use ($filtros) {
                $q->where('dni', 'like', '%' . $filtros['remitente'] . '%')
                  ->orWhere('nombres', 'like', '%' . $filtros['remitente'] . '%')
                  ->orWhere('apellido_paterno', 'like', '%' . $filtros['remitente'] . '%');
            });
        }

        // Ordenar y paginar
        $tramites = $query->orderByDesc('fecha_creacion')->paginate(15);

        // Calcular resumen
        $resumen = [
            'urgentes' => (clone $query)->where('estado', 'PENDIENTE')
                ->whereDate('fecha_creacion', '<=', now()->subDays(7))
                ->count(),
            'por_vencer' => (clone $query)->where('estado', 'OBSERVADO')
                ->whereDate('limite_observacion', '<=', now()->addDays(3))
                ->count(),
            'en_proceso' => (clone $query)->where('estado', 'EN_PROCESO')->count(),
            'atendidos_hoy' => (clone $query)->whereDate('fecha_creacion', today())->count(),
        ];

        // Para el badge de pendientes en el sidebar
        $tramitesPendientes = Tramite::where('estado', 'PENDIENTE')->count();

        // Usuarios disponibles para asignación (solo internos)
        $usuariosDisponibles = Usuario::where('tipo_user', 2)
            ->where('id', '!=', $usuario->id)
            ->get();

        return view('mesa-partes.tramites-recibidos', [
            'tramites' => $tramites,
            'resumen' => $resumen,
            'tramitesPendientes' => $tramitesPendientes,
            'usuariosDisponibles' => $usuariosDisponibles,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Muestra el formulario para rastrear trámite
     */
    public function rastrearForm()
    {
        // Retorna vista: resources/views/mesa-partes/rastrear.blade.php
        // Formulario para ingresar número de trámite
    }

    /**
     * Procesa la búsqueda de trámites
     */
    public function buscarTramites(Request $request)
    {
        // Valida número de trámite o criterios de búsqueda
        // Busca trámites en base de datos
        // Retorna resultados en JSON o vista
    }

    /**
     * Muestra el seguimiento de un trámite específico
     */
    public function rastrear($id)
    {
        // Retorna vista: resources/views/mesa-partes/seguimiento.blade.php
        // Muestra historial del trámite
        // Estados y transiciones
    }

    /**
     * Genera la hoja de ruta de un trámite
     */
    public function hojaRuta($id)
    {
        // Retorna vista: resources/views/mesa-partes/hoja-ruta.blade.php
        // Genera PDF con información del trámite
        // Historial de movimientos
    }

    /**
     * Muestra el voucher de un trámite
     */
    public function voucher($id)
    {
        // Retorna vista: resources/views/mesa-partes/voucher.blade.php
        // Muestra información del trámite y voucher
        // Opción para imprimir
    }

    /**
     * Procesa cambio de estado de trámite
     */
    public function cambiarEstado(Request $request, $id)
    {
        // Valida nuevo estado
        // Verifica transición válida
        // Actualiza estado del trámite
        // Registra observaciones si las hay
        // Notifica al usuario si corresponde
    }

    /**
     * Observa un trámite con requisitos adicionales
     */
    public function observarTramite(Request $request, $id)
    {
        // Valida observaciones
        // Establece fecha límite para observaciones
        // Actualiza estado a OBSERVADO
        // Notifica al usuario
    }

    /**
     * Finaliza un trámite
     */
    public function finalizarTramite(Request $request, $id)
    {
        // Valida que esté completo
        // Cambia estado a FINALIZADO
        // Registra fecha de finalización
        // Genera documento de cierre si aplica
    }

    /**
     * Rechaza un trámite
     */
    public function rechazarTramite(Request $request, $id)
    {
        // Valida motivo de rechazo
        // Cambia estado a RECHAZADO
        // Registra usuario que rechaza
        // Notifica al usuario
    }

    /**
     * Verifica un trámite finalizado
     */
    public function verificarTramite(Request $request, $id)
    {
        // Valida que esté finalizado
        // Cambia estado a VERIFICADO
        // Registra usuario que verifica
        // Marca como completado el proceso
    }

    /**
     * Obtiene procedimientos por área/nivel
     */
    public function obtenerProcedimientos(Request $request)
    {
        // Valida filtros de búsqueda
        // Busca procedimientos activos
        // Retorna JSON con resultados
    }

    /**
     * Genera reporte de trámites
     */
    public function reporte(Request $request)
    {
        // Valida fechas y filtros
        // Genera reporte PDF/Excel
        // Incluye estadísticas
        // Descarga archivo generado
    }

    /**
     * Obtiene trámites por nivel de usuario (AJAX)
     * Equivalente a obtenerTramitesPorNivelUsuario del PHP puro
     */
    public function obtenerTramitesPorNivelUsuario(Request $request)
    {
        $usuario = auth()->user();
        
        $pagina = $request->input('pagina', 1);
        $porPagina = $request->input('por_pagina', 10);
        
        // Decodificar filtros si viene como JSON string
        $filtrosInput = $request->input('filtros', []);
        if (is_string($filtrosInput)) {
            $filtros = json_decode($filtrosInput, true) ?? [];
        } else {
            $filtros = $filtrosInput;
        }

        try {
            $query = Tramite::with([
                'procedimiento',
                'usuario',
                'procedimiento.nodoEntrada',
                'nodoActual', // Load full nodoActual with all columns including puede_finalizar
            ]);

            if ($usuario->tipo_user == 2) {
                if (!$usuario->esAdministrador()) {
                    $this->bandejaInterna->aplicarFiltroInternoNoAdmin($query, $usuario);
                }
            } else {
                $query->where(function ($q) use ($usuario) {
                    $q->where('creado_por', $usuario->id)
                        ->orWhere(function ($q2) use ($usuario) {
                            $q2->whereNull('creado_por')->where('id_usuario', $usuario->id);
                        });
                });
            }

            // Aplicar filtros
            if (!empty($filtros['estado'])) {
                $query->where('estado', $filtros['estado']);
            }

            if (!empty($filtros['fecha_desde'])) {
                $query->whereDate('fecha_creacion', '>=', $filtros['fecha_desde']);
            }

            if (!empty($filtros['fecha_hasta'])) {
                $query->whereDate('fecha_creacion', '<=', $filtros['fecha_hasta']);
            }

            // Contar total
            $total = $query->count();
            
            // Obtener trámites paginados
            $tramites = $query->orderByDesc('fecha_creacion')
                ->skip(($pagina - 1) * $porPagina)
                ->take($porPagina)
                ->get();

            \Log::info('Tramites loaded for API', [
                'user_id' => $usuario->id,
                'count' => $tramites->count(),
                'modes' => $tramites->pluck('modo_derivacion')->toArray(),
                'nodo_actual' => $tramites->pluck('id_nodo_actual')->toArray(),
                'nodo_puede_finalizar' => $tramites->map(function($t) { 
                    return $t->nodoActual?->puede_finalizar ?? false; 
                })->toArray(),
            ]);

            $tramites->transform(function ($tramite) use ($usuario) {
                $tramite->accionesPermitidas = $this->bandejaInterna->accionesPermitidas($tramite, $usuario);

                return $tramite;
            });

            // Debug: log a sample trámite's full data
            if ($tramites->count() > 0) {
                $sample = $tramites->first();
                \Log::info('Sample trámite data for API response', [
                    'id' => $sample->id,
                    'modo_derivacion' => $sample->modo_derivacion,
                    'id_nodo_actual' => $sample->id_nodo_actual,
                    'nodoActual_loaded' => $sample->relationLoaded('nodoActual'),
                    'nodoActual' => $sample->nodoActual ? [
                        'id' => $sample->nodoActual->id,
                        'nombre' => $sample->nodoActual->nombre,
                        'puede_finalizar' => $sample->nodoActual->puede_finalizar,
                    ] : null,
                    'accionesPermitidas' => $sample->accionesPermitidas ?? [],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'tramites' => $tramites->map(function($t) {
                    // Calcular días transcurridos desde la fecha de creación
                    $diasTranscurridos = 0;
                    if ($t->fecha_creacion) {
                        // Asegurar que fecha_creacion sea una instancia de Carbon
                        $fechaCreacion = \Carbon\Carbon::parse($t->fecha_creacion);
                        // Calcular días desde fecha_creacion hasta ahora
                        $diasTranscurridos = (int) $fechaCreacion->diffInDays(now(), false);
                        // Si es negativo (fecha futura), establecer a 0
                        if ($diasTranscurridos < 0) {
                            $diasTranscurridos = 0;
                        }
                    }
                    
                    return [
                        'id' => $t->id,
                        'procedimiento_nombre' => $t->procedimiento->nombre ?? 'N/A',
                        'estado' => $t->estado,
                        'fecha_creacion' => $t->fecha_creacion ? $t->fecha_creacion->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                        'archivo_adjunto' => $t->archivo_adjunto,
                        'voucher' => $t->voucher,
                        'oficio' => $t->oficio,
                        'observaciones' => $t->observaciones,
                        'requiere_oficio' => $t->requiere_oficio,
                        'numero_voucher' => $t->numero_voucher,
                        'tramitescol' => $t->tramitescol,
                        'accionesPermitidas' => $t->accionesPermitidas ?? [],
                        'dias_transcurridos' => $diasTranscurridos,
                        'id_nodo_actual' => $t->id_nodo_actual,
                        'modo_derivacion' => $t->modo_derivacion,
                        'nodo_puede_finalizar' => (bool) ($t->nodoActual?->puede_finalizar ?? false),
                    ];
                }),
                'total' => $total,
                'paginas' => ceil($total / $porPagina),
                'pagina_actual' => $pagina,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading tramites: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'tramites' => [],
                'total' => 0,
                'paginas' => 0,
            ], 500);
        }
    }
}