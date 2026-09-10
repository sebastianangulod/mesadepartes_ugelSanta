<?php

namespace App\Services\Tramites;

use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
use App\Domain\Tramites\EstadoTramite;
use App\Domain\Tramites\ModoDerivacion;
use App\Services\Tramites\VoucherService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Servicio para gestionar el flujo de trabajo de trámites
 * 
 * Este servicio maneja todas las acciones sobre trámites:
 * - ACEPTAR: Aceptar trámite y derivarlo al siguiente nivel
 * - RECHAZAR: Rechazar trámite (definitivo o temporal)
 * - CORREGIR: Corregir trámite observado (usuario externo)
 * - DERIVAR: Derivar trámite al siguiente nivel
 * - DERIVAR_ESPECIALISTA: Derivar a otro especialista
 * - VERIFICAR: Verificar trámite
 * - PROCESAR: Procesar y finalizar trámite (genera voucher)
 * - OBSERVAR: Observar trámite
 * - OBSERVAR_CON_OFICIO: Observar con oficio adjunto
 * - SUBIR_OFICIO_USUARIO: Usuario externo sube oficio de subsanación
 */
class TramiteWorkflowService
{
    private VoucherService $voucherService;

    public function __construct(
        VoucherService $voucherService
    ) {
        $this->voucherService = $voucherService;
    }

