<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\UsuariosNiveles;
use App\Models\Administrador;
use App\Models\Nivel1;
use App\Models\Nivel2;
use App\Models\Nivel3;
use App\Models\Nivel4;
use App\Models\Departamento;
use App\Models\Provincia;
use App\Models\Distrito;
use App\Models\Tramite;
use App\Models\Procedimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Mail\BienvenidaUsuarioInterno;

/**
 * Controlador para la gestión de usuarios internos
 * 
 * Este controlador maneja el CRUD completo de usuarios internos del sistema,
 * incluyendo la asignación de niveles jerárquicos, roles administrativos
 * y la gestión de permisos. Proporciona endpoints AJAX para las vistas
 * de administración.
 * 
 * Funcionalidades principales:
 * - CRUD completo de usuarios internos
 * - Asignación y gestión de niveles jerárquicos (1-4)
 * - Designación de administradores del sistema
 * - Activación/desactivación de usuarios
 * - Validación de unicidad de email y DNI
 * - Gestión de contraseñas y seguridad
 * - Auditoría de cambios en usuarios
 * 
 * @package App\Http\Controllers\Admin
 */
class UsuariosController extends Controller
{
    /**
     * Mostrar lista de usuarios
     * Equivalente a adminUsuarios.php del PHP puro
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $tipo = $request->get('tipo', 'interno') === 'externo' ? 'externo' : 'interno';

        $usuarios = Usuario::where('tipo_user', $tipo === 'externo' ? 1 : 2)
            ->with(['usuariosNiveles', 'administrador'])
            ->orderBy('nombres')
            ->get();

        // Obtener nombres de niveles para cada usuario
        foreach ($usuarios as $usuario) {
            if ($usuario->usuariosNiveles->isNotEmpty()) {
                $nivel = $usuario->usuariosNiveles->first();
                $nombreNivel = null;
                
                switch ($nivel->nivel) {
                    case 1:
                        $nivelModel = Nivel1::find($nivel->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        break;
                    case 2:
                        $nivelModel = Nivel2::find($nivel->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        break;
                    case 3:
                        $nivelModel = Nivel3::find($nivel->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        break;
                    case 4:
                        $nivelModel = Nivel4::find($nivel->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        break;
                }
                
                $usuario->nombre_nivel = $nombreNivel;
            }
        }

        return view('admin.usuarios.index', compact('usuarios', 'tipo'));
    }

    /**
     * Obtener usuario por ID (AJAX)
     * Equivalente a action=getUser en adminUsuarios.php del PHP puro
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerUsuario($id)
    {
        try {
            $usuario = Usuario::with(['distrito.provincia.departamento', 'usuariosNiveles'])
                ->findOrFail($id);

            $data = $usuario->toArray();

            // Agregar IDs de ubicación si existen
            if ($usuario->distrito) {
                $data['distrito_id'] = $usuario->distrito->id;
                if ($usuario->distrito->provincia) {
                    $data['provincia_id'] = $usuario->distrito->provincia->id;
                    if ($usuario->distrito->provincia->departamento) {
                        $data['departamento_id'] = $usuario->distrito->provincia->departamento->id;
                    }
                }
            }

            // Obtener jerarquía completa de niveles si tiene nivel asignado
            if ($usuario->usuariosNiveles->isNotEmpty()) {
                $nivel = $usuario->usuariosNiveles->first();
                
                switch ($nivel->nivel) {
                    case 4:
                        $nivel4 = Nivel4::with('nivel3.nivel2.nivel1')->find($nivel->id_nivel);
                        if ($nivel4) {
                            $data['nivel4_id'] = $nivel4->id;
                            if ($nivel4->nivel3) {
                                $data['nivel3_id'] = $nivel4->nivel3->id;
                                if ($nivel4->nivel3->nivel2) {
                                    $data['nivel2_id'] = $nivel4->nivel3->nivel2->id;
                                    if ($nivel4->nivel3->nivel2->nivel1) {
                                        $data['nivel1_id'] = $nivel4->nivel3->nivel2->nivel1->id;
                                    }
                                }
                            }
                        }
                        break;
                    case 3:
                        $nivel3 = Nivel3::with('nivel2.nivel1')->find($nivel->id_nivel);
                        if ($nivel3) {
                            $data['nivel3_id'] = $nivel3->id;
                            if ($nivel3->nivel2) {
                                $data['nivel2_id'] = $nivel3->nivel2->id;
                                if ($nivel3->nivel2->nivel1) {
                                    $data['nivel1_id'] = $nivel3->nivel2->nivel1->id;
                                }
                            }
                        }
                        break;
                    case 2:
                        $nivel2 = Nivel2::with('nivel1')->find($nivel->id_nivel);
                        if ($nivel2) {
                            $data['nivel2_id'] = $nivel2->id;
                            if ($nivel2->nivel1) {
                                $data['nivel1_id'] = $nivel2->nivel1->id;
                            }
                        }
                        break;
                    case 1:
                        $data['nivel1_id'] = $nivel->id_nivel;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario obtenido correctamente',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nuevo usuario interno
     * Equivalente a action=create en adminUsuarios.php del PHP puro
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dniUsuario' => 'required|string|size:8|unique:usuarios,dni',
            'nombreUsuario' => 'required|string|max:20',
            'apellidoPaternoUsuario' => 'required|string|max:20',
            'apellidoMaternoUsuario' => 'required|string|max:20',
            'correoUsuario' => 'required|email|unique:usuarios,email',
            'contrasenaUsuario' => 'required|string|min:8|regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\p{P}\p{S}]).{8,}$/u',
            'direccionUsuario' => 'required|string|max:35',
            'telefonoUsuario' => 'required|string|size:9',
            'distritoUsuario' => 'required|exists:distritos,id',
            'nivel' => 'required|integer|in:1,2,3,4',
            'id_nivel' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Crear usuario
            $usuario = Usuario::create([
                'dni' => $request->dniUsuario,
                'nombres' => strtoupper($request->nombreUsuario),
                'apellido_paterno' => strtoupper($request->apellidoPaternoUsuario),
                'apellido_materno' => strtoupper($request->apellidoMaternoUsuario),
                'email' => $request->correoUsuario,
                'passw' => Hash::make($request->contrasenaUsuario),
                'direccion' => strtoupper($request->direccionUsuario),
                'telefono' => $request->telefonoUsuario,
                'distrito_id' => $request->distritoUsuario,
                'tipo_user' => 2, // Usuario interno
                'tipo_persona' => 1, // Persona natural por defecto
            ]);

            // Asignar nivel
            UsuariosNiveles::create([
                'id_usuario' => $usuario->id,
                'nivel' => $request->nivel,
                'id_nivel' => $request->id_nivel,
            ]);

            DB::commit();

            // Enviar correo de bienvenida al usuario interno
            try {
                $this->enviarEmailBienvenidaInterno($usuario, $request->contrasenaUsuario);
            } catch (\Exception $e) {
                // No interrumpir el flujo si falla el envío de email
                Log::error("Error al enviar email de bienvenida a usuario interno: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => ['id' => $usuario->id]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar usuario existente
     * Equivalente a action=update en adminUsuarios.php del PHP puro
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|max:20',
            'apellido_paterno' => 'required|string|max:20',
            'apellido_materno' => 'required|string|max:20',
            'email' => ['required', 'email', Rule::unique('usuarios')->ignore($id)],
            'telefono' => 'required|string|size:9',
            'direccion' => 'required|string|max:35',
            'distrito_id' => 'required|exists:distritos,id',
            'nivel' => 'nullable|integer|in:1,2,3,4',
            'id_nivel' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Actualizar datos básicos
            $usuario->update([
                'nombres' => strtoupper($request->nombres),
                'apellido_paterno' => strtoupper($request->apellido_paterno),
                'apellido_materno' => strtoupper($request->apellido_materno),
                'email' => $request->email,
                'telefono' => $request->telefono,
                'direccion' => strtoupper($request->direccion),
                'distrito_id' => $request->distrito_id,
            ]);

            // Actualizar nivel si se proporciona
            if ($request->has('nivel') && $request->has('id_nivel') && 
                !empty($request->nivel) && !empty($request->id_nivel)) {
                
                $nivelExistente = UsuariosNiveles::where('id_usuario', $id)->first();
                
                if ($nivelExistente) {
                    $nivelExistente->update([
                        'nivel' => $request->nivel,
                        'id_nivel' => $request->id_nivel,
                    ]);
                } else {
                    UsuariosNiveles::create([
                        'id_usuario' => $id,
                        'nivel' => $request->nivel,
                        'id_nivel' => $request->id_nivel,
                    ]);
                }
            }

            DB::commit();

            // Obtener usuario actualizado con nivel
            $usuarioActualizado = Usuario::with('usuariosNiveles')->find($id);

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => $usuarioActualizado
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información de elementos relacionados que se eliminarán en cascada
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDependencias($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            // Asignaciones como especialista en procedimientos (se borran en cascada)
            $asignacionesEspecialista = DB::table('procedimiento_usuarios')
                ->where('id_usuario', $id)
                ->count();

            // Trámites asignados al usuario: SE ELIMINAN en cascada (FK tramites.id_usuario)
            $tramitesAsignados = DB::table('tramites')
                ->where('id_usuario', $id)
                ->count();

            // Trámites creados por el usuario: se conservan pero quedan huérfanos (creado_por = null)
            $tramitesCreados = DB::table('tramites')
                ->where('creado_por', $id)
                ->count();

            $tieneDependencias = ($asignacionesEspecialista + $tramitesAsignados + $tramitesCreados) > 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'asignaciones_especialista' => $asignacionesEspecialista,
                    'tramites_asignados' => $tramitesAsignados,
                    'tramites_creados' => $tramitesCreados,
                    'tiene_dependencias' => $tieneDependencias
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al obtener dependencias de usuario: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dependencias: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar usuario
     * Equivalente a action=delete en adminUsuarios.php del PHP puro
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            // Verificar que no sea el usuario actual
            if ($usuario->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puede eliminar su propio usuario'
                ], 422);
            }

            DB::beginTransaction();

            // Eliminar nivel asignado
            UsuariosNiveles::where('id_usuario', $id)->delete();

            // Eliminar usuario
            $usuario->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Asignar múltiples niveles jerárquicos a usuario
     * 
     * @param Request $request
     * @param int $usuarioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignarNiveles(Request $request, $usuarioId)
    {
        $validator = Validator::make($request->all(), [
            'niveles' => 'required|array',
            'niveles.*.nivel' => 'required|integer|in:1,2,3,4',
            'niveles.*.id_nivel' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $usuario = Usuario::findOrFail($usuarioId);

            DB::beginTransaction();

            // Eliminar niveles existentes
            UsuariosNiveles::where('id_usuario', $usuarioId)->delete();

            // Validar y crear nuevos niveles
            foreach ($request->niveles as $nivelData) {
                $nivel = $nivelData['nivel'];
                $idNivel = $nivelData['id_nivel'];

                // Verificar que el nivel existe según su tabla
                $existe = match ($nivel) {
                    1 => Nivel1::find($idNivel),
                    2 => Nivel2::find($idNivel),
                    3 => Nivel3::find($idNivel),
                    4 => Nivel4::find($idNivel),
                    default => null,
                };

                if (!$existe) {
                    throw new \Exception("El nivel {$nivel} con ID {$idNivel} no existe");
                }

                UsuariosNiveles::create([
                    'id_usuario' => $usuarioId,
                    'nivel' => $nivel,
                    'id_nivel' => $idNivel,
                ]);
            }

            DB::commit();

            // Retornar niveles asignados con nombres
            $nivelesAsignados = $this->getNivelesConNombres($usuarioId);

            return response()->json([
                'success' => true,
                'message' => 'Niveles asignados correctamente',
                'data' => $nivelesAsignados
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener niveles asignados a usuario con nombres
     * 
     * @param int $usuarioId
     * @return array
     */
    private function getNivelesConNombres(int $usuarioId): array
    {
        $usuariosNiveles = UsuariosNiveles::where('id_usuario', $usuarioId)->get();
        $result = [];

        foreach ($usuariosNiveles as $un) {
            $nombreNivel = null;
            switch ($un->nivel) {
                case 1:
                    $nivelModel = Nivel1::find($un->id_nivel);
                    $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                    break;
                case 2:
                    $nivelModel = Nivel2::find($un->id_nivel);
                    $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                    break;
                case 3:
                    $nivelModel = Nivel3::find($un->id_nivel);
                    $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                    break;
                case 4:
                    $nivelModel = Nivel4::find($un->id_nivel);
                    $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                    break;
            }

            $result[] = [
                'nivel' => $un->nivel,
                'id_nivel' => $un->id_nivel,
                'nombre' => $nombreNivel,
            ];
        }

        return $result;
    }

