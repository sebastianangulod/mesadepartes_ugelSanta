<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validación para usuarios externos
 * 
 * Valida la creación y actualización de usuarios externos en el sistema.
 * 
 * @package App\Http\Requests
 */
class UsuarioExternoRequest extends FormRequest
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
            
            // Ubicación geográfica
            'departamento_id' => 'required|exists:departamentos,id',
            'provincia_id' => 'required|exists:provincias,id',
            'distrito_id' => 'required|exists:distritos,id',
        ];

        // Si es actualización, la contraseña puede ser opcional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }
}


