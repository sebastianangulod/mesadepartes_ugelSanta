<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para validación de registro de usuarios externos
 * 
 * Este request valida los datos de entrada para el proceso de
 * registro de nuevos usuarios externos en el sistema.
 * 
 * Reglas de validación:
 * - Nombres: requerido, máximo 100 caracteres, solo letras y espacios
 * - Apellidos: requerido, máximo 100 caracteres, solo letras y espacios
 * - DNI: requerido, único, formato numérico de 8 dígitos
 * - Email: requerido, único, formato válido de email
 * - Contraseña: requerida, mínimo 8 caracteres, confirmación
 * - Teléfono: opcional, formato numérico de 9 dígitos
 * - Departamento, Provincia, Distrito: requeridos para dirección
 * - Dirección: requerida, máximo 200 caracteres
 * - Tipo de persona: requerido (natural/jurídica)
 * - RUC: requerido si es persona jurídica, formato válido
 * - Términos y condiciones: debe ser aceptado
 * 
 * @package App\Http\Requests\Auth
 */
class RegistroRequest extends FormRequest
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
        $rules = [
            'nombres' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$/',
            'apellidos' => 'required|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$/',
            'dni' => 'required|string|size:8|regex:/^[0-9]{8}$/|unique:usuarios,dni',
            'email' => 'required|string|email|max:255|unique:usuarios,email',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
            'telefono' => 'nullable|string|size:9|regex:/^[0-9]{9}$/',
            'departamento_id' => 'required|exists:departamentos,id',
            'provincia_id' => 'required|exists:provincias,id',
            'distrito_id' => 'required|exists:distritos,id',
            'direccion' => 'required|string|max:200',
            'tipo_persona' => 'required|in:natural,juridica',
            'terminos' => 'required|accepted',
        ];

        // Si es persona jurídica, agregar validación de RUC
        if ($this->input('tipo_persona') === 'juridica') {
            $rules['ruc'] = 'required|string|size:11|regex:/^[0-9]{11}$/|unique:usuarios,ruc';
            $rules['razon_social'] = 'required|string|max:200';
        }

        return $rules;
    }

    /**
     * Obtener los mensajes de error personalizados
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nombres.required' => 'Los nombres son obligatorios.',
            'nombres.max' => 'Los nombres no pueden exceder 100 caracteres.',
            'nombres.regex' => 'Los nombres solo pueden contener letras y espacios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.max' => 'Los apellidos no pueden exceder 100 caracteres.',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex' => 'El DNI debe contener solo números.',
            'dni.unique' => 'Este DNI ya está registrado en el sistema.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.max' => 'El correo electrónico no puede exceder 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
            'telefono.size' => 'El teléfono debe tener exactamente 9 dígitos.',
            'telefono.regex' => 'El teléfono debe contener solo números.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'departamento_id.exists' => 'El departamento seleccionado no es válido.',
            'provincia_id.required' => 'La provincia es obligatoria.',
            'provincia_id.exists' => 'La provincia seleccionada no es válida.',
            'distrito_id.required' => 'El distrito es obligatorio.',
            'distrito_id.exists' => 'El distrito seleccionado no es válido.',
            'direccion.required' => 'La dirección es obligatoria.',
            'direccion.max' => 'La dirección no puede exceder 200 caracteres.',
            'tipo_persona.required' => 'El tipo de persona es obligatorio.',
            'tipo_persona.in' => 'El tipo de persona debe ser natural o jurídica.',
            'ruc.required_if' => 'El RUC es obligatorio para personas jurídicas.',
            'ruc.size' => 'El RUC debe tener exactamente 11 dígitos.',
            'ruc.regex' => 'El RUC debe contener solo números.',
            'ruc.unique' => 'Este RUC ya está registrado en el sistema.',
            'razon_social.required_if' => 'La razón social es obligatoria para personas jurídicas.',
            'razon_social.max' => 'La razón social no puede exceder 200 caracteres.',
            'terminos.required' => 'Debe aceptar los términos y condiciones.',
            'terminos.accepted' => 'Debe aceptar los términos y condiciones para continuar.',
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
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'dni' => 'DNI',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'telefono' => 'teléfono',
            'departamento_id' => 'departamento',
            'provincia_id' => 'provincia',
            'distrito_id' => 'distrito',
            'direccion' => 'dirección',
            'tipo_persona' => 'tipo de persona',
            'ruc' => 'RUC',
            'razon_social' => 'razón social',
            'terminos' => 'términos y condiciones',
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

        // Limpiar el RUC si existe
        if ($this->has('ruc')) {
            $this->merge([
                'ruc' => preg_replace('/[^0-9]/', '', $this->ruc)
            ]);
        }

        // Limpiar el teléfono si existe
        if ($this->has('telefono')) {
            $this->merge([
                'telefono' => preg_replace('/[^0-9]/', '', $this->telefono)
            ]);
        }

        // Convertir nombres y apellidos a mayúsculas
        if ($this->has('nombres')) {
            $this->merge([
                'nombres' => mb_strtoupper(trim($this->nombres), 'UTF-8')
            ]);
        }

        if ($this->has('apellidos')) {
            $this->merge([
                'apellidos' => mb_strtoupper(trim($this->apellidos), 'UTF-8')
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
        // Redirigir con errores y mantener los datos del formulario
        throw new \Illuminate\Validation\ValidationException($validator, redirect()
            ->back()
            ->withInput($this->except(['password', 'password_confirmation']))
            ->withErrors($validator));
    }
}