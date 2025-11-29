<?php

namespace App\Http\Controllers;

use App\Services\Tramites\TramiteWorkflowService;
use App\DTOs\TramiteDTO;
use App\Domain\Tramites\EstadoTramite;
use App\Http\Requests\TramiteRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

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

    /**
     * Constructor
     * 
     * @param TramiteWorkflowService $tramiteService
     */
    public function __construct(TramiteWorkflowService $tramiteService)
    {
        $this->tramiteService = $tramiteService;
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
}