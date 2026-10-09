<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TramiteController;
use App\Http\Controllers\Admin\NivelesController;
use App\Http\Controllers\Admin\ProcedimientosController;
use App\Http\Controllers\Admin\FlujoProcedimientoController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\AdminController;
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
    
    // Recuperación de contraseña (vista pública; el POST atiende a invitados
    // y a autenticados —p. ej. el formulario embebido en actualizar-contraseña—)
    Route::get('/recuperar', [AuthController::class, 'showRecuperarForm'])->name('password.request');

});

// Solicitud de recuperación por DNI: fuera del grupo guest para que
// también funcione con sesión iniciada (formulario en perfil)
Route::post('/recuperar', [AuthController::class, 'recuperar']);
// Restablecer con token: también fuera de guest, el link puede abrirse
// con sesión iniciada sin redirigir dentro del sistema
Route::get('/restablecer/{token}', [AuthController::class, 'showRestablecerForm'])->name('password.reset');
Route::post('/restablecer', [AuthController::class, 'restablecer'])->name('password.update');

// Rutas de perfil de usuario (requieren autenticación)
Route::middleware('auth')->group(function () {
    // Actualizar datos personales
    Route::get('/perfil/actualizar-datos', [AuthController::class, 'showActualizarDatosForm'])->name('perfil.actualizar-datos');
    Route::post('/perfil/actualizar-datos', [AuthController::class, 'actualizarDatos'])->name('perfil.actualizar-datos.store');
    
    // Actualizar contraseña
    Route::get('/perfil/actualizar-contraseña', [AuthController::class, 'showActualizarContraseñaForm'])->name('perfil.actualizar-contraseña');
    Route::post('/perfil/actualizar-contraseña', [AuthController::class, 'actualizarContraseña'])->name('perfil.actualizar-contraseña.store');
});
    
// API para selects anidados (departamentos, provincias, distritos) - Públicas
Route::get('/api/departamentos', [AuthController::class, 'obtenerDepartamentos'])->name('api.departamentos');
Route::get('/api/provincias/{departamentoId}', [AuthController::class, 'obtenerProvincias'])->name('api.provincias');
Route::get('/api/distritos/{provinciaId}', [AuthController::class, 'obtenerDistritos'])->name('api.distritos');

