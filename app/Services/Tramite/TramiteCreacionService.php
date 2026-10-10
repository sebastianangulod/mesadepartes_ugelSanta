<?php

namespace App\Services\Tramite;

use App\Domain\Tramites\ModoDerivacion;
use App\Models\Tramite;
use App\Models\Procedimiento;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
use App\Services\Tramites\TramiteFlujoService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio para la creación y registro de trámites
 * 
 * Responsabilidades principales:
 * - Crear nuevos trámites con validación de archivos
 * - Gestionar subida de PDF y generación de nombres de archivo
 * - Calcular periodo y tipo de trámite (PRESENCIAL/VIRTUAL)
 * 
 * Nota: Este servicio está enfocado en el ALTA de trámites.
 * El flujo de estados (aceptar, rechazar, observar, etc.) se maneja
 * en App\Services\Tramites\TramiteWorkflowService.
 */
class TramiteCreacionService
{
    public function __construct(
        private readonly TramiteFlujoService $tramiteFlujoService
    ) {
    }

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
            Log::info('Iniciando creación de trámite', [
                'datos' => $datos,
                'has_archivo' => $archivo !== null,
                'usuario_id' => Auth::id(),
            ]);

            // Validar que el usuario esté autenticado
            if (!Auth::check()) {
                Log::error('Usuario no autenticado al intentar crear trámite');
                throw new Exception("Usuario no autenticado");
            }

            $usuario = Auth::user();
            Log::info('Usuario autenticado', [
                'usuario_id' => $usuario->id,
                'tipo_user' => $usuario->tipo_user,
            ]);

            $esInterno = ((int) $usuario->tipo_user === 2);
            
            // Tipo forzado por servidor (ignora input del cliente):
            // interno siempre PRESENCIAL, externo siempre VIRTUAL.
            $tipoTramite = $esInterno ? 'PRESENCIAL' : 'VIRTUAL';
            Log::info('Tipo de trámite determinado', ['tipo' => $tipoTramite]);
            
            // Resolver titular del trámite.
            // - Externo (VIRTUAL): el titular es el propio usuario autenticado.
            // - Interno (PRESENCIAL): el titular es el ciudadano del DNI/RUC
            //   enviado; si está registrado se vincula id_titular, si no se
            //   guarda snapshot validado (sin crear usuario).
            $idTitular = null;
            $titularSnapshot = [];
            if ($esInterno) {
                $titularSnapshot = $this->resolverTitularPresencial($datos, $usuario);
                $idTitular = $titularSnapshot['id_titular'];
                unset($titularSnapshot['id_titular']);
            } else {
                $idTitular = $usuario->id;
                $titularSnapshot = [
                    'titular_tipo_persona' => $usuario->tipo_persona,
                    'titular_dni' => $usuario->dni,
                    'titular_nombres' => $usuario->nombres,
                    'titular_apellido_paterno' => $usuario->apellido_paterno,
                    'titular_apellido_materno' => $usuario->apellido_materno,
                    'titular_ruc' => $usuario->ruc,
                    'titular_razon_social' => $usuario->razon_social,
                ];
            }
            // Validar que se proporcione un procedimiento
            if (empty($datos['id_procedimiento'])) {
                Log::error('No se proporcionó id_procedimiento');
                throw new Exception("Debe seleccionar un procedimiento");
            }

            // Validar que el procedimiento exista
            $procedimiento = Procedimiento::find($datos['id_procedimiento']);
            if (!$procedimiento) {
                Log::error('Procedimiento no encontrado', ['id_procedimiento' => $datos['id_procedimiento']]);
                throw new Exception("El procedimiento seleccionado no existe");
            }
            
