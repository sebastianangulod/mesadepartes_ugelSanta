@extends('layouts.mesa-partes')

@section('title', 'Nuevo Trámite - Mesa de Partes')

@section('header', 'Mesa de Partes Virtual')

@section('content')
<div class="flex space-x-6">
    <!-- Datos del Expediente -->
    <div class="w-full bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Encabezado destacado -->
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-gray px-6 py-4 border-b-2 border-purple-800">
            <h2 class="text-xl font-bold">Datos del Expediente</h2>
        </div>
        
        <!-- Contenido del formulario -->
        <div class="p-6">
        <form id="tramiteForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="crear_tramite">
            
            <div class="mb-4">
                <label for="id_procedimiento" class="block text-gray-900 font-medium mb-2">
                    Procedimiento <span class="text-red-500">*</span>
                </label>
                <select id="id_procedimiento" name="id_procedimiento" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" required>
                    <option value="" disabled selected>Seleccionar un procedimiento</option>
                    @foreach($procedimientos as $procedimiento)
                        <option value="{{ $procedimiento->id }}">
                            {{ $procedimiento->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="archivo" class="block text-gray-900 font-medium mb-2">
                    Adjuntar Archivo (PDF) <span class="text-red-500">*</span>
                </label>
                <input type="file" id="archivo" name="archivo" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" required accept=".pdf">
                <p id="archivoError" class="text-red-500 mt-2 hidden">Por favor, seleccione un archivo PDF.</p>
                <p class="text-gray-600 text-sm mt-2">
                    Tamaño máximo: 25MB. Solo archivos PDF.
                </p>
            </div>

            <input type="hidden" name="tipo_tramite" value="{{ $tipoTramite }}">
            
            <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Crear Trámite
            </button>
        </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<style>
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100;
        padding: 48px 0 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        background-color: #2D3748 !important;
    }

    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        transition: color 0.3s;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        color: #fff;
        background-color: rgba(255, 255, 255, 0.1);
    }

    main {
        margin-left: 240px;
    }

    @media (max-width: 767.98px) {
        .sidebar {
            position: static;
            padding-top: 0;
        }
        
        main {
            margin-left: 0;
        }
    }

    .bg-purple {
        background-color: #9333ea !important;
    }

    /* Estilos para Choices.js */
    .choices {
        margin-bottom: 0;
    }
    .choices__inner {
        background-color: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        min-height: 42px;
    }
    .choices__list--single {
        padding: 0.5rem;
    }
    .choices__list--dropdown {
        background-color: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        margin-top: 4px;
    }
    .choices__list--dropdown .choices__item {
        padding: 0.5rem 1rem;
    }
    .choices__list--dropdown .choices__item--selectable {
        padding-right: 1rem;
    }
    .choices__list--dropdown .choices__item--selectable.is-highlighted {
        background-color: #f7fafc;
    }
    .choices__input {
        background-color: transparent;
        padding: 0.5rem;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Choices.js JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    let procedimientoSelect;
    
    $(document).ready(function() {
        // Inicializar Choices.js en el selector de procedimientos
        const procedimientoElement = document.getElementById('id_procedimiento');
        if (procedimientoElement) {
            procedimientoSelect = new Choices(procedimientoElement, {
                searchEnabled: true,
                placeholder: true,
                placeholderValue: 'Seleccionar un procedimiento',
                searchPlaceholderValue: 'Buscar procedimiento...',
                itemSelectText: '',
                shouldSort: false,
            });
            
            // Escuchar cambios en el selector de procedimientos usando eventos de Choices.js
            procedimientoElement.addEventListener('addItem', function(event) {
                console.log('Procedimiento seleccionado:', event.detail.value);
                validarFormulario();
            });
            
            // También escuchar cambios directos del elemento
            procedimientoElement.addEventListener('change', function() {
                console.log('Cambio detectado en procedimiento');
                validarFormulario();
            });
        }

        // Función para validar el formulario completo
        function validarFormulario() {
            var procedimientoSeleccionado = false;
            var archivoValido = false;
            
            // Verificar procedimiento
            if (procedimientoSelect && typeof procedimientoSelect.getValue === 'function') {
                var value = procedimientoSelect.getValue(true);
                procedimientoSeleccionado = value && value !== '' && (Array.isArray(value) ? value.length > 0 : true);
            } else {
                procedimientoSeleccionado = $('#id_procedimiento').val() !== '';
            }
            
            // Verificar archivo
            var file = $('#archivo')[0].files[0];
            if (file) {
                var fileType = file.type;
                archivoValido = fileType === 'application/pdf';
            }
            
            // Habilitar/deshabilitar botón
            if (procedimientoSeleccionado && archivoValido) {
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#submitBtn').prop('disabled', true);
            }
        }

        // Validar archivo PDF
        $('#archivo').change(function() {
            var file = this.files[0];
            var fileType = file ? file.type : '';
            var match = ['application/pdf'];
            var tamañoMaximo = 26214400; // 25MB
            
            if (!file) {
                $('#archivoError').removeClass('hidden');
                $('#archivoError').text('Por favor, seleccione un archivo PDF válido.');
                validarFormulario();
                return;
            }
            
            // Validar tipo de archivo
            if (!match.includes(fileType)) {
                $('#archivoError').removeClass('hidden');
                $('#archivoError').text('Por favor, seleccione un archivo PDF válido.');
                validarFormulario();
                return;
            }
            
            // Validar tamaño del archivo (25MB)
            if (file.size > tamañoMaximo) {
                $('#archivoError').removeClass('hidden');
                $('#archivoError').text('El archivo excede el tamaño máximo permitido de 25MB. Tamaño actual: ' + (file.size / 1048576).toFixed(2) + 'MB');
                validarFormulario();
                return;
            }
            
            // Si todo está bien, ocultar el error
            $('#archivoError').addClass('hidden');
            validarFormulario();
        });

        // Enviar formulario
        $('#tramiteForm').submit(function(e) {
            e.preventDefault();
            
            console.log('Formulario enviado');
            
            // Obtener valor seleccionado de Choices.js
            var selectedValue = null;
            if (procedimientoSelect && typeof procedimientoSelect.getValue === 'function') {
                var choicesValue = procedimientoSelect.getValue(true);
                console.log('Valor de Choices.js:', choicesValue);
                selectedValue = Array.isArray(choicesValue) ? choicesValue[0] : choicesValue;
            } else {
                selectedValue = $('#id_procedimiento').val();
                console.log('Valor del select nativo:', selectedValue);
            }
            
            if (!selectedValue || selectedValue === '') {
                alert('Debe seleccionar un procedimiento.');
                return;
            }

            // Verificar que se haya seleccionado un archivo
            var archivoInput = document.getElementById('archivo');
            console.log('=== VERIFICACIÓN DE ARCHIVO ===');
            console.log('archivoInput existe:', !!archivoInput);
            console.log('archivoInput.files:', archivoInput ? archivoInput.files : 'N/A');
            console.log('archivoInput.files.length:', archivoInput ? archivoInput.files.length : 'N/A');
            
            var archivo = archivoInput && archivoInput.files && archivoInput.files.length > 0 ? archivoInput.files[0] : null;
            
            if (!archivo) {
                console.error('No se encontró archivo en el input');
                alert('Debe adjuntar un archivo PDF.');
                return;
            }
            
            console.log('Archivo encontrado:', {
                name: archivo.name,
                size: archivo.size,
                type: archivo.type,
                lastModified: new Date(archivo.lastModified)
            });
            
            // Validar tipo de archivo
            if (archivo.type !== 'application/pdf') {
                alert('El archivo debe ser un PDF. Tipo detectado: ' + archivo.type);
                return;
            }
            
            // Validar tamaño (25MB = 26214400 bytes)
            var tamañoMaximo = 26214400;
            if (archivo.size > tamañoMaximo) {
                alert('El archivo no debe exceder los 25MB. Tamaño actual: ' + (archivo.size / 1048576).toFixed(2) + 'MB');
                return;
            }
            
            console.log('Datos a enviar:', {
                'id_procedimiento': selectedValue,
                'archivo_nombre': archivo.name,
                'archivo_tamaño': archivo.size,
                'archivo_tipo': archivo.type
            });

            // Crear FormData manualmente para asegurar que todo esté incluido
            var formData = new FormData();
            
            // Agregar token CSRF
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                formData.append('_token', csrfToken.getAttribute('content'));
            }
            
            // Agregar otros campos del formulario
            formData.append('action', 'crear_tramite');
            formData.append('id_procedimiento', selectedValue);
            formData.append('tipo_tramite', '{{ $tipoTramite }}');
            
            // Agregar el archivo explícitamente
            formData.append('archivo', archivo);
            
            console.log('FormData preparado, enviando...');
            console.log('Verificación FormData:', {
                'id_procedimiento': formData.get('id_procedimiento'),
                'archivo_nombre': archivo.name,
                'archivo_tamaño': archivo.size,
                'archivo_tipo': archivo.type,
                'has_archivo': formData.has('archivo')
            });
            
            // Verificar que el archivo esté en el FormData
            var archivoEnFormData = formData.get('archivo');
            if (!archivoEnFormData || archivoEnFormData.size === 0) {
                console.error('El archivo no está en el FormData o está vacío');
                alert('Error: El archivo no se pudo incluir en el formulario. Por favor, intente nuevamente.');
                return;
            }
            
            // Mostrar información del FormData antes de enviar
            console.log('=== INFORMACIÓN DEL FORMULARIO ===');
            console.log('URL:', '{{ route("mesa-partes.guardar") }}');
            console.log('Método: POST');
            console.log('Content-Type: multipart/form-data (false)');
            console.log('ProcessData: false');
            
            // Iterar sobre FormData para verificar contenido
            console.log('=== CONTENIDO DEL FORMDATA ===');
            for (var pair of formData.entries()) {
                if (pair[1] instanceof File) {
                    console.log(pair[0] + ': [FILE]', {
                        name: pair[1].name,
                        size: pair[1].size,
                        type: pair[1].type,
                        lastModified: pair[1].lastModified
                    });
                } else {
                    console.log(pair[0] + ':', pair[1]);
                }
            }
            
            $.ajax({
                url: '{{ route("mesa-partes.guardar") }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    // Agregar listener para el progreso de carga
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = (evt.loaded / evt.total) * 100;
                            console.log('Progreso de carga:', percentComplete.toFixed(2) + '%');
                        }
                    }, false);
                    return xhr;
                },
                beforeSend: function(xhr) {
                    console.log('=== ENVIANDO REQUEST ===');
                    // Mostrar indicador de carga
                    $('#submitBtn').prop('disabled', true);
                    $('#submitBtn').html('<span class="inline-block animate-spin mr-2">⏳</span> Procesando...');
                },
                success: function(response) {
                    console.log('Respuesta exitosa:', response);
                    if (response.status === 'success') {
                        alert(response.message);
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        } else {
                            $('#tramiteForm')[0].reset();
                            // Limpiar Choices.js
                            if (procedimientoSelect && typeof procedimientoSelect.clearStore === 'function') {
                                procedimientoSelect.clearStore();
                            } else if (procedimientoSelect && typeof procedimientoSelect.removeActiveItems === 'function') {
                                procedimientoSelect.removeActiveItems();
                            } else {
                                $('#id_procedimiento').val('').trigger('change');
                            }
                            $('#submitBtn').prop('disabled', true);
                        }
                    } else {
                        console.error('Error en respuesta:', response);
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error completo:', xhr);
                    console.error('Response JSON:', xhr.responseJSON);
                    console.error('Status:', xhr.status);
                    
                    var errorMessage = 'Ocurrió un error al procesar la solicitud.';
                    var errorDetails = [];
                    
                    if (xhr.responseJSON) {
                        // Si hay mensaje específico
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        // Si hay errores de validación, mostrarlos
                        if (xhr.responseJSON.errors) {
                            errorDetails = [];
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                if (Array.isArray(messages)) {
                                    messages.forEach(function(msg) {
                                        errorDetails.push(msg);
                                    });
                                } else {
                                    errorDetails.push(messages);
                                }
                            });
                            
                            if (errorDetails.length > 0) {
                                errorMessage = 'Errores de validación:\n\n' + errorDetails.join('\n');
                            }
                        }
                    }
                    
                    // Mostrar error detallado
                    alert(errorMessage);
                    
                    // También mostrar en consola para debugging
                    if (errorDetails.length > 0) {
                        console.error('Errores de validación:', errorDetails);
                    }
                }
            });
        });
    });
</script>
@endpush

