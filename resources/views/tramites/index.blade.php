@extends('layouts.app')

@section('title', 'Trámites - Mesa de Partes')

@section('header')
    <h2 class="text-2xl font-bold text-gray-900">Gestión de Trámites</h2>
    <p class="text-sm text-gray-600">Administra todos los trámites del sistema</p>
@endsection

@section('actions')
    <a href="{{ route('tramites.create') }}" class="btn-primary">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Nuevo Trámite
    </a>
@endsection

@section('content')
    <!-- Filtros y búsqueda -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form id="filtrosForm" method="GET" action="{{ route('tramites.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="busqueda" class="block text-sm font-medium text-gray-700 mb-1">Búsqueda</label>
                    <input type="text" 
                           name="busqueda" 
                           id="busqueda" 
                           value="{{ request('busqueda') }}"
                           placeholder="N° expediente, asunto, solicitante..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="estado" 
                            id="estado"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos los estados</option>
                        @foreach($estados as $estado)
                            <option value="{{ $estado }}" {{ request('estado') == $estado ? 'selected' : '' }}>
                                {{ \App\Domain\Tramites\EstadoTramite::obtenerEtiqueta($estado) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="fecha_desde" class="block text-sm font-medium text-gray-700 mb-1">Fecha desde</label>
                    <input type="date" 
                           name="fecha_desde" 
                           id="fecha_desde" 
                           value="{{ request('fecha_desde') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="fecha_hasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha hasta</label>
                    <input type="date" 
                           name="fecha_hasta" 
                           id="fecha_hasta" 
                           value="{{ request('fecha_hasta') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-4">
                <button type="button" 
                        id="limpiarFiltros"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Limpiar
                </button>
                <button type="submit" 
                        class="btn-primary">
                    Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de trámites -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Listado de Trámites</h3>
            <div class="text-sm text-gray-500">
                Mostrando {{ $tramites->firstItem() ?? 0 }} - {{ $tramites->lastItem() ?? 0 }} de {{ $tramites->total() }} resultados
            </div>
        </div>
        
        @if($tramites->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <a href="{{ request()->fullUrlWithQuery(['ordenar' => 'numero_expediente', 'direccion' => request('direccion') == 'asc' ? 'desc' : 'asc']) }}" 
                                   class="flex items-center hover:text-gray-700">
                                    N° Expediente
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                    </svg>
                                </a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Asunto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Solicitante
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fecha
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($tramites as $tramite)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $tramite->numero_expediente }}
                                    </div>
                                    @if($tramite->estaVencido())
                                        <div class="text-xs text-red-600 font-medium">
                                            ⚠ Vencido
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 font-medium">
                                        {{ Str::limit($tramite->asunto, 50) }}
                                    </div>
                                    @if($tramite->procedimiento)
                                        <div class="text-xs text-gray-500">
                                            {{ $tramite->procedimiento->nombre }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $tramite->usuario->nombre_completo }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $tramite->usuario->dni }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge {{ $tramite->estado_clases_css }}">
                                        {{ $tramite->estado_etiqueta }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($tramite->fecha_creacion)
                                        {{ $tramite->fecha_creacion->format('d/m/Y H:i') }}
                                    @elseif($tramite->created_at)
                                        {{ $tramite->created_at->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('tramites.show', $tramite->id) }}" 
                                           class="text-blue-600 hover:text-blue-900"
                                           title="Ver detalles">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        
                                        @can('update', $tramite)
                                            <a href="{{ route('tramites.edit', $tramite->id) }}" 
                                               class="text-green-600 hover:text-green-900"
                                               title="Editar">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endcan
                                        
                                        @if(auth()->user()->esPersonalMesaPartes())
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" 
                                                        class="text-gray-600 hover:text-gray-900"
                                                        title="Acciones rápidas">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                    </svg>
                                                </button>
                                                
                                                <div x-show="open" 
                                                     @click.away="open = false"
                                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                                                     style="display: none;">
                                                    <div class="py-1">
                                                        @if($tramite->estaPendiente())
                                                            <button onclick="aceptarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Aceptar trámite
                                                            </button>
                                                            <button onclick="observarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Observar trámite
                                                            </button>
                                                            <button onclick="rechazarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                                Rechazar trámite
                                                            </button>
                                                        @elseif($tramite->estaEnProceso())
                                                            <button onclick="verificarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Verificar trámite
                                                            </button>
                                                            <button onclick="observarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Observar trámite
                                                            </button>
                                                            <button onclick="rechazarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                                Rechazar trámite
                                                            </button>
                                                        @elseif($tramite->estaVerificado())
                                                            <button onclick="finalizarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-green-600 hover:bg-gray-100">
                                                                Finalizar trámite
                                                            </button>
                                                            <button onclick="observarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Observar trámite
                                                            </button>
                                                            <button onclick="rechazarTramite({{ $tramite->id }})" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                                                Rechazar trámite
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $tramites->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron trámites</h3>
                <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros de búsqueda.</p>
                <div class="mt-6">
                    <a href="{{ route('tramites.create') }}" class="btn-primary">
                        Crear nuevo trámite
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        // Limpiar filtros
        document.getElementById('limpiarFiltros').addEventListener('click', function() {
            document.getElementById('filtrosForm').reset();
            window.location.href = '{{ route('tramites.index') }}';
        });
        
        // Funciones para acciones de trámites
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
        
        // Búsqueda en tiempo real (opcional)
        let searchTimeout;
        document.getElementById('busqueda').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                document.getElementById('filtrosForm').submit();
            }, 500);
        });
    </script>
@endsection