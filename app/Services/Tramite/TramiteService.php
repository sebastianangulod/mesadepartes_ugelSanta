<?php

namespace App\Services\Tramite;

use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Servicio para gestionar la lógica de negocio de trámites
 * 
 * Este servicio maneja:
 * - Creación de trámites con validación de archivos
 * - Procesamiento de trámites con lógica de niveles
 * - Generación de vouchers y expedientes
 * - Gestión de estados y transiciones
 * - Validaciones de permisos según nivel de usuario
 */
class TramiteService
{
    private $uploadPath = 'uploads/tramites/';
    private $voucherPath = 'uploads/vouchers/';
    private $expedientePath = 'uploads/expedientes/';

    /**
     * Crear un nuevo trámite con archivo adjunto
     * 
     * @param array $datos Datos del trámite
     * @param mixed $archivo Archivo PDF a subir
     * @return array Resultado de la operación
     */
    public function crearTramite(array $datos, $archivo = null): array
    {
        DB::beginTransaction();
        
        try {
            // Validar que el usuario esté autenticado
            if (!Auth::check()) {
                throw new Exception("Usuario no autenticado");
            }

            $usuario = Auth::user();
            
            // Determinar tipo de trámite según el tipo de usuario
            $tipoTramite = ($usuario->tipo_user == 2) ? 'PRESENCIAL' : 'VIRTUAL';
            
            // Validar y procesar archivo si existe
            $nombreArchivo = null;
            if ($archivo) {
                $this->validarArchivo($archivo);
                $nombreArchivo = $this->procesarArchivo($archivo, $usuario->id);
            }

            // Obtener el periodo actual
            $periodo = date('Y');

            // Crear el trámite
            $tramite = Tramite::create([
                'archivo_adjunto' => $nombreArchivo,
                'id_procedimiento' => $datos['id_procedimiento'],
                'id_usuario' => $usuario->id,
                'estado' => 'PENDIENTE',
                'tipo_tramite' => $tipoTramite,
                'tramitescol' => $datos['numero_paginas'] ?? 1,
                'periodo' => $periodo,
                'fecha_creacion' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Trámite creado exitosamente',
                'id_tramite' => $tramite->id,
                'numero_tramite' => $tramite->id . '-' . $periodo
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            // Limpiar archivo si se subió pero falló la transacción
            if (isset($nombreArchivo) && Storage::exists($this->uploadPath . $nombreArchivo)) {
                Storage::delete($this->uploadPath . $nombreArchivo);
            }
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesar un trámite (cambiar estado)
     * 
     * @param int $idTramite ID del trámite
     * @param string $accion Acción a realizar
     * @param array $datosAdicionales Datos adicionales para la acción
     * @return array Resultado de la operación
     */
    public function procesarTramite(int $idTramite, string $accion, array $datosAdicionales = []): array
    {
        DB::beginTransaction();
        
        try {
            $tramite = Tramite::findOrFail($idTramite);
            $usuario = Auth::user();
            
            // Validar permisos y transiciones de estado
            if (!$this->validarTransicionEstado($tramite, $accion, $usuario)) {
                throw new Exception("Transición de estado no permitida");
            }

            // Procesar según la acción
            switch ($accion) {
                case 'ACEPTAR':
                    $this->aceptarTramite($tramite, $usuario);
                    break;
                    
                case 'RECHAZAR':
                    $this->rechazarTramite($tramite, $usuario, $datosAdicionales['observaciones'] ?? '');
                    break;
                    
                case 'OBSERVAR':
                    $this->observarTramite($tramite, $usuario, $datosAdicionales['observaciones'] ?? '');
                    break;
                    
                case 'CORREGIR':
                    $this->corregirTramite($tramite, $usuario, $datosAdicionales);
                    break;
                    
                case 'DERIVAR':
                    $this->derivarTramite($tramite, $usuario);
                    break;
                    
                case 'DERIVAR_ESPECIALISTA':
                    $this->derivarAEspecialista($tramite, $usuario);
                    break;
                    
                case 'VERIFICAR':
                    $this->verificarTramite($tramite, $usuario);
                    break;
                    
                case 'PROCESAR':
                    $this->procesarTramiteFinal($tramite, $usuario);
                    break;
                    
                case 'SUBIR_OFICIO_USUARIO':
                    $this->subirOficioUsuario($tramite, $usuario, $datosAdicionales['archivo_oficio'] ?? null);
                    break;
                    
                case 'OBSERVAR_CON_OFICIO':
                    $this->observarConOficio($tramite, $usuario, $datosAdicionales);
                    break;
                    
                default:
                    throw new Exception("Acción no reconocida: {$accion}");
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Trámite procesado exitosamente",
                'estado_nuevo' => $tramite->estado,
                'id_tramite' => $tramite->id
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener trámites por usuario (sus propios trámites)
     * 
     * @param int $userId ID del usuario
     * @param int $pagina Número de página
     * @param int $porPagina Registros por página
     * @param array $filtros Filtros adicionales
     * @return array Trámites del usuario
     */
    public function obtenerTramitesPorUsuario(int $userId, int $pagina = 1, int $porPagina = 10, array $filtros = []): array
    {
        $query = Tramite::with(['procedimiento', 'usuario'])
            ->where('id_usuario', $userId);

        // Aplicar filtros
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $query->where('fecha_creacion', '>=', $filtros['fecha_desde']);
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $query->where('fecha_creacion', '<=', $filtros['fecha_hasta']);
        }

        $total = $query->count();
        $tramites = $query->orderBy('fecha_creacion', 'desc')
            ->offset(($pagina - 1) * $porPagina)
            ->limit($porPagina)
            ->get();

        // Agregar acciones permitidas para cada trámite
        foreach ($tramites as $tramite) {
            $tramite->accionesPermitidas = $this->obtenerAccionesPermitidasUsuario($tramite, $userId);
        }

        return [
            'tramites' => $tramites,
            'total' => $total,
            'paginas' => ceil($total / $porPagina),
            'pagina_actual' => $pagina
        ];
    }

    /**
     * Obtener trámites por nivel de usuario (para usuarios internos)
     * 
     * @param int $userId ID del usuario interno
     * @param int $pagina Número de página
     * @param int $porPagina Registros por página
     * @param array $filtros Filtros adicionales
     * @return array Trámites accesibles por nivel
     */
    public function obtenerTramitesPorNivel(int $userId, int $pagina = 1, int $porPagina = 10, array $filtros = []): array
    {
        $usuario = Usuario::findOrFail($userId);
        $nivelesUsuario = $usuario->niveles()->get();

        if ($nivelesUsuario->isEmpty()) {
            return [
                'tramites' => [],
                'total' => 0,
                'paginas' => 0,
                'pagina_actual' => $pagina
            ];
        }

        // Construir consulta con joins para filtrar por niveles
        $query = Tramite::with(['procedimiento', 'usuario'])
            ->join('procedimientos as p', 'tramites.id_procedimiento', '=', 'p.id')
            ->where(function($q) use ($nivelesUsuario, $userId) {
                // Incluir trámites donde el usuario es responsable
                $q->where('tramites.id_usuario', $userId);
                
                // Incluir trámites accesibles por nivel
                foreach ($nivelesUsuario as $nivel) {
                    $q->orWhere(function($subQ) use ($nivel) {
                        if ($nivel->nivel == 1) {
                            // Nivel 1: acceso por área
                            $subQ->where('p.id_area', $nivel->id_nivel);
                        } elseif (in_array($nivel->nivel, [2, 3, 4])) {
                            // Niveles 2-4: acceso por nivel final e id_nivel_final
                            $subQ->where('p.nivel_final', $nivel->nivel)
                                 ->where('p.id_nivel_final', $nivel->id_nivel);
                        }
                    });
                }
            });

        // Aplicar filtros adicionales
        if (!empty($filtros['estado'])) {
            $query->where('tramites.estado', $filtros['estado']);
        }
        
        if (!empty($filtros['fecha_desde'])) {
            $query->where('tramites.fecha_creacion', '>=', $filtros['fecha_desde']);
        }
        
        if (!empty($filtros['fecha_hasta'])) {
            $query->where('tramites.fecha_creacion', '<=', $filtros['fecha_hasta']);
        }

        $total = $query->distinct()->count('tramites.id');
        
        $tramites = $query->select('tramites.*')
            ->distinct()
            ->orderBy('tramites.fecha_creacion', 'desc')
            ->offset(($pagina - 1) * $porPagina)
            ->limit($porPagina)
            ->get();

        // Agregar acciones permitidas para cada trámite
        foreach ($tramites as $tramite) {
            $tramite->accionesPermitidas = $this->obtenerAccionesPermitidasNivel($tramite, $nivelesUsuario);
        }

        return [
            'tramites' => $tramites,
            'total' => $total,
            'paginas' => ceil($total / $porPagina),
            'pagina_actual' => $pagina
        ];
    }

    /**
     * Generar voucher PDF para un trámite finalizado
     * 
     * @param Tramite $tramite Trámite a procesar
     * @return string Ruta del voucher generado
     */
    public function generarVoucher(Tramite $tramite): string
    {
        // Verificar que el trámite esté finalizado
        if ($tramite->estado !== 'FINALIZADO') {
            throw new Exception("Solo se pueden generar vouchers para trámites finalizados");
        }

        // Generar número de voucher si no existe
        if (!$tramite->numero_voucher) {
            $tramite->numero_voucher = $this->generarNumeroVoucher($tramite->tipo_tramite);
            $tramite->save();
        }

        // Crear directorio si no existe
        if (!Storage::exists($this->voucherPath)) {
            Storage::makeDirectory($this->voucherPath);
        }

        $nombreVoucher = 'voucher_' . $tramite->id . '_' . time() . '.pdf';
        $rutaVoucher = $this->voucherPath . $nombreVoucher;

        // Aquí iría la lógica para generar el PDF del voucher
        // Por ahora, creamos un archivo temporal
        Storage::put($rutaVoucher, 'Contenido del voucher PDF');

        // Actualizar trámite con la ruta del voucher
        $tramite->voucher = $rutaVoucher;
        $tramite->save();

        return $rutaVoucher;
    }

    /**
     * Generar expediente interno PDF
     * 
     * @param Tramite $tramite Trámite a procesar
     * @return string Ruta del expediente generado
     */
    public function generarExpediente(Tramite $tramite): string
    {
        // Crear directorio si no existe
        if (!Storage::exists($this->expedientePath)) {
            Storage::makeDirectory($this->expedientePath);
        }

        $nombreExpediente = 'expediente_' . $tramite->id . '_' . time() . '.pdf';
        $rutaExpediente = $this->expedientePath . $nombreExpediente;

        // Aquí iría la lógica para generar el PDF del expediente
        // Por ahora, creamos un archivo temporal
        Storage::put($rutaExpediente, 'Contenido del expediente PDF');

        return $rutaExpediente;
    }

    /**
     * Buscar trámites por criterios
     * 
     * @param array $criterios Criterios de búsqueda
     * @return array Resultados de la búsqueda
     */
    public function buscarTramites(array $criterios): array
    {
        $query = Tramite::with(['procedimiento', 'usuario']);

        // Buscar por número de trámite
        if (!empty($criterios['numero_tramite'])) {
            $query->where('id', 'like', '%' . $criterios['numero_tramite'] . '%');
        }

        // Buscar por DNI del solicitante
        if (!empty($criterios['dni'])) {
            $query->whereHas('usuario', function($q) use ($criterios) {
                $q->where('dni', 'like', '%' . $criterios['dni'] . '%');
            });
        }

        // Buscar por razón social
        if (!empty($criterios['razon_social'])) {
            $query->whereHas('usuario', function($q) use ($criterios) {
                $q->where('razon_social', 'like', '%' . $criterios['razon_social'] . '%');
            });
        }

        // Buscar por estado
        if (!empty($criterios['estado'])) {
            $query->where('estado', $criterios['estado']);
        }

        // Buscar por rango de fechas
        if (!empty($criterios['fecha_desde'])) {
            $query->where('fecha_creacion', '>=', $criterios['fecha_desde']);
        }

        if (!empty($criterios['fecha_hasta'])) {
            $query->where('fecha_creacion', '<=', $criterios['fecha_hasta']);
        }

        $tramites = $query->orderBy('fecha_creacion', 'desc')
            ->limit(50)
            ->get();

        return [
            'success' => true,
            'tramites' => $tramites,
            'total' => $tramites->count()
        ];
    }

    /**
     * Validar archivo PDF
     * 
     * @param mixed $archivo Archivo a validar
     * @throws Exception Si el archivo no es válido
     */
    private function validarArchivo($archivo): void
    {
        if (!$archivo || !$archivo->isValid()) {
            throw new Exception("Archivo inválido o no proporcionado");
        }

        // Validar tipo MIME
        if ($archivo->getMimeType() !== 'application/pdf') {
            throw new Exception("El archivo debe ser un PDF");
        }

        // Validar extensión
        if ($archivo->getClientOriginalExtension() !== 'pdf') {
            throw new Exception("El archivo debe tener extensión .pdf");
        }

        // Validar tamaño (máximo 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($archivo->getSize() > $maxSize) {
            throw new Exception("El archivo no debe superar los 10MB");
        }
    }

    /**
     * Procesar y guardar archivo
     * 
     * @param mixed $archivo Archivo a procesar
     * @param int $userId ID del usuario
     * @return string Nombre del archivo guardado
     */
    private function procesarArchivo($archivo, int $userId): string
    {
        // Sanitizar nombre del archivo
        $nombreOriginal = pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $archivo->getClientOriginalExtension();
        $nombreSanitizado = $this->sanitizarNombreArchivo($nombreOriginal);
        $nombreFinal = time() . '_' . $userId . '_' . $nombreSanitizado . '.' . $extension;

        // Guardar archivo
        $archivo->storeAs($this->uploadPath, $nombreFinal);

        return $nombreFinal;
    }

    /**
     * Sanitizar nombre de archivo
     * 
     * @param string $nombreOriginal Nombre original del archivo
     * @return string Nombre sanitizado
     */
    private function sanitizarNombreArchivo(string $nombreOriginal): string
    {
        // Eliminar caracteres especiales y espacios
        $nombre = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombreOriginal);
        // Reemplazar espacios por guiones bajos
        $nombre = str_replace(' ', '_', $nombre);
        // Convertir a minúsculas
        $nombre = strtolower($nombre);
        // Limitar longitud
        $nombre = substr($nombre, 0, 100);
        
        return $nombre;
    }

    /**
     * Validar transición de estado
     * 
     * @param Tramite $tramite Trámite a validar
     * @param string $accion Acción propuesta
     * @param mixed $usuario Usuario que realiza la acción
     * @return bool Si la transición es válida
     */
    private function validarTransicionEstado(Tramite $tramite, string $accion, $usuario): bool
    {
        // Si está rechazado, no se puede hacer nada más
        if ($tramite->estado === 'RECHAZADO') {
            return false;
        }

        // Usuario externo: solo puede corregir si está observado
        if ($usuario->tipo_user == 1) {
            return $tramite->estado === 'OBSERVADO' && $accion === 'CORREGIR';
        }

        // Obtener niveles del usuario interno
        $nivelesUsuario = $usuario->niveles()->get();
        if ($nivelesUsuario->isEmpty()) {
            return false;
        }

        $nivelUsuario = $nivelesUsuario->first()->nivel;
        $procedimiento = $tramite->procedimiento;

        // Definir transiciones válidas
        $transicionesValidas = [
            'PENDIENTE' => ['ACEPTAR', 'RECHAZAR'],
            'EN_PROCESO' => ['OBSERVAR', 'DERIVAR', 'DERIVAR_ESPECIALISTA', 'PROCESAR'],
            'VERIFICADO' => ['PROCESAR', 'RECHAZAR'],
            'OBSERVADO' => ['CORREGIR', 'VERIFICAR']
        ];

        // Verificar si la transición es válida
        if (!isset($transicionesValidas[$tramite->estado]) || 
            !in_array($accion, $transicionesValidas[$tramite->estado])) {
            return false;
        }

        // Validar permisos según nivel
        return $this->validarPermisosNivel($tramite, $accion, $nivelUsuario, $procedimiento);
    }

    /**
     * Validar permisos según nivel de usuario
     * 
     * @param Tramite $tramite Trámite a procesar
     * @param string $accion Acción a realizar
     * @param int $nivelUsuario Nivel del usuario
     * @param mixed $procedimiento Procedimiento del trámite
     * @return bool Si tiene permisos
     */
    private function validarPermisosNivel(Tramite $tramite, string $accion, int $nivelUsuario, $procedimiento): bool
    {
        $nivelFinal = $procedimiento->nivel_final;

        switch ($accion) {
            case 'ACEPTAR':
            case 'RECHAZAR':
                // Nivel 1 (Mesa de Partes) puede aceptar/rechazar
                return $nivelUsuario == 1;
                
            case 'OBSERVAR':
                // El especialista puede observar
                return $nivelUsuario == $nivelFinal;
                
            case 'DERIVAR':
                // Puede derivar si no está en el nivel final
                return $nivelUsuario < $nivelFinal;
                
            case 'DERIVAR_ESPECIALISTA':
                // Nivel 3 puede derivar al especialista (nivel 4)
                return $nivelUsuario == 3 && $nivelFinal == 4;
                
            case 'VERIFICAR':
                // El especialista puede verificar
                return $nivelUsuario == $nivelFinal;
                
            case 'PROCESAR':
                // El especialista puede procesar
                return $nivelUsuario == $nivelFinal;
                
            default:
                return false;
        }
    }

    /**
     * Aceptar trámite (cambiar a EN_PROCESO)
     * 
     * @param Tramite $tramite Trámite a aceptar
     * @param mixed $usuario Usuario que acepta
     */
    private function aceptarTramite(Tramite $tramite, $usuario): void
    {
        $procedimiento = $tramite->procedimiento;
        
        if ($procedimiento->nivel_final == 2) {
            // Si el nivel final es 2, pasar directamente a VERIFICADO
            $tramite->estado = 'VERIFICADO';
        } else {
            // Buscar especialista asignado
            $especialistaAsignado = $this->obtenerEspecialistaAsignado($procedimiento->id, $procedimiento->nivel_final);
            
            if ($especialistaAsignado) {
                $tramite->id_usuario = $especialistaAsignado;
                $tramite->estado = 'EN_PROCESO';
            } else {
                $tramite->estado = 'EN_PROCESO';
            }
        }
        
        $tramite->save();
    }

    /**
     * Rechazar trámite
     * 
     * @param Tramite $tramite Trámite a rechazar
     * @param mixed $usuario Usuario que rechaza
     * @param string $observaciones Observaciones del rechazo
     */
    private function rechazarTramite(Tramite $tramite, $usuario, string $observaciones): void
    {
        $nivelUsuario = $usuario->niveles()->first()->nivel ?? null;
        $nivelFinal = $tramite->procedimiento->nivel_final;
        
        if ($nivelUsuario == $nivelFinal) {
            // Especialista rechaza definitivamente
            $tramite->estado = 'RECHAZADO';
            $tramite->observaciones = 'RECHAZADO POR ESPECIALISTA: ' . $observaciones;
            $tramite->rechazado_por = $usuario->id;
            $tramite->fecha_rechazo = now();
        } else {
            // Observación temporal para corrección
            $tramite->estado = 'OBSERVADO';
            $tramite->ultimo_estado = $tramite->estado;
            $tramite->observaciones = $observaciones;
            $tramite->limite_observacion = now()->addDays(2);
        }
        
        $tramite->save();
    }

    /**
     * Observar trámite
     * 
     * @param Tramite $tramite Trámite a observar
     * @param mixed $usuario Usuario que observa
     * @param string $observaciones Observaciones
     */
    private function observarTramite(Tramite $tramite, $usuario, string $observaciones): void
    {
        $tramite->estado = 'OBSERVADO';
        $tramite->ultimo_estado = $tramite->estado;
        $tramite->observaciones = $observaciones;
        $tramite->limite_observacion = now()->addDays(2);
        $tramite->save();
    }

    /**
     * Corregir trámite observado
     * 
     * @param Tramite $tramite Trámite a corregir
     * @param mixed $usuario Usuario que corrige
     * @param array $datos Datos de corrección
     */
    private function corregirTramite(Tramite $tramite, $usuario, array $datos): void
    {
        // Validar archivo adjunto
        if (empty($datos['archivo_adjunto'])) {
            throw new Exception("Debe adjuntar un archivo PDF para corregir el trámite");
        }

        // Procesar nuevo archivo
        $nombreArchivo = $this->procesarArchivo($datos['archivo_adjunto'], $usuario->id);
        
        // Actualizar trámite
        $tramite->archivo_adjunto = $nombreArchivo;
        $tramite->estado = $tramite->ultimo_estado ?: 'PENDIENTE';
        $tramite->ultimo_estado = null;
        $tramite->observaciones = null;
        $tramite->limite_observacion = null;
        $tramite->save();
    }

    /**
     * Derivar trámite al siguiente nivel
     * 
     * @param Tramite $tramite Trámite a derivar
     * @param mixed $usuario Usuario que deriva
     */
    private function derivarTramite(Tramite $tramite, $usuario): void
    {
        $nivelUsuario = $usuario->niveles()->first()->nivel;
        $nivelFinal = $tramite->procedimiento->nivel_final;
        
        // Si ya está en el nivel final, no se puede derivar más
        if ($nivelUsuario >= $nivelFinal) {
            throw new Exception("El trámite ya está en el nivel final");
        }
        
        $nivelSiguiente = $nivelUsuario + 1;
        $usuarioSiguiente = $this->obtenerUsuarioSiguienteNivel($tramite, $nivelSiguiente);
        
        if (!$usuarioSiguiente) {
            throw new Exception("No se encontró usuario del siguiente nivel");
        }
        
        $tramite->id_usuario = $usuarioSiguiente;
        $tramite->estado = 'EN_PROCESO';
        $tramite->save();
    }

    /**
     * Derivar trámite a especialista
     * 
     * @param Tramite $tramite Trámite a derivar
     * @param mixed $usuario Usuario que deriva
     */
    private function derivarAEspecialista(Tramite $tramite, $usuario): void
    {
        $especialistas = $this->obtenerEspecialistasAsignados($tramite->procedimiento->id, $tramite->procedimiento->nivel_final);
        
        // Buscar especialista diferente al actual
        $especialistaDestino = null;
        foreach ($especialistas as $especialista) {
            if ($especialista != $tramite->id_usuario) {
                $especialistaDestino = $especialista;
                break;
            }
        }
        
        if (!$especialistaDestino) {
            throw new Exception("No se encontró especialista disponible para derivar");
        }
        
        $tramite->id_usuario = $especialistaDestino;
        $tramite->estado = 'EN_PROCESO';
        $tramite->save();
    }

    /**
     * Verificar trámite
     * 
     * @param Tramite $tramite Trámite a verificar
     * @param mixed $usuario Usuario que verifica
     */
    private function verificarTramite(Tramite $tramite, $usuario): void
    {
        $tramite->estado = 'VERIFICADO';
        $tramite->save();
    }

    /**
     * Procesar trámite final (generar voucher)
     * 
     * @param Tramite $tramite Trámite a procesar
     * @param mixed $usuario Usuario que procesa
     */
    private function procesarTramiteFinal(Tramite $tramite, $usuario): void
    {
        // Generar voucher
        $rutaVoucher = $this->generarVoucher($tramite);
        
        // Actualizar trámite
        $tramite->estado = 'FINALIZADO';
        $tramite->voucher = $rutaVoucher;
        $tramite->save();
    }

    /**
     * Subir oficio por usuario externo
     * 
     * @param Tramite $tramite Trámite a actualizar
     * @param mixed $usuario Usuario que sube el oficio
     * @param mixed $archivoOficio Archivo del oficio
     */
    private function subirOficioUsuario(Tramite $tramite, $usuario, $archivoOficio): void
    {
        if ($usuario->tipo_user != 1) {
            throw new Exception("Solo usuarios externos pueden subir oficios");
        }

        if (!$archivoOficio) {
            throw new Exception("Debe adjuntar el oficio");
        }

        // Procesar archivo del oficio
        $nombreOficio = $this->procesarArchivo($archivoOficio, $usuario->id);
        
        $tramite->oficio = $nombreOficio;
        $tramite->estado = 'EN_PROCESO';
        $tramite->ultimo_estado = 'OBSERVADO';
        $tramite->save();
    }

    /**
     * Observar trámite con oficio
     * 
     * @param Tramite $tramite Trámite a observar
     * @param mixed $usuario Usuario que observa
     * @param array $datos Datos incluyendo el oficio
     */
    private function observarConOficio(Tramite $tramite, $usuario, array $datos): void
    {
        if (empty($datos['oficio'])) {
            throw new Exception("Debe adjuntar el oficio para esta observación");
        }

        // Procesar archivo del oficio
        $nombreOficio = $this->procesarArchivo($datos['oficio'], $usuario->id);
        
        $tramite->estado = 'OBSERVADO';
        $tramite->observaciones = $datos['observaciones'] ?? '';
        $tramite->oficio = $nombreOficio;
        $tramite->save();
    }

    /**
     * Obtener especialista asignado a un procedimiento
     * 
     * @param int $idProcedimiento ID del procedimiento
     * @param int $nivelFinal Nivel final del procedimiento
     * @return int|null ID del especialista o null
     */
    private function obtenerEspecialistaAsignado(int $idProcedimiento, int $nivelFinal): ?int
    {
        // Buscar usuarios del nivel final asignados al procedimiento
        $especialistas = DB::table('usuarios')
            ->join('usuarios_niveles', 'usuarios.id', '=', 'usuarios_niveles.id_usuario')
            ->join('procedimiento_usuarios', 'usuarios.id', '=', 'procedimiento_usuarios.id_usuario')
            ->where('procedimiento_usuarios.id_procedimiento', $idProcedimiento)
            ->where('usuarios_niveles.nivel', $nivelFinal)
            ->where('usuarios.activo', true)
            ->orderBy('usuarios.nombres')
            ->limit(1)
            ->pluck('usuarios.id')
            ->first();

        return $especialistas;
    }

    /**
     * Obtener especialistas asignados a un procedimiento
     * 
     * @param int $idProcedimiento ID del procedimiento
     * @param int $nivelFinal Nivel final del procedimiento
     * @return array IDs de los especialistas
     */
    private function obtenerEspecialistasAsignados(int $idProcedimiento, int $nivelFinal): array
    {
        return DB::table('usuarios')
            ->join('usuarios_niveles', 'usuarios.id', '=', 'usuarios_niveles.id_usuario')
            ->join('procedimiento_usuarios', 'usuarios.id', '=', 'procedimiento_usuarios.id_usuario')
            ->where('procedimiento_usuarios.id_procedimiento', $idProcedimiento)
            ->where('usuarios_niveles.nivel', $nivelFinal)
            ->where('usuarios.activo', true)
            ->pluck('usuarios.id')
            ->toArray();
    }

    /**
     * Obtener usuario del siguiente nivel
     * 
     * @param Tramite $tramite Trámite actual
     * @param int $nivelSiguiente Nivel siguiente
     * @return int|null ID del usuario siguiente o null
     */
    private function obtenerUsuarioSiguienteNivel(Tramite $tramite, int $nivelSiguiente): ?int
    {
        $procedimiento = $tramite->procedimiento;
        
        if ($nivelSiguiente == $procedimiento->nivel_final) {
            // Nivel final: buscar usuario vinculado al procedimiento y nivel final
            return DB::table('usuarios')
                ->join('usuarios_niveles', 'usuarios.id', '=', 'usuarios_niveles.id_usuario')
                ->join('procedimiento_usuarios', 'usuarios.id', '=', 'procedimiento_usuarios.id_usuario')
                ->where('procedimiento_usuarios.id_procedimiento', $procedimiento->id)
                ->where('usuarios_niveles.nivel', $nivelSiguiente)
                ->where('usuarios_niveles.id_nivel', $procedimiento->id_nivel_final)
                ->where('usuarios.activo', true)
                ->limit(1)
                ->pluck('usuarios.id')
                ->first();
        } else {
            // Niveles intermedios: buscar usuario por nivel y área correspondiente
            $idNivelBuscar = null;
            
            if ($nivelSiguiente == 2) {
                $idNivelBuscar = $procedimiento->id_subarea;
            } elseif ($nivelSiguiente == 3) {
                if ($procedimiento->nivel_final == 4) {
                    // Buscar el primer id de nivel 3 para la subárea
                    $idNivelBuscar = DB::table('nivel_3')
                        ->where('id_nivel2', $procedimiento->id_subarea)
                        ->limit(1)
                        ->pluck('id')
                        ->first();
                } else {
                    $idNivelBuscar = $procedimiento->id_nivel_final;
                }
            }

            if (!$idNivelBuscar) {
                return null;
            }

            return DB::table('usuarios')
                ->join('usuarios_niveles', 'usuarios.id', '=', 'usuarios_niveles.id_usuario')
                ->where('usuarios_niveles.nivel', $nivelSiguiente)
                ->where('usuarios_niveles.id_nivel', $idNivelBuscar)
                ->where('usuarios.activo', true)
                ->limit(1)
                ->pluck('usuarios.id')
                ->first();
        }
    }

    /**
     * Generar número de voucher
     * 
     * @param string $tipoTramite Tipo de trámite (PRESENCIAL/VIRTUAL)
     * @return string Número de voucher generado
     */
    private function generarNumeroVoucher(string $tipoTramite): string
    {
        $prefijo = ($tipoTramite === 'PRESENCIAL') ? 'P' : 'V';
        
        // Obtener el último número de voucher del mismo tipo
        $ultimoNumero = Tramite::where('numero_voucher', 'like', $prefijo . '%')
            ->whereNotNull('numero_voucher')
            ->orderBy('numero_voucher', 'desc')
            ->value('numero_voucher');

        if ($ultimoNumero) {
            $numero = intval(substr($ultimoNumero, 1)) + 1;
        } else {
            $numero = 1;
        }

        return $prefijo . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener acciones permitidas para usuario externo
     * 
     * @param Tramite $tramite Trámite a evaluar
     * @param int $userId ID del usuario
     * @return array Acciones permitidas
     */
    private function obtenerAccionesPermitidasUsuario(Tramite $tramite, int $userId): array
    {
        $acciones = [];
        
        if ($tramite->estado === 'OBSERVADO') {
            $acciones[] = 'CORREGIR';
        }
        
        return $acciones;
    }

    /**
     * Obtener acciones permitidas para usuario por nivel
     * 
     * @param Tramite $tramite Trámite a evaluar
     * @param mixed $nivelesUsuario Niveles del usuario
     * @return array Acciones permitidas
     */
    private function obtenerAccionesPermitidasNivel(Tramite $tramite, $nivelesUsuario): array
    {
        if ($nivelesUsuario->isEmpty()) {
            return [];
        }

        $nivelUsuario = $nivelesUsuario->first()->nivel;
        $estado = $tramite->estado;
        $nivelFinal = $tramite->procedimiento->nivel_final;
        
        $acciones = [];

        // Si el trámite está rechazado, no hay acciones disponibles
        if ($estado === 'RECHAZADO') {
            return [];
        }

        switch ($nivelUsuario) {
            case 1: // Mesa de Partes
                if ($estado === 'PENDIENTE') {
                    $acciones = ['ACEPTAR', 'RECHAZAR'];
                } elseif ($estado === 'EN_PROCESO') {
                    $acciones = ['OBSERVAR', 'DERIVAR'];
                }
                break;
                
            case 2: // Nivel 2
                if ($estado === 'EN_PROCESO') {
                    $acciones = ['OBSERVAR', 'DERIVAR', 'ACEPTAR', 'RECHAZAR'];
                }
                break;
                
            case 3: // Nivel 3
                if ($nivelFinal == 4 && $estado === 'EN_PROCESO') {
                    $acciones = ['VERIFICAR', 'DERIVAR_ESPECIALISTA'];
                } elseif ($nivelFinal == 3) {
                    if ($estado === 'EN_PROCESO') {
                        $acciones = ['OBSERVAR', 'PROCESAR'];
                    } elseif ($estado === 'VERIFICADO') {
                        $acciones = ['PROCESAR', 'RECHAZAR'];
                    }
                }
                break;
                
            case 4: // Especialista
                if ($estado === 'EN_PROCESO') {
                    $acciones = ['OBSERVAR', 'OBSERVAR_CON_OFICIO', 'PROCESAR'];
                } elseif ($estado === 'VERIFICADO') {
                    $acciones = ['PROCESAR', 'RECHAZAR'];
                } elseif ($estado === 'OBSERVADO') {
                    $acciones = ['VERIFICAR'];
                }
                break;
        }

        return $acciones;
    }
}