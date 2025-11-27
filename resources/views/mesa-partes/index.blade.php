@extends('layouts.app')

@section('title', 'Mesa de Partes - Dashboard')

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
                        <a class="nav-link active" href="{{ route('mesa-partes.index') }}">
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
                <h1 class="h2">Dashboard - Mesa de Partes</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="{{ route('mesa-partes.tramite.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus"></i> Nuevo Trámite
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Trámites Realizados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $estadisticas['tramites_realizados'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Trámites Completados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $estadisticas['tramites_completados'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Trámites en Proceso
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $estadisticas['tramites_proceso'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Trámites Observados
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        {{ $estadisticas['tramites_observados'] ?? 0 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones rápidas -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plus-circle"></i> Acciones Rápidas
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('mesa-partes.tramite.create') }}" class="btn btn-primary">
                                    <i class="fas fa-file-alt"></i> Iniciar Nuevo Trámite
                                </a>
                                <a href="{{ route('mesa-partes.rastrear') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-search"></i> Rastrear un Trámite
                                </a>
                                <a href="{{ route('mesa-partes.documentos-enviados') }}" class="btn btn-outline-info">
                                    <i class="fas fa-list"></i> Ver Mis Documentos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Información Importante
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-lightbulb"></i> Consejos útiles:</h6>
                                <ul class="mb-0">
                                    <li>Verifica todos los datos antes de enviar un trámite</li>
                                    <li>Guarda el número de voucher para futuras consultas</li>
                                    <li>Los trámites observados deben corregirse dentro de 5 días</li>
                                    <li>Puedes rastrear tu trámite en cualquier momento</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trámites recientes -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history"></i> Tus Trámites Recientes
                            </h5>
                            <a href="{{ route('mesa-partes.documentos-enviados') }}" class="btn btn-sm btn-outline-primary">
                                Ver Todos
                            </a>
                        </div>
                        <div class="card-body">
                            @if(isset($tramitesRecientes) && $tramitesRecientes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>N° de Trámite</th>
                                                <th>Tipo de Documento</th>
                                                <th>Asunto</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tramitesRecientes as $tramite)
                                                <tr>
                                                    <td>{{ $tramite->numero_tramite }}</td>
                                                    <td>{{ $tramite->tipoDocumento->nombre ?? '-' }}</td>
                                                    <td>{{ Str::limit($tramite->asunto, 50) }}</td>
                                                    <td>{{ $tramite->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $tramite->estado_color }}">
                                                            {{ $tramite->estado_nombre }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('mesa-partes.rastrear', ['codigo' => $tramite->codigo_seguimiento]) }}" 
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Rastrear">
                                                            <i class="fas fa-search"></i>
                                                        </a>
                                                        @if($tramite->puedeDescargarVoucher())
                                                            <a href="{{ route('mesa-partes.voucher', ['id' => $tramite->id]) }}" 
                                                               class="btn btn-sm btn-outline-secondary"
                                                               title="Descargar Voucher"
                                                               target="_blank">
                                                                <i class="fas fa-download"></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No tienes trámites recientes.</p>
                                    <a href="{{ route('mesa-partes.tramite.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Crear tu primer trámite
                                    </a>
                                </div>
                            @endif
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

    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Actualizar estadísticas cada 30 segundos
        setInterval(function() {
            // Implementar: AJAX para actualizar estadísticas
        }, 30000);
    });
</script>
@endpush