            // Validar y procesar archivo si existe
            $nombreArchivo = null;
            if ($archivo) {
                Log::info('Validando archivo', [
                    'nombre' => $archivo->getClientOriginalName(),
                    'tamaño' => $archivo->getSize(),
                    'mime' => $archivo->getMimeType(),
                ]);
                
                $this->validarArchivo($archivo);
                $nombreArchivo = $this->procesarArchivo($archivo, $usuario->id);
                Log::info('Archivo procesado exitosamente', ['nombre_archivo' => $nombreArchivo]);
            } else {
                Log::error('No se proporcionó archivo');
                throw new Exception("Debe adjuntar un archivo PDF");
            }

            // Obtener el periodo actual
            $periodo = date('Y');

            // Contar páginas del PDF si existe ($nombreArchivo es ruta relativa)
            $numeroPaginas = 1;
            if ($nombreArchivo) {
                $rutaArchivo = \App\Support\DocumentStorage::disk()->path($nombreArchivo);
                $numeroPaginas = $this->contarPaginasPDF($rutaArchivo);
            }

            // Buscar usuario de Nivel 1 para el área del procedimiento (id_area = nivel_1.id)
            $usuarioNivel1 = null;
            if ($procedimiento->id_area) {
                $nivel1Usuario = UsuariosNiveles::where('nivel', 1)
                    ->where('id_nivel', $procedimiento->id_area)
                    ->whereHas('usuario', fn($q) => $q->where('tipo_user', 2))
                    ->first();
                if ($nivel1Usuario) {
                    $usuarioNivel1 = $nivel1Usuario->id_usuario;
                }
            }

            // PENDIENTE en Nivel 1 (legacy): asignado al usuario del área correspondiente
            // id_nodo_actual = null → modo legacy (niveles 1-4)
            // Al aceptar, si el procedimiento tiene id_nodo_entrada, se activará modo configurable
            $tramite = Tramite::create(array_merge([
                'archivo_adjunto' => $nombreArchivo,
                'id_procedimiento' => $datos['id_procedimiento'],
                'id_usuario' => $usuarioNivel1 ?? $usuario->id,  // Responsable Nivel 1 o fallback
                'creado_por' => $usuario->id, // Quien registra (interno en presencial)
                'id_titular' => $idTitular, // Ciudadano titular (o propio usuario en virtual)
                'estado' => 'PENDIENTE',
                'nivel_actual' => 1,
                'id_nodo_actual' => null,
                'modo_derivacion' => ModoDerivacion::AUTO,
                'tipo_tramite' => $tipoTramite, // Forzado por servidor
                'tramitescol' => $numeroPaginas,
                'periodo' => $periodo,
                'fecha_creacion' => now(),
            ], $titularSnapshot));

