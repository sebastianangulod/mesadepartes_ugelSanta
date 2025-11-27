<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validación para usuarios y administración
 * 
 * Valida la creación y actualización de usuarios en el sistema,
 * incluyendo validaciones específicas para:
 * - Datos personales del usuario
   * - Asignación de niveles y permisos
 * - Credenciales de acceso
 * - Validaciones según tipo de usuario (interno/externo)
 * 
 * @package App\Http\Requests
 */
class UsuarioRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para esta acción
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja en middleware
    }

    /**
     * Reglas de validación para el request
     * 
     * @return array
     */
    public function rules(): array
    {
        $usuarioId = $this->route('usuario'); // Obtener ID del usuario en rutas de actualización

        $rules = [
            // Datos personales
            'documento_tipo' => 'required|in:dni,ruc,carnet_extranjeria,pasaporte',
            'documento_numero' => 'required|string|max:20|unique:usuarios,documento_numero,' . $usuarioId,
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'required|string|max:100',
            'correo' => 'required|email|max:255|unique:usuarios,correo,' . $usuarioId,
            'telefono' => 'required|string|max:20',
            'direccion' => 'required|string|max:500',
            
            // Datos de acceso
            'usuario' => 'required|string|max:50|unique:usuarios,usuario,' . $usuarioId,
            'password' => 'required|string|min:8|confirmed',
            'estado' => 'required|in:activo,inactivo,suspendido',
            
            // Tipo y nivel de usuario
            'tipo_usuario' => 'required|in:interno,externo',
            'nivel_1_id' => 'nullable|exists:nivel_1s,id',
            'nivel_2_id' => 'nullable|exists:nivel_2s,id',
            'nivel_3_id' => 'nullable|exists:nivel_3s,id',
            'nivel_4_id' => 'nullable|exists:nivel_4s,id',
            
            // Datos específicos para usuarios externos
            'entidad_externa' => 'required_if:tipo_usuario,externo|string|max:255',
            'cargo_externo' => 'required_if:tipo_usuario,externo|string|max:255',
            
            // Datos específicos para usuarios internos
            'cargo_interno' => 'required_if:tipo_usuario,interno|string|max:255',
            'oficina_id' => 'required_if:tipo_usuario,interno|exists:oficinas,id',
            
            // Permisos y roles
            'es_administrador' => 'boolean',
            'permisos' => 'array',
            'permisos.*' => 'exists:permisos,id',
            
            // Ubicación geográfica
            'departamento_id' => 'required|exists:departamentos,id',
            'provincia_id' => 'required|exists:provincias,id',
            'distrito_id' => 'required|exists:distritos,id',
        ];

        // Reglas específicas según el método HTTP
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // En actualización, password puede ser opcional
            $rules['password'] = 'nullable|string|min:8|confirmed';
            
            // Algunos campos únicos pueden ser opcionales en actualización
            $rules['documento_numero'] = 'sometimes|required|string|max:20|unique:usuarios,documento_numero,' . $usuarioId;
            $rules['correo'] = 'sometimes|required|email|max:255|unique:usuarios,correo,' . $usuarioId;
            $rules['usuario'] = 'sometimes|required|string|max:50|unique:usuarios,usuario,' . $usuarioId;
        }

        return $rules;
    }

    /**
     * Mensajes de error personalizados
     * 
     * @return array
     */
    public function messages(): array
    {
        return [
            'documento_tipo.required' => 'El tipo de documento es obligatorio',
            'documento_tipo.in' => 'El tipo de documento debe ser: DNI, RUC, Carnet de Extranjería o Pasaporte',
            'documento_numero.required' => 'El número de documento es obligatorio',
            'documento_numero.unique' => 'Este número de documento ya está registrado',
            'nombres.required' => 'Los nombres son obligatorios',
            'apellido_paterno.required' => 'El apellido paterno es obligatorio',
            'apellido_materno.required' => 'El apellido materno es obligatorio',
            'correo.required' => 'El correo electrónico es obligatorio',
            'correo.email' => 'Debe proporcionar un correo electrónico válido',
            'correo.unique' => 'Este correo electrónico ya está registrado',
            'telefono.required' => 'El teléfono de contacto es obligatorio',
            'direccion.required' => 'La dirección es obligatoria',
            'usuario.required' => 'El nombre de usuario es obligatorio',
            'usuario.unique' => 'Este nombre de usuario ya está en uso',
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'estado.required' => 'El estado del usuario es obligatorio',
            'tipo_usuario.required' => 'El tipo de usuario es obligatorio',
            'entidad_externa.required_if' => 'La entidad externa es obligatoria para usuarios externos',
            'cargo_externo.required_if' => 'El cargo externo es obligatorio para usuarios externos',
            'cargo_interno.required_if' => 'El cargo interno es obligatorio para usuarios internos',
            'oficina_id.required_if' => 'La oficina es obligatoria para usuarios internos',
            'departamento_id.required' => 'El departamento es obligatorio',
            'provincia_id.required' => 'La provincia es obligatoria',
            'distrito_id.required' => 'El distrito es obligatorio',
        ];
    }

    /**
     * Obtiene datos validados del request
     * 
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();
        
        // Agregar validaciones adicionales personalizadas
        $this->validarDocumentoIdentidad($data);
        $this->validarJerarquiaUsuario($data);
        $this->validarUbicacionGeografica($data);
        
        return $data;
    }

    /**
     * Valida el formato del documento de identidad según el tipo
     * 
     * @param array $data Datos a validar
     * @return void
     */
    private function validarDocumentoIdentidad(array &$data): void
    {
        switch ($data['documento_tipo']) {
            case 'dni':
                if (!preg_match('/^\\d{8}$/', $data['documento_numero'])) {
                    $this->validator->errors()->add('documento_numero', 'El DNI debe tener 8 dígitos');
                }
                break;
                
            case 'ruc':
                if (!preg_match('/^\\d{11}$/', $data['documento_numero'])) {
                    $this->validator->errors()->add('documento_numero', 'El RUC debe tener 11 dígitos');
                }
                break;
                
            case 'carnet_extranjeria':
                if (!preg_match('/^[A-Z0-9]{6,12}$/', strtoupper($data['documento_numero']))) {
                    $this->validator->errors()->add('documento_numero', 'El carnet de extranjería debe tener entre 6 y 12 caracteres alfanuméricos');
                }
                break;
                
            case 'pasaporte':
                if (!preg_match('/^[A-Z0-9]{6,12}$/', strtoupper($data['documento_numero']))) {
                    $this->validator->errors()->add('documento_numero', 'El pasaporte debe tener entre 6 y 12 caracteres alfanuméricos');
                }
                break;
        }
    }

    /**
     * Valida la coherencia de la jerarquía del usuario
     * 
     * @param array $data Datos a validar
     * @return void
     */
    private function validarJerarquiaUsuario(array &$data): void
    {
        // Validar que el nivel 2 pertenezca al nivel 1
        // Validar que el nivel 3 pertenezca al nivel 2
        // Validar que el nivel 4 pertenezca al nivel 3
        // Esto requerirá consultas a la base de datos
    }

    /**
     * Valida la coherencia geográfica de la ubicación
     * 
     * @param array $data Datos a validar
     * @return void
     */
    private function validarUbicacionGeografica(array &$data): void
    {
        // Validar que provincia pertenezca al departamento
        // Validar que distrito pertenezca a la provincia
        // Esto requerirá consultas a la base de datos
    }
}