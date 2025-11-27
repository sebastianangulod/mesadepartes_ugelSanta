@extends('layouts.app')

@section('title', 'Mis Documentos Enviados')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Mesa de Partes</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mesa-partes.index') }}">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mesa-partes.tramite.create') }}">
                            <i class="fas fa-file-alt"></i> Nuevo Trámite
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('mesa-partes.documentos-enviados') }}">
                            <i class="fas fa-paper-plane"></i> Mis Documentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mesa-partes.rastrear') }}">
                            <i class="fas fa-search"></i> Rastrear Trámite
                        </a>
                    </li>
                </ul>
                
                @if(auth()->user()->esInterno())
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Área Interna</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mesa-partes.tramites-recibidos') }}">
                            <i class="fas fa-inbox"></i> Trámites Recibidos
                            @if(isset($tramitesPendientes) && $tramitesPendientes > 0)
                                <span class="badge bg-danger">{{ $tramitesPendientes }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
                @endif
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Mis Documentos Enviados</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('mesa-partes.tramite.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Nuevo Trámite
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filtros de Búsqueda
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('mesa-partes.documentos-enviados') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="numero_tramite" class="form-label">N° de Trámite</label>
                                <input type="text" class="form-control" id="numero_tramite" name="numero_tramite" 
                                       value="{{ request('numero_tramite') }}" placeholder="Ej: 2024-000001">
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="observado" {{ request('estado') == 'observado' ? 'selected' : '' }}>Observado</option>
                                    <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                                    <option value="rechazado" {{ request('estado') == 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                       value="{{ request('fecha_desde') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                       value="{{ request('fecha_hasta') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="asunto" class="form-label">Asunto</label>
                                <input type="text" class="form-control" id="asunto" name="asunto" 
                                       value="{{ request('asunto') }}" placeholder="Buscar por asunto...">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="{{ route('mesa-partes.documentos-enviados') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de documentos -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Lista de Documentos
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportarPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($tramites->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="tabla-documentos">
                                <thead>
                                    <tr>
                                        <th>N° de Trámite</th>
                                        <th>Tipo de Documento</th>
                                        <th>Asunto</th>
                                        <th>Fecha de Envío</th>
                                        <th>Estado</th>
                                        <th>Última Actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tramites as $tramite)
                                        <tr>
                                            <td>
                                                <strong>{{ $tramite->numero_tramite }}</strong>
                                                <br>
                                                <small class="text-muted">Código: {{ $tramite->codigo_seguimiento }}</small>
                                            </td>
                                            <td>
                                                {{ $tramite->tipoDocumento->nombre ?? 'No especificado' }}
                                                <br>
                                                <small class="text-muted">{{ $tramite->procedimiento->nombre ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    {{ Str::limit($tramite->asunto, 100) }}
                                                </div>
                                            </td>
                                            <td>
                                                {{ $tramite->created_at->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">{{ $tramite->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $tramite->estado_color }}" 
                                                      data-bs-toggle="tooltip" 
                                                      title="{{ $tramite->estado_descripcion }}">
                                                    {{ $tramite->estado_nombre }}
                                                </span>
                                                @if($tramite->estaObservado())
                                                    <br>
                                                    <small class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> 
                                                        Requiere atención
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $tramite->updated_at->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">{{ $tramite->updated_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('mesa-partes.rastrear', ['codigo' => $tramite->codigo_seguimiento]) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Rastrear trámite">
                                                        <i class="fas fa-search"></i>
                                                    </a>
                                                    @if($tramite->puedeDescargarVoucher())
                                                        <a href="{{ route('mesa-partes.voucher', ['id' => $tramite->id]) }}" 
                                                           class="btn btn-sm btn-outline-secondary" 
                                                           title="Descargar voucher"
                                                           target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endif
                                                    @if($tramite->puedeDescargarHojaRuta())
                                                        <a href="{{ route('mesa-partes.hoja-ruta', ['id' => $tramite->id]) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="Descargar hoja de ruta"
                                                           target="_blank">
                                                            <i class="fas fa-route"></i>
                                                        </a>
                                                    @endif
                                                    @if($tramite->estaObservado() && $tramite->puedeSubsanar())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-warning" 
                                                                onclick="abrirModalSubsanacion({{ $tramite->id }})"
                                                                title="Subsanar observación">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <small class="text-muted">
                                    Mostrando {{ $tramites->firstItem() }} - {{ $tramites->lastItem() }} de {{ $tramites->total() }} resultados
                                </small>
                            </div>
                            <div>
                                {{ $tramites->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-alt fa-4x text-muted mb-4"></i>
                            <h5 class="text-muted">No tienes documentos registrados</h5>
                            <p class="text-muted mb-4">
                                @if(request()->hasAny(['numero_tramite', 'estado', 'fecha_desde', 'fecha_hasta', 'asunto']))
                                    No se encontraron trámites con los filtros aplicados.
                                @else
                                    Aún no has realizado ningún trámite en el sistema.
                                @endif
                            </p>
                            <a href="{{ route('mesa-partes.tramite.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Realizar mi primer trámite
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal de subsanación -->
<div class="modal fade" id="modalSubsanacion" tabindex="-1" aria-labelledby="modalSubsanacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSubsanacionLabel">
                    <i class="fas fa-edit"></i> Subsanar Observación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formSubsanacion" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div id="observacionActual" class="alert alert-warning mb-3">
                        <!-- Se llenará dinámicamente -->
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentario_subsanacion" class="form-label">
                            Comentario de Subsanación <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="comentario_subsanacion" name="comentario_subsanacion" 
                                  rows="4" required placeholder="Describa cómo se subsanó la observación..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="archivos_subsanacion" class="form-label">Adjuntar Archivos (opcional)</label>
                        <input type="file" class="form-control" id="archivos_subsanacion" name="archivos_subsanacion[]" 
                               multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">
                            Puede adjuntar hasta 5 archivos. Formatos permitidos: PDF, DOC, DOCX, JPG, PNG
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enviar Subsanación
                    </button>
                </div>
            </form>
        </div>
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
    }

    .sidebar .nav-link {
        font-weight: 500;
        color: #333;
    }

    .sidebar .nav-link.active {
        color: #007bff;
    }

    .sidebar-heading {
        font-size: .75rem;
        text-transform: uppercase;
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

    #tabla-documentos tbody tr {
        cursor: pointer;
    }

    #tabla-documentos tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
    }
</style>
@endpush

@push('scripts')
<script>
    function exportarExcel() {
        // Implementar: exportación a Excel
        window.location.href = "{{ route('mesa-partes.exportar-excel') }}?" + new URLSearchParams(window.location.search).toString();
    }

    function exportarPDF() {
        // Implementar: exportación a PDF
        window.location.href = "{{ route('mesa-partes.exportar-pdf') }}?" + new URLSearchParams(window.location.search).toString();
    }

    function abrirModalSubsanacion(tramiteId) {
        // Implementar: cargar datos del trámite y observación
        $('#formSubsanacion').attr('action', `/mesa-partes/tramites/${tramiteId}/subsanar`);
        $('#modalSubsanacion').modal('show');
    }

    $(document).ready(function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Click en fila de la tabla
        $('#tabla-documentos tbody tr').click(function(e) {
            // No hacer nada si se hace click en un botón
            if ($(e.target).closest('a, button').length === 0) {
                var codigo = $(this).find('small.text-muted').first().text().replace('Código: ', '');
                window.location.href = `/mesa-partes/rastrear?codigo=${codigo}`;
            }
        });

        // Validación del formulario de subsanación
        $('#formSubsanacion').on('submit', function(e) {
            var archivos = $('#archivos_subsanacion')[0].files;
            if (archivos.length > 5) {
                e.preventDefault();
                alert('No puede adjuntar más de 5 archivos.');
                return false;
            }
        });
    });
</script>
@endpush