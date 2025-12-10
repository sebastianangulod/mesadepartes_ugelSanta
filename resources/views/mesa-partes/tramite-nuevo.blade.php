@extends('layouts.app')

@section('title', 'Nuevo Trámite - Mesa de Partes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white">
                    <span>Mesa de Partes UGEL</span>
                </h6>
                <div class="mb-4 px-3">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                        </button>
                    </form>
                </div>
                <div class="mb-4 px-3 text-center text-white">
                    <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <p class="mb-0">{{ Auth::user()->nombres }} {{ Auth::user()->apellido_paterno }}</p>
                    <p class="mb-0 small">
                        @if(Auth::user()->tipo_persona == 1)
                            Persona Natural
                        @elseif(Auth::user()->tipo_persona == 2)
                            Persona Jurídica
                        @elseif(Auth::user()->tipo_persona == 3)
                            Entidad Pública
                        @endif
                    </p>
                </div>
                <ul class="nav flex-column px-3">
                    @if(Auth::user()->tipo_user == 1)
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white active" href="{{ route('mesa-partes.nuevo') }}">
                                <i class="fas fa-plus"></i> Trámite Nuevo
                            </a>
                        </li>
                    @endif
                    
                    @if(Auth::user()->es_administrador)
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="{{ route('dashboard') }}">
                                <i class="fas fa-cog"></i> Administrador
                            </a>
                        </li>
                    @endif
                    
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="{{ route('mesa-partes.tramites-recibidos') }}">
                            <i class="fas fa-inbox"></i> Recepción de Trámites
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="{{ route('mesa-partes.rastrear') }}">
                            <i class="fas fa-search"></i> Rastrear Trámites
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a class="nav-link text-white" href="{{ route('mesa-partes.documentos-enviados') }}">
                            <i class="fas fa-paper-plane"></i> Documentos Enviados
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
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
            
            var selectedValue = $('#id_procedimiento').val();
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
                            $('#id_procedimiento').val('').trigger('change');
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