    /**
     * Aceptar trámite (cambiar a EN_PROCESO)
     * Si el procedimiento tiene id_nodo_entrada, activa modo configurable
     * 
     * @param int $tramiteId
     * @return Tramite
     * @throws Exception
     */
    public function aceptar(int $tramiteId): Tramite
    {
        return DB::transaction(function () use ($tramiteId) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el trámite esté en estado PENDIENTE
            if ($tramite->estado !== EstadoTramite::PENDIENTE) {
                throw new Exception('Solo se pueden aceptar trámites en estado PENDIENTE');
            }

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Validar que el trámite ya está asignado al nivel 1
            if ($tramite->nivel_actual !== 1) {
                throw new Exception(
                    'El trámite debe estar asignado al nivel 1 para ser aceptado. ' .
                    'Nivel actual: ' . ($tramite->nivel_actual ?? 'NULL')
                );
            }

            // Cambiar estado de PENDIENTE a EN_PROCESO
            $tramite->estado = EstadoTramite::EN_PROCESO;

            // Si el procedimiento tiene nodo de entrada configurado, activar modo configurable
            if ($tramite->procedimiento && $tramite->procedimiento->id_nodo_entrada) {
                $tramite->id_nodo_actual = $tramite->procedimiento->id_nodo_entrada;
                $tramite->modo_derivacion = ModoDerivacion::AUTO;
$tramite->modo_manual_por_usuario = null;
            }

            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Rechazar trámite
     * 
     * @param int $tramiteId
     * @param string $motivo
     * @return Tramite
     * @throws Exception
     */
    public function rechazar(int $tramiteId, string $motivo): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $motivo) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Validar límite de observación si está observado
            if ($tramite->estado === EstadoTramite::OBSERVADO) {
                if (!$this->validarLimiteObservacion($tramite)) {
                    throw new Exception('Se ha excedido el límite de tiempo para observaciones');
                }
            }

            // Obtener nivel del usuario (según nivel actual del trámite)
            $nivelUsuario = $this->obtenerNivelUsuario($usuario->id, $tramite->nivel_actual);
            $nivelFinal = $tramite->procedimiento->nivel_final;

            if ($nivelUsuario && $nivelUsuario['nivel'] == $nivelFinal) {
                // Especialista rechaza - motivo de rechazo definitivo
                $tramite->estado = EstadoTramite::RECHAZADO;
                $tramite->ultimo_estado = $tramite->estado;
                $tramite->observaciones = 'RECHAZADO POR ESPECIALISTA: ' . $motivo;
                $tramite->limite_observacion = null;
                $tramite->rechazado_por = $usuario->id;
                $tramite->fecha_rechazo = now();
            } else {
                // Mesa de partes u otros niveles - observación para corrección
                $tramite->estado = EstadoTramite::OBSERVADO;
                $tramite->ultimo_estado = $tramite->estado;
                $tramite->observaciones = $motivo;
                $tramite->limite_observacion = now()->addDays(2);
            }

            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Corregir trámite observado (usuario externo sube nuevo archivo)
     * 
     * @param int $tramiteId
     * @param mixed $archivo
     * @return Tramite
     * @throws Exception
     */
    public function corregir(int $tramiteId, $archivo): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $archivo) {
            $tramite = Tramite::findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el trámite esté observado
            if ($tramite->estado !== EstadoTramite::OBSERVADO) {
                throw new Exception('Solo se pueden corregir trámites observados');
            }

            // Validar que el usuario sea quien creó el trámite
            if ($tramite->creado_por != $usuario->id) {
                throw new Exception('No tiene permiso para corregir este trámite');
            }

            // Validar archivo
            $this->validarArchivoPDF($archivo);

            // Guardar archivo
            $nombreArchivo = $this->guardarArchivo($archivo, 'tramites');

            // Contar páginas del PDF
            $numeroPaginas = $this->contarPaginasPDF(
                Storage::path('uploads/tramites/' . $nombreArchivo)
            );

            // Obtener el estado anterior, por defecto 'PENDIENTE' si está vacío
            $estadoAnterior = $tramite->ultimo_estado ?: EstadoTramite::PENDIENTE;

            // Eliminar archivo anterior si existe
            if ($tramite->archivo_adjunto && Storage::exists('uploads/tramites/' . $tramite->archivo_adjunto)) {
                Storage::delete('uploads/tramites/' . $tramite->archivo_adjunto);
            }

            // Actualizar trámite
            $tramite->archivo_adjunto = $nombreArchivo;
            $tramite->estado = $estadoAnterior;
            $tramite->ultimo_estado = null;
            $tramite->observaciones = null;
            $tramite->limite_observacion = null;
            $tramite->tramitescol = $numeroPaginas;
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Observar trámite
     * 
     * @param int $tramiteId
     * @param string $observacion
     * @return Tramite
     * @throws Exception
     */
    public function observar(int $tramiteId, string $observacion): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $observacion) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Validar que el usuario tenga permiso para observar (solo nivel final)
            $nivelUsuario = $this->obtenerNivelUsuario($usuario->id, $tramite->nivel_actual);
            if (!$nivelUsuario || $nivelUsuario['nivel'] != $tramite->procedimiento->nivel_final) {
                throw new Exception('Solo el nivel final puede observar trámites');
            }

