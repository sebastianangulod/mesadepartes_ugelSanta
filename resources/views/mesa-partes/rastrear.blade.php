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
                    <form id="formRastrear" method="POST" action="{{ route('mesa-partes.rastrear.buscar') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="numero_tramite">Número de Trámite</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="numero_tramite" 
                                           name="numero_tramite" 
                                           placeholder="Ingrese el número de trámite (ej: 123-2024)"
                                           required>
                                    <small class="form-text text-muted">
                                        Formato: Número-Año (ej: 123-2024)
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dni_solicitante">DNI del Solicitante (Opcional)</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="dni_solicitante" 
                                           name="dni_solicitante" 
                                           placeholder="Ingrese el DNI del solicitante"
                                           maxlength="8">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Buscar Trámite
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-eraser"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados de búsqueda -->
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

    // Manejar envío del formulario de búsqueda
    $('#formRastrear').on('submit', function(e) {
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
                    $('#formRastrear').submit();
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