    /**
     * Obtener niveles asignados a usuario (AJAX)
     * 
     * @param int $usuarioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerNivelesAsignados($usuarioId)
    {
        try {
            $usuario = Usuario::findOrFail($usuarioId);
            $nivelesAsignados = $this->getNivelesConNombres($usuarioId);

            return response()->json([
                'success' => true,
                'data' => $nivelesAsignados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener niveles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hacer usuario administrador
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function hacerAdministrador($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            if (Administrador::where('id_usuario', $id)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario ya es administrador'
                ], 422);
            }

            DB::beginTransaction();

            Administrador::create([
                'id_usuario' => $id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario convertido en administrador correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al hacer administrador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quitar rol de administrador
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function quitarAdministrador($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            if ($usuario->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puede quitarse el rol de administrador a sí mismo'
                ], 422);
            }

            $adminsCount = Administrador::count();
            if ($adminsCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe haber al menos un administrador en el sistema'
                ], 422);
            }

            DB::beginTransaction();

            Administrador::where('id_usuario', $id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rol de administrador removido correctamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al quitar administrador: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resetear contraseña de usuario
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetearContrasena($id)
    {
        try {
            $usuario = Usuario::findOrFail($id);

            if ($usuario->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puede resetear su propia contraseña desde aquí'
                ], 422);
            }

            $nuevaContrasena = Str::random(11) . collect(['@', '$', '!', '%', '*', '?', '&', '_'])->random();

            DB::beginTransaction();

            $usuario->update([
                'passw' => Hash::make($nuevaContrasena),
            ]);

            DB::commit();

            // TODO: Enviar email con nueva contraseña

            return response()->json([
                'success' => true,
                'message' => 'Contraseña reseteada correctamente',
                'data' => [
                    'nueva_contrasena' => $nuevaContrasena
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al resetear contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar email de bienvenida a usuario interno
     * 
     * @param \App\Models\Usuario $usuario
     * @param string $contrasenaTemporal
     * @return void
     */
    protected function enviarEmailBienvenidaInterno($usuario, $contrasenaTemporal)
    {
        try {
            // Obtener información del nivel asignado
            $nivelAsignado = UsuariosNiveles::where('id_usuario', $usuario->id)->first();
            $infoArea = [];
            $nivelesAsignados = [];
            
            if ($nivelAsignado) {
                // Obtener nombre del nivel según el tipo
                $nombreNivel = null;
                switch ($nivelAsignado->nivel) {
                    case 1:
                        $nivelModel = Nivel1::find($nivelAsignado->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        $infoArea = [
                            'id' => $nivelAsignado->id_nivel,
                            'nombre' => $nombreNivel,
                            'tipo' => 'Área'
                        ];
                        break;
                    case 2:
                        $nivelModel = Nivel2::find($nivelAsignado->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        $infoArea = [
                            'id' => $nivelAsignado->id_nivel,
                            'nombre' => $nombreNivel,
                            'tipo' => 'Sub-área'
                        ];
                        break;
                    case 3:
                        $nivelModel = Nivel3::find($nivelAsignado->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        $infoArea = [
                            'id' => $nivelAsignado->id_nivel,
                            'nombre' => $nombreNivel,
                            'tipo' => 'Especialidad'
                        ];
                        break;
                    case 4:
                        $nivelModel = Nivel4::find($nivelAsignado->id_nivel);
                        $nombreNivel = $nivelModel ? $nivelModel->nombre : null;
                        $infoArea = [
                            'id' => $nivelAsignado->id_nivel,
                            'nombre' => $nombreNivel,
                            'tipo' => 'Sub-especialidad'
                        ];
                        break;
                }
                
                $nivelesAsignados = [
                    [
                        'nivel' => $nivelAsignado->nivel,
                        'id_nivel' => $nivelAsignado->id_nivel,
                        'nombre' => $nombreNivel
                    ]
                ];
            }
            
            // Verificar si es administrador
            $esAdministrador = Administrador::where('id_usuario', $usuario->id)->exists();
            $permisos = $esAdministrador ? ['admin'] : ['acceso_basico'];
            
            // Preparar datos del usuario para el correo
            $datosUsuario = [
                'id' => $usuario->id,
                'dni' => $usuario->dni,
                'nombres' => $usuario->nombres,
                'apellido_paterno' => $usuario->apellido_paterno,
                'apellido_materno' => $usuario->apellido_materno,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'nombre_completo' => $usuario->nombres . ' ' . $usuario->apellido_paterno . ' ' . $usuario->apellido_materno,
            ];
            
            // Enviar correo de bienvenida
            Mail::to($usuario->email)->send(
                new BienvenidaUsuarioInterno(
                    $datosUsuario,
                    $contrasenaTemporal,
                    $infoArea,
                    $nivelesAsignados,
                    $permisos
                )
            );
            
            Log::info("Email de bienvenida enviado a usuario interno: {$usuario->email} (ID: {$usuario->id})");
        } catch (\Exception $e) {
            Log::error("Error al enviar email de bienvenida a usuario interno: " . $e->getMessage());
            throw $e;
        }
    }
}