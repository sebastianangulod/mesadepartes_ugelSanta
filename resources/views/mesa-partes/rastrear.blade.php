@extends('layouts.app')

@section('title', 'Rastrear Trámite - Mesa de Partes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-search"></i> Rastrear Trámite
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Formulario de búsqueda -->
                    <form id="searchForm" method="POST" action="{{ route('mesa-partes.rastrear.buscar') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="numeroVoucher" class="form-label">Número de Voucher</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="numeroVoucher" 
                                           name="numero_voucher" 
                                           placeholder="Ingrese el número de voucher"
                                           value="{{ request('numero_voucher') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="tipoProcedimiento" class="form-label">Tipo de Procedimiento</label>
                                    <select id="tipoProcedimiento" name="tipo_procedimiento" class="form-select">
                                        <option value="">Todos</option>
                                        @foreach($procedimientos as $procedimiento)
                                            <option value="{{ $procedimiento->id }}" {{ request('tipo_procedimiento') == $procedimiento->id ? 'selected' : '' }}>
                                                {{ $procedimiento->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select id="estado" name="estado" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="OBSERVADO" {{ request('estado') == 'OBSERVADO' ? 'selected' : '' }}>Observado</option>
                                        <option value="EN_PROCESO" {{ request('estado') == 'EN_PROCESO' ? 'selected' : '' }}>En Proceso</option>
                                        <option value="VERIFICADO" {{ request('estado') == 'VERIFICADO' ? 'selected' : '' }}>Verificado</option>
                                        <option value="FINALIZADO" {{ request('estado') == 'FINALIZADO' ? 'selected' : '' }}>Finalizado</option>
                                        <option value="RECHAZADO" {{ request('estado') == 'RECHAZADO' ? 'selected' : '' }}>Rechazado</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="fechaDesde" class="form-label">Fecha Desde</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fechaDesde" 
                                           name="fecha_desde" 
                                           value="{{ request('fecha_desde') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="fechaHasta" class="form-label">Fecha Hasta</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fechaHasta" 
                                           name="fecha_hasta" 
                                           value="{{ request('fecha_hasta') }}">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda -->
    <div id="resultadosBusqueda" class="mt-4">
        @if(isset($tramites) && $tramites->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resultados de Búsqueda</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>N° Trámite</th>
                                    <th>Procedimiento</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Voucher</th>
                                    <th>Hoja de Ruta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tramites as $tramite)
                                    <tr>
                                        <td>{{ $tramite->id }}</td>
                                        <td>{{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}</td>
                                        <td>{{ $tramite->procedimiento->nombre ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $tramite->estado == 'PENDIENTE' ? 'warning' : ($tramite->estado == 'FINALIZADO' ? 'success' : 'info') }}">
                                                {{ $tramite->estado }}
                                            </span>
                                        </td>
                                        <td>{{ $tramite->fecha_creacion->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($tramite->numero_voucher)
                                                <a href="{{ route('mesa-partes.voucher', $tramite->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                                    <i class="fas fa-download"></i> Voucher
                                                </a>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button onclick="verHojaRuta({{ $tramite->id }})" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-route"></i> Ver
                                            </button>
                                        </td>
                                        <td>
                                            <a href="{{ route('mesa-partes.voucher', $tramite->id) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                <i class="fas fa-eye"></i> Ver
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @elseif(isset($tramites) && $tramites->count() == 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se encontraron trámites con los criterios de búsqueda especificados.
            </div>
        @endif
    </div>

    <!-- Indicador de carga -->
    <div id="loadingIndicator" class="hidden text-center py-4">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Buscando...</span>
        </div>
        <p class="mt-2 text-muted">Buscando trámites...</p>
    </div>

    <!-- Modal Hoja de Ruta -->
    <div id="hojaRutaModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hoja de Ruta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tbody id="hr-tbody">
                            <!-- Se llenará dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda (versión antigua, mantener por compatibilidad) -->
    <div id="resultados" class="row" style="display: none;">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i> Información del Trámite
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Información del trámite -->
                    <div id="info-tramite" class="mb-4">
                        <!-- Se llenará dinámicamente con JavaScript -->
                    </div>

                    <!-- Historial de estados -->
                    <div id="historial-estados" class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-history"></i> Historial de Estados
                        </h4>
                        <div class="timeline">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>

                    <!-- Documentos adjuntos -->
                    <div id="documentos-adjuntos" class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-paperclip"></i> Documentos Adjuntos
                        </h4>
                        <div class="list-group">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>

                    <!-- Acciones disponibles -->
                    <div id="acciones-disponibles" class="mb-4">
                        <h4 class="mb-3">
                            <i class="fas fa-cogs"></i> Acciones Disponibles
                        </h4>
                        <div class="btn-group" role="group">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje de no encontrado -->
    <div id="no-encontrado" class="row" style="display: none;">
        <div class="col-12">
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Trámite no encontrado.</strong> 
                Por favor, verifique el número de trámite y el DNI del solicitante.
            </div>
        </div>
    </div>
</div>

<!-- Modal para subsanar observación -->
<div class="modal fade" id="modalSubsanar" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Subsanar Observación
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formSubsanar" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="id_tramite_subsanar" name="id_tramite">
                    
                    <div class="form-group">
                        <label for="archivo_corregido">Archivo Corregido (PDF)</label>
                        <input type="file" 
                               class="form-control" 
                               id="archivo_corregido" 
                               name="archivo_corregido" 
                               accept=".pdf" 
                               required>
                        <small class="form-text text-muted">
                            Solo se aceptan archivos PDF de máximo 10MB
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="observaciones_subsanar">Observaciones (Opcional)</label>
                        <textarea class="form-control" 
                                  id="observaciones_subsanar" 
                                  name="observaciones" 
                                  rows="3"
                                  placeholder="Describa los cambios realizados"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Subsanar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Configurar token CSRF para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Función para ver hoja de ruta
    window.verHojaRuta = function(idTramite) {
        fetch('{{ url("/mesa-partes/hoja-ruta") }}/' + idTramite, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '';
                if (data.niveles && data.niveles.length > 0) {
                    data.niveles.forEach(function(nivel) {
                        html += `<tr>
                            <th>Nivel ${nivel.nivel}</th>
                            <td>${nivel.nombre || '-'}</td>
                        </tr>`;
                    });
                }
                $('#hr-tbody').html(html);
                $('#hojaRutaModal').modal('show');
            } else {
                alert('Error al cargar la hoja de ruta');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la hoja de ruta');
        });
    };

    // Manejar envío del formulario de búsqueda
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                // Mostrar indicador de carga
                $('#resultados').hide();
                $('#no-encontrado').hide();
                
                // Deshabilitar botón mientras se procesa
                $('button[type="submit"]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
            },
            success: function(response) {
                if (response.success && response.tramite) {
                    mostrarResultados(response.tramite);
                    $('#resultados').show();
                } else {
                    $('#no-encontrado').show();
                }
            },
            error: function(xhr) {
                let mensaje = 'Error al buscar el trámite.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                
                // Mostrar mensaje de error en el bloque de alerta estandarizado
                $('#no-encontrado .alert').text(mensaje);
                $('#no-encontrado').show();
            },
            complete: function() {
                // Rehabilitar botón
                $('button[type="submit"]').prop('disabled', false)
                    .html('<i class="fas fa-search"></i> Buscar Trámite');
            }
        });
    });

    // Función para mostrar resultados
    function mostrarResultados(tramite) {
        // Mostrar información del trámite
        $('#info-tramite').html(`
            <div class="row">
                <div class="col-md-6">
                    <h5><strong>Número de Trámite:</strong> ${tramite.numero_tramite}</h5>
                    <p><strong>Fecha de Registro:</strong> ${tramite.fecha_registro}</p>
                    <p><strong>Procedimiento:</strong> ${tramite.procedimiento_nombre}</p>
                    <p><strong>Tipo de Trámite:</strong> ${tramite.tipo_tramite}</p>
                </div>
                <div class="col-md-6">
                    <h5><strong>Solicitante:</strong> ${tramite.solicitante_nombre}</h5>
                    <p><strong>DNI:</strong> ${tramite.solicitante_dni}</p>
                    <p><strong>Estado Actual:</strong> 
                        <span class="badge badge-${getEstadoColor(tramite.estado)}">
                            ${tramite.estado}
                        </span>
                    </p>
                    ${tramite.numero_voucher ? `<p><strong>Número de Voucher:</strong> ${tramite.numero_voucher}</p>` : ''}
                </div>
            </div>
        `);

        // Mostrar historial de estados
        if (tramite.historial && tramite.historial.length > 0) {
            let historialHtml = '';
            tramite.historial.forEach(function(estado, index) {
                historialHtml += `
                    <div class="timeline-item ${index === 0 ? 'active' : ''}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h6>${estado.estado}</h6>
                            <p>${estado.fecha}</p>
                            ${estado.observaciones ? `<p class="text-muted">${estado.observaciones}</p>` : ''}
                        </div>
                    </div>
                `;
            });
            $('#historial-estados .timeline').html(historialHtml);
        }

        // Mostrar documentos adjuntos
        if (tramite.archivo_adjunto) {
            $('#documentos-adjuntos .list-group').html(`
                <a href="${tramite.archivo_url}" target="_blank" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-pdf"></i> ${tramite.archivo_adjunto}
                </a>
            `);
        }

        // Mostrar acciones disponibles
        if (tramite.acciones_permitidas && tramite.acciones_permitidas.length > 0) {
            let accionesHtml = '';
            tramite.acciones_permitidas.forEach(function(accion) {
                switch(accion) {
                    case 'CORREGIR':
                        accionesHtml += `
                            <button type="button" 
                                    class="btn btn-warning" 
                                    onclick="prepararSubsanar(${tramite.id})">
                                <i class="fas fa-edit"></i> Subsanar Observación
                            </button>
                        `;
                        break;
                    case 'VERIFICAR':
                        accionesHtml += `
                            <button type="button" 
                                    class="btn btn-info" 
                                    onclick="verificarTramite(${tramite.id})">
                                <i class="fas fa-check"></i> Verificar
                            </button>
                        `;
                        break;
                }
            });
            $('#acciones-disponibles .btn-group').html(accionesHtml);
        }
    }

    // Función auxiliar para obtener color según estado
    function getEstadoColor(estado) {
        const colores = {
            'PENDIENTE': 'warning',
            'EN_PROCESO': 'info',
            'OBSERVADO': 'danger',
            'VERIFICADO': 'success',
            'FINALIZADO': 'primary',
            'RECHAZADO': 'dark'
        };
        return colores[estado] || 'secondary';
    }

    // Preparar subsanación
    window.prepararSubsanar = function(idTramite) {
        $('#id_tramite_subsanar').val(idTramite);
        $('#modalSubsanar').modal('show');
    };

    // Manejar envío del formulario de subsanación
    $('#formSubsanar').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: '{{ route("mesa-partes.rastrear.subsanar") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#modalSubsanar button[type="submit"]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
            },
            success: function(response) {
                if (response.success) {
                    alert('Trámite subsanado exitosamente.');
                    $('#modalSubsanar').modal('hide');
                    // Recargar resultados
                    $('#searchForm').submit();
                } else {
                    alert(response.message || 'Error al subsanar el trámite.');
                }
            },
            error: function(xhr) {
                let mensaje = 'Error al procesar la subsanación.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensaje = xhr.responseJSON.message;
                }
                alert(mensaje);
            },
            complete: function() {
                $('#modalSubsanar button[type="submit"]').prop('disabled', false)
                    .html('<i class="fas fa-upload"></i> Subsanar');
            }
        });
    });
});
</script>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    bottom: auto;
    height: 20px;
}

.timeline-marker {
    position: absolute;
    left: 10px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #6c757d;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item.active .timeline-marker {
    background-color: #007bff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
}

.timeline-content p {
    margin-bottom: 2px;
    font-size: 0.9em;
    color: #6c757d;
}
</style>
@endpush