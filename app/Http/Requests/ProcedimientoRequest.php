<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validación para procedimientos y configuración administrativa
 * 
 * Valida la creación y actualización de procedimientos en el sistema,
 * incluyendo validaciones específicas para:
 * - Datos básicos del procedimiento
 * - Configuración de plazos y requisitos
 * - Asignación de usuarios y niveles
 * - Validaciones de negocio y dependencias
 * 
 * @package App\Http\Requests
 */
class ProcedimientoRequest extends FormRequest
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
        $procedimientoId = $this->route('procedimiento'); // Obtener ID del procedimiento en rutas de actualización

        $rules = [
            // Datos básicos del procedimiento
            'codigo' => 'required|string|max:20|unique:procedimientos,codigo,' . $procedimientoId,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string|max:1000',
            'objetivo' => 'required|string|max:1000',
            'tipo' => 'required|in:administrativo,legal,tecnico,economico',
            'categoria' => 'required|in:tramite,servicio,consulta,otro',
            
            // Configuración de plazos
            'plazo_atencion_dias' => 'required|integer|min:1|max:365',
            'plazo_legal_dias' => 'nullable|integer|min:1|max:365',
            'es_gratuito' => 'boolean',
            'costo' => 'required_if:es_gratuito,false|numeric|min:0',
            'moneda' => 'required_if:es_gratuito,false|in:PEN,USD',
            
            // Requisitos y documentación
            'requisitos' => 'required|array|min:1',
            'requisitos.*.descripcion' => 'required|string|max:500',
            'requisitos.*.es_obligatorio' => 'boolean',
            'requisitos.*.tipo_documento' => 'required|in:original,copia,fotocopia,autenticada',
            
            // Configuración de niveles
            'nivel_1_id' => 'nullable|exists:nivel_1s,id',
            'nivel_2_id' => 'nullable|exists:nivel_2s,id',
            'nivel_3_id' => 'nullable|exists:nivel_3s,id',
            'nivel_4_id' => 'nullable|exists:nivel_4s,id',
            
            // Usuarios responsables
            'usuarios_responsables' => 'required|array|min:1',
            'usuarios_responsables.*' => 'exists:usuarios,id',
            
            // Configuración adicional
            'es_activo' => 'boolean',
            'requiere_aprobacion' => 'boolean',
            'nivel_aprobacion' => 'required_if:requiere_aprobacion,true|in:1,2,3,4',
            
            // Validaciones de negocio
            'procedimiento_base_id' => 'nullable|exists:procedimientos,id',
            'dependencias' => 'array',
            'dependencias.*' => 'exists:procedimientos,id',
            
            // Campos personalizados
            'campos_adicionales' => 'nullable|array',
            'campos_adicionales.*.nombre' => 'required|string|max:100',
            'campos_adicionales.*.tipo' => 'required|in:texto,numero,fecha,lista,archivo',
            'campos_adicionales.*.es_obligatorio' => 'boolean',
        ];

        // Reglas específicas según el método HTTP
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // En actualización, algunos campos pueden ser opcionales
            $rules['codigo'] = 'sometimes|required|string|max:20|unique:procedimientos,codigo,' . $procedimientoId;
            $rules['requisitos'] = 'sometimes|required|array|min:1';
            $rules['usuarios_responsables'] = 'sometimes|required|array|min:1';
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
            'codigo.required' => 'El código del procedimiento es obligatorio',
            'codigo.unique' => 'Este código de procedimiento ya está registrado',
            'nombre.required' => 'El nombre del procedimiento es obligatorio',
            'descripcion.required' => 'La descripción del procedimiento es obligatoria',
            'objetivo.required' => 'El objetivo del procedimiento es obligatorio',
            'tipo.required' => 'El tipo de procedimiento es obligatorio',
            'categoria.required' => 'La categoría del procedimiento es obligatoria',
            'plazo_atencion_dias.required' => 'El plazo de atención es obligatorio',
            'plazo_atencion_dias.min' => 'El plazo de atención debe ser al menos 1 día',
            'costo.required_if' => 'El costo es obligatorio si el procedimiento no es gratuito',
            'moneda.required_if' => 'La moneda es obligatoria si hay un costo',
            'requisitos.required' => 'Debe definir al menos un requisito',
            'requisitos.*.descripcion.required' => 'La descripción del requisito es obligatoria',
            'usuarios_responsables.required' => 'Debe asignar al menos un usuario responsable',
            'usuarios_responsables.*.exists' => 'Uno de los usuarios responsables no existe',
            'nivel_aprobacion.required_if' => 'El nivel de aprobación es obligatorio si requiere aprobación',
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
        $this->validarJerarquiaNiveles($data);
        $this->validarPlazosCoherentes($data);
        $this->validarDependencias($data);
        
        return $data;
    }

    /**
     * Valida la coherencia de la jerarquía de niveles
     * 
     * @param array $data Datos a validar
     * @return void
     */
    private function validarJerarquiaNiveles(array &$data): void
    {
        // Validar que si se selecciona nivel 2, debe haber nivel 1
        if (!empty($data['nivel_2_id']) && empty($data['nivel_1_id'])) {
            $this->validator->errors()->add('nivel_1_id', 'Debe seleccionar un nivel 1 si asigna nivel 2');
        }
        
        // Validar que si se selecciona nivel 3, debe haber nivel 2
        if (!empty($data['nivel_3_id']) && empty($data['nivel_2_id'])) {
            $this->validator->errors()->add('nivel_2_id', 'Debe seleccionar un nivel 2 si asigna nivel 3');
        }
        
        // Validar que si se selecciona nivel 4, debe haber nivel 3
        if (!empty($data['nivel_4_id']) && empty($data['nivel_3_id'])) {
            $this->validator->errors()->add('nivel_3_id', 'Debe seleccionar un nivel 3 si asigna nivel 4');
        }
    }

    /**
     * Valida que los plazos sean coherentes
     * 
     * @param array $data Datos a validar
     * @return void
     */
    private function validarPlazosCoherentes(array &$data): void
    {
        // Validar que el plazo legal no sea menor que el plazo de atención
        if (!empty($data['plazo_legal_dias']) && $data['plazo_legal_dias'] < $data['plazo_atencion_dias']) {
            $this->validator->errors()->add('plazo_legal_dias', 'El plazo legal no puede ser menor que el plazo de atención');
        }
    }

    /**
     * Valida que no haya dependencias circulares
     * 
     * @param array $data Datos a validar
     * @return void
     */
    private function validarDependencias(array &$data): void
    {
        // Validar que el procedimiento no dependa de sí mismo
        if (!empty($data['dependencias'])) {
            if (in_array($this->route('procedimiento'), $data['dependencias'])) {
                $this->validator->errors()->add('dependencias', 'Un procedimiento no puede depender de sí mismo');
            }
        }
        
        // Aquí se podrían agregar más validaciones de dependencias
        // como evitar ciclos en la dependencia de procedimientos
    }
}