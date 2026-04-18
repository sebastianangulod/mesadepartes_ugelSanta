<?php

namespace App\Http\Controllers;

use App\Services\Tramites\TramiteWorkflowService;
use App\Services\Tramites\TramiteFlujoService;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    private TramiteFlujoService $tramiteFlujoService;

    /**
     * Constructor
     * 
     * @param TramiteWorkflowService $tramiteService
     * @param TramiteCreacionService $tramiteCreacionService
     * @param TramiteFlujoService $tramiteFlujoService
     */
    public function __construct(
        TramiteWorkflowService $tramiteService,
        TramiteCreacionService $tramiteCreacionService,
        TramiteFlujoService $tramiteFlujoService
    ) {
        $this->tramiteService = $tramiteService;
        $this->tramiteCreacionService = $tramiteCreacionService;
        $this->tramiteFlujoService = $tramiteFlujoService;
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

        // Cualquier usuario autenticado puede ver el trámite
        // (la autenticación se valida en el middleware 'auth')

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
     * PENDIENTE sin nodo: ingreso al nodo de entrada del procedimiento (flujo configurable).
     */
    public function aceptarIngresoFlujo(Tramite $tramite): JsonResponse
    {
        $this->authorize('view', $tramite);

        try {
            $actualizado = $this->tramiteFlujoService->aceptarIngresoDesdePendienteSinNodo($tramite);

            return response()->json([
                'success' => true,
                'message' => 'Trámite ingresado al flujo correctamente',
                'data' => $actualizado,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Fija el trámite en derivación manual permanente.
     */
    public function forzarModoDerivacionManualFlujo(Tramite $tramite): JsonResponse
    {
        $this->authorize('view', $tramite);

        try {
            $actualizado = $this->tramiteFlujoService->forzarModoDerivacionManual($tramite);

            return response()->json([
                'success' => true,
                'message' => 'Derivación manual activada para este trámite',
                'data' => $actualizado,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
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
     * Mostrar formulario para crear nuevo trámite
     * Equivalente a tramiteNuevo.php del PHP puro
     * 
     * Usuarios externos crean trámites VIRTUALES
     * Usuarios internos crean trámites PRESENCIALES
     * 
     * @return View
     */
    public function nuevo(): View
    {
        $procedimientos = Procedimiento::orderBy('nombre')->get();

        // Determinar tipo de trámite según el tipo de usuario
        // Usuarios internos (tipo_user = 2) crean trámites PRESENCIALES
        // Usuarios externos (tipo_user = 1) crean trámites VIRTUALES
        $tipoTramite = Auth::check() && Auth::user()->tipo_user == 2 ? 'PRESENCIAL' : 'VIRTUAL';

        return view('mesa-partes.tramite-nuevo', [
            'procedimientos' => $procedimientos,
            'tipoTramite' => $tipoTramite,
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
            // Log de depuración completo
            \Log::info('Intentando crear trámite - DEBUG COMPLETO', [
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'content_length' => $request->header('Content-Length'),
                'request_data' => $request->except(['archivo']),
                'all_input' => $request->all(),
                'has_archivo' => $request->hasFile('archivo'),
                'files' => $request->allFiles(),
                'archivo_size' => $request->hasFile('archivo') ? $request->file('archivo')->getSize() : null,
                'archivo_mime' => $request->hasFile('archivo') ? $request->file('archivo')->getMimeType() : null,
                'archivo_name' => $request->hasFile('archivo') ? $request->file('archivo')->getClientOriginalName() : null,
                'php_upload_max_filesize' => ini_get('upload_max_filesize'),
                'php_post_max_size' => ini_get('post_max_size'),
                'php_max_file_uploads' => ini_get('max_file_uploads'),
            ]);

            // Validar primero el procedimiento
            $request->validate([
                'id_procedimiento' => 'required|exists:procedimientos,id',
            ], [
                'id_procedimiento.required' => 'Debe seleccionar un procedimiento',
                'id_procedimiento.exists' => 'El procedimiento seleccionado no existe',
            ]);

            // Verificar archivo antes de validar
            if (!$request->hasFile('archivo')) {
                // Verificar si hay error de carga de archivo
                $uploadError = null;
                if (isset($_FILES['archivo']['error'])) {
                    $errorCode = $_FILES['archivo']['error'];
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por PHP (upload_max_filesize: ' . ini_get('upload_max_filesize') . ')',
                        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo permitido por el formulario',
                        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
                        UPLOAD_ERR_NO_FILE => 'No se seleccionó ningún archivo',
                        UPLOAD_ERR_NO_TMP_DIR => 'Falta el directorio temporal',
                        UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
                        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga del archivo',
                    ];
                    $uploadError = $errorMessages[$errorCode] ?? 'Error desconocido al cargar el archivo (código: ' . $errorCode . ')';
                }
                
                \Log::error('No se recibió archivo en el request', [
                    'all_files' => $request->allFiles(),
                    'files_array' => $_FILES ?? 'No disponible',
                    'upload_error' => $uploadError,
                ]);
                
                $errorMessage = $uploadError ?? 'No se recibió el archivo. Por favor, verifique que el archivo no exceda el tamaño máximo permitido por el servidor.';
                
                // Convertir límites de PHP a bytes para comparación
                $uploadMaxFilesize = $this->convertToBytes(ini_get('upload_max_filesize'));
                $postMaxSize = $this->convertToBytes(ini_get('post_max_size'));
                
                // Obtener tamaño del archivo si está disponible
                $fileSize = isset($_FILES['archivo']['size']) ? $_FILES['archivo']['size'] : null;
                
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage,
                    'errors' => ['archivo' => [$errorMessage]],
                    'debug' => [
                        'php_upload_max_filesize' => ini_get('upload_max_filesize') . ' (' . number_format($uploadMaxFilesize / 1024 / 1024, 2) . ' MB)',
                        'php_post_max_size' => ini_get('post_max_size') . ' (' . number_format($postMaxSize / 1024 / 1024, 2) . ' MB)',
                        'php_max_file_uploads' => ini_get('max_file_uploads'),
                        'has_file' => $request->hasFile('archivo'),
                        'all_files' => array_keys($request->allFiles()),
                        'upload_error_code' => $_FILES['archivo']['error'] ?? null,
                        'file_size' => $fileSize ? number_format($fileSize / 1024 / 1024, 2) . ' MB' : 'No disponible',
                        'suggestion' => 'Por favor, configure php.ini con: upload_max_filesize = 10M y post_max_size = 12M'
                    ]
                ], 422);
            }

            // Validar el archivo
            $validated = $request->validate([
                'archivo' => 'required|file|mimes:pdf|max:10240', // 10MB máximo
            ], [
                'archivo.required' => 'Debe adjuntar un archivo PDF',
                'archivo.file' => 'El archivo adjunto no es válido',
                'archivo.mimes' => 'El archivo debe ser un PDF',
                'archivo.max' => 'El archivo no debe exceder los 10MB',
            ]);
            
            // Agregar id_procedimiento al array validado
            $validated['id_procedimiento'] = $request->input('id_procedimiento');

            \Log::info('Validación exitosa, creando trámite', [
                'id_procedimiento' => $validated['id_procedimiento'],
            ]);

            $resultado = $this->tramiteCreacionService->crearTramite([
                'id_procedimiento' => $validated['id_procedimiento'],
            ], $request->file('archivo'));

            if ($resultado['success']) {
                \Log::info('Trámite creado exitosamente', [
                    'id_tramite' => $resultado['id_tramite'],
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => $resultado['message'],
                    'id_tramite' => $resultado['id_tramite'],
                    'redirect' => route('mesa-partes.confirmacion', $resultado['id_tramite']),
                ]);
            } else {
                \Log::error('Error al crear trámite', [
                    'message' => $resultado['message'],
                ]);
                
                return response()->json([
                    'status' => 'error',
                    'message' => $resultado['message'],
                ], 400);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Error de validación al crear trámite', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['archivo']),
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación. Por favor, revise los campos del formulario.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error inesperado al crear trámite', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
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
        $tramite = Tramite::with(['procedimiento', 'usuario', 'creador'])->findOrFail($id);

        // Cualquier usuario autenticado puede ver la confirmación
        // (la autenticación se valida en el middleware 'auth')

        // Asegurar que fecha_creacion esté establecida
        if (!$tramite->fecha_creacion) {
            $tramite->fecha_creacion = now();
            $tramite->save();
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
        $tramite = Tramite::with(['procedimiento', 'usuario', 'creador'])->findOrFail($id);

        // Cualquier usuario autenticado puede ver el voucher
        // (la autenticación se valida en el middleware 'auth')

        // Obtener niveles de derivación (similar a hojaRuta)
        $nivelesDerivacion = [];
        if ($tramite->procedimiento) {
            $proc = $tramite->procedimiento;
            
            // Nivel 1 (Área)
            if ($proc->id_area) {
                $area = Nivel1::find($proc->id_area);
                if ($area) {
                    $nivelesDerivacion[] = $area->nombre;
                }
            }
            
            // Nivel 2 (Subárea)
            if ($proc->id_subarea) {
                $subarea = Nivel2::find($proc->id_subarea);
                if ($subarea) {
                    $nivelesDerivacion[] = $subarea->nombre;
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
                        $nivelesDerivacion[] = $nivel3->nombre;
                    }
                }
            }
            
            // Nivel 4 (si corresponde)
            if ($proc->nivel_final == '4') {
                $nivel4 = Nivel4::find($proc->id_nivel_final);
                if ($nivel4) {
                    $nivelesDerivacion[] = $nivel4->nombre;
                }
            }
        }

        // Si no hay niveles, mostrar "NINGUNO"
        if (empty($nivelesDerivacion)) {
            $nivelesDerivacion[] = 'NINGUNO';
        }

        return view('mesa-partes.voucher', [
            'tramite' => $tramite,
            'nivelesDerivacion' => $nivelesDerivacion,
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
     * Corregir trámite observado (usuario externo)
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function corregir(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $request->validate([
            'archivo_adjunto' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $tramite = $this->tramiteService->corregir($id, $request->file('archivo_adjunto'));

            return response()->json([
                'success' => true,
                'message' => 'Trámite corregido exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al corregir el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Derivar trámite al siguiente nivel
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function derivar(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        try {
            $tramite = $this->tramiteService->derivar($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite derivado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al derivar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Derivar trámite a otro especialista
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function derivarEspecialista(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        try {
            $tramite = $this->tramiteService->derivarEspecialista($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite derivado al especialista exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al derivar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Observar trámite con oficio adjunto
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function observarConOficio(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $request->validate([
            'observacion' => 'required|string|max:1000',
            'oficio' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $tramite = $this->tramiteService->observarConOficio(
                $id,
                $request->input('observacion'),
                $request->file('oficio')
            );

            return response()->json([
                'success' => true,
                'message' => 'Trámite observado con oficio exitosamente',
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
     * Usuario externo sube oficio de subsanación
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function subirOficioUsuario(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $request->validate([
            'archivo_oficio' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $tramite = $this->tramiteService->subirOficioUsuario($id, $request->file('archivo_oficio'));

            return response()->json([
                'success' => true,
                'message' => 'Oficio subido exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir el oficio: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar trámite (finalizar y generar voucher)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function procesar(int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        try {
            $tramite = $this->tramiteService->procesar($id);

            return response()->json([
                'success' => true,
                'message' => 'Trámite procesado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resultados de búsqueda de trámites
     * 
     * @param Request $request
     * @return JsonResponse|View
     */
    public function resultadosBusqueda(Request $request)
    {
        $this->authorize('viewAny', Tramite::class);

        $termino = $request->input('termino', '');
        $filtros = $request->only(['estado', 'usuario_id', 'procedimiento_id']);

        try {
            $tramites = $this->tramiteService->buscarTramites($termino, $filtros);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $tramites,
                ]);
            }

            return view('tramites.buscar', [
                'tramites' => $tramites,
                'termino' => $termino,
                'filtros' => $filtros,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar trámites: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Agregar documento a un trámite
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function agregarDocumento(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('update', $tramite);

        $request->validate([
            'documento' => 'required|file|mimes:pdf|max:10240',
            'descripcion' => 'nullable|string|max:500',
        ]);

        try {
            // TODO: Implementar lógica para agregar documentos adicionales
            // Por ahora, solo validamos que el trámite existe
            
            return response()->json([
                'success' => true,
                'message' => 'Documento agregado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Eliminar documento de un trámite
     * 
     * @param int $tramiteId
     * @param int $documentoId
     * @return JsonResponse
     */
    public function eliminarDocumento(int $tramiteId, int $documentoId): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tramiteId);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('update', $tramite);

        try {
            // TODO: Implementar lógica para eliminar documentos adicionales
            
            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Agregar comentario a un trámite
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function agregarComentario(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('view', $tramite);

        $request->validate([
            'comentario' => 'required|string|max:1000',
        ]);

        try {
            // TODO: Implementar lógica para agregar comentarios
            // Por ahora, solo validamos que el trámite existe
            
            return response()->json([
                'success' => true,
                'message' => 'Comentario agregado exitosamente',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar comentario: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar trámites pendientes
     * 
     * @param Request $request
     * @return View|JsonResponse
     */
    public function pendientes(Request $request)
    {
        $this->authorize('viewAny', Tramite::class);

        try {
            $tramites = Tramite::with(['usuario', 'procedimiento'])
                ->where('estado', EstadoTramite::PENDIENTE)
                ->orderByDesc('fecha_creacion')
                ->paginate(15);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $tramites,
                ]);
            }

            return view('tramites.pendientes', [
                'tramites' => $tramites,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener trámites pendientes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Listar trámites vencidos
     * 
     * @param Request $request
     * @return View|JsonResponse
     */
    public function vencidos(Request $request)
    {
        $this->authorize('viewAny', Tramite::class);

        try {
            $tramites = Tramite::with(['usuario', 'procedimiento'])
                ->vencidos(30)
                ->orderByDesc('fecha_creacion')
                ->paginate(15);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $tramites,
                ]);
            }

            return view('tramites.vencidos', [
                'tramites' => $tramites,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener trámites vencidos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exportar trámites
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
     */
    public function exportar(Request $request)
    {
        $this->authorize('viewAny', Tramite::class);

        $request->validate([
            'formato' => 'nullable|in:excel,csv,pdf',
            'filtros' => 'nullable|array',
        ]);

        try {
            $formato = $request->input('formato', 'excel');
            $filtros = $request->input('filtros', []);

            // TODO: Implementar exportación de trámites
            // Por ahora, retornamos un mensaje
            
            return response()->json([
                'success' => false,
                'message' => 'Funcionalidad de exportación en desarrollo',
            ], 501);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar trámites: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Anular trámite
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function anular(Request $request, int $id): JsonResponse
    {
        $tramite = $this->tramiteService->obtenerTramiteCompleto($id);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('delete', $tramite);

        $request->validate([
            'motivo' => 'required|string|max:1000',
        ]);

        try {
            // Anular es similar a rechazar pero con un estado especial
            // Por ahora, lo tratamos como rechazo
            $tramite = $this->tramiteService->rechazar($id, $request->input('motivo'));

            return response()->json([
                'success' => true,
                'message' => 'Trámite anulado exitosamente',
                'data' => $tramite,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al anular el trámite: ' . $e->getMessage(),
            ], 500);
        }
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

    /**
     * Convertir tamaño de archivo en formato PHP (ej: "2M", "10M") a bytes
     * 
     * @param string $size
     * @return int
     */
    private function convertToBytes(string $size): int
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
                // no break
            case 'm':
                $size *= 1024;
                // no break
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }

    /**
     * Descargar archivo de un trámite (con validación de permisos)
     * 
     * @param int $id
     * @param string $nombreArchivo
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function descargarArchivo(int $id, string $nombreArchivo)
    {
        $tramite = Tramite::with('procedimiento')->findOrFail($id);

        // Cualquier usuario autenticado puede descargar archivos
        // (la autenticación se valida en el middleware 'auth')

        // Validar que el archivo pertenece al trámite
        $archivoValido = false;
        $rutaArchivo = null;
        $rutaRelativa = null;

        // Verificar archivo adjunto
        if ($tramite->archivo_adjunto) {
            $nombreArchivoBD = basename($tramite->archivo_adjunto);
            if ($nombreArchivoBD === $nombreArchivo || $tramite->archivo_adjunto === $nombreArchivo) {
                // Intentar primero en disco 'public'
                $rutaRelativa = 'uploads/tramites/' . $nombreArchivoBD;
                if (Storage::disk('public')->exists($rutaRelativa)) {
                    $archivoValido = true;
                    $rutaArchivo = Storage::disk('public')->path($rutaRelativa);
                } else {
                    // Intentar en disco 'local' (por si se guardó antes del cambio)
                    if (Storage::disk('local')->exists($rutaRelativa)) {
                        $archivoValido = true;
                        $rutaArchivo = Storage::disk('local')->path($rutaRelativa);
                    }
                }
            }
        }

        // Verificar archivo expediente
        if (!$archivoValido && $tramite->archivo_expediente) {
            $nombreArchivoBD = basename($tramite->archivo_expediente);
            if ($nombreArchivoBD === $nombreArchivo || $tramite->archivo_expediente === $nombreArchivo) {
                $rutaRelativa = 'uploads/tramites/' . $nombreArchivoBD;
                if (Storage::disk('public')->exists($rutaRelativa)) {
                    $archivoValido = true;
                    $rutaArchivo = Storage::disk('public')->path($rutaRelativa);
                } else {
                    $rutaRelativa = 'uploads/expedientes/' . $nombreArchivoBD;
                    if (Storage::disk('public')->exists($rutaRelativa)) {
                        $archivoValido = true;
                        $rutaArchivo = Storage::disk('public')->path($rutaRelativa);
                    }
                }
            }
        }

        // Verificar voucher
        if (!$archivoValido && $tramite->voucher) {
            $nombreArchivoBD = basename($tramite->voucher);
            if ($nombreArchivoBD === $nombreArchivo || $tramite->voucher === $nombreArchivo) {
                // Intentar primero en disco 'public'
                $rutaRelativa = 'uploads/vouchers/' . $nombreArchivoBD;
                if (Storage::disk('public')->exists($rutaRelativa)) {
                    $archivoValido = true;
                    $rutaArchivo = Storage::disk('public')->path($rutaRelativa);
                } else {
                    // Intentar en disco 'local' (por si se guardó antes del cambio)
                    if (Storage::disk('local')->exists($rutaRelativa)) {
                        $archivoValido = true;
                        $rutaArchivo = Storage::disk('local')->path($rutaRelativa);
                    } else {
                        // Intentar en la ruta antigua: storage/app/uploads/vouchers/
                        $rutaAntigua = storage_path('app/uploads/vouchers/' . $nombreArchivoBD);
                        if (file_exists($rutaAntigua)) {
                            $archivoValido = true;
                            $rutaArchivo = $rutaAntigua;
                        }
                    }
                }
            }
        }

        // Verificar archivo_voucher
        if (!$archivoValido && $tramite->archivo_voucher) {
            $nombreArchivoBD = basename($tramite->archivo_voucher);
            if ($nombreArchivoBD === $nombreArchivo || $tramite->archivo_voucher === $nombreArchivo) {
                $rutaRelativa = 'uploads/vouchers/' . $nombreArchivoBD;
                if (Storage::disk('public')->exists($rutaRelativa)) {
                    $archivoValido = true;
                    $rutaArchivo = Storage::disk('public')->path($rutaRelativa);
                }
            }
        }

        // Verificar oficios (buscar en la tabla de oficios si existe)
        if (!$archivoValido) {
            try {
                $oficio = DB::table('oficios')
                    ->where('id_tramite', $id)
                    ->where('archivo', $nombreArchivo)
                    ->first();
                
                if ($oficio) {
                    $rutaRelativa = 'uploads/oficios/' . $nombreArchivo;
                    if (Storage::disk('public')->exists($rutaRelativa)) {
                        $archivoValido = true;
                        $rutaArchivo = Storage::disk('public')->path($rutaRelativa);
                    }
                }
            } catch (\Exception $e) {
                // Si la tabla no existe, continuar sin error
            }
        }

        if (!$archivoValido || !$rutaArchivo) {
            // Log detallado para debugging
            $rutasBuscadas = [];
            if ($tramite->archivo_adjunto) {
                $rutasBuscadas[] = 'public:uploads/tramites/' . basename($tramite->archivo_adjunto);
                $rutasBuscadas[] = 'local:uploads/tramites/' . basename($tramite->archivo_adjunto);
            }
            if ($tramite->voucher) {
                $rutasBuscadas[] = 'public:uploads/vouchers/' . basename($tramite->voucher);
                $rutasBuscadas[] = 'local:uploads/vouchers/' . basename($tramite->voucher);
            }
            
            \Log::error('Archivo no encontrado', [
                'tramite_id' => $id,
                'nombre_archivo' => $nombreArchivo,
                'archivo_adjunto' => $tramite->archivo_adjunto,
                'archivo_expediente' => $tramite->archivo_expediente,
                'voucher' => $tramite->voucher,
                'archivo_voucher' => $tramite->archivo_voucher ?? null,
                'rutas_buscadas' => $rutasBuscadas,
                'storage_public_path' => Storage::disk('public')->path(''),
                'storage_local_path' => Storage::disk('local')->path(''),
            ]);
            abort(404, 'Archivo no encontrado: ' . $nombreArchivo);
        }

        return response()->download($rutaArchivo, $nombreArchivo);
    }
}