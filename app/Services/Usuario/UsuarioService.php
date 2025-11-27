<?php

namespace App\Services\Usuario;

use App\Models\Usuario;
use App\Models\Administrador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Servicio para gestionar la lógica de negocio de usuarios
 * 
 * Este servicio maneja:
 * - CRUD de usuarios
 * - Gestión de administradores
 * - Asignación de niveles a usuarios
 * - Validaciones de datos de usuario
 * - Gestión de tipos de usuario (externo/interno)
 */
class UsuarioService
{
    /**
     * Crear nuevo usuario
     * 
     * @param array $datos Datos del usuario
     * @return array Resultado de la operación
     */
    public function crear(array $datos): array
    {
        DB::beginTransaction();
        
        try {
            // Validar datos
            $this->validarDatosUsuario($datos);

            // Verificar si el email ya existe
            if (Usuario::where('email', $datos['email'])->exists()) {
                throw new Exception("El email ya está registrado");
            }

            // Verificar si el DNI ya existe
            if (Usuario::where('dni', $datos['dni'])->exists()) {
                throw new Exception("El DNI ya está registrado");
            }

            // Verificar si el RUC ya existe (si se proporciona)
            if (!empty($datos['ruc']) && Usuario::where('ruc', $datos['ruc'])->exists()) {
                throw new Exception("El RUC ya está registrado");
            }

            // Crear usuario
            $usuario = Usuario::create([
                'nombres' => $datos['nombres'],
                'apellido_paterno' => $datos['apellido_paterno'],
                'apellido_materno' => $datos['apellido_materno'],
                'email' => $datos['email'],
                'dni' => $datos['dni'],
                'ruc' => $datos['ruc'] ?? null,
                'razon_social' => $datos['razon_social'] ?? null,
                'direccion' => $datos['direccion'],
                'distrito_id' => $datos['distrito_id'],
                'telefono' => $datos['telefono'],
                'tipo_user' => $datos['tipo_user'],
                'tipo_persona' => $datos['tipo_persona'],
                'passw' => Hash::make($datos['password'])
            ]);

            // Asignar niveles si es usuario interno
            if ($datos['tipo_user'] == 2 && !empty($datos['niveles'])) {
                $this->asignarNiveles($usuario->id, $datos['niveles']);
            }

            // Marcar como administrador si se indica
            if (!empty($datos['es_administrador'])) {
                $this->hacerAdministrador($usuario->id);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario->fresh(['distrito.provincia.departamento'])
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
     * Actualizar usuario
     * 
     * @param int $id ID del usuario
     * @param array $datos Datos a actualizar
     * @return array Resultado de la operación
     */
    public function actualizar(int $id, array $datos): array
    {
        DB::beginTransaction();
        
        try {
            $usuario = Usuario::findOrFail($id);

            // Validar datos
            $this->validarDatosUsuario($datos, $id);

            // Verificar si el email ya existe (excluyendo el usuario actual)
            if (Usuario::where('email', $datos['email'])->where('id', '!= $id')->exists()) {
                throw new Exception("El email ya está registrado por otro usuario");
            }

            // Verificar si el DNI ya existe (excluyendo el usuario actual)
            if (Usuario::where('dni', $datos['dni'])->where('id', '!=', $id)->exists()) {
                throw new Exception("El DNI ya está registrado por otro usuario");
            }

            // Verificar si el RUC ya existe (excluyendo el usuario actual)
            if (!empty($datos['ruc']) && Usuario::where('ruc', $datos['ruc'])->where('id', '!=', $id)->exists()) {
                throw new Exception("El RUC ya está registrado por otro usuario");
            }

            // Preparar datos para actualizar
            $datosActualizar = [
                'nombres' => $datos['nombres'] ?? $usuario->nombres,
                'apellido_paterno' => $datos['apellido_paterno'] ?? $usuario->apellido_paterno,
                'apellido_materno' => $datos['apellido_materno'] ?? $usuario->apellido_materno,
                'email' => $datos['email'] ?? $usuario->email,
                'dni' => $datos['dni'] ?? $usuario->dni,
                'ruc' => $datos['ruc'] ?? $usuario->ruc,
                'razon_social' => $datos['razon_social'] ?? $usuario->razon_social,
                'direccion' => $datos['direccion'] ?? $usuario->direccion,
                'distrito_id' => $datos['distrito_id'] ?? $usuario->distrito_id,
                'telefono' => $datos['telefono'] ?? $usuario->telefono,
                'tipo_user' => $datos['tipo_user'] ?? $usuario->tipo_user,
                'tipo_persona' => $datos['tipo_persona'] ?? $usuario->tipo_persona
            ];

            // Actualizar contraseña si se proporciona
            if (!empty($datos['password'])) {
                $datosActualizar['passw'] = Hash::make($datos['password']);
            }

            $usuario->update($datosActualizar);

            // Actualizar niveles si es usuario interno
            if ($usuario->tipo_user == 2 && isset($datos['niveles'])) {
                $this->actualizarNiveles($usuario->id, $datos['niveles']);
            }

            // Actualizar estado de administrador
            if (isset($datos['es_administrador'])) {
                if ($datos['es_administrador']) {
                    $this->hacerAdministrador($usuario->id);
                } else {
                    $this->removerAdministrador($usuario->id);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'usuario' => $usuario->fresh(['distrito.provincia.departamento'])
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
     * Eliminar usuario
     * 
     * @param int $id ID del usuario
     * @return array Resultado de la operación
     */
    public function eliminar(int $id): array
    {
        DB::beginTransaction();
        
        try {
            $usuario = Usuario::findOrFail($id);

            // Verificar si tiene trámites asociados
            if ($usuario->tramites()->exists()) {
                throw new Exception("No se puede eliminar un usuario que tiene trámites asociados");
            }

            // Eliminar niveles asignados
            DB::table('usuarios_niveles')
                ->where('id_usuario', $id)
                ->delete();

            // Eliminar asignaciones a procedimientos
            DB::table('procedimiento_usuarios')
                ->where('id_usuario', $id)
                ->delete();

            // Eliminar de administradores si es administrador
            Administrador::where('id_usuario', $id)->delete();

            // Eliminar usuario
            $usuario->delete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
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
     * Obtener todos los usuarios con sus relaciones
     * 
     * @return array Usuarios con relaciones
     */
    public function obtenerTodos(): array
    {
        $usuarios = Usuario::with(['distrito.provincia.departamento', 'niveles'])
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get();

        return [
            'success' => true,
            'usuarios' => $usuarios
        ];
    }

    /**
     * Obtener usuario por ID
     * 
     * @param int $id ID del usuario
     * @return array Usuario con relaciones
     */
    public function obtenerPorId(int $id): array
    {
        try {
            $usuario = Usuario::with([
                'distrito.provincia.departamento',
                'niveles',
                'procedimientosAsignados'
            ])->findOrFail($id);

            return [
                'success' => true,
                'usuario' => $usuario
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }
    }

    /**
     * Buscar usuarios por término
     * 
     * @param string $termino Término de búsqueda
     * @return array Usuarios encontrados
     */
    public function buscar(string $termino): array
    {
        $usuarios = Usuario::with(['distrito.provincia.departamento'])
            ->where(function($query) use ($termino) {
                $query->where('nombres', 'like', '%' . $termino . '%')
                    ->orWhere('apellido_paterno', 'like', '%' . $termino . '%')
                    ->orWhere('apellido_materno', 'like', '%' . $termino . '%')
                    ->orWhere('email', 'like', '%' . $termino . '%')
                    ->orWhere('dni', 'like', '%' . $termino . '%')
                    ->orWhere('razon_social', 'like', '%' . $termino . '%');
            })
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->limit(20)
            ->get();

        return [
            'success' => true,
            'usuarios' => $usuarios
        ];
    }

    /**
     * Obtener usuarios por tipo
     * 
     * @param int $tipoUser Tipo de usuario (1: externo, 2: interno)
     * @return array Usuarios del tipo especificado
     */
    public function obtenerPorTipo(int $tipoUser): array
    {
        $usuarios = Usuario::with(['distrito.provincia.departamento'])
            ->where('tipo_user', $tipoUser)
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get();

        return [
            'success' => true,
            'usuarios' => $usuarios
        ];
    }

    /**
     * Obtener usuarios internos con niveles
     * 
     * @return array Usuarios internos con sus niveles
     */
    public function obtenerUsuariosInternos(): array
    {
        $usuarios = Usuario::with(['distrito.provincia.departamento', 'niveles'])
            ->where('tipo_user', 2)
            ->orderBy('nombres')
            ->orderBy('apellido_paterno')
            ->get();

        return [
            'success' => true,
            'usuarios' => $usuarios
        ];
    }

    /**
     * Asignar niveles a un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @param array $niveles Niveles a asignar
     * @return array Resultado de la operación
     */
    public function asignarNiveles(int $idUsuario, array $niveles): array
    {
        DB::beginTransaction();
        
        try {
            $usuario = Usuario::findOrFail($idUsuario);

            // Verificar que sea usuario interno
            if ($usuario->tipo_user != 2) {
                throw new Exception("Solo los usuarios internos pueden tener niveles asignados");
            }

            // Eliminar niveles existentes
            DB::table('usuarios_niveles')
                ->where('id_usuario', $idUsuario)
                ->delete();

            // Asignar nuevos niveles
            foreach ($niveles as $nivel) {
                if (!isset($nivel['nivel']) || !isset($nivel['id_nivel'])) {
                    throw new Exception("Datos de nivel inválidos");
                }

                // Validar que el nivel exista
                $this->validarNivelExiste($nivel['nivel'], $nivel['id_nivel']);

                DB::table('usuarios_niveles')->insert([
                    'id_usuario' => $idUsuario,
                    'nivel' => $nivel['nivel'],
                    'id_nivel' => $nivel['id_nivel']
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Niveles asignados exitosamente'
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
     * Actualizar niveles de un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @param array $niveles Nuevos niveles
     * @return array Resultado de la operación
     */
    public function actualizarNiveles(int $idUsuario, array $niveles): array
    {
        return $this->asignarNiveles($idUsuario, $niveles);
    }

    /**
     * Hacer administrador a un usuario
     * 
     * @param int $idUsuario ID del usuario
     * @return array Resultado de la operación
     */
    public function hacerAdministrador(int $idUsuario): array
    {
        try {
            $usuario = Usuario::findOrFail($idUsuario);

            // Verificar que sea usuario interno
            if ($usuario->tipo_user != 2) {
                throw new Exception("Solo los usuarios internos pueden ser administradores");
            }

            // Verificar si ya es administrador
            if (Administrador::where('id_usuario', $idUsuario)->exists()) {
                return [
                    'success' => true,
                    'message' => 'El usuario ya es administrador'
                ];
            }

            // Crear registro de administrador
            Administrador::create([
                'id_usuario' => $idUsuario
            ]);

            return [
                'success' => true,
                'message' => 'Usuario convertido en administrador exitosamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Remover administrador
     * 
     * @param int $idUsuario ID del usuario
     * @return array Resultado de la operación
     */
    public function removerAdministrador(int $idUsuario): array
    {
        try {
            $eliminado = Administrador::where('id_usuario', $idUsuario)->delete();

            if ($eliminado) {
                return [
                    'success' => true,
                    'message' => 'Administrador removido exitosamente'
                ];
            } else {
                return [
                    'success' => true,
                    'message' => 'El usuario no era administrador'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si un usuario es administrador
     * 
     * @param int $idUsuario ID del usuario
     * @return bool Si es administrador
     */
    public function esAdministrador(int $idUsuario): bool
    {
        return Administrador::where('id_usuario', $idUsuario)->exists();
    }

    /**
     * Obtener todos los administradores
     * 
     * @return array Administradores con información de usuario
     */
    public function obtenerAdministradores(): array
    {
        $administradores = Administrador::with(['usuario.distrito.provincia.departamento'])
            ->join('usuarios', 'administradores.id_usuario', '=', 'usuarios.id')
            ->orderBy('usuarios.nombres')
            ->orderBy('usuarios.apellido_paterno')
            ->get();

        return [
            'success' => true,
            'administradores' => $administradores
        ];
    }

    /**
     * Validar datos del usuario
     * 
     * @param array $datos Datos a validar
     * @param int $idUsuario ID del usuario (para excluir en validaciones únicas)
     * @throws Exception Si hay datos inválidos
     */
    private function validarDatosUsuario(array $datos, int $idUsuario = null): void
    {
        // Validar campos requeridos
        $camposRequeridos = ['nombres', 'apellido_paterno', 'apellido_materno', 'email', 'dni', 'direccion', 'distrito_id', 'telefono', 'tipo_user', 'tipo_persona'];
        
        foreach ($camposRequeridos as $campo) {
            if (empty($datos[$campo])) {
                throw new Exception("El campo {$campo} es requerido");
            }
        }

        // Validar formato de email
        if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El email no tiene un formato válido");
        }

        // Validar longitud de campos
        if (strlen($datos['nombres']) > 20) {
            throw new Exception("Los nombres no pueden exceder 20 caracteres");
        }

        if (strlen($datos['apellido_paterno']) > 20) {
            throw new Exception("El apellido paterno no puede exceder 20 caracteres");
        }

        if (strlen($datos['apellido_materno']) > 20) {
            throw new Exception("El apellido materno no puede exceder 20 caracteres");
        }

        if (strlen($datos['email']) > 45) {
            throw new Exception("El email no puede exceder 45 caracteres");
        }

        if (strlen($datos['dni']) != 8 || !is_numeric($datos['dni'])) {
            throw new Exception("El DNI debe tener exactamente 8 dígitos numéricos");
        }

        if (strlen($datos['direccion']) > 35) {
            throw new Exception("La dirección no puede exceder 35 caracteres");
        }

        if (strlen($datos['telefono']) > 9 || !is_numeric($datos['telefono'])) {
            throw new Exception("El teléfono debe tener máximo 9 dígitos numéricos");
        }

        // Validar RUC si se proporciona
        if (!empty($datos['ruc'])) {
            if (strlen($datos['ruc']) != 11 || !is_numeric($datos['ruc'])) {
                throw new Exception("El RUC debe tener exactamente 11 dígitos numéricos");
            }
        }

        // Validar tipo de usuario
        if (!in_array($datos['tipo_user'], [1, 2])) {
            throw new Exception("El tipo de usuario debe ser 1 (externo) o 2 (interno)");
        }

        // Validar tipo de persona
        if (!in_array($datos['tipo_persona'], [1, 2])) {
            throw new Exception("El tipo de persona debe ser 1 (natural) o 2 (jurídica)");
        }

        // Validar contraseña si se proporciona
        if (!empty($datos['password'])) {
            if (strlen($datos['password']) < 6) {
                throw new Exception("La contraseña debe tener al menos 6 caracteres");
            }
        }

        // Validar que el distrito exista
        if (!DB::table('distritos')->where('id', $datos['distrito_id'])->exists()) {
            throw new Exception("El distrito especificado no existe");
        }
    }

    /**
     * Validar que un nivel exista
     * 
     * @param int $nivel Nivel a validar
     * @param int $idNivel ID del nivel
     * @throws Exception Si el nivel no existe
     */
    private function validarNivelExiste(int $nivel, int $idNivel): void
    {
        switch ($nivel) {
            case 1:
                if (!DB::table('nivel_1')->where('id', $idNivel)->exists()) {
                    throw new Exception("El nivel 1 especificado no existe");
                }
                break;
            case 2:
                if (!DB::table('nivel_2')->where('id', $idNivel)->exists()) {
                    throw new Exception("El nivel 2 especificado no existe");
                }
                break;
            case 3:
                if (!DB::table('nivel_3')->where('id', $idNivel)->exists()) {
                    throw new Exception("El nivel 3 especificado no existe");
                }
                break;
            case 4:
                if (!DB::table('nivel_4')->where('id', $idNivel)->exists()) {
                    throw new Exception("El nivel 4 especificado no existe");
                }
                break;
            default:
                throw new Exception("Nivel inválido");
        }
    }
}