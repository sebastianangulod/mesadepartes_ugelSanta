@extends('layouts.app')

@section('title', 'Detalle de Trámite - Mesa de Partes')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Detalle de Trámite</h2>
            <p class="text-sm text-gray-600">Información completa del trámite</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('tramites.index') }}" class="btn-secondary">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver
            </a>
            @can('update', $tramite)
                <a href="{{ route('tramites.edit', $tramite->id) }}" class="btn-primary">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
            @endcan
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Información principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Información básica -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información del Trámite</h3>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Número de Expediente</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-medium">
                            {{ $tramite->numero_expediente ?? 'MP-' . str_pad($tramite->id, 6, '0', STR_PAD_LEFT) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estado</dt>
                        <dd class="mt-1">
                            <span class="status-badge {{ $tramite->estado_clases_css }}">
                                {{ $tramite->estado_etiqueta }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Asunto</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->asunto ?? 'Sin asunto' }}
                        </dd>
                    </div>
                    @if($tramite->descripcion)
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->descripcion }}
                        </dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tipo de Trámite</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->tipo_tramite ?? 'VIRTUAL' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Creación</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($tramite->fecha_creacion)
                                {{ $tramite->fecha_creacion->format('d/m/Y H:i') }}
                            @elseif($tramite->created_at)
                                {{ $tramite->created_at->format('d/m/Y H:i') }}
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    @if($tramite->fecha_presentacion)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Presentación</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($tramite->fecha_presentacion)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->fecha_finalizacion)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Finalización</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($tramite->fecha_finalizacion)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>

            <!-- Procedimiento -->
            @if($tramite->procedimiento)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Procedimiento</h3>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->procedimiento->nombre }}
                        </dd>
                    </div>
                    @if($tramite->procedimiento->codigo)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Código</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->procedimiento->codigo }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->procedimiento->descripcion)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Descripción</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->procedimiento->descripcion }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            @endif

            <!-- Observaciones -->
            @if($tramite->observaciones)
            <div class="bg-yellow-50 rounded-lg shadow-sm border border-yellow-200 p-6">
                <h3 class="text-lg font-semibold text-yellow-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Observaciones
                </h3>
                <p class="text-sm text-yellow-800">{{ $tramite->observaciones }}</p>
                @if($tramite->limite_observacion)
                <p class="text-xs text-yellow-600 mt-2">
                    <strong>Límite para corrección:</strong> {{ $tramite->limite_observacion->format('d/m/Y') }}
                </p>
                @endif
            </div>
            @endif

            <!-- Motivo de Rechazo -->
            @if($tramite->motivo_rechazo || $tramite->estaRechazado())
            <div class="bg-red-50 rounded-lg shadow-sm border border-red-200 p-6">
                <h3 class="text-lg font-semibold text-red-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Motivo de Rechazo
                </h3>
                <p class="text-sm text-red-800">{{ $tramite->motivo_rechazo ?? 'No especificado' }}</p>
                @if($tramite->fecha_rechazo)
                <p class="text-xs text-red-600 mt-2">
                    <strong>Fecha de rechazo:</strong> {{ \Carbon\Carbon::parse($tramite->fecha_rechazo)->format('d/m/Y H:i') }}
                </p>
                @endif
            </div>
            @endif

            <!-- Archivos -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Archivos Adjuntos</h3>
                <div class="space-y-3">
                    @if($tramite->archivo_adjunto || $tramite->archivo_expediente)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $tramite->nombre_archivo_adjunto ?? 'Archivo adjunto' }}
                                    </p>
                                    <p class="text-xs text-gray-500">Expediente</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . ($tramite->archivo_adjunto ?? $tramite->archivo_expediente)) }}" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver
                            </a>
                        </div>
                    @endif
                    @if($tramite->voucher || $tramite->archivo_voucher)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $tramite->nombre_voucher ?? 'Voucher' }}
                                    </p>
                                    <p class="text-xs text-gray-500">Comprobante de pago</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . ($tramite->voucher ?? $tramite->archivo_voucher)) }}" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver
                            </a>
                        </div>
                    @endif
                    @if((!$tramite->archivo_adjunto && !$tramite->archivo_expediente) && (!$tramite->voucher && !$tramite->archivo_voucher))
                        <p class="text-sm text-gray-500">No hay archivos adjuntos</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Información del Solicitante -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitante</h3>
                @if($tramite->usuario)
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nombre Completo</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->usuario->nombre_completo }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">DNI</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->usuario->dni }}
                        </dd>
                    </div>
                    @if($tramite->usuario->email)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->usuario->email }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->usuario->telefono)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Teléfono</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->usuario->telefono }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->usuario->direccion)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dirección</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->usuario->direccion }}
                        </dd>
                    </div>
                    @endif
                </dl>
                @else
                    <p class="text-sm text-gray-500">Información no disponible</p>
                @endif
            </div>

            <!-- Acciones Rápidas -->
            @if(auth()->user()->esPersonalMesaPartes())
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                <div class="space-y-2">
                    @if($tramite->estaPendiente())
                        <button onclick="aceptarTramite({{ $tramite->id }})" 
                                class="w-full btn-success text-left">
                            Aceptar Trámite
                        </button>
                        <button onclick="observarTramite({{ $tramite->id }})" 
                                class="w-full btn-warning text-left">
                            Observar Trámite
                        </button>
                        <button onclick="rechazarTramite({{ $tramite->id }})" 
                                class="w-full btn-danger text-left">
                            Rechazar Trámite
                        </button>
                    @elseif($tramite->estaEnProceso())
                        <button onclick="verificarTramite({{ $tramite->id }})" 
                                class="w-full btn-success text-left">
                            Verificar Trámite
                        </button>
                        <button onclick="observarTramite({{ $tramite->id }})" 
                                class="w-full btn-warning text-left">
                            Observar Trámite
                        </button>
                        <button onclick="rechazarTramite({{ $tramite->id }})" 
                                class="w-full btn-danger text-left">
                            Rechazar Trámite
                        </button>
                    @elseif($tramite->estaVerificado())
                        <button onclick="finalizarTramite({{ $tramite->id }})" 
                                class="w-full btn-success text-left">
                            Finalizar Trámite
                        </button>
                        <button onclick="observarTramite({{ $tramite->id }})" 
                                class="w-full btn-warning text-left">
                            Observar Trámite
                        </button>
                    @endif
                </div>
            </div>
            @endif

            <!-- Información Adicional -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Adicional</h3>
                <dl class="space-y-3">
                    @if($tramite->numero_voucher)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Número de Voucher</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->numero_voucher }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->periodo)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Período</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->periodo }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->oficio)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Oficio</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->oficio }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->revisor)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Revisado por</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $tramite->revisor->nombre_completo }}
                        </dd>
                    </div>
                    @endif
                    @if($tramite->fecha_revision)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Fecha de Revisión</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($tramite->fecha_revision)->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function aceptarTramite(id) {
            if (confirm('¿Está seguro de aceptar este trámite?')) {
                fetch(`{{ url('tramites') }}/${id}/aceptar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        }
        
        function rechazarTramite(id) {
            const motivo = prompt('Ingrese el motivo del rechazo:');
            if (motivo) {
                fetch(`{{ url('tramites') }}/${id}/rechazar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ motivo: motivo })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        }
        
        function observarTramite(id) {
            const observacion = prompt('Ingrese la observación:');
            if (observacion) {
                fetch(`{{ url('tramites') }}/${id}/observar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ observacion: observacion })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        }
        
        function verificarTramite(id) {
            if (confirm('¿Está seguro de verificar este trámite?')) {
                fetch(`{{ url('tramites') }}/${id}/verificar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        }
        
        function finalizarTramite(id) {
            if (confirm('¿Está seguro de finalizar este trámite?')) {
                fetch(`{{ url('tramites') }}/${id}/finalizar`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                });
            }
        }
    </script>
@endsection

