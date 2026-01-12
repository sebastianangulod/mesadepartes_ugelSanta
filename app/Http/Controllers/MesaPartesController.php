<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use App\Repositories\TramiteRepository;

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

    public function __construct(TramiteRepository $tramiteRepository)
    {
        $this->tramiteRepository = $tramiteRepository;
    }

    /**
     * Muestra el dashboard de mesa de partes
     */
    public function index()
    {
        $usuario = auth()->user();

        // Estadísticas básicas de los trámites del usuario autenticado
        $queryBase = Tramite::where('id_usuario', $usuario->id);

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
        $documentos = Tramite::where('id_usuario', $usuario->id)
            ->with('procedimiento')
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

        // Construir query base
        $query = Tramite::with(['procedimiento', 'usuario']);

        // Si es usuario interno, obtener trámites según su nivel asignado
        if ($usuario->tipo_user == 2) {
            // Obtener niveles asignados al usuario
            $nivelesUsuario = \App\Models\UsuariosNiveles::where('id_usuario', $usuario->id)->get();
            
            if ($nivelesUsuario->count() > 0) {
                // Obtener procedimientos asignados al usuario o que coincidan con sus niveles
                $procedimientosIds = [];
                
                foreach ($nivelesUsuario as $nivelUsuario) {
                    $nivel = $nivelUsuario->nivel;
                    $idNivel = $nivelUsuario->id_nivel;
                    
                    // Buscar procedimientos que coincidan con el nivel del usuario
                    $procedimientos = Procedimiento::where(function($q) use ($nivel, $idNivel) {
                        if ($nivel == 1) {
                            $q->where('id_area', $idNivel);
                        } elseif ($nivel == 2) {
                            $q->where('id_subarea', $idNivel);
                        } elseif ($nivel == 3) {
                            $q->where('nivel_final', '3')->where('id_nivel_final', $idNivel)
                              ->orWhere('nivel_final', '4')
                              ->whereHas('nivelFinal', function($q2) use ($idNivel) {
                                  $q2->where('id_nivel3', $idNivel);
                              });
                        } elseif ($nivel == 4) {
                            $q->where('nivel_final', '4')->where('id_nivel_final', $idNivel);
                        }
                    })->pluck('id');
                    
                    $procedimientosIds = array_merge($procedimientosIds, $procedimientos->toArray());
                }
                
                // Incluir trámites donde el usuario es responsable O que coincidan con sus niveles
                if (count($procedimientosIds) > 0) {
                    $query->where(function($q) use ($procedimientosIds, $usuario) {
                        $q->whereIn('id_procedimiento', array_unique($procedimientosIds))
                          ->orWhere('id_usuario', $usuario->id);
                    });
                } else {
                    // Si no hay procedimientos, solo mostrar trámites donde es responsable
                    $query->where('id_usuario', $usuario->id);
                }
            } else {
                // Si no tiene niveles asignados, mostrar todos los trámites pendientes
                // (comportamiento por defecto para administradores o usuarios sin asignación específica)
            }
        } else {
            // Usuario externo: solo sus propios trámites
            $query->where('id_usuario', $usuario->id);
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
        $filtros = $request->input('filtros', []);

        try {
            $query = Tramite::with(['procedimiento', 'usuario']);

            // Si es usuario interno, obtener trámites según su nivel asignado
            if ($usuario->tipo_user == 2) {
                $nivelesUsuario = \App\Models\UsuariosNiveles::where('id_usuario', $usuario->id)->get();
                
                if ($nivelesUsuario->count() > 0) {
                    $procedimientosIds = [];
                    
                    foreach ($nivelesUsuario as $nivelUsuario) {
                        $nivel = $nivelUsuario->nivel;
                        $idNivel = $nivelUsuario->id_nivel;
                        
                        $procedimientos = Procedimiento::where(function($q) use ($nivel, $idNivel) {
                            if ($nivel == 1) {
                                $q->where('id_area', $idNivel);
                            } elseif ($nivel == 2) {
                                $q->where('id_subarea', $idNivel);
                            } elseif ($nivel == 3) {
                                $q->where(function($q2) use ($idNivel) {
                                    $q2->where('nivel_final', '3')->where('id_nivel_final', $idNivel)
                                       ->orWhere(function($q3) use ($idNivel) {
                                           $q3->where('nivel_final', '4')
                                              ->whereHas('nivel4', function($q4) use ($idNivel) {
                                                  $q4->where('id_nivel3', $idNivel);
                                              });
                                       });
                                });
                            } elseif ($nivel == 4) {
                                $q->where('nivel_final', '4')->where('id_nivel_final', $idNivel);
                            }
                        })->pluck('id');
                        
                        $procedimientosIds = array_merge($procedimientosIds, $procedimientos->toArray());
                    }
                    
                    // Incluir trámites donde el usuario es responsable O que coincidan con sus niveles
                    // Esto es equivalente a: (procedimientos coinciden) OR (t.id_usuario = usuario.id)
                    if (count($procedimientosIds) > 0) {
                        $query->where(function($q) use ($procedimientosIds, $usuario) {
                            $q->whereIn('id_procedimiento', array_unique($procedimientosIds))
                              ->orWhere('id_usuario', $usuario->id);
                        });
                    } else {
                        // Si no hay procedimientos, solo mostrar trámites donde es responsable
                        $query->where('id_usuario', $usuario->id);
                    }
                }
            } else {
                // Usuario externo: solo sus propios trámites
                $query->where('id_usuario', $usuario->id);
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

            // Agregar acciones permitidas para cada trámite
            $tramites->transform(function($tramite) use ($usuario) {
                $acciones = [];
                $nivelUsuario = $usuario->usuariosNiveles->first()->nivel ?? null;
                $nivelFinalProcedimiento = $tramite->procedimiento->nivel_final ?? null;

                // Lógica de acciones permitidas según estado y nivel (equivalente al PHP puro)
                $tieneOficio = !empty($tramite->oficio);
                
                switch($tramite->estado) {
                    case 'PENDIENTE':
                        if ($usuario->tipo_user == 2) {
                            $acciones[] = 'ACEPTAR';
                            $acciones[] = 'RECHAZAR';
                            $acciones[] = 'DERIVAR';
                        }
                        break;
                    case 'EN_PROCESO':
                        if ($usuario->tipo_user == 2) {
                            if ($nivelUsuario && $nivelUsuario < $nivelFinalProcedimiento) {
                                $acciones[] = 'DERIVAR';
                            }
                            if ($nivelUsuario == $nivelFinalProcedimiento) {
                                $acciones[] = 'DERIVAR_ESPECIALISTA';
                                // PROCESAR solo si no hay oficio requerido
                                if (!$tieneOficio) {
                                    $acciones[] = 'PROCESAR';
                                }
                            }
                            // OBSERVAR disponible para nivel final
                            if ($nivelUsuario == $nivelFinalProcedimiento) {
                                $acciones[] = 'OBSERVAR';
                                $acciones[] = 'OBSERVAR_CON_OFICIO';
                                // VERIFICAR si hay oficio
                                if ($tieneOficio) {
                                    $acciones[] = 'VERIFICAR';
                                }
                            }
                            // RECHAZAR disponible para todos los niveles en EN_PROCESO
                            $acciones[] = 'RECHAZAR';
                        }
                        break;
                    case 'OBSERVADO':
                        if ($usuario->tipo_user == 1) {
                            $acciones[] = 'CORREGIR';
                        } else if ($usuario->tipo_user == 2) {
                            // Usuario interno puede verificar tras subsanación
                            if ($nivelUsuario == $nivelFinalProcedimiento || $nivelUsuario == 2) {
                                $acciones[] = 'VERIFICAR';
                            }
                            $acciones[] = 'OBSERVAR';
                        }
                        break;
                    case 'VERIFICADO':
                        if ($usuario->tipo_user == 2 && $nivelUsuario == $nivelFinalProcedimiento) {
                            $acciones[] = 'PROCESAR';
                            $acciones[] = 'RECHAZAR';
                        }
                        break;
                }

                $tramite->accionesPermitidas = $acciones;
                return $tramite;
            });

            return response()->json([
                'status' => 'success',
                'tramites' => $tramites->map(function($t) {
                    return [
                        'id' => $t->id,
                        'procedimiento_nombre' => $t->procedimiento->nombre ?? 'N/A',
                        'estado' => $t->estado,
                        'fecha_creacion' => $t->fecha_creacion->format('Y-m-d H:i:s'),
                        'archivo_adjunto' => $t->archivo_adjunto,
                        'voucher' => $t->voucher,
                        'oficio' => $t->oficio,
                        'numero_voucher' => $t->numero_voucher,
                        'tramitescol' => $t->tramitescol,
                        'accionesPermitidas' => $t->accionesPermitidas ?? [],
                    ];
                }),
                'total' => $total,
                'paginas' => ceil($total / $porPagina),
                'pagina_actual' => $pagina,
            ]);
        } catch (\Exception $e) {
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