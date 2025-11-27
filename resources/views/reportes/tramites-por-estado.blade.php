@extends('layouts.app')

@section('title', 'Reporte de Trámites por Estado')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Reporte de Trámites por Estado
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filtros del reporte -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin">Fecha Fin:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="procedimiento_id">Procedimiento:</label>
                            <select id="procedimiento_id" name="procedimiento_id" class="form-control">
                                <option value="">Todos los procedimientos</option>
                                <!-- Las opciones se cargarán dinámicamente -->
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div class="form-group">
                                <button type="button" id="btn_generar_reporte" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Generar Reporte
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de trámites por estado -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Distribución de Trámites por Estado</h4>
                                </div>
                                <div class="card-body">
                                    <canvas id="grafico_tramites_estado" style="height: 400px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Resumen por Estado</h4>
                                </div>
                                <div class="card-body">
                                    <div id="resumen_estados">
                                        <!-- Los datos se cargarán dinámicamente -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de detalles -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Detalle de Trámites por Estado</h4>
                                    <div class="card-tools">
                                        <button type="button" id="btn_exportar_excel" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-excel"></i> Exportar Excel
                                        </button>
                                        <button type="button" id="btn_exportar_pdf" class="btn btn-danger btn-sm">
                                            <i class="fas fa-file-pdf"></i> Exportar PDF
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tabla_tramites_estado" class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Estado</th>
                                                    <th>Cantidad</th>
                                                    <th>Porcentaje</th>
                                                    <th>Tiempo Promedio (días)</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Los datos se cargarán dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
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
    // Inicializar gráfico
    let graficoEstado = null;
    
    // Función para cargar y mostrar el reporte
    function cargarReporte() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        const procedimientoId = $('#procedimiento_id').val();
        
        // Mostrar indicador de carga
        $('#resumen_estados').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
        
        $.ajax({
            url: '{{ route("api.reportes.tramites-por-estado") }}',
            method: 'GET',
            data: {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                procedimiento_id: procedimientoId
            },
            success: function(response) {
                actualizarGrafico(response.datos);
                actualizarTabla(response.datos);
                actualizarResumen(response.datos);
            },
            error: function() {
                $('#resumen_estados').html('<div class="alert alert-danger">Error al cargar el reporte</div>');
            }
        });
    }
    
    // Función para actualizar el gráfico
    function actualizarGrafico(datos) {
        const ctx = document.getElementById('grafico_tramites_estado').getContext('2d');
        
        if (graficoEstado) {
            graficoEstado.destroy();
        }
        
        graficoEstado = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: datos.map(item => item.estado),
                datasets: [{
                    data: datos.map(item => item.cantidad),
                    backgroundColor: [
                        '#ffc107', // Amarillo - Pendiente
                        '#17a2b8', // Cyan - En Proceso
                        '#28a745', // Verde - Atendido
                        '#dc3545', // Rojo - Rechazado
                        '#6f42c1', // Púrpura - Observado
                        '#fd7e14'  // Naranja - Anulado
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
    }
    
    // Función para actualizar la tabla
    function actualizarTabla(datos) {
        const tbody = $('#tabla_tramites_estado tbody');
        tbody.empty();
        
        const total = datos.reduce((sum, item) => sum + item.cantidad, 0);
        
        datos.forEach(item => {
            const porcentaje = total > 0 ? ((item.cantidad / total) * 100).toFixed(2) : 0;
            const fila = `
                <tr>
                    <td><span class="badge" style="background-color: ${item.color}">${item.estado}</span></td>
                    <td>${item.cantidad}</td>
                    <td>${porcentaje}%</td>
                    <td>${item.tiempo_promedio || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verDetalleEstado('${item.estado}')">
                            <i class="fas fa-eye"></i> Ver Detalle
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
    
    // Función para actualizar el resumen
    function actualizarResumen(datos) {
        const total = datos.reduce((sum, item) => sum + item.cantidad, 0);
        let html = '<div class="info-box">';
        html += '<span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>';
        html += '<div class="info-box-content">';
        html += '<span class="info-box-text">Total de Trámites</span>';
        html += '<span class="info-box-number">' + total + '</span>';
        html += '</div></div>';
        
        datos.forEach(item => {
            const porcentaje = total > 0 ? ((item.cantidad / total) * 100).toFixed(1) : 0;
            html += `
                <div class="progress-group">
                    <span class="progress-text">${item.estado}</span>
                    <span class="progress-number"><b>${item.cantidad}</b>/${total}</span>
                    <div class="progress sm">
                        <div class="progress-bar" style="width: ${porcentaje}%; background-color: ${item.color};"></div>
                    </div>
                </div>
            `;
        });
        
        $('#resumen_estados').html(html);
    }
    
    // Función para ver detalle de un estado
    window.verDetalleEstado = function(estado) {
        // Implementar lógica para mostrar detalle de trámites en un modal
        alert('Función en desarrollo: Ver detalle de trámites con estado ' + estado);
    };
    
    // Eventos
    $('#btn_generar_reporte').click(cargarReporte);
    
    $('#btn_exportar_excel').click(function() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        const procedimientoId = $('#procedimiento_id').val();
        
        window.open(`{{ route('reportes.exportar-excel') }}?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&procedimiento_id=${procedimientoId}&tipo=estado`, '_blank');
    });
    
    $('#btn_exportar_pdf').click(function() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();
        const procedimientoId = $('#procedimiento_id').val();
        
        window.open(`{{ route('reportes.exportar-pdf') }}?fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&procedimiento_id=${procedimientoId}&tipo=estado`, '_blank');
    });
    
    // Cargar reporte inicial
    cargarReporte();
});
</script>
@endsection