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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
        $usuarios = Usuario::where('tipo_user', 2)
            ->with('usuariosNiveles')
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

        return view('admin.usuarios.index', compact('usuarios'));
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
     * Mostrar formulario de creación de usuario
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Implementar: retornar vista admin.usuarios.create
        // Incluir: formulario con campos de usuario
        // Cargar: niveles disponibles, áreas, procedimientos
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
     * Mostrar formulario de edición de usuario
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        // Implementar: retornar vista admin.usuarios.edit
        // Cargar: datos del usuario, niveles asignados
        // Incluir: formulario de edición con datos actuales
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
     * Asignar niveles jerárquicos a usuario
     * 
     * @param Request $request
     * @param int $usuarioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function asignarNiveles(Request $request, $usuarioId)
    {
        // Implementar: gestionar asignación de niveles
        // Validar: niveles existen y están activos
        // Verificar: consistencia jerárquica (nivel1->nivel2->nivel3->nivel4)
        // Actualizar: tabla usuarios_niveles
        // Retornar: JSON con nuevos niveles asignados
    }

    /**
     * Hacer usuario administrador
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function hacerAdministrador($id)
    {
        // Implementar: convertir usuario en administrador
        // Verificar: usuario existe y está activo
        // Crear: registro en tabla administradores
        // Asignar: permisos administrativos
        // Retornar: JSON con resultado
    }

    /**
     * Quitar rol de administrador
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function quitarAdministrador($id)
    {
        // Implementar: remover rol de administrador
        // Verificar: hay otros administradores activos
        // Eliminar: registro de tabla administradores
        // Retornar: JSON con resultado
    }

    /**
     * Resetear contraseña de usuario
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetearContrasena($id)
    {
        // Implementar: generar nueva contraseña temporal
        // Actualizar: contraseña del usuario
        // Enviar: correo con nueva contraseña
        // Retornar: JSON con resultado
    }

    /**
     * Activar usuario desactivado
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function activar($id)
    {
        // Implementar: reactivar usuario
        // Verificar: email y DNI aún no están en uso
        // Activar: usuario y sus asignaciones
        // Retornar: JSON con resultado
    }

    /**
     * Obtener niveles asignados a usuario
     * 
     * @param int $usuarioId
     * @return \Illuminate\Http\JsonResponse
     */
    public function obtenerNivelesAsignados($usuarioId)
    {
        // Implementar: retornar estructura de niveles asignados
        // Formato: nested JSON con jerarquía completa
        // Incluir: nivel1, nivel2, nivel3, nivel4
        // Usar en: formularios de edición
    }

    /**
     * Verificar dependencias antes de eliminar
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarDependencias($id)
    {
        // Implementar: contar dependencias del usuario
        // Contar: trámites creados, trámites asignados
        // Contar: procedimientos asignados
        // Verificar: si es administrador
        // Retornar: JSON con conteo de dependencias
    }

    /**
     * Exportar lista de usuarios
     * 
     * @param string $formato (excel|pdf)
     * @return \Illuminate\Http\Response
     */
    public function exportar($formato = 'excel')
    {
        // Implementar: exportar lista de usuarios
        // Excel: usar Laravel Excel o similar
        // PDF: usar DomPDF o similar
        // Incluir: filtros aplicados si los hay
    }
}