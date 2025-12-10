<?php

namespace App\Http\Controllers;

use App\Services\Tramites\TramiteWorkflowService;
use App\Services\Tramite\TramiteCreacionService;
use App\DTOs\TramiteDTO;
use App\Domain\Tramites\EstadoTramite;
use App\Http\Requests\TramiteRequest;
use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Controlador de Trámites
 * 
 * Este controlador maneja todas las operaciones CRUD y acciones
 * específicas de los trámites del sistema de mesa de partes.
 * 
 * @package App\Http\Controllers
 */
class TramiteController extends Controller
{
    private TramiteWorkflowService $tramiteService;
    private TramiteCreacionService $tramiteCreacionService;

    /**
     * Constructor
     * 
     * @param TramiteWorkflowService $tramiteService
     * @param TramiteCreacionService $tramiteCreacionService
     */
    public function __construct(
        TramiteWorkflowService $tramiteService,
        TramiteCreacionService $tramiteCreacionService
    ) {
        $this->tramiteService = $tramiteService;
        $this->tramiteCreacionService = $tramiteCreacionService;
    }

    /**
     * Listar todos los trámites
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Tramite::class);

        $filtros = $request->only(['estado', 'usuario_id', 'procedimiento_id', 'fecha_desde', 'fecha_hasta']);
        
        $tramites = $this->tramiteService->listarTramites($filtros);
        
        return view('tramites.index', [
            'tramites' => $tramites,
            'estados' => EstadoTramite::todos(),
            'filtros' => $filtros,
        ]);
    }

    /**
     * Mostrar formulario de creación
     * 
     * @return View
     */
    public function create(): View
    {
        $this->authorize('create', Tramite::class);

        return view('tramites.create', [
            'estados' => EstadoTramite::todos(),
            'procedimientos' => Procedimiento::activos()->get(),
        ]);
    }

