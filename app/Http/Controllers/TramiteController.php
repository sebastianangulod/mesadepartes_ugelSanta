<?php

namespace App\Http\Controllers;

use App\Services\Tramites\TramiteWorkflowService;
use App\Services\Tramites\TramiteFlujoService;
use App\Services\Tramite\TramiteCreacionService;
use App\Domain\Tramites\EstadoTramite;
use App\Models\Tramite;
use App\Models\FlujoNodo;
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
     * Trámite gestionado por el motor de nodos (procedimiento con entrada y pendiente sin nodo o ya con nodo).
     */
    private function tramiteUsaMotorNodos(Tramite $tramite): bool
    {
        $tramite->loadMissing('procedimiento');

        if (!$tramite->procedimiento?->id_nodo_entrada) {
            return false;
        }

        if ($tramite->id_nodo_actual) {
            return true;
        }

        return $tramite->estado === EstadoTramite::PENDIENTE && $tramite->id_nodo_actual === null;
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
        $this->authorize('modoManual', $tramite);

        \Log::info('forzarModoDerivacionManualFlujo called', [
            'tramite_id' => $tramite->id,
            'modo_derivacion_before' => $tramite->modo_derivacion,
            'id_nodo_actual' => $tramite->id_nodo_actual,
            'user_id' => auth()->id(),
        ]);

        try {
            $actualizado = $this->tramiteFlujoService->forzarModoDerivacionManual($tramite);

            \Log::info('forzarModoDerivacionManualFlujo success', [
                'tramite_id' => $actualizado->id,
                'modo_derivacion_after' => $actualizado->modo_derivacion,
                'modo_manual_por_usuario' => $actualizado->modo_manual_por_usuario,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Derivación manual activada para este trámite',
                'data' => $actualizado,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error activando modo manual: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Desactiva la derivación manual y vuelve a modo AUTO.
     */
    public function desactivarModoDerivacionManualFlujo(Tramite $tramite): JsonResponse
    {
        $this->authorize('modoAuto', $tramite);

        \Log::info('desactivarModoDerivacionManualFlujo called', [
            'tramite_id' => $tramite->id,
            'modo_derivacion_before' => $tramite->modo_derivacion,
            'id_nodo_actual' => $tramite->id_nodo_actual,
            'user_id' => auth()->id(),
        ]);

        try {
            $actualizado = $this->tramiteFlujoService->desactivarModoDerivacionManual($tramite);

            \Log::info('desactivarModoDerivacionManualFlujo success', [
                'tramite_id' => $actualizado->id,
                'modo_derivacion_after' => $actualizado->modo_derivacion,
                'modo_manual_por_usuario' => $actualizado->modo_manual_por_usuario,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Derivación automática restaurada para este trámite',
                'data' => $actualizado,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error restaurando modo automático: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Verifica si se puede reactivar la derivación automática.
     */
    public function puedeReactivarAuto(Tramite $tramite): JsonResponse
    {
        $puede = $this->tramiteFlujoService->puedeReactivarAuto($tramite);

        return response()->json([
            'success' => true,
            'puede' => $puede,
            'message' => $puede ? null : 'No se puede reactivar: el trámite está fuera de su ruta original o el nodo no tiene derivación automática configurada.',
        ]);
    }

    /**
     * Aceptar trámite (cambiar a EN_PROCESO)
     * 
     * @param int $tramite
     * @return JsonResponse
     */
    public function aceptar(int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('aceptar', $tramite);

        try {
            if ($tramite->estado === EstadoTramite::PENDIENTE && $tramite->id_nodo_actual === null) {
                $tramite = $this->tramiteFlujoService->aceptarIngresoDesdePendienteSinNodo($tramite);

                return response()->json([
                    'success' => true,
                    'message' => 'Trámite ingresado al flujo correctamente',
                    'data' => $tramite,
                ]);
            }

            $tramite = $this->tramiteService->aceptar($tid);

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
     * @param int $tramite
     * @return JsonResponse
     */
    public function rechazar(Request $request, int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
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
            if ($this->tramiteUsaMotorNodos($tramite)) {
                $tramite = $this->tramiteFlujoService->rechazarEnFlujoConfigurable(
                    $tramite,
                    $request->input('motivo'),
                    Auth::user()
                );
            } else {
                $tramite = $this->tramiteService->rechazar($tid, $request->input('motivo'));
            }

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
     * @param int $tramite
     * @return JsonResponse
     */
    public function observar(Request $request, int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
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
            if ($this->tramiteUsaMotorNodos($tramite)) {
                $tramite = $this->tramiteFlujoService->observarEnFlujoConfigurable(
                    $tramite,
                    $request->input('observacion'),
                    Auth::user()
                );
            } else {
                $tramite = $this->tramiteService->observar($tid, $request->input('observacion'));
            }

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
     * @param int $tramite
     * @return JsonResponse
     */
    public function verificar(int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('verificar', $tramite);

        try {
            if ($tramite->id_nodo_actual) {
                $tramite = $this->tramiteFlujoService->verificarEnFlujoConfigurable($tramite, Auth::user());
            } else {
                $tramite = $this->tramiteService->verificar($tid);
            }

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
     * Retroceder un paso en el flujo por nodos (deshace última derivación o ingreso).
     */
    public function retrocederFlujo(Tramite $tramite): JsonResponse
    {
        $this->authorize('view', $tramite);

        $modelo = $this->tramiteService->obtenerTramiteCompleto($tramite->id) ?? $tramite;

        try {
            $actualizado = $this->tramiteFlujoService->retrocederEnFlujoConfigurable($modelo, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'El trámite retrocedió en el flujo correctamente',
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
     * Opciones de derivación (transiciones) para el trámite en su nodo actual.
     */
    public function opcionesDerivacionFlujo(Tramite $tramite): JsonResponse
    {
        $this->authorize('view', $tramite);

        $completo = $this->tramiteService->obtenerTramiteCompleto($tramite->id) ?? $tramite;

        return response()->json([
            'success' => true,
            'modo_derivacion' => $completo->modo_derivacion,
            'transiciones' => $this->tramiteFlujoService->listarOpcionesDerivacionDesdeTramite($completo),
        ]);
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
                'archivo' => 'required|file|mimes:pdf|max:25600', // 25MB maximo
            ], [
                'archivo.required' => 'Debe adjuntar un archivo PDF',
                'archivo.file' => 'El archivo adjunto no es válido',
                'archivo.mimes' => 'El archivo debe ser un PDF',
                'archivo.max' => 'El archivo no debe exceder los 25MB',
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
            'numero_tramite' => 'nullable|integer',
            'numero_voucher' => 'nullable|string',
            'dni_remitente' => 'nullable|string|size:8',
            'tipo_procedimiento' => 'nullable|exists:procedimientos,id',
            'estado' => 'nullable|in:PENDIENTE,OBSERVADO,EN_PROCESO,VERIFICADO,FINALIZADO,RECHAZADO',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $query = Tramite::with(['procedimiento', 'usuario']);

        // Si hay número de trámite (ID), buscar por ese campo
        if (!empty($validated['numero_tramite'])) {
            $query->where('id', $validated['numero_tramite']);
        }

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

        // Si es usuario externo, solo mostrar sus propios trámites (los que creó)
        if (Auth::check() && Auth::user()->tipo_user == 1) {
            $query->where(function ($q) {
                $q->where('creado_por', Auth::id())
                    ->orWhere(function ($q2) {
                        $q2->whereNull('creado_por')->where('id_usuario', Auth::id());
                    });
            });
        }

        $tramites = $query->orderByDesc('fecha_creacion')->get();

        // Formatear datos para la respuesta
        $tramitesFormateados = $tramites->map(function ($tramite) {
            $fecha = $tramite->fecha_creacion;
            return [
                'id' => $tramite->id,
                'numero_voucher' => $tramite->numero_voucher ?? ($tramite->id . '-' . ($tramite->periodo ?? date('Y'))),
                'procedimiento_nombre' => $tramite->procedimiento->nombre ?? 'N/A',
                'estado' => $tramite->estado,
                'fecha_creacion' => $fecha ? $fecha->format('Y-m-d H:i:s') : null,
                'fecha_formateada' => $fecha ? $fecha->format('d/m/Y H:i') : 'N/A',
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
     * @param int $tramite
     * @return JsonResponse
     */
    public function corregir(Request $request, int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $request->validate([
            'archivo_adjunto' => 'required|file|mimes:pdf|max:25600',
        ]);

        try {
            $tramite = $this->tramiteService->corregir($tid, $request->file('archivo_adjunto'));

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
    public function derivar(Request $request, int $tramite): JsonResponse
    {
        $id = $tramite;
        $request->validate([
            'hacia_nodo_id' => 'nullable|integer|exists:flujo_nodos,id',
        ]);

        $tramiteModel = $this->tramiteService->obtenerTramiteCompleto($id);

        if (!$tramiteModel) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        try {
            if ($tramiteModel->id_nodo_actual) {
                $hacia = $request->filled('hacia_nodo_id') ? (int) $request->input('hacia_nodo_id') : null;
                $tramiteModel = $this->tramiteFlujoService->derivarPorGrafo($tramiteModel, Auth::user(), $hacia);
            } else {
                $tramiteModel = $this->tramiteService->derivar($id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trámite derivado exitosamente',
                'data' => $tramiteModel,
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
    public function derivarEspecialista(int $tramite): JsonResponse
    {
        $id = $tramite;
        $tramiteModel = $this->tramiteService->obtenerTramiteCompleto($id);

        if (!$tramiteModel) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        try {
            if ($tramiteModel->id_nodo_actual) {
                $tramiteModel = $this->tramiteFlujoService->derivarAColegaMismoNodo($tramiteModel, Auth::user());
            } else {
                $tramiteModel = $this->tramiteService->derivarEspecialista($id);
            }

            return response()->json([
                'success' => true,
                'message' => 'Trámite derivado al especialista exitosamente',
                'data' => $tramiteModel,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al derivar el trámite: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Derivar trámite a otro Nivel 1 (antes de aceptar)
     * Solo para trámites en estado PENDIENTE con nivel_actual = 1
     * 
     * @param Request $request
     * @param int $tramite
     * @return JsonResponse
     */
    public function derivarNivel1(Request $request, int $tramite): JsonResponse
    {
        $id = $tramite;
        $request->validate([
            'nuevo_nivel1_id' => 'required|integer|exists:nivel_1,id',
        ]);

        $tramiteModel = $this->tramiteService->obtenerTramiteCompleto($id);

        if (!$tramiteModel) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $this->authorize('derivarNivel1', $tramiteModel);

        // Solo permitir en PENDIENTE y nivel 1 (legacy)
        if ($tramiteModel->estado !== 'PENDIENTE' || $tramiteModel->nivel_actual !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede cambiar de Nivel 1 en trámites PENDIENTE en nivel 1',
            ], 422);
        }

        try {
            // Buscar usuario del nuevo Nivel 1
            $nuevoUsuario = \App\Models\UsuariosNiveles::where('nivel', 1)
                ->where('id_nivel', $request->input('nuevo_nivel1_id'))
                ->whereHas('usuario', fn($q) => $q->where('tipo_user', 2))
                ->first();

            if (!$nuevoUsuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay usuario interno asignado al Nivel 1 seleccionado',
                ], 422);
            }

            $tramiteModel->id_usuario = $nuevoUsuario->id_usuario;
            $tramiteModel->save();

            return response()->json([
                'success' => true,
                'message' => 'Trámite reasignado a otro Nivel 1 exitosamente',
                'data' => $tramiteModel->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reasignar Nivel 1: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Observar trámite con oficio (solo motivo, sin adjuntar archivo)
     * El oficio debe ser subido posteriormente por el creador del trámite
     * 
     * @param Request $request
     * @param int $tramite
     * @return JsonResponse
     */
    public function observarConOficio(Request $request, int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $request->validate([
            'observacion' => 'required|string|max:1000',
        ]);

        $this->authorize('observar', $tramite);

        try {
            if ($tramite->id_nodo_actual) {
                $tramite = $this->tramiteFlujoService->observarConOficioEnFlujoConfigurable(
                    $tramite,
                    $request->input('observacion'),
                    Auth::user()
                );
            } else {
                $tramite = $this->tramiteService->observarConOficio(
                    $tid,
                    $request->input('observacion')
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Trámite observado con oficio exitosamente. El creador debe adjuntar el oficio.',
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
     * @param int $tramite
     * @return JsonResponse
     */
    public function subirOficioUsuario(Request $request, int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        $request->validate([
            'archivo_oficio' => 'required|file|mimes:pdf|max:25600',
        ]);

        try {
            $tramite = $this->tramiteService->subirOficioUsuario($tid, $request->file('archivo_oficio'));

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
     * @param int $tramite
     * @return JsonResponse
     */
    public function procesar(int $tramite): JsonResponse
    {
        $tid = $tramite;
        $tramite = $this->tramiteService->obtenerTramiteCompleto($tid);
        
        if (!$tramite) {
            return response()->json([
                'success' => false,
                'message' => 'Trámite no encontrado',
            ], 404);
        }

        try {
            if ($tramite->id_nodo_actual) {
                $this->authorize('finalizar', $tramite);
                $tramite = $this->tramiteFlujoService->procesarEnFlujoConfigurable($tramite, Auth::user());
            } else {
                $tramite = $this->tramiteService->procesar($tid);
            }

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
     * Obtener hoja de ruta dinámica de un trámite
     * Muestra: niveles estáticos del procedimiento + historial real de movimientos
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function hojaRuta(int $id): JsonResponse
    {
        try {
            $tramite = Tramite::with([
                'procedimiento', 
                'usuario', 
                'nodoActual',
                'movimientosFlujo.usuario',
                'movimientosFlujo.nodoDesde',
                'movimientosFlujo.nodoHacia'
            ])->findOrFail($id);
            
            // 1. Niveles estáticos del procedimiento (ruta esperada)
            $nivelesEstaticos = $this->obtenerNivelesEstaticos($tramite);
            
            // 2. Historial real de movimientos (ruta real)
            $historial = $this->obtenerHistorialMovimientos($tramite);
            
            // 3. Posición actual
            $posicionActual = $this->obtenerPosicionActual($tramite);
            
            return response()->json([
                'success' => true,
                'niveles_estaticos' => $nivelesEstaticos,
                'historial' => $historial,
                'posicion_actual' => $posicionActual,
                'tramite' => [
                    'id' => $tramite->id,
                    'fecha_creacion' => $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y H:i:s') : 'N/A',
                    'nombres' => $tramite->usuario->nombres ?? '',
                    'apellido_paterno' => $tramite->usuario->apellido_paterno ?? '',
                    'apellido_materno' => $tramite->usuario->apellido_materno ?? '',
                    'procedimiento_nombre' => $tramite->procedimiento->nombre ?? 'N/A',
                    'estado' => $tramite->estado,
                    'id_nodo_actual' => $tramite->id_nodo_actual,
                    'nodo_actual_nombre' => $tramite->nodoActual->nombre ?? null,
                    'modo_derivacion' => $tramite->modo_derivacion,
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
     * Obtiene los niveles estáticos del procedimiento (ruta esperada)
     */
    private function obtenerNivelesEstaticos(Tramite $tramite): array
    {
        $niveles = [];
        
        if (!$tramite->procedimiento) {
            return $niveles;
        }
        
        $proc = $tramite->procedimiento;
        
        // Nivel 1 (Área)
        if ($proc->id_area) {
            $area = Nivel1::find($proc->id_area);
            if ($area) {
                $niveles[] = [
                    'nivel' => 1, 
                    'nombre' => $area->nombre,
                    'tipo' => 'estatico',
                    'orden' => 1
                ];
            }
        }
        
        // Nivel 2 (Subárea)
        if ($proc->id_subarea) {
            $subarea = Nivel2::find($proc->id_subarea);
            if ($subarea) {
                $niveles[] = [
                    'nivel' => 2, 
                    'nombre' => $subarea->nombre,
                    'tipo' => 'estatico',
                    'orden' => 2
                ];
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
                    $niveles[] = [
                        'nivel' => 3, 
                        'nombre' => $nivel3->nombre,
                        'tipo' => 'estatico',
                        'orden' => 3
                    ];
                }
            }
        }
        
        // Nivel 4 (si corresponde)
        if ($proc->nivel_final == '4') {
            $nivel4 = Nivel4::find($proc->id_nivel_final);
            if ($nivel4) {
                $niveles[] = [
                    'nivel' => 4, 
                    'nombre' => $nivel4->nombre,
                    'tipo' => 'estatico',
                    'orden' => 4
                ];
            }
        }

        // ID del nodo del procedimiento en cada orden: el badge ACTUAL solo
        // se muestra si el trámite está en uno de estos nodos (no en otro
        // procedimiento por derivación manual)
        foreach ($niveles as &$nv) {
            $nv['id_nodo'] = FlujoNodo::where('id_procedimiento', $proc->id)
                ->where('orden', $nv['orden'])
                ->value('id');
        }
        unset($nv);

        return $niveles;
    }
    
    /**
     * Obtiene el historial real de movimientos del trámite
     */
    private function obtenerHistorialMovimientos(Tramite $tramite): array
    {
        $historial = [];

        // Ascendente: arriba la creación, abajo lo más reciente
        $movimientos = $tramite->movimientosFlujo->sortBy('id')->values();

        foreach ($movimientos as $mov) {
            $accionLegible = $this->traducirAccion($mov->accion);
            
            $historial[] = [
                'fecha' => $mov->created_at ? $mov->created_at->format('d/m/Y H:i:s') : 'N/A',
                'accion' => $mov->accion,
                'accion_legible' => $accionLegible,
                'usuario' => $mov->usuario ? [
                    'nombre' => $mov->usuario->nombres . ' ' . $mov->usuario->apellido_paterno,
                    'email' => $mov->usuario->email
                ] : null,
                'nodo_desde' => $mov->nodoDesde ? [
                    'id' => $mov->nodoDesde->id,
                    'nombre' => $mov->nodoDesde->nombre,
                    'orden' => $mov->nodoDesde->orden
                ] : null,
                'nodo_hacia' => $mov->nodoHacia ? [
                    'id' => $mov->nodoHacia->id,
                    'nombre' => $mov->nodoHacia->nombre,
                    'orden' => $mov->nodoHacia->orden
                ] : null,
                'metadata' => $mov->metadata,
            ];
        }
        
        return $historial;
    }
    
    /**
     * Obtiene la posición actual del trámite
     */
    private function obtenerPosicionActual(Tramite $tramite): ?array
    {
        if (!$tramite->id_nodo_actual || !$tramite->nodoActual) {
            return null;
        }
        
        return [
            'nodo_id' => $tramite->nodoActual->id,
            'nodo_nombre' => $tramite->nodoActual->nombre,
            'nodo_orden' => $tramite->nodoActual->orden,
            'puede_finalizar' => (bool) $tramite->nodoActual->puede_finalizar,
            'estado' => $tramite->estado,
            'modo_derivacion' => $tramite->modo_derivacion,
        ];
    }
    
    /**
     * Traduce la acción técnica a texto legible
     */
    private function traducirAccion(string $accion): string
    {
        $map = [
            'ACEPTAR_INGRESO' => 'Ingreso al flujo',
            'DERIVAR_NODO' => 'Derivación',
            'DERIVAR_COLEGA' => 'Reasignación a colega',
            'RETROCEDER_NODO' => 'Retroceso en flujo',
            'RETROCEDER_INGRESO' => 'Retroceso de ingreso',
            'MODO_MANUAL_FORZADO' => 'Activación modo manual',
            'MODO_AUTO_RESTAURADO' => 'Restauración modo automático',
            'OBSERVAR' => 'Observación',
            'OBSERVAR_CON_OFICIO' => 'Observación con oficio',
            'VERIFICAR' => 'Verificación',
            'RECHAZAR' => 'Rechazo',
            'PROCESAR_FINALIZAR' => 'Finalización',
        ];
        
        return $map[$accion] ?? $accion;
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
     * @param int $tramite
     * @param string $nombreArchivo
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    /**
     * Visor de documento dentro del sistema (página con el PDF embebido).
     */
    public function visorDocumento(int $tramite, string $nombreArchivo)
    {
        $tramiteModel = Tramite::with('procedimiento')->findOrFail($tramite);

        $this->authorize('view', $tramiteModel);

        if (!$this->localizarArchivo($tramiteModel, $nombreArchivo)) {
            abort(404, 'Archivo no encontrado: ' . $nombreArchivo);
        }

        return view('mesa-partes.visor', [
            'tramite' => $tramiteModel,
            'nombreArchivo' => $nombreArchivo,
        ]);
    }

    /**
     * Ver documento del trámite dentro del navegador (visor interno).
     * Responde el PDF inline para que se abra en la pestaña del sistema.
     */
    public function verArchivo(int $tramite, string $nombreArchivo)
    {
        $tramite = Tramite::with('procedimiento')->findOrFail($tramite);

        $this->authorize('view', $tramite);

        $rutaArchivo = $this->localizarArchivo($tramite, $nombreArchivo);
        if (!$rutaArchivo) {
            abort(404, 'Archivo no encontrado: ' . $nombreArchivo);
        }

        return response()->file($rutaArchivo, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $nombreArchivo . '"',
        ]);
    }

    /**
     * Localiza el archivo del trámite: valida que pertenezca al trámite y lo
     * busca primero en disco 'public' y luego en 'local' (compatibilidad con
     * archivos guardados antes de unificar el almacenamiento).
     *
     * @return string|null Ruta absoluta o null si no existe
     */
    private function localizarArchivo(Tramite $tramite, string $nombreArchivo): ?string
    {
        // Cada campo valida que el nombre pedido coincida con el registrado;
        // la resolución (ruta con año o legacy por nombre) la hace DocumentStorage.
        $campos = [
            [$tramite->archivo_adjunto, ['tramites']],
            [$tramite->archivo_expediente, ['tramites', 'expedientes']],
            [$tramite->voucher, ['vouchers']],
            [$tramite->archivo_voucher, ['vouchers']],
            [$tramite->oficio, ['oficios']],
        ];

        foreach ($campos as [$guardado, $dirs]) {
            if (!$guardado) {
                continue;
            }
            if (basename($guardado) !== $nombreArchivo && $guardado !== $nombreArchivo) {
                continue;
            }
            $ruta = \App\Support\DocumentStorage::localizar($guardado, $dirs);
            if ($ruta) {
                return $ruta;
            }
        }

        // Compatibilidad: oficios antiguos referenciados en tabla 'oficios' (si existe)
        try {
            $oficio = DB::table('oficios')
                ->where('id_tramite', $tramite->id)
                ->where('archivo', $nombreArchivo)
                ->first();
            if ($oficio) {
                return \App\Support\DocumentStorage::localizar('uploads/oficios/' . $nombreArchivo, ['oficios']);
            }
        } catch (\Throwable) {
            // Si la tabla no existe, continuar sin error
        }

        return null;
    }

    public function descargarArchivo(int $tramite, string $nombreArchivo)
    {
        $tramite = Tramite::with('procedimiento')->findOrFail($tramite);

        $this->authorize('view', $tramite);

        $rutaArchivo = $this->localizarArchivo($tramite, $nombreArchivo);

        if (!$rutaArchivo) {
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
                'tramite_id' => $tramite->id,
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
