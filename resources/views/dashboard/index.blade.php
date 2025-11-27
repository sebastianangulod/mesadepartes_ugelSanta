@extends('layouts.app')

@section('title', 'Dashboard - Mesa de Partes')

@section('content')
<div class="container-fluid">
    <!-- Encabezado del Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-tachometer-alt"></i> Dashboard - Mesa de Partes
                </h1>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="actualizarDashboard()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <a href="{{ route('tramites.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Nuevo Trámite
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="row">
        <!-- Total de Trámites -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Trámites
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total_tramites">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trámites Pendientes -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Trámites Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="tramites_pendientes">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trámites en Proceso -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                En Proceso
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="tramites_proceso">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-spinner fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trámites Finalizados -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Finalizados Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="tramites_finalizados">
                                <i class="fas fa-spinner fa-spin"></i>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos y Tablas -->
    <div class="row">
        <!-- Gráfico de Trámites por Mes -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-line"></i> Trámites por Mes
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="grafico_tramites_mes" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribución por Estado -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribución por Estado
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="grafico_estados" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tablas de Trámites -->
    <div class="row">
        <!-- Trámites Recientes -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Trámites Recientes
                    </h6>
                    <a href="{{ route('tramites.index') }}" class="btn btn-sm btn-primary">Ver Todos</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tabla_tramites_recientes">
                            <thead class="thead-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Procedimiento</th>
                                    <th>Solicitante</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trámites Pendientes -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Trámites Pendientes
                    </h6>
                    <a href="{{ route('tramites.pendientes') }}" class="btn btn-sm btn-warning">Ver Todos</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="tabla_tramites_pendientes">
                            <thead class="thead-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Procedimiento</th>
                                    <th>Días</th>
                                    <th>Prioridad</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i> Cargando...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas y Notificaciones -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-bell"></i> Alertas y Notificaciones
                    </h6>
                </div>
                <div class="card-body">
                    <div id="contenedor_alertas">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Cargando alertas...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Inicializar dashboard
    inicializarDashboard();
    
    // Actualizar cada 30 segundos
    setInterval(actualizarDashboard, 30000);
});

// Función para inicializar el dashboard
function inicializarDashboard() {
    cargarResumenTramites();
    cargarGraficoTramitesMes();
    cargarGraficoEstados();
    cargarTramitesRecientes();
    cargarTramitesPendientes();
    cargarAlertas();
}

// Función para actualizar todo el dashboard
function actualizarDashboard() {
    inicializarDashboard();
    toastr.success('Dashboard actualizado');
}

// Función para cargar el resumen de trámites
function cargarResumenTramites() {
    $.ajax({
        url: '{{ route("api.dashboard.estadisticas") }}',
        method: 'GET',
        success: function(response) {
            $('#total_tramites').text(response.total_tramites);
            $('#tramites_pendientes').text(response.pendientes);
            $('#tramites_proceso').text(response.en_proceso);
            $('#tramites_finalizados').text(response.finalizados);
        },
        error: function() {
            $('#total_tramites').text('Error');
            $('#tramites_pendientes').text('Error');
            $('#tramites_proceso').text('Error');
            $('#tramites_finalizados').text('Error');
        }
    });
}

// Función para cargar el gráfico de trámites por mes
function cargarGraficoTramitesMes() {
    $.ajax({
        url: '{{ route("api.dashboard.tramites-por-mes") }}',
        method: 'GET',
        success: function(response) {
            const ctx = document.getElementById('grafico_tramites_mes').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: response.labels,
                    datasets: [{
                        label: 'Trámites',
                        data: response.values,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        error: function() {
            $('#grafico_tramites_mes').html('<div class="text-center text-danger">Error al cargar gráfico</div>');
        }
    });
}

// Función para cargar el gráfico de estados
function cargarGraficoEstados() {
    $.ajax({
        url: '{{ route("api.dashboard.tramites-por-estado") }}',
        method: 'GET',
        success: function(response) {
            const ctx = document.getElementById('grafico_estados').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: response.labels,
                    datasets: [{
                        data: response.values,
                        backgroundColor: [
                            '#ffc107', // Amarillo - Pendientes
                            '#17a2b8', // Cyan - En Proceso
                            '#28a745', // Verde - Finalizados
                            '#dc3545', // Rojo - Observados
                            '#6c757d'  // Gris - Anulados
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        },
        error: function() {
            $('#grafico_estados').html('<div class="text-center text-danger">Error al cargar gráfico</div>');
        }
    });
}

// Función para cargar trámites recientes
function cargarTramitesRecientes() {
    $.ajax({
        url: '{{ route("api.dashboard.tramites-recientes") }}',
        method: 'GET',
        success: function(response) {
            const tbody = $('#tabla_tramites_recientes tbody');
            tbody.empty();
            
            if (response.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center">No hay trámites recientes</td></tr>');
                return;
            }
            
            response.forEach(tramite => {
                const fila = `
                    <tr>
                        <td><a href="{{ route('tramites.index') }}/${tramite.id}">${tramite.codigo}</a></td>
                        <td>${tramite.procedimiento}</td>
                        <td>${tramite.solicitante}</td>
                        <td><span class="badge badge-${obtenerClaseEstado(tramite.estado)}">${tramite.estado}</span></td>
                        <td>${tramite.fecha}</td>
                    </tr>
                `;
                tbody.append(fila);
            });
        },
        error: function() {
            $('#tabla_tramites_recientes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar datos</td></tr>');
        }
    });
}

// Función para cargar trámites pendientes
function cargarTramitesPendientes() {
    $.ajax({
        url: '{{ route("api.dashboard.tramites-pendientes") }}',
        method: 'GET',
        success: function(response) {
            const tbody = $('#tabla_tramites_pendientes tbody');
            tbody.empty();
            
            if (response.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center">No hay trámites pendientes</td></tr>');
                return;
            }
            
            response.forEach(tramite => {
                const fila = `
                    <tr>
                        <td><a href="{{ route('tramites.index') }}/${tramite.id}">${tramite.codigo}</a></td>
                        <td>${tramite.procedimiento}</td>
                        <td><span class="badge badge-${tramite.dias > 5 ? 'danger' : 'warning'}">${tramite.dias} días</span></td>
                        <td><span class="badge badge-${obtenerClasePrioridad(tramite.prioridad)}">${tramite.prioridad}</span></td>
                        <td>
                            <a href="{{ route('tramites.index') }}/${tramite.id}/edit" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                        </td>
                    </tr>
                `;
                tbody.append(fila);
            });
        },
        error: function() {
            $('#tabla_tramites_pendientes tbody').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar datos</td></tr>');
        }
    });
}

// Función para cargar alertas
function cargarAlertas() {
    $.ajax({
        url: '{{ route("api.dashboard.alertas") }}',
        method: 'GET',
        success: function(response) {
            const contenedor = $('#contenedor_alertas');
            
            if (response.length === 0) {
                contenedor.html('<div class="alert alert-info">No hay alertas pendientes</div>');
                return;
            }
            
            let html = '';
            response.forEach(alerta => {
                html += `
                    <div class="alert alert-${alerta.tipo} alert-dismissible fade show" role="alert">
                        <strong><i class="fas fa-${alerta.icono}"></i> ${alerta.titulo}</strong>
                        ${alerta.mensaje}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
            });
            
            contenedor.html(html);
        },
        error: function() {
            $('#contenedor_alertas').html('<div class="alert alert-danger">Error al cargar alertas</div>');
        }
    });
}

// Funciones auxiliares
function obtenerClaseEstado(estado) {
    const clases = {
        'pendiente': 'warning',
        'en_proceso': 'info',
        'atendido': 'success',
        'rechazado': 'danger',
        'observado': 'secondary'
    };
    return clases[estado] || 'dark';
}

function obtenerClasePrioridad(prioridad) {
    const clases = {
        'baja': 'secondary',
        'media': 'primary',
        'alta': 'warning',
        'urgente': 'danger'
    };
    return clases[prioridad] || 'dark';
}
</script>
@endsection