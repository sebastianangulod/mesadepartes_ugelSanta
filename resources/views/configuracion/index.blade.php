@extends('layouts.app')

@section('title', 'Configuración - General')

@section('content')
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">Configuración del Sistema</h1>
            <p class="text-muted">Gestione los parámetros generales del sistema</p>
        </div>
    </div>

    <!-- Menú de navegación de configuración -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('configuracion.index') }}">
                                <i class="fas fa-cog"></i> General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('configuracion.correo') }}">
                                <i class="fas fa-envelope"></i> Correo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('configuracion.notificaciones') }}">
                                <i class="fas fa-bell"></i> Notificaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('configuracion.institucion') }}">
                                <i class="fas fa-building"></i> Institución
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('configuracion.seguridad') }}">
                                <i class="fas fa-shield-alt"></i> Seguridad
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('configuracion.respaldo') }}">
                                <i class="fas fa-database"></i> Respaldo
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de configuración general -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Parámetros Generales</h6>
                </div>
                <div class="card-body">
                    <form id="configuracion-general-form" method="POST" action="{{ route('configuracion.actualizar') }}">
                        @csrf
                        
                        <!-- Configuración de trámites -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">Configuración de Trámites</h5>
                                
                                <div class="form-group">
                                    <label for="plazo_atencion">Plazo de Atención (días hábiles)</label>
                                    <input type="number" class="form-control" id="plazo_atencion" name="plazo_atencion" 
                                           value="{{ old('plazo_atencion', config('sistema.plazo_atencion', 15)) }}" min="1" max="30">
                                    <small class="form-text text-muted">Número de días hábiles para atender un trámite</small>
                                </div>

                                <div class="form-group">
                                    <label for="max_documentos">Máximo de Documentos por Trámite</label>
                                    <input type="number" class="form-control" id="max_documentos" name="max_documentos" 
                                           value="{{ old('max_documentos', config('sistema.max_documentos', 10)) }}" min="1" max="20">
                                    <small class="form-text text-muted">Número máximo de documentos que se pueden adjuntar</small>
                                </div>

                                <div class="form-group">
                                    <label for="tamano_max_archivo">Tamaño Máximo de Archivo (MB)</label>
                                    <input type="number" class="form-control" id="tamano_max_archivo" name="tamano_max_archivo" 
                                           value="{{ old('tamano_max_archivo', config('sistema.tamano_max_archivo', 10)) }}" min="1" max="50">
                                    <small class="form-text text-muted">Tamaño máximo permitido por archivo en megabytes</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3">Configuración de Sistema</h5>
                                
                                <div class="form-group">
                                    <label for="hora_inicio_atencion">Hora de Inicio de Atención</label>
                                    <input type="time" class="form-control" id="hora_inicio_atencion" name="hora_inicio_atencion" 
                                           value="{{ old('hora_inicio_atencion', config('sistema.hora_inicio_atencion', '08:00')) }}">
                                    <small class="form-text text-muted">Hora de inicio del horario de atención</small>
                                </div>

                                <div class="form-group">
                                    <label for="hora_fin_atencion">Hora de Fin de Atención</label>
                                    <input type="time" class="form-control" id="hora_fin_atencion" name="hora_fin_atencion" 
                                           value="{{ old('hora_fin_atencion', config('sistema.hora_fin_atencion', '16:00')) }}">
                                    <small class="form-text text-muted">Hora de fin del horario de atención</small>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="permitir_tramites_virtual" 
                                               name="permitir_tramites_virtual" 
                                               {{ old('permitir_tramites_virtual', config('sistema.permitir_tramites_virtual', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permitir_tramites_virtual">
                                            Permitir Trámites Virtuales
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Habilitar/deshabilitar trámites en línea</small>
                                </div>
                            </div>
                        </div>

                        <!-- Configuración de notificaciones -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Configuración de Notificaciones</h5>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="notificar_nuevo_tramite" 
                                               name="notificar_nuevo_tramite" 
                                               {{ old('notificar_nuevo_tramite', config('sistema.notificar_nuevo_tramite', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notificar_nuevo_tramite">
                                            Notificar Nuevo Trámite
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Enviar notificación cuando se registre un nuevo trámite</small>
                                </div>

                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="notificar_cambio_estado" 
                                               name="notificar_cambio_estado" 
                                               {{ old('notificar_cambio_estado', config('sistema.notificar_cambio_estado', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notificar_cambio_estado">
                                            Notificar Cambio de Estado
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Notificar al solicitante cuando cambie el estado del trámite</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="notificar_tramite_vencido" 
                                               name="notificar_tramite_vencido" 
                                               {{ old('notificar_tramite_vencido', config('sistema.notificar_tramite_vencido', true)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="notificar_tramite_vencido">
                                            Notificar Trámites Vencidos
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Alertar sobre trámites que estén por vencer o vencidos</small>
                                </div>

                                <div class="form-group">
                                    <label for="frecuencia_notificaciones">Frecuencia de Notificaciones</label>
                                    <select class="form-control" id="frecuencia_notificaciones" name="frecuencia_notificaciones">
                                        <option value="inmediata" {{ old('frecuencia_notificaciones', config('sistema.frecuencia_notificaciones', 'inmediata')) == 'inmediata' ? 'selected' : '' }}>
                                            Inmediata
                                        </option>
                                        <option value="diaria" {{ old('frecuencia_notificaciones', config('sistema.frecuencia_notificaciones', 'inmediata')) == 'diaria' ? 'selected' : '' }}>
                                            Diaria
                                        </option>
                                        <option value="semanal" {{ old('frecuencia_notificaciones', config('sistema.frecuencia_notificaciones', 'inmediata')) == 'semanal' ? 'selected' : '' }}>
                                            Semanal
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i> Restablecer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Validación del formulario
    $('#configuracion-general-form').on('submit', function(e) {
        e.preventDefault();
        
        // Validar que la hora de fin sea mayor que la hora de inicio
        let horaInicio = $('#hora_inicio_atencion').val();
        let horaFin = $('#hora_fin_atencion').val();
        
        if (horaInicio >= horaFin) {
            alert('La hora de fin debe ser mayor que la hora de inicio');
            return false;
        }
        
        // Enviar formulario mediante AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('Configuración guardada exitosamente');
            },
            error: function(xhr) {
                alert('Error al guardar la configuración');
                console.error(xhr.responseText);
            }
        });
    });
});
</script>
@endsection