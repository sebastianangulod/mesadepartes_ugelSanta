@extends('layouts.app')

@section('title', 'Crear Trámite - Mesa de Partes')

@section('header')
    <h2 class="text-2xl font-bold text-gray-900">Crear Nuevo Trámite</h2>
    <p class="text-sm text-gray-600">Complete el formulario para registrar un nuevo trámite</p>
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <form id="tramiteForm" method="POST" action="{{ route('tramites.store') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="p-6 space-y-6">
                <!-- Información general -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información General</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="numero_expediente" class="block text-sm font-medium text-gray-700 mb-1">
                                Número de Expediente
                            </label>
                            <input type="text" 
                                   name="numero_expediente" 
                                   id="numero_expediente" 
                                   value="{{ old('numero_expediente', 'EXP-' . date('Y') . '-' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT)) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('numero_expediente') border-red-500 @enderror"
                                   readonly>
                            @error('numero_expediente')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="id_procedimiento" class="block text-sm font-medium text-gray-700 mb-1">
                                Procedimiento
                            </label>
                            <select name="id_procedimiento" 
                                    id="id_procedimiento"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('id_procedimiento') border-red-500 @enderror">
                                <option value="">Seleccione un procedimiento</option>
                                @foreach($procedimientos as $procedimiento)
                                    <option value="{{ $procedimiento->id }}" {{ old('id_procedimiento') == $procedimiento->id ? 'selected' : '' }}>
                                        {{ $procedimiento->nombre_completo }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_procedimiento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Asunto y descripción -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detalles del Trámite</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="asunto" class="block text-sm font-medium text-gray-700 mb-1">
                                Asunto *
                            </label>
                            <input type="text" 
                                   name="asunto" 
                                   id="asunto" 
                                   value="{{ old('asunto') }}"
                                   placeholder="Ingrese el asunto del trámite"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('asunto') border-red-500 @enderror"
                                   required>
                            @error('asunto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-1">
                                Descripción
                            </label>
                            <textarea name="descripcion" 
                                      id="descripcion" 
                                      rows="4"
                                      placeholder="Ingrese una descripción detallada del trámite"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descripcion') border-red-500 @enderror">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Archivos -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Documentos Adjuntos</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="archivo_expediente" class="block text-sm font-medium text-gray-700 mb-1">
                                Expediente
                            </label>
                            <input type="file" 
                                   name="archivo_expediente" 
                                   id="archivo_expediente"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('archivo_expediente') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Formatos: PDF, JPG, JPEG, PNG. Tamaño máximo: 10MB</p>
                            @error('archivo_expediente')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="archivo_voucher" class="block text-sm font-medium text-gray-700 mb-1">
                                Voucher de Pago
                            </label>
                            <input type="file" 
                                   name="archivo_voucher" 
                                   id="archivo_voucher"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('archivo_voucher') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Formatos: PDF, JPG, JPEG, PNG. Tamaño máximo: 10MB</p>
                            @error('archivo_voucher')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Observaciones -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Observaciones</h3>
                    
                    <div>
                        <label for="observaciones" class="block text-sm font-medium text-gray-700 mb-1">
                            Observaciones adicionales
                        </label>
                        <textarea name="observaciones" 
                                  id="observaciones" 
                                  rows="3"
                                  placeholder="Ingrese cualquier observación adicional"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('observaciones') border-red-500 @enderror">{{ old('observaciones') }}</textarea>
                        @error('observaciones')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                <a href="{{ route('tramites.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Cancelar
                </a>
                <button type="submit" 
                        class="btn-primary"
                        id="submitButton">
                    <span id="submitText">Crear Trámite</span>
                    <span id="submitLoading" style="display: none;">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Procesando...
                    </span>
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Prevenir doble envío del formulario
        document.getElementById('tramiteForm').addEventListener('submit', function(e) {
            const submitButton = document.getElementById('submitButton');
            const submitText = document.getElementById('submitText');
            const submitLoading = document.getElementById('submitLoading');
            
            // Deshabilitar el botón y mostrar loading
            submitButton.disabled = true;
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline-flex';
            
            // Validación adicional
            const asunto = document.getElementById('asunto').value.trim();
            if (!asunto) {
                e.preventDefault();
                alert('Por favor ingrese el asunto del trámite');
                submitButton.disabled = false;
                submitText.style.display = 'inline';
                submitLoading.style.display = 'none';
                return false;
            }
        });
        
        // Validación de archivos
        document.getElementById('archivo_expediente').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('El archivo no puede exceder los 10MB');
                    e.target.value = '';
                    return;
                }
                
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('El formato del archivo no es válido. Solo se permiten PDF, JPG, JPEG y PNG.');
                    e.target.value = '';
                    return;
                }
            }
        });
        
        document.getElementById('archivo_voucher').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('El archivo no puede exceder los 10MB');
                    e.target.value = '';
                    return;
                }
                
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('El formato del archivo no es válido. Solo se permiten PDF, JPG, JPEG y PNG.');
                    e.target.value = '';
                    return;
                }
            }
        });
    </script>
@endsection