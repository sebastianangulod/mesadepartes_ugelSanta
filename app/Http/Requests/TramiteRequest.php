<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request de validación para la creación y actualización de trámites
 * 
 * Valida todos los campos necesarios para el registro de un trámite
 * en el sistema de mesa de partes, incluyendo:
 * - Datos del remitente
 * - Información del trámite
 * - Adjuntos y documentación
 * - Validaciones específicas por tipo de procedimiento
 * 
 * @package App\Http\Requests
 */
class TramiteRequest extends FormRequest
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
        $rules = [
            // Datos del remitente
            'remitente_tipo' => 'required|in:natural,juridica',
            'remitente_documento' => 'required|string|max:20',
            'remitente_nombre' => 'required|string|max:255',
            'remitente_apellido' => 'required_if:remitente_tipo,natural|string|max:255',
            'remitente_razon_social' => 'required_if:remitente_tipo,juridica|string|max:255',
            'remitente_correo' => 'required|email|max:255',
            'remitente_telefono' => 'required|string|max:20',
            'remitente_direccion' => 'required|string|max:500',
            
            // Datos del trámite
            'procedimiento_id' => 'required|exists:procedimientos,id',
            'asunto' => 'required|string|max:500',
            'descripcion' => 'required|string|max:2000',
            'folios' => 'required|integer|min:1|max:999',
            'prioridad' => 'required|in:baja,media,alta,urgente',
            
            // Ubicación
            'departamento_id' => 'required|exists:departamentos,id',
            'provincia_id' => 'required|exists:provincias,id',
            'distrito_id' => 'required|exists:distritos,id',
            
            // Documentos adjuntos
            'documentos' => 'required|array|min:1',
            'documentos.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:25600',
            'documentos.*.descripcion' => 'required|string|max:255',
            
            // Validaciones adicionales para externos
            'es_externo' => 'boolean',
            'entidad_externa' => 'required_if:es_externo,true|string|max:255',
        ];

        // Reglas específicas según el método HTTP
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // En actualización, algunos campos pueden ser opcionales
            $rules['remitente_documento'] = 'sometimes|required|string|max:20';
            $rules['remitente_correo'] = 'sometimes|required|email|max:255';
            $rules['documentos'] = 'sometimes|required|array|min:1';
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
            'remitente_tipo.required' => 'El tipo de remitente es obligatorio',
            'remitente_tipo.in' => 'El tipo de remitente debe ser natural o jurídica',
            'remitente_documento.required' => 'El documento de identidad es obligatorio',
            'remitente_documento.max' => 'El documento no puede exceder 20 caracteres',
            'remitente_nombre.required' => 'El nombre del remitente es obligatorio',
            'remitente_apellido.required_if' => 'Los apellidos son obligatorios para personas naturales',
            'remitente_razon_social.required_if' => 'La razón social es obligatoria para personas jurídicas',
            'remitente_correo.required' => 'El correo electrónico es obligatorio',
            'remitente_correo.email' => 'Debe proporcionar un correo electrónico válido',
            'remitente_telefono.required' => 'El teléfono de contacto es obligatorio',
            'remitente_direccion.required' => 'La dirección es obligatoria',
            'procedimiento_id.required' => 'Debe seleccionar un procedimiento',
            'procedimiento_id.exists' => 'El procedimiento seleccionado no existe',
            'asunto.required' => 'El asunto del trámite es obligatorio',
            'asunto.max' => 'El asunto no puede exceder 500 caracteres',
            'descripcion.required' => 'La descripción del trámite es obligatoria',
            'folios.required' => 'El número de folios es obligatorio',
            'folios.integer' => 'Los folios deben ser un número entero',
            'folios.min' => 'Debe haber al menos 1 folio',
            'prioridad.required' => 'La prioridad del trámite es obligatoria',
            'documentos.required' => 'Debe adjuntar al menos un documento',
            'documentos.*.file' => 'Cada documento debe ser un archivo válido',
            'documentos.*.mimes' => 'Los archivos deben ser de tipo: pdf, doc, docx, xls, xlsx, jpg, jpeg, png',
            'documentos.*.max' => 'Cada archivo no puede exceder 25MB',
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
        // Validar formato de DNI, RUC, etc. según corresponda
        if ($data['remitente_tipo'] === 'natural') {
            // Validar DNI (8 dígitos)
            if (!preg_match('/^\\d{8}$/', $data['remitente_documento'])) {
                $this->validator->errors()->add('remitente_documento', 'El DNI debe tener 8 dígitos');
            }
        } else {
            // Validar RUC (11 dígitos)
            if (!preg_match('/^\\d{11}$/', $data['remitente_documento'])) {
                $this->validator->errors()->add('remitente_documento', 'El RUC debe tener 11 dígitos');
            }
        }
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