            $tramite->estado = EstadoTramite::OBSERVADO;
            $tramite->ultimo_estado = $tramite->estado;
            $tramite->observaciones = $observacion;
            $tramite->limite_observacion = now()->addDays(2);
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Observar trámite con oficio adjunto
     * 
     * @param int $tramiteId
     * @param string $observacion
     * @param mixed $oficio
     * @return Tramite
     * @throws Exception
     */
    public function observarConOficio(int $tramiteId, string $observacion, $oficio): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $observacion, $oficio) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Validar archivo de oficio
            $this->validarArchivoPDF($oficio);

            // Guardar oficio
            $nombreOficio = $this->guardarArchivo($oficio, 'oficios');

            // Actualizar trámite
            $tramite->estado = EstadoTramite::OBSERVADO;
            $tramite->observaciones = $observacion;
            $tramite->oficio = $nombreOficio;
            $tramite->limite_observacion = now()->addDays(2);
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Usuario externo sube oficio de subsanación
     * 
     * @param int $tramiteId
     * @param mixed $oficio
     * @return Tramite
     * @throws Exception
     */
    public function subirOficioUsuario(int $tramiteId, $oficio): Tramite
    {
        return DB::transaction(function () use ($tramiteId, $oficio) {
            $tramite = Tramite::findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el trámite esté observado
            if ($tramite->estado !== EstadoTramite::OBSERVADO) {
                throw new Exception('Solo se pueden subir oficios para trámites observados');
            }

            // Validar que el usuario sea externo
            if ($usuario->tipo_user != 1) {
                throw new Exception('Solo usuarios externos pueden subir oficios');
            }

            // Validar que el usuario sea quien creó el trámite
            if ($tramite->creado_por != $usuario->id) {
                throw new Exception('No tiene permiso para subir oficio a este trámite');
            }

            // Validar archivo
            $this->validarArchivoPDF($oficio);

            // Guardar oficio
            $nombreOficio = $this->guardarArchivo($oficio, 'oficios');

            // Actualizar trámite
            $tramite->oficio = $nombreOficio;
            $tramite->estado = EstadoTramite::EN_PROCESO;
            $tramite->ultimo_estado = EstadoTramite::OBSERVADO;
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Derivar trámite al siguiente nivel
     * 
     * @param int $tramiteId
     * @return Tramite
     * @throws Exception
     */
    public function derivar(int $tramiteId): Tramite
    {
        return DB::transaction(function () use ($tramiteId) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Obtener nivel actual del usuario (según nivel del trámite para usuarios con varios niveles)
            $nivelUsuario = $this->obtenerNivelUsuario($usuario->id, $tramite->nivel_actual);
            if (!$nivelUsuario) {
                throw new Exception('Usuario no tiene nivel asignado para el nivel actual del trámite');
            }

            $nivelActual = $nivelUsuario['nivel'];
            $nivelFinal = $tramite->procedimiento->nivel_final;
            $idNivelFinal = $tramite->procedimiento->id_nivel_final;

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Si ya está en el nivel final, no se puede derivar más
            if ($nivelActual == $nivelFinal) {
                throw new Exception('El trámite ya está en el nivel final, no se puede derivar más');
            }

            $nivelSiguiente = $nivelActual + 1;

            // Buscar usuario del siguiente nivel
            $usuarioSiguiente = $this->buscarUsuarioSiguienteNivel(
                $tramite->procedimiento,
                $nivelSiguiente,
                $nivelFinal,
                $idNivelFinal
            );

            if (!$usuarioSiguiente) {
                throw new Exception(
                    'No se encontró usuario del siguiente nivel para derivar. ' .
                    'Por favor, verifique que haya un usuario de nivel ' . $nivelSiguiente . 
                    ' vinculado al área/subárea correspondiente.'
                );
            }

            // Actualizar trámite: avanzar al siguiente nivel
            $tramite->id_usuario = $usuarioSiguiente;
            $tramite->nivel_actual = $nivelSiguiente; // Actualizar nivel actual
            $tramite->estado = EstadoTramite::EN_PROCESO;
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Derivar trámite a otro especialista
     * 
     * @param int $tramiteId
     * @return Tramite
     * @throws Exception
     */
    public function derivarEspecialista(int $tramiteId): Tramite
    {
        return DB::transaction(function () use ($tramiteId) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Obtener todos los especialistas vinculados al procedimiento
            $especialistas = DB::table('procedimiento_usuarios')
                ->where('id_procedimiento', $tramite->id_procedimiento)
                ->pluck('id_usuario')
                ->toArray();

            // Filtrar el que sea distinto al usuario actual
            $especialista = null;
            foreach ($especialistas as $espId) {
                if ((string)$espId !== (string)$tramite->id_usuario) {
                    $especialista = $espId;
                    break;
                }
            }

            if (!$especialista) {
                throw new Exception(
                    'No se encontró un especialista distinto al usuario actual ' .
                    'vinculado a este procedimiento.'
                );
            }

            // Derivar el trámite al especialista
            $tramite->id_usuario = $especialista;
            $tramite->estado = EstadoTramite::EN_PROCESO;
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Verificar trámite
     * 
     * @param int $tramiteId
     * @return Tramite
     * @throws Exception
     */
    public function verificar(int $tramiteId): Tramite
    {
        return DB::transaction(function () use ($tramiteId) {
            $tramite = Tramite::with('procedimiento')->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Obtener nivel del usuario (según nivel actual del trámite)
            $nivelUsuario = $this->obtenerNivelUsuario($usuario->id, $tramite->nivel_actual);
            $nivelFinal = $tramite->procedimiento->nivel_final;

            // Solo la mesa de partes (nivel 2) o el nivel final pueden verificar
            if (!$nivelUsuario || 
                ($nivelUsuario['nivel'] != 2 && $nivelUsuario['nivel'] != $nivelFinal)) {
                throw new Exception('Solo la mesa de partes o el nivel final pueden verificar el trámite');
            }

            // Validar que el trámite esté en estado válido para verificar
            if ($tramite->estado !== EstadoTramite::OBSERVADO && 
                !($tramite->estado === EstadoTramite::EN_PROCESO && $tramite->oficio)) {
                throw new Exception('El trámite no puede ser verificado en su estado actual');
            }

            $tramite->estado = EstadoTramite::VERIFICADO;
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Procesar y finalizar trámite (genera voucher)
     * 
     * @param int $tramiteId
     * @return Tramite
     * @throws Exception
     */
    public function procesar(int $tramiteId): Tramite
    {
        return DB::transaction(function () use ($tramiteId) {
            $tramite = Tramite::with(['procedimiento', 'usuario'])->findOrFail($tramiteId);
            $usuario = Auth::user();

            // Validar que el usuario puede actuar sobre este trámite
            $this->validarNivelActual($tramite, $usuario->id);

            // Obtener nivel del usuario (según nivel actual del trámite)
            $nivelUsuario = $this->obtenerNivelUsuario($usuario->id, $tramite->nivel_actual);
            $nivelFinal = $tramite->procedimiento->nivel_final;

            // Solo el nivel final puede procesar
            if (!$nivelUsuario || $nivelUsuario['nivel'] != $nivelFinal) {
                throw new Exception('Solo el nivel final puede procesar el trámite');
            }

            // Validar que el trámite pueda ser procesado
            $puedeProcesar = false;
            if ($tramite->estado === EstadoTramite::VERIFICADO) {
                $puedeProcesar = true;
            } elseif ($tramite->estado === EstadoTramite::EN_PROCESO && empty($tramite->oficio)) {
                $puedeProcesar = true;
            }

            if (!$puedeProcesar) {
                throw new Exception(
                    'El trámite solo puede ser procesado si está VERIFICADO o ' .
                    'en EN_PROCESO sin requerir oficio.'
                );
            }

            // Generar número de voucher
            $numeroVoucher = $this->voucherService->generarNumeroVoucher($tramite->tipo_tramite);

            // Generar PDF del voucher
            $voucherPath = $this->voucherService->generarVoucher($tramite);

            // Actualizar trámite
            $tramite->estado = EstadoTramite::FINALIZADO;
            $tramite->numero_voucher = $numeroVoucher;
            $tramite->voucher = $voucherPath;
            $tramite->save();

            return $tramite->fresh();
        });
    }

    /**
     * Finalizar trámite (alias de procesar)
     * 
     * @param int $tramiteId
     * @return Tramite
     * @throws Exception
     */
    public function finalizar(int $tramiteId): Tramite
    {
        return $this->procesar($tramiteId);
    }

    /**
     * Validar que el usuario actual puede actuar sobre el trámite
     * Solo el nivel actual del trámite puede realizar acciones
     * 
     * @param Tramite $tramite
     * @param int $usuarioId
     * @throws Exception
     */
    private function validarNivelActual(Tramite $tramite, int $usuarioId): void
    {
        // EXCEPCIÓN: Los administradores pueden actuar sobre cualquier trámite
        $usuario = Usuario::find($usuarioId);
        if ($usuario && $usuario->esAdministrador()) {
            return; // Los administradores pueden actuar sin restricciones
        }

        // Si el trámite está en PENDIENTE: cualquier usuario del mismo nivel y área puede actuar (aceptar/derivar)
        if ($tramite->estado === EstadoTramite::PENDIENTE) {
            $nivelUsuario = $this->obtenerNivelUsuario($usuarioId, $tramite->nivel_actual);
            if (!$nivelUsuario) {
                throw new Exception('Usuario no tiene nivel asignado para el nivel actual del trámite');
            }
            if ($tramite->nivel_actual != $nivelUsuario['nivel']) {
                throw new Exception(
                    'Solo el nivel actual del trámite (nivel ' . $tramite->nivel_actual . ') puede realizar esta acción. ' .
                    'Su nivel es ' . $nivelUsuario['nivel'] . '.'
                );
            }
            // Validar que pertenece al mismo área/subárea del trámite
            $procedimiento = $tramite->procedimiento;
            if (!$procedimiento) {
                throw new Exception('No se pudo obtener información del procedimiento');
            }
            $puedeActuar = false;
            if ($tramite->nivel_actual == 1) {
                $puedeActuar = ($procedimiento->id_area == $nivelUsuario['id_nivel']);
            } elseif ($tramite->nivel_actual == 2) {
                $puedeActuar = ($procedimiento->id_subarea == $nivelUsuario['id_nivel']);
            } elseif ($tramite->nivel_actual == 3) {
                $nivel3Modelo = \App\Models\Nivel3::find($nivelUsuario['id_nivel']);
                $puedeActuar = $nivel3Modelo && $nivel3Modelo->id_nivel2 && ($procedimiento->id_subarea == $nivel3Modelo->id_nivel2);
            } elseif ($tramite->nivel_actual == 4) {
                $puedeActuar = ($procedimiento->id_nivel_final == $nivelUsuario['id_nivel']);
            }
            if (!$puedeActuar) {
                throw new Exception(
                    'No tiene permisos para actuar sobre este trámite. ' .
                    'Debe pertenecer al mismo nivel y área/subárea del trámite.'
                );
            }
            return;
        }

        // Para otros estados, validar nivel y permisos según tipo de nivel (usar nivel actual del trámite)
        $nivelUsuario = $this->obtenerNivelUsuario($usuarioId, $tramite->nivel_actual);
        if (!$nivelUsuario) {
            throw new Exception('Usuario no tiene nivel asignado');
        }

        $nivelActualUsuario = $nivelUsuario['nivel'];
        $nivelActualTramite = $tramite->nivel_actual;

        // Validar que el nivel del usuario coincide con el nivel actual del trámite
        if ($nivelActualTramite != $nivelActualUsuario) {
            throw new Exception(
                'Solo el nivel actual del trámite (nivel ' . $nivelActualTramite . ') puede realizar esta acción. ' .
                'Su nivel es ' . $nivelActualUsuario . '.'
            );
        }

        // Obtener información del procedimiento para determinar si es nivel final
        $procedimiento = $tramite->procedimiento;
        if (!$procedimiento) {
            throw new Exception('No se pudo obtener información del procedimiento');
        }

        $nivelFinal = $procedimiento->nivel_final;
        $esNivelFinal = false;

        // Determinar si el nivel actual es el nivel final del procedimiento
        if ($nivelActualTramite == 4) {
            // Nivel 4 siempre es final
            $esNivelFinal = true;
        } elseif ($nivelActualTramite == 3 && $nivelFinal == '3') {
            // Nivel 3 es final si nivel_final = '3' y id_nivel_final coincide
            $esNivelFinal = ($procedimiento->id_nivel_final == $nivelUsuario['id_nivel']);
        } elseif ($nivelActualTramite == 2 && $nivelFinal == '2') {
            // Nivel 2 es final si nivel_final = '2' y id_nivel_final coincide
            $esNivelFinal = ($procedimiento->id_nivel_final == $nivelUsuario['id_nivel']);
        }

        // Para niveles finales: solo el usuario asignado puede actuar
        // Para niveles intermedios: cualquier usuario del mismo nivel puede actuar
        if ($esNivelFinal) {
            // Nivel final: solo el usuario asignado puede actuar
            if ($tramite->id_usuario != $usuarioId) {
                throw new Exception('Solo el especialista asignado puede realizar esta acción en el nivel final');
            }
        } else {
            // Nivel intermedio: validar que el usuario pertenece al mismo nivel/subárea
            // Esto ya está validado por el nivel, pero verificamos que pertenezca a la misma área/subárea
            $puedeActuar = false;

            if ($nivelActualTramite == 1) {
                // Nivel 1: verificar que pertenece a la misma área
                $puedeActuar = ($procedimiento->id_area == $nivelUsuario['id_nivel']);
            } elseif ($nivelActualTramite == 2) {
                // Nivel 2: verificar que pertenece a la misma subárea
                $puedeActuar = ($procedimiento->id_subarea == $nivelUsuario['id_nivel']);
            } elseif ($nivelActualTramite == 3) {
                // Nivel 3: verificar que pertenece a la misma subárea (a través del nivel 3)
                $nivel3Modelo = \App\Models\Nivel3::find($nivelUsuario['id_nivel']);
                if ($nivel3Modelo && $nivel3Modelo->id_nivel2) {
                    $puedeActuar = ($procedimiento->id_subarea == $nivel3Modelo->id_nivel2);
                }
            }

            if (!$puedeActuar) {
                throw new Exception(
                    'No tiene permisos para actuar sobre este trámite. ' .
                    'Debe pertenecer al mismo nivel y área/subárea del trámite.'
                );
            }
        }
    }

    /**
     * Obtener nivel del usuario (opcionalmente filtrado por número de nivel)
     * Si el usuario tiene varios niveles (ej. 2 y 4), pasar $nivelFiltro para obtener el que coincide con el trámite.
     *
     * @param int $usuarioId
     * @param int|null $nivelFiltro Si se indica, devuelve la fila que coincida con este nivel
     * @return array|null ['nivel' => int, 'id_nivel' => int]
     */
    private function obtenerNivelUsuario(int $usuarioId, ?int $nivelFiltro = null): ?array
    {
        $query = UsuariosNiveles::where('id_usuario', $usuarioId);
        if ($nivelFiltro !== null) {
            $query->where('nivel', $nivelFiltro);
        }
        $nivel = $query->first();

        if (!$nivel) {
            return null;
        }

        return [
            'nivel' => $nivel->nivel,
            'id_nivel' => $nivel->id_nivel,
        ];
    }

    /**
     * Obtener especialista asignado a un procedimiento
     * 
     * @param int $idProcedimiento
     * @param string $nivelFinal
     * @return int|null
     */
    private function obtenerEspecialistaAsignado(int $idProcedimiento, string $nivelFinal): ?int
    {
        $especialista = DB::table('usuarios as u')
            ->join('usuarios_niveles as un', 'u.id', '=', 'un.id_usuario')
            ->join('procedimiento_usuarios as pu', 'u.id', '=', 'pu.id_usuario')
            ->where('pu.id_procedimiento', $idProcedimiento)
            ->where('un.nivel', $nivelFinal)
            ->where('u.tipo_user', 2) // Solo usuarios internos
            ->orderBy('u.nombres')
            ->select('u.id')
            ->first();

        return $especialista ? $especialista->id : null;
    }

    /**
     * Buscar usuario del siguiente nivel para derivación
     * 
     * @param Procedimiento $procedimiento
     * @param int $nivelSiguiente
     * @param string $nivelFinal
     * @param int|null $idNivelFinal
     * @return int|null
     */
    private function buscarUsuarioSiguienteNivel(
        Procedimiento $procedimiento,
        int $nivelSiguiente,
        string $nivelFinal,
        ?int $idNivelFinal
    ): ?int {
        if ($nivelSiguiente == $nivelFinal) {
            // Nivel final: buscar usuario vinculado al procedimiento y nivel final
            $usuario = DB::table('usuarios as u')
                ->join('usuarios_niveles as un', 'u.id', '=', 'un.id_usuario')
                ->join('procedimiento_usuarios as pu', 'u.id', '=', 'pu.id_usuario')
                ->where('un.nivel', $nivelSiguiente)
                ->where('pu.id_procedimiento', $procedimiento->id)
                ->where('un.id_nivel', $idNivelFinal)
                ->where('u.tipo_user', 2) // Solo usuarios internos
                ->select('u.id')
                ->first();
        } else {
            // Niveles intermedios: buscar usuario solo en usuarios_niveles
            $idNivelBuscar = null;
            
            if ($nivelSiguiente == 1) {
                // Nivel 1: usar el área del procedimiento
                $idNivelBuscar = $procedimiento->id_area;
            } elseif ($nivelSiguiente == 2) {
                $idNivelBuscar = $procedimiento->id_subarea;
            } elseif ($nivelSiguiente == 3) {
                if ($nivelFinal == '4') {
                    // Buscar el primer id de nivel 3 para la subárea
                    $nivel3 = DB::table('nivel_3')
                        ->where('id_nivel2', $procedimiento->id_subarea)
                        ->select('id')
                        ->first();
                    $idNivelBuscar = $nivel3 ? $nivel3->id : null;
                } else {
                    $idNivelBuscar = $idNivelFinal;
                }
            } elseif ($nivelSiguiente == 4) {
                $idNivelBuscar = $idNivelFinal;
            }

            if (!$idNivelBuscar) {
                return null;
            }

            $usuario = DB::table('usuarios as u')
                ->join('usuarios_niveles as un', 'u.id', '=', 'un.id_usuario')
                ->where('un.nivel', $nivelSiguiente)
                ->where('un.id_nivel', $idNivelBuscar)
                ->where('u.tipo_user', 2) // Solo usuarios internos
                ->select('u.id')
                ->first();
        }

        return $usuario ? $usuario->id : null;
    }

    /**
     * Validar límite de observación
     * 
     * @param Tramite $tramite
     * @return bool
     */
    private function validarLimiteObservacion(Tramite $tramite): bool
    {
        if (!$tramite->limite_observacion) {
            return true;
        }

        return now() <= $tramite->limite_observacion;
    }

    /**
     * Validar archivo PDF
     * 
     * @param mixed $archivo
     * @throws Exception
     */
    private function validarArchivoPDF($archivo): void
    {
        if (!$archivo || !$archivo->isValid()) {
            throw new Exception('El archivo no es válido');
        }

        if ($archivo->getClientOriginalExtension() !== 'pdf') {
            throw new Exception('El archivo debe ser un PDF');
        }

        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($archivo->getSize() > $maxSize) {
            throw new Exception('El archivo no debe superar los 10MB');
        }
    }

    /**
     * Guardar archivo en el sistema de archivos
     * 
     * @param mixed $archivo
     * @param string $directorio
     * @return string Nombre del archivo guardado
     */
    private function guardarArchivo($archivo, string $directorio): string
    {
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = $archivo->getClientOriginalExtension();
        $nombreSanitizado = $this->sanitizarNombreArchivo(
            pathinfo($nombreOriginal, PATHINFO_FILENAME)
        );
        $nombreFinal = time() . '_' . $nombreSanitizado . '.' . $extension;

        $archivo->storeAs('uploads/' . $directorio, $nombreFinal);

        return $nombreFinal;
    }

    /**
     * Sanitizar nombre de archivo
     * 
     * @param string $nombre
     * @return string
     */
    private function sanitizarNombreArchivo(string $nombre): string
    {
        // Eliminar caracteres especiales y espacios
        $nombre = preg_replace('/[^a-zA-Z0-9._-]/', '', $nombre);
        // Reemplazar espacios por guiones bajos
        $nombre = str_replace(' ', '_', $nombre);
        // Convertir a minúsculas
        $nombre = strtolower($nombre);
        // Limitar longitud
        $nombre = substr($nombre, 0, 100);
        
        return $nombre;
    }

    /**
     * Contar páginas de un PDF usando FPDI
     * 
     * @param string $rutaArchivo
     * @return int
     */
    private function contarPaginasPDF(string $rutaArchivo): int
    {
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($rutaArchivo);
            return $pageCount;
        } catch (\Exception $e) {
            // Si falla, retornar 1 como valor por defecto
            return 1;
        }
    }

    /**
     * Listar trámites con filtros
     * 
     * @param array $filtros
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function listarTramites(array $filtros = [])
    {
        $query = Tramite::with(['usuario', 'procedimiento']);

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['usuario_id'])) {
            $query->where('id_usuario', $filtros['usuario_id']);
        }

        if (!empty($filtros['procedimiento_id'])) {
            $query->where('id_procedimiento', $filtros['procedimiento_id']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->whereDate('fecha_creacion', '>=', $filtros['fecha_desde']);
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->whereDate('fecha_creacion', '<=', $filtros['fecha_hasta']);
        }

        return $query->orderByDesc('fecha_creacion')->paginate(15);
    }

    /**
     * Obtener trámite completo con relaciones
     * 
     * @param int $id
     * @return Tramite|null
     */
    public function obtenerTramiteCompleto(int $id): ?Tramite
    {
        return Tramite::with(['usuario', 'procedimiento'])->find($id);
    }

    /**
     * Buscar trámites
     * 
     * @param string $termino
     * @param array $filtros
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function buscarTramites(string $termino, array $filtros = [])
    {
        $query = Tramite::with(['usuario', 'procedimiento']);

        if (!empty($termino)) {
            $query->where(function ($q) use ($termino) {
                $q->where('numero_voucher', 'like', "%{$termino}%")
                  ->orWhereHas('usuario', function ($uq) use ($termino) {
                      $uq->where('dni', 'like', "%{$termino}%")
                        ->orWhere('nombres', 'like', "%{$termino}%")
                        ->orWhere('apellido_paterno', 'like', "%{$termino}%");
                  })
                  ->orWhereHas('procedimiento', function ($pq) use ($termino) {
                      $pq->where('nombre', 'like', "%{$termino}%");
                  });
            });
        }

        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }

        if (!empty($filtros['usuario_id'])) {
            $query->where('id_usuario', $filtros['usuario_id']);
        }

        if (!empty($filtros['procedimiento_id'])) {
            $query->where('id_procedimiento', $filtros['procedimiento_id']);
        }

        return $query->orderByDesc('fecha_creacion')->get();
    }

    /**
     * Obtener estadísticas por estado
     * 
     * @return array
     */
    public function obtenerEstadisticasPorEstado(): array
    {
        return Tramite::selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->toArray();
    }

    /**
     * Eliminar trámite
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function eliminar(int $id): bool
    {
        $tramite = Tramite::findOrFail($id);

        if (!$tramite->puedeSerEliminado()) {
            throw new Exception('No se puede eliminar un trámite en este estado');
        }

        // Eliminar archivos asociados
        if ($tramite->archivo_adjunto && Storage::exists('uploads/tramites/' . $tramite->archivo_adjunto)) {
            Storage::delete('uploads/tramites/' . $tramite->archivo_adjunto);
        }

        if ($tramite->voucher && Storage::exists('uploads/vouchers/' . $tramite->voucher)) {
            Storage::delete('uploads/vouchers/' . $tramite->voucher);
        }

        return $tramite->delete();
    }
}

