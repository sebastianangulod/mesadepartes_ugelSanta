<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\Configuracion\ConfiguracionController;
use App\Http\Controllers\Admin\AreasController;
use App\Http\Controllers\Admin\NivelesController;
use App\Http\Controllers\Admin\ProcedimientosController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\MesaPartesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación públicas
Route::middleware('guest')->group(function () {
    // Login externo
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Login interno
    Route::get('/login-interno', [AuthController::class, 'showLoginInternoForm'])->name('login.interno');
    Route::post('/login-interno', [AuthController::class, 'loginInterno']);
    
    // Registro
    Route::get('/registro', [AuthController::class, 'showRegistroForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'registro']);
    
    // Recuperación de contraseña
    Route::get('/recuperar', [AuthController::class, 'showRecuperarForm'])->name('password.request');
    Route::post('/recuperar', [AuthController::class, 'recuperar']);
    Route::get('/restablecer/{token}', [AuthController::class, 'showRestablecerForm'])->name('password.reset');
    Route::post('/restablecer', [AuthController::class, 'restablecer'])->name('password.update');
    
});
    
// API para selects anidados (departamentos, provincias, distritos) - Públicas
Route::get('/api/departamentos', [AuthController::class, 'obtenerDepartamentos'])->name('api.departamentos');
Route::get('/api/provincias/{departamentoId}', [AuthController::class, 'obtenerProvincias'])->name('api.provincias');
Route::get('/api/distritos/{provinciaId}', [AuthController::class, 'obtenerDistritos'])->name('api.distritos');

// API para validaciones de registro - Públicas (sin middleware guest para permitir peticiones AJAX)
Route::post('/api/validar-dni', [AuthController::class, 'validarDni'])->name('api.validar-dni');
Route::post('/api/validar-ruc', [AuthController::class, 'validarRuc'])->name('api.validar-ruc');
Route::post('/api/validar-email', [AuthController::class, 'validarEmail'])->name('api.validar-email');

// Ruta de prueba de email (solo para desarrollo - eliminar en producción)
Route::get('/test-email/{email?}', function ($email = null) {
    if (!$email) {
        return response()->json([
            'error' => 'Debes proporcionar un email. Ejemplo: /test-email/tu-email@ejemplo.com'
        ], 400);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return response()->json([
            'error' => 'El email proporcionado no es válido.'
        ], 400);
    }

    try {
        \Illuminate\Support\Facades\Mail::raw('Este es un email de prueba del sistema Mesa de Partes UGEL Santa. Si recibes este mensaje, la configuración de correo está funcionando correctamente.', function ($message) use ($email) {
            $message->to($email)
                    ->subject('Prueba de Email - Mesa de Partes UGEL Santa');
        });

        return response()->json([
            'success' => true,
            'message' => 'Email enviado exitosamente a: ' . $email,
            'config' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'details' => [
                'type' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ],
            'config' => [
                'mailer' => config('mail.default'),
                'host' => config('mail.mailers.smtp.host'),
                'port' => config('mail.mailers.smtp.port'),
                'encryption' => config('mail.mailers.smtp.encryption'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ]
        ], 500);
    }
})->name('test.email');

// Logout (accesible solo para usuarios autenticados)
Route::middleware('auth')->post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas públicas de mesa de partes
Route::prefix('mesa-partes')->group(function () {
    // Rastreo de trámites (público)
    Route::get('/rastrear', [TramiteController::class, 'rastrear'])->name('mesa-partes.rastrear');
    Route::post('/rastrear/buscar', [TramiteController::class, 'buscarParaRastreo'])->name('mesa-partes.rastrear.buscar');
    
    // Registro de nuevos trámites (público)
    Route::get('/nuevo', [TramiteController::class, 'nuevo'])->name('mesa-partes.nuevo');
    Route::post('/guardar', [TramiteController::class, 'guardar'])->name('mesa-partes.guardar');
    
    // Confirmación y voucher
    Route::get('/confirmacion/{id}', [TramiteController::class, 'confirmacion'])->name('mesa-partes.confirmacion');
    Route::get('/voucher/{id}', [TramiteController::class, 'voucher'])->name('mesa-partes.voucher');
    Route::get('/hoja-ruta/{id}', [TramiteController::class, 'hojaRuta'])->name('mesa-partes.hoja-ruta');
});

// Grupo de rutas protegidas
Route::middleware(['auth'])->group(function () {
    
    // Rutas resource para trámites
    Route::resource('tramites', TramiteController::class);
    
    // Rutas personalizadas para acciones de trámites
    Route::prefix('tramites')->group(function () {
        
        // Búsqueda de trámites
        Route::get('/buscar', [TramiteController::class, 'buscar'])->name('tramites.buscar');
        Route::post('/buscar/resultados', [TramiteController::class, 'resultadosBusqueda'])->name('tramites.buscar.resultados');
        
            // Acciones de estado
            Route::post('/{tramite}/aceptar', [TramiteController::class, 'aceptar'])->name('tramites.aceptar');
            Route::post('/{tramite}/rechazar', [TramiteController::class, 'rechazar'])->name('tramites.rechazar');
            Route::post('/{tramite}/observar', [TramiteController::class, 'observar'])->name('tramites.observar');
            Route::post('/{tramite}/observar-con-oficio', [TramiteController::class, 'observarConOficio'])->name('tramites.observar-con-oficio');
            Route::post('/{tramite}/verificar', [TramiteController::class, 'verificar'])->name('tramites.verificar');
            Route::post('/{tramite}/finalizar', [TramiteController::class, 'finalizar'])->name('tramites.finalizar');
            Route::post('/{tramite}/procesar', [TramiteController::class, 'procesar'])->name('tramites.procesar');
            Route::post('/{tramite}/anular', [TramiteController::class, 'anular'])->name('tramites.anular');
            
            // Acciones de derivación
            Route::post('/{tramite}/derivar', [TramiteController::class, 'derivar'])->name('tramites.derivar');
            Route::post('/{tramite}/derivar-especialista', [TramiteController::class, 'derivarEspecialista'])->name('tramites.derivar-especialista');
            
            // Acciones de corrección (usuario externo)
            Route::post('/{tramite}/corregir', [TramiteController::class, 'corregir'])->name('tramites.corregir');
            Route::post('/{tramite}/subir-oficio', [TramiteController::class, 'subirOficioUsuario'])->name('tramites.subir-oficio');
        
        // Gestión de documentos
        Route::post('/{tramite}/documentos/agregar', [TramiteController::class, 'agregarDocumento'])->name('tramites.documentos.agregar');
        Route::delete('/{tramite}/documentos/{documento}/eliminar', [TramiteController::class, 'eliminarDocumento'])->name('tramites.documentos.eliminar');
        
        // Comentarios y observaciones
        Route::post('/{tramite}/comentarios/agregar', [TramiteController::class, 'agregarComentario'])->name('tramites.comentarios.agregar');
        
        // Estadísticas
        Route::get('/estadisticas', [TramiteController::class, 'estadisticas'])->name('tramites.estadisticas');
        
        // Trámites pendientes
        Route::get('/pendientes', [TramiteController::class, 'pendientes'])->name('tramites.pendientes');
        Route::get('/vencidos', [TramiteController::class, 'vencidos'])->name('tramites.vencidos');
        
        // Exportar trámites
        Route::get('/exportar', [TramiteController::class, 'exportar'])->name('tramites.exportar');
        
    });
    
    // Rutas de reportes
    Route::prefix('reportes')->group(function () {
        Route::get('/', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/tramites-por-estado', [ReporteController::class, 'tramitesPorEstado'])->name('reportes.tramites-por-estado');
        Route::get('/tramites-por-periodo', [ReporteController::class, 'tramitesPorPeriodo'])->name('reportes.tramites-por-periodo');
        Route::get('/tramites-por-usuario', [ReporteController::class, 'tramitesPorUsuario'])->name('reportes.tramites-por-usuario');
        Route::get('/tramites-por-procedimiento', [ReporteController::class, 'tramitesPorProcedimiento'])->name('reportes.tramites-por-procedimiento');
        Route::get('/tramites-pendientes', [ReporteController::class, 'tramitesPendientes'])->name('reportes.tramites-pendientes');
        Route::get('/tramites-finalizados', [ReporteController::class, 'tramitesFinalizados'])->name('reportes.tramites-finalizados');
        Route::get('/tramites-anulados', [ReporteController::class, 'tramitesAnulados'])->name('reportes.tramites-anulados');
        Route::get('/exportar-excel', [ReporteController::class, 'exportarExcel'])->name('reportes.exportar-excel');
        Route::get('/exportar-pdf', [ReporteController::class, 'exportarPDF'])->name('reportes.exportar-pdf');
    });

    // Rutas de configuración
    Route::prefix('configuracion')->group(function () {
        Route::get('/', [ConfiguracionController::class, 'index'])->name('configuracion.index');
        Route::post('/actualizar', [ConfiguracionController::class, 'actualizarConfiguracion'])->name('configuracion.actualizar');
        Route::get('/correo', [ConfiguracionController::class, 'correo'])->name('configuracion.correo');
        Route::post('/correo/actualizar', [ConfiguracionController::class, 'actualizarCorreo'])->name('configuracion.correo.actualizar');
        Route::get('/notificaciones', [ConfiguracionController::class, 'notificaciones'])->name('configuracion.notificaciones');
        Route::post('/notificaciones/actualizar', [ConfiguracionController::class, 'actualizarNotificaciones'])->name('configuracion.notificaciones.actualizar');
        Route::get('/institucion', [ConfiguracionController::class, 'institucion'])->name('configuracion.institucion');
        Route::post('/institucion/actualizar', [ConfiguracionController::class, 'actualizarInstitucion'])->name('configuracion.institucion.actualizar');
        Route::get('/seguridad', [ConfiguracionController::class, 'seguridad'])->name('configuracion.seguridad');
        Route::post('/seguridad/actualizar', [ConfiguracionController::class, 'actualizarSeguridad'])->name('configuracion.seguridad.actualizar');
        Route::get('/respaldo', [ConfiguracionController::class, 'respaldo'])->name('configuracion.respaldo');
        Route::post('/respaldo/generar', [ConfiguracionController::class, 'generarRespaldo'])->name('configuracion.respaldo.generar');
        Route::post('/respaldo/restaurar', [ConfiguracionController::class, 'restaurarRespaldo'])->name('configuracion.respaldo.restaurar');
        Route::get('/logs', [ConfiguracionController::class, 'logs'])->name('configuracion.logs');
        Route::post('/logs/limpiar', [ConfiguracionController::class, 'limpiarLogs'])->name('configuracion.logs.limpiar');
        Route::get('/informacion', [ConfiguracionController::class, 'informacionSistema'])->name('configuracion.informacion');
        Route::post('/cache/limpiar', [ConfiguracionController::class, 'limpiarCache'])->name('configuracion.cache.limpiar');
    });

    // Rutas de administración
    Route::prefix('admin')->group(function () {
        // Usuarios
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('admin.usuarios');
        Route::post('/usuarios', [UsuariosController::class, 'store'])->name('admin.usuarios.store');
        Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('admin.usuarios.update');
        Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->name('admin.usuarios.destroy');
        Route::get('/usuarios/{id}/obtener', [UsuariosController::class, 'obtenerUsuario'])->name('admin.usuarios.obtener');
        
        // Áreas (Nivel 1)
        Route::get('/areas', [AreasController::class, 'index'])->name('admin.areas');
        Route::post('/areas', [AreasController::class, 'store'])->name('admin.areas.store');
        Route::put('/areas/{id}', [AreasController::class, 'update'])->name('admin.areas.update');
        Route::delete('/areas/{id}', [AreasController::class, 'destroy'])->name('admin.areas.destroy');
        Route::get('/areas/obtener', [AreasController::class, 'obtenerAreas'])->name('admin.areas.obtener');
        
        // Niveles
        Route::get('/niveles', [NivelesController::class, 'index'])->name('admin.niveles');
        Route::post('/niveles', [NivelesController::class, 'store'])->name('admin.niveles.store');
        Route::put('/niveles/{id}', [NivelesController::class, 'update'])->name('admin.niveles.update');
        Route::delete('/niveles/{id}', [NivelesController::class, 'destroy'])->name('admin.niveles.destroy');
        Route::get('/niveles/nivel2/{nivel1Id}', [NivelesController::class, 'obtenerNivel2'])->name('admin.niveles.nivel2');
        Route::get('/niveles/nivel3/{nivel2Id}', [NivelesController::class, 'obtenerNivel3'])->name('admin.niveles.nivel3');
        Route::get('/niveles/nivel4/{nivel3Id}', [NivelesController::class, 'obtenerNivel4'])->name('admin.niveles.nivel4');
        
        // Procedimientos
        Route::get('/procedimientos', [ProcedimientosController::class, 'index'])->name('admin.procedimientos');
        Route::post('/procedimientos', [ProcedimientosController::class, 'store'])->name('admin.procedimientos.store');
        Route::put('/procedimientos/{id}', [ProcedimientosController::class, 'update'])->name('admin.procedimientos.update');
        Route::delete('/procedimientos/{id}', [ProcedimientosController::class, 'destroy'])->name('admin.procedimientos.destroy');
        Route::get('/procedimientos/{id}/obtener', [ProcedimientosController::class, 'obtener'])->name('admin.procedimientos.obtener');
    });

    // Dashboard principal
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');
    
    // Rutas adicionales del sistema
    Route::get('/home', function () {
        return redirect()->route('dashboard');
    })->name('home');
    
    // Rutas API para dashboard (AJAX)
    
    // Rutas internas de mesa de partes (usuarios autenticados)
    Route::prefix('mesa-partes')->group(function () {
        // Dashboard Mesa de Partes (para externos e internos)
        Route::get('/', [MesaPartesController::class, 'index'])->name('mesa-partes.index');

        // Documentos enviados por el usuario autenticado
        Route::get('/documentos-enviados', [MesaPartesController::class, 'documentosEnviados'])
            ->name('mesa-partes.documentos-enviados');

        // Bandeja interna de trámites recibidos (usuarios internos)
        Route::get('/tramites-recibidos', [MesaPartesController::class, 'tramitesRecibidos'])
            ->name('mesa-partes.tramites-recibidos');
        Route::post('/tramites-recibidos/ajax', [MesaPartesController::class, 'obtenerTramitesPorNivelUsuario'])
            ->name('mesa-partes.tramites-recibidos.ajax');
    });
    // Rutas API AJAX para administración
    Route::prefix('api')->group(function () {
        // Niveles dinámicos para selects
        Route::get('/niveles/nivel2/{nivel1Id}', [NivelesController::class, 'obtenerNivel2'])->name('api.niveles.nivel2');
        Route::get('/niveles/nivel3/{nivel2Id}', [NivelesController::class, 'obtenerNivel3'])->name('api.niveles.nivel3');
        Route::get('/niveles/nivel4/{nivel3Id}', [NivelesController::class, 'obtenerNivel4'])->name('api.niveles.nivel4');
        
        // Áreas
        Route::get('/areas', [AreasController::class, 'obtenerAreas'])->name('api.areas');
        
        // Dashboard
        Route::get('/dashboard/estadisticas', function () {
            // Implementar lógica para estadísticas del dashboard
            return response()->json([
                'total_tramites' => 150,
                'pendientes' => 25,
                'en_proceso' => 45,
                'finalizados' => 80
            ]);
        })->name('api.dashboard.estadisticas');
        
        Route::get('/dashboard/tramites-recientes', function () {
            // Implementar lógica para trámites recientes
            return response()->json([
                [
                    'id' => 1,
                    'codigo' => 'MP-2024-001',
                    'procedimiento' => 'Inscripción de Estudiantes',
                    'solicitante' => 'Juan Pérez',
                    'estado' => 'pendiente',
                    'fecha' => '2024-01-15'
                ],
                [
                    'id' => 2,
                    'codigo' => 'MP-2024-002',
                    'procedimiento' => 'Traslado de Estudiantes',
                    'solicitante' => 'María García',
                    'estado' => 'en_proceso',
                    'fecha' => '2024-01-16'
                ]
            ]);
        })->name('api.dashboard.tramites-recientes');
        
        Route::get('/dashboard/tramites-por-mes', function () {
            // Implementar lógica para trámites por mes
            return response()->json([
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                'values' => [25, 30, 35, 28, 40, 45]
            ]);
        })->name('api.dashboard.tramites-por-mes');
        
        Route::get('/dashboard/tramites-por-estado', function () {
            // Implementar lógica para trámites por estado
            return response()->json([
                'labels' => ['Pendientes', 'En Proceso', 'Finalizados', 'Observados', 'Anulados'],
                'values' => [25, 45, 80, 10, 5]
            ]);
        })->name('api.dashboard.tramites-por-estado');
        
        Route::get('/dashboard/tramites-pendientes', function () {
            try {
                $tramites = \App\Models\Tramite::with(['procedimiento'])
                    ->where('estado', \App\Domain\Tramites\EstadoTramite::PENDIENTE)
                    ->orderByDesc('created_at')
                    ->limit(10)
                    ->get()
                    ->map(function ($tramite) {
                        $dias = $tramite->created_at ? $tramite->created_at->diffInDays(now()) : 0;
                        
                        return [
                            'id' => $tramite->id,
                            'codigo' => 'MP-' . str_pad($tramite->id, 6, '0', STR_PAD_LEFT),
                            'procedimiento' => $tramite->procedimiento ? $tramite->procedimiento->nombre : 'Sin procedimiento',
                            'dias' => $dias,
                            'prioridad' => $dias > 5 ? 'Alta' : ($dias > 3 ? 'Media' : 'Baja')
                        ];
                    });
                
                return response()->json($tramites);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al obtener trámites pendientes: ' . $e->getMessage());
                return response()->json([], 500);
            }
        })->name('api.dashboard.tramites-pendientes');
        
        Route::get('/dashboard/alertas', function () {
            try {
                $alertas = [];
                
                // Alertas de trámites vencidos (más de 30 días)
                $tramitesVencidos = \App\Models\Tramite::where('estado', '!=', \App\Domain\Tramites\EstadoTramite::FINALIZADO)
                    ->where('estado', '!=', \App\Domain\Tramites\EstadoTramite::RECHAZADO)
                    ->where('created_at', '<', now()->subDays(30))
                    ->count();
                
                if ($tramitesVencidos > 0) {
                    $alertas[] = [
                        'tipo' => 'danger',
                        'icono' => 'exclamation-triangle',
                        'titulo' => 'Trámites Vencidos',
                        'mensaje' => "Tienes {$tramitesVencidos} trámite(s) vencido(s) que requieren atención inmediata."
                    ];
                }
                
                // Alertas de trámites pendientes por mucho tiempo (más de 15 días)
                $tramitesPendientesLargos = \App\Models\Tramite::where('estado', \App\Domain\Tramites\EstadoTramite::PENDIENTE)
                    ->where('created_at', '<', now()->subDays(15))
                    ->count();
                
                if ($tramitesPendientesLargos > 0) {
                    $alertas[] = [
                        'tipo' => 'warning',
                        'icono' => 'clock',
                        'titulo' => 'Trámites Pendientes',
                        'mensaje' => "Hay {$tramitesPendientesLargos} trámite(s) pendiente(s) por más de 15 días."
                    ];
                }
                
                // Alertas de trámites observados sin respuesta
                $tramitesObservados = \App\Models\Tramite::where('estado', \App\Domain\Tramites\EstadoTramite::OBSERVADO)
                    ->whereNotNull('limite_observacion')
                    ->where('limite_observacion', '<', now())
                    ->count();
                
                if ($tramitesObservados > 0) {
                    $alertas[] = [
                        'tipo' => 'warning',
                        'icono' => 'eye',
                        'titulo' => 'Observaciones Vencidas',
                        'mensaje' => "Hay {$tramitesObservados} trámite(s) observado(s) cuyo plazo de corrección ha vencido."
                    ];
                }
                
                // Si no hay alertas, mostrar mensaje informativo
                if (empty($alertas)) {
                    // Opcional: puedes retornar un array vacío o un mensaje positivo
                    // return response()->json([]);
                }
                
                return response()->json($alertas);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al obtener alertas: ' . $e->getMessage());
                return response()->json([], 500);
            }
        })->name('api.dashboard.alertas');
    });
    
});

// Ruta de prueba (eliminar en producción)
Route::get('/test', function () {
    return response()->json([
        'message' => 'Sistema de Mesa de Partes - Laravel',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString(),
    ]);
});