            $this->tramiteFlujoService->registrarMovimiento(
                $tramite,
                (int) $usuario->id,
                'CREAR',
                null,
                null,
                [
                    'id_procedimiento' => $procedimiento->id,
                    'tipo_tramite' => $tipoTramite,
                    'id_titular' => $idTitular,
                    'titular_dni' => $titularSnapshot['titular_dni'] ?? null,
                ]
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Trámite creado exitosamente',
                'id_tramite' => $tramite->id,
                'numero_tramite' => $tramite->id . '-' . $periodo
            ];

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error al crear trámite', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'datos' => $datos,
            ]);
            
            // Limpiar archivo si se subió pero falló la transacción
            if (isset($nombreArchivo)) {
                \App\Support\DocumentStorage::eliminar($nombreArchivo, ['tramites']);
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Resuelve y valida el titular ciudadano de un trámite PRESENCIAL.
     *
     * @param array $datos titular_tipo_persona, titular_dni, titular_nombres,
     *  titular_apellido_paterno, titular_apellido_materno, titular_ruc,
     *  titular_razon_social
     * @return array Snapshot + id_titular (null si no registrado)
     * @throws Exception
     */
    protected function resolverTitularPresencial(array $datos, $usuarioInterno): array
    {
        $tipo = (int) ($datos['titular_tipo_persona'] ?? 0);
        if (!in_array($tipo, [1, 2, 3], true)) {
            throw new Exception('Debe seleccionar el tipo de persona del ciudadano.');
        }

        // Entidad pública: solo nombre de la entidad (sin DNI ni RUC).
        // El nombre cumple el rol de identificador (columna razon_social,
        // igual que la razón social del RUC en persona jurídica).
        if ($tipo === 3) {
            $razon = mb_strtoupper(trim((string) ($datos['titular_razon_social'] ?? '')), 'UTF-8');
            if ($razon === '') {
                throw new Exception('Debe ingresar el nombre de la entidad pública.');
            }

            $existente = Usuario::where('tipo_persona', 3)
                ->whereRaw('UPPER(razon_social) = ?', [$razon])
                ->first();
            if ($existente && (int) $existente->id === (int) $usuarioInterno->id) {
                throw new Exception('La entidad ingresada corresponde al propio usuario interno.');
            }

            Log::info('Titular presencial resuelto (entidad pública)', [
                'razon_social' => $razon,
                'registrado' => (bool) $existente,
                'id_titular' => $existente?->id,
            ]);

            return [
                'id_titular' => $existente?->id,
                'titular_tipo_persona' => $tipo,
                'titular_dni' => null,
                'titular_nombres' => null,
                'titular_apellido_paterno' => null,
                'titular_apellido_materno' => null,
                'titular_ruc' => null,
                'titular_razon_social' => $razon,
            ];
        }

        $dni = preg_replace('/[^0-9]/', '', (string) ($datos['titular_dni'] ?? ''));
        if (!preg_match('/^\d{8}$/', $dni)) {
            throw new Exception('El DNI del ciudadano debe tener 8 dígitos.');
        }

        $nombres = mb_strtoupper(trim((string) ($datos['titular_nombres'] ?? '')), 'UTF-8');
        $apPaterno = mb_strtoupper(trim((string) ($datos['titular_apellido_paterno'] ?? '')), 'UTF-8');
        $apMaterno = mb_strtoupper(trim((string) ($datos['titular_apellido_materno'] ?? '')), 'UTF-8');
        if ($nombres === '' || $apPaterno === '' || $apMaterno === '') {
            throw new Exception('Debe validar el DNI del ciudadano para obtener sus nombres.');
        }

        $ruc = null;
        $razon = null;
        if ($tipo === 2) {
            $ruc = preg_replace('/[^0-9]/', '', (string) ($datos['titular_ruc'] ?? ''));
            $razon = mb_strtoupper(trim((string) ($datos['titular_razon_social'] ?? '')), 'UTF-8');
            if (!preg_match('/^\d{11}$/', $ruc)) {
                throw new Exception('El RUC debe tener 11 dígitos.');
            }
            if ($razon === '') {
                throw new Exception('Debe validar el RUC para obtener la razón social.');
            }
        }

        // Vincular si el ciudadano ya existe (dni es unique en usuarios).
        $existente = Usuario::where('dni', $dni)->first();
        if ($existente && (int) $existente->id === (int) $usuarioInterno->id) {
            throw new Exception('El DNI ingresado corresponde al propio usuario interno.');
        }

        Log::info('Titular presencial resuelto', [
            'dni' => $dni,
            'tipo_persona' => $tipo,
            'registrado' => (bool) $existente,
            'id_titular' => $existente?->id,
        ]);

        return [
            'id_titular' => $existente?->id,
            'titular_tipo_persona' => $tipo,
            'titular_dni' => $dni,
            'titular_nombres' => $nombres,
            'titular_apellido_paterno' => $apPaterno,
            'titular_apellido_materno' => $apMaterno,
            'titular_ruc' => $ruc,
            'titular_razon_social' => $razon,
        ];
    }

    /**
     * Validar archivo PDF
     * 
     * @param mixed $archivo
     * @throws Exception
     */
    protected function validarArchivo($archivo): void
    {
        if (!$archivo) {
            Log::error('Archivo es null o no existe');
            throw new Exception("Debe adjuntar un archivo PDF");
        }

        if (!$archivo->isValid()) {
            Log::error('Archivo no es válido', [
                'error' => $archivo->getError(),
                'nombre' => $archivo->getClientOriginalName(),
            ]);
            throw new Exception("El archivo no es válido. Error: " . $archivo->getError());
        }

        $extension = strtolower($archivo->getClientOriginalExtension());
        $mimeType = $archivo->getMimeType();
        
        Log::info('Validando tipo de archivo', [
            'extension' => $extension,
            'mime_type' => $mimeType,
            'nombre' => $archivo->getClientOriginalName(),
        ]);

        if ($extension !== 'pdf' && $mimeType !== 'application/pdf') {
            Log::error('Archivo no es PDF', [
                'extension' => $extension,
                'mime_type' => $mimeType,
            ]);
            throw new Exception("El archivo debe ser un PDF. Tipo recibido: " . $mimeType);
        }

        // Validar tamaño (25MB = 26214400 bytes)
        $tamañoMaximo = 26214400;
        if ($archivo->getSize() > $tamañoMaximo) {
            Log::error('Archivo excede tamaño máximo', [
                'tamaño' => $archivo->getSize(),
                'maximo' => $tamañoMaximo,
            ]);
            throw new Exception("El archivo no debe exceder los 25MB");
        }
    }

    /**
     * Procesar archivo y guardarlo en el sistema de archivos
     * 
     * @param mixed $archivo
     * @param int $usuarioId
     * @return string Nombre del archivo guardado
     */
    protected function procesarArchivo($archivo, int $usuarioId): string
    {
        // Sanitizar nombre del archivo
        $nombreOriginal = $archivo->getClientOriginalName();
        $extension = strtolower($archivo->getClientOriginalExtension() ?: 'pdf');
        $nombreSanitizado = $this->sanitizarNombreArchivo(
            pathinfo($nombreOriginal, PATHINFO_FILENAME)
        );
        $nombreArchivo = time() . '_' . $nombreSanitizado . '.' . $extension;

        // Devuelve la ruta relativa (con año) para guardar en BD
        return \App\Support\DocumentStorage::guardar($archivo, 'tramites', $nombreArchivo);
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
     * Contar páginas de un PDF
     * 
     * @param string $rutaArchivo
     * @return int
     */
    private function contarPaginasPDF(string $rutaArchivo): int
    {
        try {
            // Método alternativo: leer el archivo y buscar el patrón de páginas
            // Esto es más simple y no requiere dependencias adicionales
            $content = @file_get_contents($rutaArchivo);
            if ($content === false) {
                Log::warning('No se pudo leer el archivo PDF para contar páginas', ['ruta' => $rutaArchivo]);
                return 1;
            }
            
            // Buscar el patrón /Count seguido de un número (número de páginas)
            // Este es un método simple que funciona para la mayoría de PDFs
            preg_match_all('/\/Count\s+(\d+)/', $content, $matches);
            
            if (!empty($matches[1])) {
                // Tomar el último valor encontrado (generalmente el más grande)
                $pageCount = max(array_map('intval', $matches[1]));
                if ($pageCount > 0) {
                    Log::info('Páginas contadas usando método /Count', ['paginas' => $pageCount]);
                    return $pageCount;
                }
            }
            
            // Método alternativo: buscar el patrón /Type\s*\/Page[^s]
            preg_match_all('/\/Type\s*\/Page[^s]/', $content, $pageMatches);
            if (!empty($pageMatches[0])) {
                $pageCount = count($pageMatches[0]);
                if ($pageCount > 0) {
                    Log::info('Páginas contadas usando método /Type/Page', ['paginas' => $pageCount]);
            return $pageCount;
                }
            }
            
            // Si no se puede determinar, retornar 1
            Log::info('No se pudo determinar el número de páginas del PDF, usando valor por defecto', ['ruta' => $rutaArchivo]);
            return 1;
            
        } catch (\Exception $e) {
            Log::warning('Error al contar páginas del PDF', [
                'ruta' => $rutaArchivo,
                'error' => $e->getMessage()
            ]);
            // Si falla, retornar 1 como valor por defecto
            return 1;
        }
    }
}