    /**
     * Almacenar nuevo trámite
     * 
     * @param TramiteRequest $request
     * @return JsonResponse
     */
    public function store(TramiteRequest $request): JsonResponse
    {
        $this->authorize('create', Tramite::class);

        try {
            $datos = new TramiteDTO($request->validated());
            $tramite = $this->tramiteService->crear($datos);

            return response()->json([
                'success' => true,
                'message' => 'Trámite creado exitosamente',
                'data' => $tramite,
                'redirect' => route('tramites.show', $tramite->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar trámite específico
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            abort(404, 'Trámite no encontrado');
        }

        $this->authorize('view', $tramite);

        return view('tramites.show', [
            'tramite' => $tramite,
            'estados' => EstadoTramite::todos(),
        ]);
    }

    /**
     * Mostrar formulario de edición
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            abort(404, 'Trámite no encontrado');
        }

        $this->authorize('update', $tramite);

        return view('tramites.edit', [
            'tramite' => $tramite,
            'estados' => EstadoTramite::todos(),
            'procedimientos' => Procedimiento::activos()->get(),
        ]);
    }

    /**
     * Actualizar trámite
     * 
     * @param TramiteRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(TramiteRequest $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('update', $tramite);

        try {
            $datos = new TramiteDTO(array_merge($request->validated(), ['id' => $id]));
            $tramite = $this->tramiteService->actualizar($id, $datos);

            return response()->json([
                'success' => true,
                'message' => 'Trámite actualizado exitosamente',
                'data' => $tramite,
                'redirect' => route('tramites.show', $tramite->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar trámite
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('delete', $tramite);

        try {
            $this->tramiteService->eliminar($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite eliminado exitosamente',
                'redirect' => route('tramites.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Buscar trámites
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function buscar(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tramite::class);

        $termino = $request->input('termino', '');
        $filtros = $request->only(['estado', 'usuario_id', 'procedimiento_id']);

        try {
            $tramites = $this->tramiteService->buscarTramites($termino, $filtros);

            return response()->json([
                'success' => true,
                'data' => $tramites,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar trámites: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aceptar trámite (cambiar a EN_PROCESO)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function aceptar(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('aceptar', $tramite);

        try {
            $tramite = $this->tramiteService->aceptar($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite aceptado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rechazar trámite
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function rechazar(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('rechazar', $tramite);

        $request->validate([
            'motivo' => 'required|string|max:1000',
        ]);

        try {
            $tramite = $this->tramiteService->rechazar($id, $request->input('motivo'));

            return response()->json([
                'success' => true,
                'message' => 'Trámite rechazado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al rechazar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Observar trámite
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function observar(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('observar', $tramite);

        $request->validate([
            'observacion' => 'required|string|max:1000',
        ]);

        try {
            $tramite = $this->tramiteService->observar($id, $request->input('observacion'));

            return response()->json([
                'success' => true,
                'message' => 'Trámite observado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al observar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar trámite
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function verificar(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('verificar', $tramite);

        try {
            $tramite = $this->tramiteService->verificar($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite verificado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Finalizar trámite
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function finalizar(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('finalizar', $tramite);

        try {
            $tramite = $this->tramiteService->finalizar($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite finalizado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de trámites
     * 
     * @return JsonResponse
     */
    public function estadisticas(): JsonResponse
    {
        $this->authorize('viewAny', Tramite::class);

        try {
            $estadisticas = $this->tramiteService->obtenerEstadisticasPorEstado();

            return response()->json([
                'success' => true,
                'data' => $estadisticas,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar formulario para crear nuevo trámite (solo usuarios externos)
     * Equivalente a tramiteNuevo.php del PHP puro
     * 
     * @return View
     */
    public function nuevo(): View
    {
        // Solo usuarios externos pueden crear trámites desde esta ruta
        if (Auth::check() && Auth::user()->tipo_user == 2) {
            abort(403, 'Los usuarios internos no pueden crear trámites desde esta sección.');
        }

        $procedimientos = Procedimiento::orderBy('nombre')->get();

        return view('mesa-partes.tramite-nuevo', [
            'procedimientos' => $procedimientos,
            'tipoTramite' => Auth::check() && Auth::user()->tipo_user == 2 ? 'PRESENCIAL' : 'VIRTUAL',
        ]);
    }

    /**
     * Guardar nuevo trámite
     * Equivalente al guardar del PHP puro
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function guardar(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_procedimiento' => 'required|exists:procedimientos,id',
                'archivo' => 'required|file|mimes:pdf|max:10240', // 10MB máximo
            ]);

            $resultado = $this->tramiteCreacionService->crearTramite([
                'id_procedimiento' => $validated['id_procedimiento'],
            ], $request->file('archivo'));

            if ($resultado['success']) {
                return response()->json([
                    'status' => 'success',
                    'message' => $resultado['message'],
                    'id_tramite' => $resultado['id_tramite'],
                    'redirect' => route('mesa-partes.confirmacion', $resultado['id_tramite']),
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => $resultado['message'],
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mostrar página de confirmación después de crear trámite
     * 
     * @param int $id
     * @return View
     */
    public function confirmacion(int $id): View
    {
        $tramite = Tramite::with(['procedimiento', 'usuario'])->findOrFail($id);

        // Verificar que el trámite pertenece al usuario autenticado
        if (Auth::check() && $tramite->id_usuario != Auth::id()) {
            abort(403, 'No tiene permiso para ver este trámite.');
        }

        return view('mesa-partes.confirmacion', [
            'tramite' => $tramite,
        ]);
    }

    /**
     * Mostrar voucher del trámite
     * Equivalente a voucher.php del PHP puro
     * 
     * @param int $id
     * @return View
     */
    public function voucher(int $id): View
    {
        $tramite = Tramite::with(['procedimiento', 'usuario'])->findOrFail($id);

        // Verificar que el trámite pertenece al usuario autenticado o es usuario interno
        if (Auth::check()) {
            $usuario = Auth::user();
            if ($usuario->tipo_user == 1 && $tramite->id_usuario != $usuario->id) {
                abort(403, 'No tiene permiso para ver este voucher.');
            }
        }

        return view('mesa-partes.voucher', [
            'tramite' => $tramite,
        ]);
    }

    /**
     * Mostrar formulario de rastreo de trámites
     * Equivalente a rastrearTramites.php del PHP puro
     * 
     * @return View
     */
    public function rastrear(): View
    {
        $procedimientos = Procedimiento::orderBy('nombre')->get();
        $tramites = collect(); // Inicializar como colección vacía

        // Para el badge de pendientes en el sidebar (solo para usuarios internos)
        $tramitesPendientes = 0;
        if (Auth::check() && Auth::user()->tipo_user == 2) {
            $tramitesPendientes = Tramite::where('estado', 'PENDIENTE')->count();
        }

        return view('mesa-partes.rastrear', [
            'procedimientos' => $procedimientos,
            'tramites' => $tramites,
            'tramitesPendientes' => $tramitesPendientes,
        ]);
    }

    /**
     * Buscar trámites para rastreo
     * 
     * @param Request $request
     * @return JsonResponse|View
     */
    public function buscarParaRastreo(Request $request)
    {
        $validated = $request->validate([
            'numero_voucher' => 'nullable|string',
            'dni_remitente' => 'nullable|string|size:8',
            'tipo_procedimiento' => 'nullable|exists:procedimientos,id',
            'estado' => 'nullable|in:PENDIENTE,OBSERVADO,EN_PROCESO,VERIFICADO,FINALIZADO,RECHAZADO',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $query = Tramite::with(['procedimiento', 'usuario']);

        // Si hay número de voucher, buscar por ese campo
        if (!empty($validated['numero_voucher'])) {
            $query->where('numero_voucher', 'like', '%' . $validated['numero_voucher'] . '%');
        }

        // Si hay DNI del remitente, buscar por ese campo
        if (!empty($validated['dni_remitente'])) {
            $query->whereHas('usuario', function ($q) use ($validated) {
                $q->where('dni', $validated['dni_remitente']);
            });
        }

        // Filtros adicionales
        if (!empty($validated['tipo_procedimiento'])) {
            $query->where('id_procedimiento', $validated['tipo_procedimiento']);
        }

        if (!empty($validated['estado'])) {
            $query->where('estado', $validated['estado']);
        }

        if (!empty($validated['fecha_desde'])) {
            $query->whereDate('fecha_creacion', '>=', $validated['fecha_desde']);
        }

        if (!empty($validated['fecha_hasta'])) {
            $query->whereDate('fecha_creacion', '<=', $validated['fecha_hasta']);
        }

        // Si es usuario externo, solo mostrar sus propios trámites
        if (Auth::check() && Auth::user()->tipo_user == 1) {
            $query->where('id_usuario', Auth::id());
        }

        $tramites = $query->orderByDesc('fecha_creacion')->get();

        // Formatear datos para la respuesta
        $tramitesFormateados = $tramites->map(function ($tramite) {
            return [
                'id' => $tramite->id,
                'numero_voucher' => $tramite->numero_voucher ?? ($tramite->id . '-' . ($tramite->periodo ?? date('Y'))),
                'procedimiento_nombre' => $tramite->procedimiento->nombre ?? 'N/A',
                'estado' => $tramite->estado,
                'fecha_creacion' => $tramite->fecha_creacion->format('Y-m-d H:i:s'),
                'fecha_formateada' => $tramite->fecha_creacion->format('d/m/Y H:i'),
            ];
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'tramites' => $tramitesFormateados,
            ]);
        }

        return view('mesa-partes.rastrear', [
            'tramites' => $tramites,
            'procedimientos' => Procedimiento::orderBy('nombre')->get(),
            'filtros' => $validated,
        ]);
    }

    /**
     * Obtener hoja de ruta de un trámite
     * Equivalente a hojaderuta.php del PHP puro
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function hojaRuta(int $id): JsonResponse
    {
        try {
            $tramite = Tramite::with(['procedimiento', 'usuario'])->findOrFail($id);
            
            $niveles = [];
            
            if ($tramite->procedimiento) {
                $proc = $tramite->procedimiento;
                
                // Nivel 1 (Área)
                if ($proc->id_area) {
                    $area = Nivel1::find($proc->id_area);
                    if ($area) {
                        $niveles[] = ['nivel' => 1, 'nombre' => $area->nombre];
                    }
                }
                
                // Nivel 2 (Subárea)
                if ($proc->id_subarea) {
                    $subarea = Nivel2::find($proc->id_subarea);
                    if ($subarea) {
                        $niveles[] = ['nivel' => 2, 'nombre' => $subarea->nombre];
                    }
                }
                
                // Nivel 3 (si corresponde)
                if (in_array($proc->nivel_final, ['3', '4'])) {
                    $idNivel3 = null;
                    if ($proc->nivel_final == '3') {
                        $idNivel3 = $proc->id_nivel_final;
                    } elseif ($proc->nivel_final == '4') {
                        $nivel4 = Nivel4::find($proc->id_nivel_final);
                        if ($nivel4 && $nivel4->id_nivel3) {
                            $idNivel3 = $nivel4->id_nivel3;
                        }
                    }
                    
                    if ($idNivel3) {
                        $nivel3 = Nivel3::find($idNivel3);
                        if ($nivel3) {
                            $niveles[] = ['nivel' => 3, 'nombre' => $nivel3->nombre];
                        }
                    }
                }
                
                // Nivel 4 (si corresponde)
                if ($proc->nivel_final == '4') {
                    $nivel4 = Nivel4::find($proc->id_nivel_final);
                    if ($nivel4) {
                        $niveles[] = ['nivel' => 4, 'nombre' => $nivel4->nombre];
                    }
                }
            }
            
            // Obtener información adicional del trámite
            $areaNombre = '';
            $subareaNombre = '';
            $especialidadNombre = '';
            
            if ($tramite->procedimiento) {
                if ($tramite->procedimiento->id_area) {
                    $area = Nivel1::find($tramite->procedimiento->id_area);
                    $areaNombre = $area ? $area->nombre : '';
                }
                if ($tramite->procedimiento->id_subarea) {
                    $subarea = Nivel2::find($tramite->procedimiento->id_subarea);
                    $subareaNombre = $subarea ? $subarea->nombre : '';
                }
                // Especialidad es el nivel final (3 o 4)
                if ($tramite->procedimiento->nivel_final == '3') {
                    $nivel3 = Nivel3::find($tramite->procedimiento->id_nivel_final);
                    $especialidadNombre = $nivel3 ? $nivel3->nombre : '';
                } elseif ($tramite->procedimiento->nivel_final == '4') {
                    $nivel4 = Nivel4::find($tramite->procedimiento->id_nivel_final);
                    $especialidadNombre = $nivel4 ? $nivel4->nombre : '';
                }
            }
            
            return response()->json([
                'success' => true,
                'niveles' => $niveles,
                'tramite' => [
                    'id' => $tramite->id,
                    'fecha_creacion' => $tramite->fecha_creacion->format('d/m/Y H:i:s'),
                    'nombres' => $tramite->usuario->nombres,
                    'apellido_paterno' => $tramite->usuario->apellido_paterno,
                    'apellido_materno' => $tramite->usuario->apellido_materno,
                    'procedimiento_nombre' => $tramite->procedimiento->nombre ?? 'N/A',
                    'area_nombre' => $areaNombre,
                    'subarea_nombre' => $subareaNombre,
                    'especialidad_nombre' => $especialidadNombre,
                    'estado' => $tramite->estado,
                    'tramitescol' => $tramite->tramitescol ?? '1',
                    'apellido_materno' => $tramite->usuario->apellido_materno,
                    'procedimiento_nombre' => $tramite->procedimiento->nombre ?? 'N/A',
                    'estado' => $tramite->estado,
                    'tramitescol' => $tramite->tramitescol ?? 1,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la hoja de ruta: ' . $e->getMessage(),
            ], 500);
        }
    }
}