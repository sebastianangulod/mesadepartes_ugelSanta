<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request para validación de usuarios en administración
 * 
 * Este request valida los datos de entrada para el CRUD de usuarios
 * internos en el panel de administración.
 * 
 * Reglas de validación:
 * - Nombre completo: requerido, máximo 150 caracteres
 * - Email: requerido, único, formato válido, máximo 255 caracteres
 * - DNI: requerido, único, formato numérico de 8 dígitos
 * - Teléfono: opcional, formato numérico de 9 dígitos
 * - Cargo: requerido, máximo 100 caracteres
 * - Área: requerida, debe existir en la base de datos
 * - Niveles: array de niveles jerárquicos válidos
 * - Contraseña: requerida solo en creación, mínimo 8 caracteres
 * - Estado: requerido (activo/inactivo)
 * 
 * @package App\Http\Requests\Admin
 */
class UsuarioRequest extends FormRequest
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
        $usuarioId = $this->route('usuario') ?? $this->route('id');

        $rules = [
            'nombre_completo' => 'required|string|max:150',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('usuarios_internos', 'email')->ignore($usuarioId)
            ],
            'dni' => [
                'required',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/',
                Rule::unique('usuarios_internos', 'dni')->ignore($usuarioId)
            ],
            'telefono' => 'nullable|string|size:9|regex:/^[0-9]{9}$/',
            'cargo' => 'required|string|max:100',
            'area_id' => 'required|exists:areas,id',
            'niveles' => 'required|array|min:1',
            'niveles.*' => 'exists:niveles,id',
            'estado' => 'required|in:activo,inactivo',
        ];

        // Si es creación, la contraseña es obligatoria
        if ($this->isMethod('POST')) {
            $rules['password'] = 'required|string|min:8';
        } else {
            // Si es actualización, la contraseña es opcional
            $rules['password'] = 'nullable|string|min:8';
        }

        // Validaciones específicas para niveles
        if ($this->has('niveles')) {
            $rules = array_merge($rules, $this->getNivelesRules());
        }

        return $rules;
    }

    /**
     * Obtener reglas de validación específicas para niveles
     *
     * @return array
     */
    protected function getNivelesRules()
    {
        $rules = [];

        // Validar consistencia jerárquica de niveles
        if ($this->has('nivel_1_id')) {
            $rules['nivel_1_id'] = 'required|exists:nivel1,id';
        }
        
        if ($this->has('nivel_2_id')) {
            $rules['nivel_2_id'] = 'required|exists:nivel2,id';
            // Verificar que nivel_2 pertenezca a nivel_1
            $rules['nivel_2_id'] .= '|exists:nivel2,nivel_1_id,' . $this->nivel_1_id;
        }
        
        if ($this->has('nivel_3_id')) {
            $rules['nivel_3_id'] = 'required|exists:nivel3,id';
            // Verificar que nivel_3 pertenezca a nivel_2
            $rules['nivel_3_id'] .= '|exists:nivel3,nivel_2_id,' . $this->nivel_2_id;
        }
        
        if ($this->has('nivel_4_id')) {
            $rules['nivel_4_id'] = 'required|exists:nivel4,id';
            // Verificar que nivel_4 pertenezca a nivel_3
            $rules['nivel_4_id'] .= '|exists:nivel4,nivel_3_id,' . $this->nivel_3_id;
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
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'nombre_completo.max' => 'El nombre completo no puede exceder 150 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'dni.required' => 'El DNI es obligatorio.',
            'dni.size' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.regex' => 'El DNI debe contener solo números.',
            'dni.unique' => 'Este DNI ya está registrado.',
            'telefono.size' => 'El teléfono debe tener exactamente 9 dígitos.',
            'telefono.regex' => 'El teléfono debe contener solo números.',
            'cargo.required' => 'El cargo es obligatorio.',
            'cargo.max' => 'El cargo no puede exceder 100 caracteres.',
            'area_id.required' => 'El área es obligatoria.',
            'area_id.exists' => 'El área seleccionada no es válida.',
            'niveles.required' => 'Debe asignar al menos un nivel al usuario.',
            'niveles.array' => 'Los niveles deben ser un array válido.',
            'niveles.min' => 'Debe asignar al menos un nivel al usuario.',
            'niveles.*.exists' => 'Uno de los niveles seleccionados no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado debe ser activo o inactivo.',
            'nivel_1_id.required' => 'El nivel 1 es obligatorio.',
            'nivel_2_id.required' => 'El nivel 2 es obligatorio.',
            'nivel_3_id.required' => 'El nivel 3 es obligatorio.',
            'nivel_4_id.required' => 'El nivel 4 es obligatorio.',
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
            'nombre_completo' => 'nombre completo',
            'email' => 'correo electrónico',
            'dni' => 'DNI',
            'telefono' => 'teléfono',
            'cargo' => 'cargo',
            'area_id' => 'área',
            'niveles' => 'niveles',
            'password' => 'contraseña',
            'estado' => 'estado',
            'nivel_1_id' => 'nivel 1',
            'nivel_2_id' => 'nivel 2',
            'nivel_3_id' => 'nivel 3',
            'nivel_4_id' => 'nivel 4',
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

        // Limpiar el teléfono si existe
        if ($this->has('telefono')) {
            $this->merge([
                'telefono' => preg_replace('/[^0-9]/', '', $this->telefono)
            ]);
        }

        // Convertir nombre completo a mayúsculas
        if ($this->has('nombre_completo')) {
            $this->merge([
                'nombre_completo' => mb_strtoupper(trim($this->nombre_completo), 'UTF-8')
            ]);
        }

        // Convertir cargo a mayúsculas
        if ($this->has('cargo')) {
            $this->merge([
                'cargo' => mb_strtoupper(trim($this->cargo), 'UTF-8')
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
            ->withInput($this->except(['password']))
            ->withErrors($validator));
    }

    /**
     * Validaciones adicionales después de la validación básica
     *
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar consistencia de niveles jerárquicos
            $this->validarJerarquiaNiveles($validator);
            
            // Validar que el usuario no sea el único administrador que se desactiva
            $this->validarUltimoAdministrador($validator);
        });
    }

    /**
     * Validar consistencia de la jerarquía de niveles
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function validarJerarquiaNiveles($validator)
    {
        // Implementar validación de consistencia jerárquica
        // Verificar que si se selecciona nivel 2, también haya nivel 1
        // Verificar que si se selecciona nivel 3, también haya nivel 2
        // Verificar que si se selecciona nivel 4, también haya nivel 3
    }

    /**
     * Validar que no se desactive al único administrador
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    protected function validarUltimoAdministrador($validator)
    {
        // Implementar validación para evitar desactivar al único administrador
        // Verificar que haya al menos un administrador activo
    }
}