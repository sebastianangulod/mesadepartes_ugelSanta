@extends('layouts.app')

@section('title', 'Nuevo Trámite - Mesa de Partes')

@section('content')
<div class="flex">
    <x-mesa-partes-sidebar :tramitesPendientes="0" />
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-8">
            <x-alerts />
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Mesa de Partes Virtual</h1>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header bg-purple text-white">
                            <h5 class="card-title mb-0">Datos del Expediente</h5>
                        </div>
                        <div class="card-body">
                            <form id="tramiteForm" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="action" value="crear_tramite">
                                
                                <div class="mb-4">
                                    <label for="id_procedimiento" class="form-label">
                                        Procedimiento <span class="text-danger">*</span>
                                    </label>
                                    <select id="id_procedimiento" name="id_procedimiento" class="form-select" required>
                                        <option value="" disabled selected>Seleccionar un procedimiento</option>
                                        @foreach($procedimientos as $procedimiento)
                                            <option value="{{ $procedimiento->id }}">
                                                {{ $procedimiento->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="archivo" class="form-label">
                                        Adjuntar Archivo (PDF) <span class="text-danger">*</span>
                                    </label>
                                    <input type="file" id="archivo" name="archivo" class="form-control" required accept=".pdf">
                                    <div id="archivoError" class="text-danger d-none">
                                        Por favor, seleccione un archivo PDF.
                                    </div>
                                    <small class="form-text text-muted">
                                        Tamaño máximo: 10MB. Solo archivos PDF.
                                    </small>
                                </div>

                                <input type="hidden" name="tipo_tramite" value="{{ $tipoTramite }}">
                                
                                <button type="submit" id="submitBtn" class="btn btn-primary" disabled>
                                    <i class="fas fa-save"></i> Crear Trámite
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
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
        }

        // Validar archivo PDF
        $('#archivo').change(function() {
            var file = this.files[0];
            var fileType = file ? file.type : '';
            var match = ['application/pdf'];
            
            if (!file || !match.includes(fileType)) {
                $('#archivoError').removeClass('d-none');
                $('#submitBtn').prop('disabled', true);
            } else {
                $('#archivoError').addClass('d-none');
                $('#submitBtn').prop('disabled', false);
            }
        });

        // Enviar formulario
        $('#tramiteForm').submit(function(e) {
            e.preventDefault();
            
            // Obtener valor seleccionado de Choices.js
            var selectedValue = null;
            if (procedimientoSelect && typeof procedimientoSelect.getValue === 'function') {
                selectedValue = procedimientoSelect.getValue(true); // true para obtener solo el value
            } else {
                selectedValue = $('#id_procedimiento').val();
            }
            
            if (!selectedValue) {
                alert('Debe seleccionar un procedimiento.');
                return;
            }

            var formData = new FormData(this);
            
            $.ajax({
                url: '{{ route("mesa-partes.guardar") }}',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
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
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Ocurrió un error al procesar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                }
            });
        });
    });
</script>
@endpush

