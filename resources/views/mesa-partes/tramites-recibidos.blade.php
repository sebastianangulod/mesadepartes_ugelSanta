@extends('layouts.app')

@section('title', 'Trámites Recibidos')

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
                        <a class="nav-link" href="{{ route('mesa-partes.documentos-enviados') }}">
                            <i class="fas fa-paper-plane"></i> Mis Documentos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('mesa-partes.rastrear') }}">
                            <i class="fas fa-search"></i> Rastrear Trámite
                        </a>
                    </li>
                </ul>
                
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Área Interna</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('mesa-partes.tramites-recibidos') }}">
                            <i class="fas fa-inbox"></i> Trámites Recibidos
                            @if(isset($tramitesPendientes) && $tramitesPendientes > 0)
                                <span class="badge bg-danger">{{ $tramitesPendientes }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <x-alerts />
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Trámites Recibidos</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="actualizarLista()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
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
                    <form method="GET" action="{{ route('mesa-partes.tramites-recibidos') }}">
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
                                    <option value="pendiente_asignacion" {{ request('estado') == 'pendiente_asignacion' ? 'selected' : '' }}>Pendiente de Asignación</option>
                                    <option value="asignado" {{ request('estado') == 'asignado' ? 'selected' : '' }}>Asignado</option>
                                    <option value="en_proceso" {{ request('estado') == 'en_proceso' ? 'selected' : '' }}>En Proceso</option>
                                    <option value="observado" {{ request('estado') == 'observado' ? 'selected' : '' }}>Observado</option>
                                    <option value="por_firmar" {{ request('estado') == 'por_firmar' ? 'selected' : '' }}>Por Firmar</option>
                                    <option value="finalizado" {{ request('estado') == 'finalizado' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="prioridad" class="form-label">Prioridad</label>
                                <select class="form-select" id="prioridad" name="prioridad">
                                    <option value="">Todas las prioridades</option>
                                    <option value="alta" {{ request('prioridad') == 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="media" {{ request('prioridad') == 'media' ? 'selected' : '' }}>Media</option>
                                    <option value="baja" {{ request('prioridad') == 'baja' ? 'selected' : '' }}>Baja</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" 
                                       value="{{ request('fecha_desde') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" 
                                       value="{{ request('fecha_hasta') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="remitente" class="form-label">Remitente</label>
                                <input type="text" class="form-control" id="remitente" name="remitente" 
                                       value="{{ request('remitente') }}" placeholder="Nombre o DNI...">
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="{{ route('mesa-partes.tramites-recibidos') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen de trámites -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Urgentes</h6>
                                    <h3 class="mb-0">{{ $resumen['urgentes'] ?? 0 }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Por Vencer</h6>
                                    <h3 class="mb-0">{{ $resumen['por_vencer'] ?? 0 }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">En Proceso</h6>
                                    <h3 class="mb-0">{{ $resumen['en_proceso'] ?? 0 }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-tasks fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Atendidos Hoy</h6>
                                    <h3 class="mb-0">{{ $resumen['atendidos_hoy'] ?? 0 }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de trámites -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-inbox"></i> Bandeja de Trámites
                    </h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="asignarSeleccionados()">
                            <i class="fas fa-user-check"></i> Asignar Seleccionados
                        </button>
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
                            <table class="table table-hover" id="tabla-tramites">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="seleccionar_todos" 
                                                   onchange="toggleSeleccionarTodos()">
                                        </th>
                                        <th>N° de Trámite</th>
                                        <th>Prioridad</th>
                                        <th>Remitente</th>
                                        <th>Asunto</th>
                                        <th>Fecha de Ingreso</th>
                                        <th>Días Transcurridos</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tramites as $tramite)
                                        <tr class="{{ $tramite->esUrgente() ? 'table-danger' : '' }}">
                                            <td>
                                                <input type="checkbox" class="seleccionar-tramite" 
                                                       value="{{ $tramite->id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $tramite->numero_tramite }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $tramite->codigo_seguimiento }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $tramite->prioridad_color }}">
                                                    {{ ucfirst($tramite->prioridad) }}
                                                </span>
                                                @if($tramite->esUrgente())
                                                    <i class="fas fa-exclamation-triangle text-danger" 
                                                       title="Trámite urgente"></i>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $tramite->remitente_nombre }}
                                                <br>
                                                <small class="text-muted">{{ $tramite->remitente_dni }}</small>
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    {{ Str::limit($tramite->asunto, 80) }}
                                                </div>
                                                @if($tramite->tieneArchivos())
                                                    <small class="text-muted">
                                                        <i class="fas fa-paperclip"></i> Con adjuntos
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $tramite->created_at->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">{{ $tramite->created_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="{{ $tramite->dias_transcurridos > 7 ? 'text-danger' : 'text-muted' }}">
                                                    {{ $tramite->dias_transcurridos }} días
                                                </span>
                                                @if($tramite->estaPorVencer())
                                                    <br>
                                                    <small class="text-warning">
                                                        <i class="fas fa-clock"></i> Por vencer
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $tramite->estado_color }}" 
                                                      data-bs-toggle="tooltip" 
                                                      title="{{ $tramite->estado_descripcion }}">
                                                    {{ $tramite->estado_nombre }}
                                                </span>
                                                @if($tramite->estaAsignado())
                                                    <br>
                                                    <small class="text-muted">
                                                        Asignado a: {{ $tramite->usuario_asignado_nombre }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('mesa-partes.rastrear', ['codigo' => $tramite->codigo_seguimiento]) }}" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($tramite->puedeAsignar())
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-success" 
                                                                onclick="asignarTramite({{ $tramite->id }})"
                                                                title="Asignar trámite">
                                                            <i class="fas fa-user-check"></i>
                                                        </button>
                                                    @endif
                                                    @if($tramite->puedeProcesar())
                                                        <a href="{{ route('mesa-partes.tramite.procesar', ['id' => $tramite->id]) }}" 
                                                           class="btn btn-sm btn-outline-info" 
                                                           title="Procesar trámite">
                                                            <i class="fas fa-cogs"></i>
                                                        </a>
                                                    @endif
                                                    @if($tramite->puedeDescargarVoucher())
                                                        <a href="{{ route('mesa-partes.voucher', ['id' => $tramite->id]) }}" 
                                                           class="btn btn-sm btn-outline-secondary" 
                                                           title="Descargar voucher"
                                                           target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
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
                            <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                            <h5 class="text-muted">No hay trámites en tu bandeja</h5>
                            <p class="text-muted mb-4">
                                @if(request()->hasAny(['numero_tramite', 'estado', 'prioridad', 'fecha_desde', 'fecha_hasta', 'remitente']))
                                    No se encontraron trámites con los filtros aplicados.
                                @else
                                    No tienes trámites asignados actualmente.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal de asignación -->
<div class="modal fade" id="modalAsignar" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarLabel">
                    <i class="fas fa-user-check"></i> Asignar Trámite
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAsignar" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="usuario_asignado_id" class="form-label">
                            Usuario a Asignar <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="usuario_asignado_id" name="usuario_asignado_id" required>
                            <option value="">Seleccione un usuario...</option>
                            @foreach($usuariosDisponibles as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="comentario_asignacion" class="form-label">Comentario (opcional)</label>
                        <textarea class="form-control" id="comentario_asignacion" name="comentario_asignacion" 
                                  rows="3" placeholder="Describa la razón de la asignación..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notificar_por_email" name="notificar_por_email" checked>
                        <label class="form-check-label" for="notificar_por_email">
                            Notificar por email al usuario asignado
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Asignar
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

    #tabla-tramites tbody tr {
        cursor: pointer;
    }

    #tabla-tramites tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
    }

    .table-danger {
        background-color: #f8d7da !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    function actualizarLista() {
        location.reload();
    }

    function exportarExcel() {
        // Implementar: exportación a Excel
        window.location.href = "{{ route('mesa-partes.exportar-excel') }}?" + new URLSearchParams(window.location.search).toString();
    }

    function exportarPDF() {
        // Implementar: exportación a PDF
        window.location.href = "{{ route('mesa-partes.exportar-pdf') }}?" + new URLSearchParams(window.location.search).toString();
    }

    function toggleSeleccionarTodos() {
        var seleccionarTodos = document.getElementById('seleccionar_todos').checked;
        var checkboxes = document.querySelectorAll('.seleccionar-tramite');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = seleccionarTodos;
        });
    }

    function asignarTramite(tramiteId) {
        $('#formAsignar').attr('action', `/mesa-partes/tramites/${tramiteId}/asignar`);
        $('#modalAsignar').modal('show');
    }

    function asignarSeleccionados() {
        var seleccionados = [];
        document.querySelectorAll('.seleccionar-tramite:checked').forEach(function(checkbox) {
            seleccionados.push(checkbox.value);
        });
        
        if (seleccionados.length === 0) {
            alert('Por favor, seleccione al menos un trámite.');
            return;
        }
        
        // Implementar: asignación masiva
        if (confirm(`¿Está seguro de asignar ${seleccionados.length} trámite(s)?`)) {
            // Implementar: lógica de asignación masiva
        }
    }

    $(document).ready(function() {
        // Inicializar tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Click en fila de la tabla
        $('#tabla-tramites tbody tr').click(function(e) {
            // No hacer nada si se hace click en un checkbox o botón
            if ($(e.target).closest('input[type="checkbox"], a, button').length === 0) {
                var codigo = $(this).find('small.text-muted').first().text();
                window.location.href = `/mesa-partes/rastrear?codigo=${codigo}`;
            }
        });

        // Auto-actualización cada 2 minutos
        setInterval(function() {
            actualizarLista();
        }, 120000);
    });
</script>
@endpush