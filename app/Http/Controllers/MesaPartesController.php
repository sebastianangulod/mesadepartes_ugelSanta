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
            // EXCEPCIÓN: Los administradores pueden ver TODOS los trámites sin restricciones
            if ($usuario->esAdministrador()) {
                // Los administradores ven todos los trámites, sin filtro de nivel
                // No se aplica ningún filtro adicional aquí
            } else {
                // Usuarios internos NO administradores: filtrar según nivel asignado
            $nivelesUsuario = \App\Models\UsuariosNiveles::where('id_usuario', $usuario->id)->get();
            
            if ($nivelesUsuario->count() > 0) {
                    // Obtener los niveles del usuario
                    $nivelesDelUsuario = $nivelesUsuario->pluck('nivel')->unique()->toArray();
                    
                    // Lógica especial por nivel:
                    // - Niveles intermedios (1, 2, 3 cuando no es final): todos los usuarios del mismo nivel/subárea ven todos los trámites
                    // - Nivel final: solo el usuario asignado ve el trámite
                    $query->where(function($q) use ($nivelesDelUsuario, $usuario, $nivelesUsuario) {
                        // Nivel 1: todos los usuarios de la misma área ven todos los trámites de nivel 1
                        if (in_array(1, $nivelesDelUsuario)) {
                            $nivel1Usuario = $nivelesUsuario->where('nivel', 1)->first();
                            if ($nivel1Usuario) {
                                $idAreaUsuario = $nivel1Usuario->id_nivel; // id_nivel para nivel 1 es el ID del área
                                $q->where(function($subQ) use ($idAreaUsuario) {
                                    $subQ->where('nivel_actual', 1)
                                         ->whereHas('procedimiento', function($procQ) use ($idAreaUsuario) {
                                             $procQ->where('id_area', $idAreaUsuario);
                                         });
                                });
                            }
                        }
                        
                        // Nivel 2: si es intermedio (nivel_final != '2'), todos los usuarios de la misma subárea ven todos los trámites
                        if (in_array(2, $nivelesDelUsuario)) {
                            $nivel2Usuario = $nivelesUsuario->where('nivel', 2)->first();
                            if ($nivel2Usuario) {
                                $idSubareaUsuario = $nivel2Usuario->id_nivel; // id_nivel para nivel 2 es el ID de la subárea
                                $q->orWhere(function($subQ) use ($idSubareaUsuario) {
                                    $subQ->where('nivel_actual', 2)
                                         ->whereHas('procedimiento', function($procQ) use ($idSubareaUsuario) {
                                             // Solo si nivel 2 NO es el nivel final (es intermedio)
                                             $procQ->where('id_subarea', $idSubareaUsuario)
                                                   ->where('nivel_final', '!=', '2');
                                         });
                                });
                            }
                        }
                        
                            // Nivel 3: si es intermedio (nivel_final = '4'), todos los usuarios del mismo nivel 3 ven todos los trámites
                            if (in_array(3, $nivelesDelUsuario)) {
                                $nivel3Usuario = $nivelesUsuario->where('nivel', 3)->first();
                                if ($nivel3Usuario) {
                                    $idNivel3Usuario = $nivel3Usuario->id_nivel;
                                    // Obtener la subárea del nivel 3 del usuario
                                    $nivel3Modelo = \App\Models\Nivel3::find($idNivel3Usuario);
                                    if ($nivel3Modelo && $nivel3Modelo->id_nivel2) {
                                        $idSubareaNivel3 = $nivel3Modelo->id_nivel2;
                                        $q->orWhere(function($subQ) use ($idNivel3Usuario, $idSubareaNivel3) {
                                            $subQ->where('nivel_actual', 3)
                                                 ->whereHas('procedimiento', function($procQ) use ($idNivel3Usuario, $idSubareaNivel3) {
                                                     // Solo si nivel 3 NO es el nivel final (es intermedio cuando nivel_final = '4')
                                                     // Y pertenece a la misma subárea
                                                     $procQ->where('nivel_final', '4')
                                                           ->where('id_subarea', $idSubareaNivel3)
                                                           ->where('id_nivel_final', '!=', $idNivel3Usuario); // No es el nivel final específico
                                                 });
                                        });
                                    }
                                }
                            }
                        
                        // Niveles finales: solo el usuario asignado ve el trámite
                        // Esto incluye: nivel 2 cuando es final, nivel 3 cuando es final, nivel 4 siempre
                        $nivelesFinales = [];
                        foreach ($nivelesDelUsuario as $nivel) {
                            // Nivel 2 es final si el procedimiento tiene nivel_final = '2'
                            // Nivel 3 es final si el procedimiento tiene nivel_final = '3'
                            // Nivel 4 siempre es final
                            if ($nivel == 4) {
                                $nivelesFinales[] = 4;
                            } elseif ($nivel == 3) {
                                // Nivel 3 puede ser final o intermedio, verificamos por procedimiento
                                $nivel3Usuario = $nivelesUsuario->where('nivel', 3)->first();
                                if ($nivel3Usuario) {
                                    $idNivel3Usuario = $nivel3Usuario->id_nivel;
                                    // Si el procedimiento tiene nivel_final = '3' y id_nivel_final = id_nivel del usuario, es final
                                    $q->orWhere(function($subQ) use ($idNivel3Usuario, $usuario) {
                                        $subQ->where('nivel_actual', 3)
                                             ->where('id_usuario', $usuario->id)
                                             ->whereHas('procedimiento', function($procQ) use ($idNivel3Usuario) {
                                                 $procQ->where('nivel_final', '3')
                                                       ->where('id_nivel_final', $idNivel3Usuario);
                                             });
                                    });
                                }
                        } elseif ($nivel == 2) {
                                // Nivel 2 puede ser final o intermedio, verificamos por procedimiento
                                $nivel2Usuario = $nivelesUsuario->where('nivel', 2)->first();
                                if ($nivel2Usuario) {
                                    $idNivel2Usuario = $nivel2Usuario->id_nivel;
                                    // Si el procedimiento tiene nivel_final = '2' y id_nivel_final = id_nivel del usuario, es final
                                    $q->orWhere(function($subQ) use ($idNivel2Usuario, $usuario) {
                                        $subQ->where('nivel_actual', 2)
                                             ->where('id_usuario', $usuario->id)
                                             ->whereHas('procedimiento', function($procQ) use ($idNivel2Usuario) {
                                                 $procQ->where('nivel_final', '2')
                                                       ->where('id_nivel_final', $idNivel2Usuario);
                              });
                                    });
                                }
                            }
                        }
                        
                        // Nivel 4 siempre es final: solo el usuario asignado
                        if (in_array(4, $nivelesDelUsuario)) {
                            $q->orWhere(function($subQ) use ($usuario) {
                                $subQ->where('nivel_actual', 4)
                                     ->where('id_usuario', $usuario->id);
                            });
                        }
                    });
                    
                    // Log para depuración (solo en desarrollo)
                    if (config('app.debug')) {
                        \Log::debug('Filtrado de trámites para usuario interno', [
                            'usuario_id' => $usuario->id,
                            'niveles_usuario' => $nivelesDelUsuario,
                            'query_sql' => $query->toSql(),
                            'query_bindings' => $query->getBindings()
                        ]);
                    }
                } else {
                    // Si no tiene niveles asignados, no debería ver ningún trámite por defecto
                    $query->where('id', null); // Retorna un conjunto vacío
                }
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
        
        // Decodificar filtros si viene como JSON string
        $filtrosInput = $request->input('filtros', []);
        if (is_string($filtrosInput)) {
            $filtros = json_decode($filtrosInput, true) ?? [];
        } else {
            $filtros = $filtrosInput;
        }

        try {
            $query = Tramite::with(['procedimiento', 'usuario']);

            // Si es usuario interno, obtener trámites según su nivel asignado
            if ($usuario->tipo_user == 2) {
                // EXCEPCIÓN: Los administradores pueden ver TODOS los trámites sin restricciones
                if ($usuario->esAdministrador()) {
                    // Los administradores ven todos los trámites, sin filtro de nivel
                    // No se aplica ningún filtro adicional aquí
                } else {
                    // Usuarios internos NO administradores: filtrar según nivel asignado
                $nivelesUsuario = \App\Models\UsuariosNiveles::where('id_usuario', $usuario->id)->get();
                
                if ($nivelesUsuario->count() > 0) {
                        // Obtener los niveles del usuario
                        $nivelesDelUsuario = $nivelesUsuario->pluck('nivel')->unique()->toArray();
                        
                        // Lógica especial por nivel:
                        // - Niveles intermedios (1, 2, 3 cuando no es final): todos los usuarios del mismo nivel/subárea ven todos los trámites
                        // - Nivel final: solo el usuario asignado ve el trámite
                        $query->where(function($q) use ($nivelesDelUsuario, $usuario, $nivelesUsuario) {
                            // Nivel 1: todos los usuarios de la misma área ven todos los trámites de nivel 1
                            if (in_array(1, $nivelesDelUsuario)) {
                                $nivel1Usuario = $nivelesUsuario->where('nivel', 1)->first();
                                if ($nivel1Usuario) {
                                    $idAreaUsuario = $nivel1Usuario->id_nivel; // id_nivel para nivel 1 es el ID del área
                                    $q->where(function($subQ) use ($idAreaUsuario) {
                                        $subQ->where('nivel_actual', 1)
                                             ->whereHas('procedimiento', function($procQ) use ($idAreaUsuario) {
                                                 $procQ->where('id_area', $idAreaUsuario);
                                             });
                                    });
                                }
                            }
                            
                            // Nivel 2: si es intermedio (nivel_final != '2'), todos los usuarios de la misma subárea ven todos los trámites
                            if (in_array(2, $nivelesDelUsuario)) {
                                $nivel2Usuario = $nivelesUsuario->where('nivel', 2)->first();
                                if ($nivel2Usuario) {
                                    $idSubareaUsuario = $nivel2Usuario->id_nivel; // id_nivel para nivel 2 es el ID de la subárea
                                    $q->orWhere(function($subQ) use ($idSubareaUsuario) {
                                        $subQ->where('nivel_actual', 2)
                                             ->whereHas('procedimiento', function($procQ) use ($idSubareaUsuario) {
                                                 // Solo si nivel 2 NO es el nivel final (es intermedio)
                                                 $procQ->where('id_subarea', $idSubareaUsuario)
                                                       ->where('nivel_final', '!=', '2');
                                             });
                                    });
                                }
                            }
                            
                            // Nivel 3: si es intermedio (nivel_final = '4'), todos los usuarios del mismo nivel 3 ven todos los trámites
                            if (in_array(3, $nivelesDelUsuario)) {
                                $nivel3Usuario = $nivelesUsuario->where('nivel', 3)->first();
                                if ($nivel3Usuario) {
                                    $idNivel3Usuario = $nivel3Usuario->id_nivel;
                                    // Obtener la subárea del nivel 3 del usuario
                                    $nivel3Modelo = \App\Models\Nivel3::find($idNivel3Usuario);
                                    if ($nivel3Modelo && $nivel3Modelo->id_nivel2) {
                                        $idSubareaNivel3 = $nivel3Modelo->id_nivel2;
                                        $q->orWhere(function($subQ) use ($idNivel3Usuario, $idSubareaNivel3) {
                                            $subQ->where('nivel_actual', 3)
                                                 ->whereHas('procedimiento', function($procQ) use ($idNivel3Usuario, $idSubareaNivel3) {
                                                     // Solo si nivel 3 NO es el nivel final (es intermedio cuando nivel_final = '4')
                                                     // Y pertenece a la misma subárea
                                                     $procQ->where('nivel_final', '4')
                                                           ->where('id_subarea', $idSubareaNivel3)
                                                           ->where('id_nivel_final', '!=', $idNivel3Usuario); // No es el nivel final específico
                                                 });
                                        });
                                    }
                                }
                            }
                            
                            // Niveles finales: solo el usuario asignado ve el trámite
                            // Esto incluye: nivel 2 cuando es final, nivel 3 cuando es final, nivel 4 siempre
                            $nivelesFinales = [];
                            foreach ($nivelesDelUsuario as $nivel) {
                                // Nivel 2 es final si el procedimiento tiene nivel_final = '2'
                                // Nivel 3 es final si el procedimiento tiene nivel_final = '3'
                                // Nivel 4 siempre es final
                                if ($nivel == 4) {
                                    $nivelesFinales[] = 4;
                                } elseif ($nivel == 3) {
                                    // Nivel 3 puede ser final o intermedio, verificamos por procedimiento
                                    $nivel3Usuario = $nivelesUsuario->where('nivel', 3)->first();
                                    if ($nivel3Usuario) {
                                        $idNivel3Usuario = $nivel3Usuario->id_nivel;
                                        // Si el procedimiento tiene nivel_final = '3' y id_nivel_final = id_nivel del usuario, es final
                                        $q->orWhere(function($subQ) use ($idNivel3Usuario, $usuario) {
                                            $subQ->where('nivel_actual', 3)
                                                 ->where('id_usuario', $usuario->id)
                                                 ->whereHas('procedimiento', function($procQ) use ($idNivel3Usuario) {
                                                     $procQ->where('nivel_final', '3')
                                                           ->where('id_nivel_final', $idNivel3Usuario);
                                                 });
                                        });
                                    }
                            } elseif ($nivel == 2) {
                                    // Nivel 2 puede ser final o intermedio, verificamos por procedimiento
                                    $nivel2Usuario = $nivelesUsuario->where('nivel', 2)->first();
                                    if ($nivel2Usuario) {
                                        $idNivel2Usuario = $nivel2Usuario->id_nivel;
                                        // Si el procedimiento tiene nivel_final = '2' y id_nivel_final = id_nivel del usuario, es final
                                        $q->orWhere(function($subQ) use ($idNivel2Usuario, $usuario) {
                                            $subQ->where('nivel_actual', 2)
                                                 ->where('id_usuario', $usuario->id)
                                                 ->whereHas('procedimiento', function($procQ) use ($idNivel2Usuario) {
                                                     $procQ->where('nivel_final', '2')
                                                           ->where('id_nivel_final', $idNivel2Usuario);
                                              });
                                       });
                                    }
                                }
                            }
                            
                            // Nivel 4 siempre es final: solo el usuario asignado
                            if (in_array(4, $nivelesDelUsuario)) {
                                $q->orWhere(function($subQ) use ($usuario) {
                                    $subQ->where('nivel_actual', 4)
                                         ->where('id_usuario', $usuario->id);
                                });
                            }
                        });
                        
                        // Log para depuración (solo en desarrollo)
                        if (config('app.debug')) {
                            \Log::debug('Filtrado de trámites AJAX para usuario interno', [
                                'usuario_id' => $usuario->id,
                                'niveles_usuario' => $nivelesDelUsuario,
                                'query_sql' => $query->toSql(),
                                'query_bindings' => $query->getBindings()
                            ]);
                        }
                    } else {
                        // Si no tiene niveles asignados, no mostrar ningún trámite
                        // (solo administradores pueden ver todos sin niveles asignados)
                        $query->whereRaw('1 = 0'); // Forzar que no devuelva resultados
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
                            // Todos los niveles internos pueden ACEPTAR, RECHAZAR y OBSERVAR en PENDIENTE
                            $acciones[] = 'ACEPTAR';
                            $acciones[] = 'RECHAZAR';
                            $acciones[] = 'OBSERVAR';
                            
                            // Solo niveles intermedios pueden DERIVAR directamente
                            if ($nivelUsuario && $nivelUsuario < $nivelFinalProcedimiento) {
                                $acciones[] = 'DERIVAR';
                            }
                        }
                        break;
                    case 'EN_PROCESO':
                        if ($usuario->tipo_user == 2) {
                            if ($nivelUsuario && $nivelUsuario < $nivelFinalProcedimiento) {
                                // Niveles intermedios (1, 2, 3 si no es final): OBSERVAR, ACEPTAR/DERIVAR, RECHAZAR
                                $acciones[] = 'OBSERVAR';
                                $acciones[] = 'DERIVAR'; // Derivar al siguiente nivel
                                $acciones[] = 'RECHAZAR';
                            } elseif ($nivelUsuario == $nivelFinalProcedimiento) {
                                // Nivel final: opciones definitivas
                            $acciones[] = 'OBSERVAR';
                            $acciones[] = 'OBSERVAR_CON_OFICIO';
                                
                                // PROCESAR solo si no hay oficio requerido
                                if (!$tieneOficio) {
                                    $acciones[] = 'PROCESAR';
                                }
                                
                                // VERIFICAR si hay oficio
                                if ($tieneOficio) {
                                $acciones[] = 'VERIFICAR';
                                }
                                
                                // RECHAZAR definitivo solo para nivel final
                                $acciones[] = 'RECHAZAR';
                            }
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
                        'numero_voucher' => $t->numero_voucher,
                        'tramitescol' => $t->tramitescol,
                        'accionesPermitidas' => $t->accionesPermitidas ?? [],
                        'dias_transcurridos' => $diasTranscurridos,
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