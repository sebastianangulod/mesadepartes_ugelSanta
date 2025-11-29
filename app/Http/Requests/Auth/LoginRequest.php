<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para validación de login de usuarios
 * 
 * Este request valida los datos de entrada para el proceso de
 * autenticación tanto de usuarios externos como internos.
 * 
 * Reglas de validación:
 * - DNI: requerido, formato numérico, longitud 8 dígitos
 * - Contraseña: requerida, mínimo 6 caracteres
 * - Tipo de persona: requerido solo para externos (natural/jurídica)
 * - Tipo de usuario: requerido (externo/interno)
 * - reCAPTCHA: validado en el controlador si aplica
 * 
 * @package App\Http\Requests\Auth
 */
class LoginRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado para hacer esta petición
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Obtener las reglas de validación que aplican a la petición
     *
     * @return array
     */
    public function rules()
    {
        // Este request se usa solo para login externo
        // El login interno tiene su propio flujo
        return [
            'dni' => 'required|string|size:8|regex:/^[0-9]{8}$/',
            'password' => 'required|string|min:6',
            // 1: Persona Natural, 2: Persona Jurídica, 3: Entidad Pública
            'tipo_persona' => 'required|in:1,2,3',
            // Campos opcionales que se validan en el controlador según tipo_persona
            'ruc' => 'nullable|string|max:45',
            'nombre_entidad' => 'nullable|string|max:255',
            'remember' => 'sometimes|boolean',
        ];
    }

    /**
     * Obtener los mensajes de error personalizados
     *
     * @return array
     */
    public function messages()
    {
        return [
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex' => 'El DNI debe contener solo números.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'tipo_persona.in' => 'El tipo de persona debe ser: 1 (Persona Natural), 2 (Persona Jurídica) o 3 (Entidad Pública).',
        ];
    }

    /**
     * Obtener los nombres de los campos personalizados
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'dni' => 'DNI',
            'password' => 'contraseña',
            'tipo_usuario' => 'tipo de usuario',
            'tipo_persona' => 'tipo de persona',
        ];
    }

    /**
     * Preparar los datos para la validación
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Limpiar el DNI de espacios y caracteres no numéricos
        if ($this->has('dni')) {
            $this->merge([
                'dni' => preg_replace('/[^0-9]/', '', $this->dni)
            ]);
        }
    }

    /**
     * Manejar una validación fallida
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return void
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Redirigir con errores y mantener el tipo de usuario seleccionado
        throw new \Illuminate\Validation\ValidationException($validator, redirect()
            ->back()
            ->withInput($this->except('password'))
            ->withErrors($validator));
    }
}