// API para validaciones de registro - Públicas (sin middleware guest para permitir peticiones AJAX)
Route::post('/api/validar-dni', [AuthController::class, 'validarDni'])->name('api.validar-dni');
Route::post('/api/validar-ruc', [AuthController::class, 'validarRuc'])->name('api.validar-ruc');
Route::post('/api/validar-email', [AuthController::class, 'validarEmail'])->name('api.validar-email');

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

    // Rutas personalizadas para acciones de trámites
    Route::prefix('tramites')->group(function () {

        Route::post('/{tramite}/flujo/aceptar-ingreso', [TramiteController::class, 'aceptarIngresoFlujo'])->name('tramites.flujo.aceptar-ingreso');
        Route::post('/{tramite}/flujo/modo-manual', [TramiteController::class, 'forzarModoDerivacionManualFlujo'])->name('tramites.flujo.modo-manual');
        Route::post('/{tramite}/flujo/modo-auto', [TramiteController::class, 'desactivarModoDerivacionManualFlujo'])->name('tramites.flujo.modo-auto');
        Route::get('/{tramite}/puede-reactivar-auto', [TramiteController::class, 'puedeReactivarAuto'])->name('tramites.puede-reactivar-auto');
        Route::get('/{tramite}/flujo/derivacion-opciones', [TramiteController::class, 'opcionesDerivacionFlujo'])->name('tramites.flujo.derivacion-opciones');
        Route::post('/{tramite}/flujo/retroceder', [TramiteController::class, 'retrocederFlujo'])->name('tramites.flujo.retroceder');
        
            // Acciones de estado
            Route::post('/{tramite}/aceptar', [TramiteController::class, 'aceptar'])->name('tramites.aceptar');
            Route::post('/{tramite}/rechazar', [TramiteController::class, 'rechazar'])->name('tramites.rechazar');
            Route::post('/{tramite}/observar', [TramiteController::class, 'observar'])->name('tramites.observar');
            Route::post('/{tramite}/observar-con-oficio', [TramiteController::class, 'observarConOficio'])->name('tramites.observar-con-oficio');
            Route::post('/{tramite}/verificar', [TramiteController::class, 'verificar'])->name('tramites.verificar');
            Route::post('/{tramite}/procesar', [TramiteController::class, 'procesar'])->name('tramites.procesar');
            
            // Acciones de derivación
            Route::post('/{tramite}/derivar', [TramiteController::class, 'derivar'])->name('tramites.derivar');
            Route::post('/{tramite}/derivar-especialista', [TramiteController::class, 'derivarEspecialista'])->name('tramites.derivar-especialista');
            
            // Acciones de corrección (usuario externo)
            Route::post('/{tramite}/corregir', [TramiteController::class, 'corregir'])->name('tramites.corregir');
            Route::post('/{tramite}/subir-oficio', [TramiteController::class, 'subirOficioUsuario'])->name('tramites.subir-oficio');

        // Descargar archivo de trámite (protegido)
        Route::get('/{tramite}/archivo/{nombreArchivo}', [TramiteController::class, 'descargarArchivo'])->name('tramites.archivo.descargar');

        // Visor de documento dentro del sistema (protegido)
        Route::get('/{tramite}/visor/{nombreArchivo}', [TramiteController::class, 'visorDocumento'])->name('tramites.archivo.visor');
        // Ver documento en el navegador - visor interno (protegido)
        Route::get('/{tramite}/ver/{nombreArchivo}', [TramiteController::class, 'verArchivo'])->name('tramites.archivo.ver');
        
    });
    
    // Rutas de administración (solo para administradores)
    Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        // Dashboard de administración
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        
        // Usuarios
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('admin.usuarios');
        Route::post('/usuarios', [UsuariosController::class, 'store'])->name('admin.usuarios.store');
        Route::put('/usuarios/{id}', [UsuariosController::class, 'update'])->name('admin.usuarios.update');
        Route::delete('/usuarios/{id}', [UsuariosController::class, 'destroy'])->name('admin.usuarios.destroy');
        Route::get('/usuarios/{id}/obtener', [UsuariosController::class, 'obtenerUsuario'])->name('admin.usuarios.obtener');
        Route::get('/usuarios/{id}/dependencias', [UsuariosController::class, 'getDependencias'])->name('admin.usuarios.dependencias');
        // Niveles múltiples
        Route::get('/usuarios/{id}/niveles', [UsuariosController::class, 'obtenerNivelesAsignados'])->name('admin.usuarios.niveles');
        Route::put('/usuarios/{id}/niveles', [UsuariosController::class, 'asignarNiveles'])->name('admin.usuarios.niveles.asignar');
        // Admin roles
        Route::post('/usuarios/{id}/hacer-admin', [UsuariosController::class, 'hacerAdministrador'])->name('admin.usuarios.hacer-admin');
        Route::post('/usuarios/{id}/quitar-admin', [UsuariosController::class, 'quitarAdministrador'])->name('admin.usuarios.quitar-admin');
        Route::post('/usuarios/{id}/resetear-password', [UsuariosController::class, 'resetearContrasena'])->name('admin.usuarios.resetear-password');
        
        // Niveles (incluye Nivel 1 = áreas; gestión unificada)
        Route::get('/niveles', [NivelesController::class, 'index'])->name('admin.niveles');
        Route::post('/niveles', [NivelesController::class, 'store'])->name('admin.niveles.store');
        Route::put('/niveles/{id}', [NivelesController::class, 'update'])->name('admin.niveles.update');
        Route::delete('/niveles/{id}', [NivelesController::class, 'destroy'])->name('admin.niveles.destroy');
        Route::get('/niveles/nivel1', [NivelesController::class, 'obtenerNivel1'])->name('admin.niveles.nivel1');
        Route::get('/niveles/nivel2/{nivel1Id}', [NivelesController::class, 'obtenerNivel2'])->name('admin.niveles.nivel2');
        Route::get('/niveles/nivel3/{nivel2Id}', [NivelesController::class, 'obtenerNivel3'])->name('admin.niveles.nivel3');
        Route::get('/niveles/nivel4/{nivel3Id}', [NivelesController::class, 'obtenerNivel4'])->name('admin.niveles.nivel4');
        // Obtener todos los niveles sin filtro de padre (para asignación múltiple)
        Route::get('/niveles/nivel2/all', [NivelesController::class, 'obtenerTodosNivel2'])->name('admin.niveles.nivel2.all');
        Route::get('/niveles/nivel3/all', [NivelesController::class, 'obtenerTodosNivel3'])->name('admin.niveles.nivel3.all');
        Route::get('/niveles/nivel4/all', [NivelesController::class, 'obtenerTodosNivel4'])->name('admin.niveles.nivel4.all');
        Route::get('/niveles/{id}/dependencias', [NivelesController::class, 'getDependencias'])->name('admin.niveles.dependencias');
        
        // Procedimientos
        Route::get('/procedimientos', [ProcedimientosController::class, 'index'])->name('admin.procedimientos');
        Route::post('/procedimientos', [ProcedimientosController::class, 'store'])->name('admin.procedimientos.store');
        Route::put('/procedimientos/{id}', [ProcedimientosController::class, 'update'])->name('admin.procedimientos.update');
        Route::delete('/procedimientos/{id}', [ProcedimientosController::class, 'destroy'])->name('admin.procedimientos.destroy');
        Route::get('/procedimientos/{id}/obtener', [ProcedimientosController::class, 'obtener'])->name('admin.procedimientos.obtener');
        Route::get('/procedimientos/{id}/especialistas', [ProcedimientosController::class, 'obtenerEspecialistasDisponibles'])->name('admin.procedimientos.especialistas');
        Route::post('/procedimientos/{id}/asignar-especialistas', [ProcedimientosController::class, 'asignarEspecialistas'])->name('admin.procedimientos.asignar-especialistas');
        Route::get('/procedimientos/{id}/dependencias', [ProcedimientosController::class, 'getDependencias'])->name('admin.procedimientos.dependencias');

        Route::get('/procedimientos/{procedimiento}/flujo', [FlujoProcedimientoController::class, 'edit'])->name('admin.procedimientos.flujo');
        Route::get('/procedimientos/{procedimiento}/flujo/api', [FlujoProcedimientoController::class, 'estructura'])->name('admin.procedimientos.flujo.api');
        Route::post('/procedimientos/{procedimiento}/flujo/nodo-entrada', [FlujoProcedimientoController::class, 'setNodoEntrada'])->name('admin.procedimientos.flujo.nodo-entrada');
        Route::post('/procedimientos/{procedimiento}/flujo/regenerar-jerarquia', [FlujoProcedimientoController::class, 'regenerarDesdeJerarquia'])->name('admin.procedimientos.flujo.regenerar-jerarquia');
    });

    // API pública para nodos globales (accesible para usuarios autenticados en mesa-partes)
    Route::middleware(['auth'])->group(function () {
        Route::get('/flujo/nodos-globales', [FlujoProcedimientoController::class, 'nodosGlobales'])->name('flujo.nodos-globales');
    });
    
    // Rutas adicionales del sistema
    Route::get('/home', function () {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $usuario = Auth::user();
        
        // Redirigir según el tipo de usuario
        if ($usuario->esAdministrador()) {
            return redirect()->route('admin.index');
        } elseif ($usuario->esInterno()) {
            return redirect()->route('mesa-partes.tramites-recibidos');
        } else {
            // Usuario externo
            return redirect()->route('mesa-partes.index');
        }
    })->name('home');
    
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
        // Buscar ciudadano por DNI (solo internos, para trámite presencial)
        Route::get('/ciudadano/{dni}', [AuthController::class, 'buscarCiudadanoPorDni'])->name('api.ciudadano-buscar');
    });
    
});
