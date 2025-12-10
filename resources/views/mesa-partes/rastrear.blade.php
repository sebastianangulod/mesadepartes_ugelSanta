{{--
    Vista de Rastrear Trámites
    Equivalente a rastrearTramites.php del PHP puro
    Para usuarios externos e internos
--}}
@extends('layouts.app')

@section('title', 'Rastrear Trámites - Mesa de Partes')

@section('content')
<div class="flex">
    <x-mesa-partes-sidebar :tramitesPendientes="$tramitesPendientes ?? 0" />

    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-8">
        <x-alerts />
        
        <h2 class="text-3xl font-bold mb-6">Rastrear Trámites</h2>
        
        <div class="bg-white rounded-lg shadow p-6">
            <form id="searchForm" class="mb-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="numeroVoucher" class="block text-sm font-medium text-gray-700 mb-1">Número de Voucher</label>
                        <input type="text" id="numeroVoucher" name="numero_voucher" 
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               placeholder="Ingrese el número de voucher"
                               value="{{ request('numero_voucher') }}">
                    </div>
                    <div>
                        <label for="tipoProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Procedimiento</label>
                        <select id="tipoProcedimiento" name="tipo_procedimiento" 
                                class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            @foreach($procedimientos as $procedimiento)
                                <option value="{{ $procedimiento->id }}" {{ request('tipo_procedimiento') == $procedimiento->id ? 'selected' : '' }}>
                                    {{ $procedimiento->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="estado" name="estado" class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="PENDIENTE" {{ request('estado') == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                            <option value="OBSERVADO" {{ request('estado') == 'OBSERVADO' ? 'selected' : '' }}>Observado</option>
                            <option value="EN_PROCESO" {{ request('estado') == 'EN_PROCESO' ? 'selected' : '' }}>En Proceso</option>
                            <option value="VERIFICADO" {{ request('estado') == 'VERIFICADO' ? 'selected' : '' }}>Verificado</option>
                            <option value="FINALIZADO" {{ request('estado') == 'FINALIZADO' ? 'selected' : '' }}>Finalizado</option>
                            <option value="RECHAZADO" {{ request('estado') == 'RECHAZADO' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                    </div>
                    <div>
                        <label for="fechaDesde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                        <input type="date" id="fechaDesde" name="fecha_desde" 
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="{{ request('fecha_desde') }}">
                    </div>
                    <div>
                        <label for="fechaHasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                        <input type="date" id="fechaHasta" name="fecha_hasta" 
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                            Buscar
                        </button>
                    </div>
                </div>
            </form>

            <!-- Resultados de búsqueda -->
            <div id="resultadosBusqueda" class="mt-4">
                @if(isset($tramites) && $tramites->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procedimiento</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($tramites as $tramite)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $tramite->numero_voucher ?? ($tramite->id . '-' . ($tramite->periodo ?? date('Y'))) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $tramite->procedimiento->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $tramite->estado == 'PENDIENTE' ? 'bg-yellow-100 text-yellow-800' : ($tramite->estado == 'FINALIZADO' ? 'bg-green-100 text-green-800' : ($tramite->estado == 'OBSERVADO' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) }}">
                                            {{ $tramite->estado }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $tramite->fecha_creacion->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="verHojaRuta({{ $tramite->id }})" class="text-blue-600 hover:text-blue-900 mr-3">
                                            Ver Hoja de Ruta
                                        </button>
                                        @if($tramite->estado === 'OBSERVADO')
                                        <button onclick="mostrarModalCorregir({{ $tramite->id }})" class="text-yellow-600 hover:text-yellow-900">
                                            Corregir
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif(isset($tramites) && $tramites->count() == 0)
                    <div class="text-center py-4 text-gray-500">
                        No se encontraron trámites que coincidan con los criterios de búsqueda.
                    </div>
                @endif
            </div>

            <!-- Indicador de carga -->
            <div id="loadingIndicator" class="hidden text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Buscando trámites...</p>
            </div>
        </div>
    </main>
</div>

<!-- Modal Hoja de Ruta -->
<div id="hojaRutaModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Hoja de Ruta</h3>
            <button onclick="cerrarHojaRuta()" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="mt-2">
            <table class="min-w-full bg-white border border-gray-300">
                <tbody>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Expediente N°</th>
                        <td id="hr-expediente" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Fecha de Registro</th>
                        <td id="hr-fecha" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Solicitante</th>
                        <td id="hr-solicitante" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Procedimiento</th>
                        <td id="hr-procedimiento" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Área</th>
                        <td id="hr-area" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Subárea</th>
                        <td id="hr-subarea" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Especialidad</th>
                        <td id="hr-especialidad" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Estado</th>
                        <td id="hr-estado" class="py-2 px-4 border"></td>
                    </tr>
                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Folios</th>
                        <td id="hr-folios" class="py-2 px-4 border"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Corregir Trámite -->
<div id="modalCorregir" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">Corregir Trámite</h3>
            <button onclick="cerrarModalCorregir()" class="text-gray-600 hover:text-gray-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formCorregir" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_tramite" id="corregir_id_tramite">
            <div class="mb-4">
                <label for="corregir_archivo" class="block text-sm font-medium text-gray-700 mb-1">Archivo Corregido (PDF)</label>
                <input type="file" name="archivo_adjunto" id="corregir_archivo" accept="application/pdf" 
                       class="w-full p-2 border rounded-lg" required />
            </div>
            <div id="corregirMensaje" class="mb-4 text-sm"></div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="cerrarModalCorregir()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Enviar Corrección
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const resultadosBusqueda = document.getElementById('resultadosBusqueda');
        const loadingIndicator = document.getElementById('loadingIndicator');

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            buscarTramites();
        });

        function buscarTramites() {
            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);

            loadingIndicator.classList.remove('hidden');
            resultadosBusqueda.innerHTML = '';

            fetch('{{ route("mesa-partes.rastrear.buscar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: params
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.classList.add('hidden');
                if (data.status === 'success') {
                    mostrarResultados(data.tramites);
                } else {
                    mostrarError(data.message || 'No se encontraron resultados');
                }
            })
            .catch(error => {
                loadingIndicator.classList.add('hidden');
                mostrarError('Error al buscar trámites');
                console.error('Error:', error);
            });
        }

        function mostrarResultados(tramites) {
            if (tramites.length === 0) {
                resultadosBusqueda.innerHTML = `
                    <div class="text-center py-4 text-gray-500">
                        No se encontraron trámites que coincidan con los criterios de búsqueda.
                    </div>
                `;
                return;
            }

            const tabla = document.createElement('table');
            tabla.className = 'min-w-full divide-y divide-gray-200';
            tabla.innerHTML = `
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procedimiento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                </tbody>
            `;

            const tbody = tabla.querySelector('tbody');
            tramites.forEach(tramite => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tramite.numero_voucher}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tramite.procedimiento_nombre}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${obtenerClaseEstado(tramite.estado)}">
                            ${tramite.estado}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tramite.fecha_formateada}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <button onclick="verHojaRuta(${tramite.id})" class="text-blue-600 hover:text-blue-900 mr-3">
                            Ver Hoja de Ruta
                        </button>
                        ${tramite.estado === 'OBSERVADO' ? `<button onclick="mostrarModalCorregir(${tramite.id})" class="text-yellow-600 hover:text-yellow-900">Corregir</button>` : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });

            resultadosBusqueda.innerHTML = '';
            resultadosBusqueda.appendChild(tabla);
        }

        function mostrarError(mensaje) {
            resultadosBusqueda.innerHTML = `
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">${mensaje}</span>
                </div>
            `;
        }

        function obtenerClaseEstado(estado) {
            const clases = {
                'PENDIENTE': 'bg-yellow-100 text-yellow-800',
                'OBSERVADO': 'bg-red-100 text-red-800',
                'EN_PROCESO': 'bg-blue-100 text-blue-800',
                'VERIFICADO': 'bg-green-100 text-green-800',
                'FINALIZADO': 'bg-gray-100 text-gray-800',
                'RECHAZADO': 'bg-red-200 text-red-900'
            };
            return clases[estado] || 'bg-gray-100 text-gray-800';
        }

        window.verHojaRuta = function(idTramite) {
            fetch('{{ url("/mesa-partes/hoja-ruta") }}/' + idTramite, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('hr-expediente').textContent = data.tramite.id;
                    document.getElementById('hr-fecha').textContent = data.tramite.fecha_creacion;
                    document.getElementById('hr-solicitante').textContent = 
                        `${data.tramite.nombres} ${data.tramite.apellido_paterno} ${data.tramite.apellido_materno}`;
                    document.getElementById('hr-procedimiento').textContent = data.tramite.procedimiento_nombre;
                    document.getElementById('hr-area').textContent = data.tramite.area_nombre || '-';
                    document.getElementById('hr-subarea').textContent = data.tramite.subarea_nombre || '-';
                    document.getElementById('hr-especialidad').textContent = data.tramite.especialidad_nombre || '-';
                    document.getElementById('hr-estado').textContent = data.tramite.estado;
                    document.getElementById('hr-folios').textContent = data.tramite.tramitescol || '1';
                    
                    document.getElementById('hojaRutaModal').classList.remove('hidden');
                } else {
                    alert('Error al cargar la hoja de ruta');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar la hoja de ruta');
            });
        };

        window.cerrarHojaRuta = function() {
            document.getElementById('hojaRutaModal').classList.add('hidden');
        };

        window.mostrarModalCorregir = function(idTramite) {
            document.getElementById('corregir_id_tramite').value = idTramite;
            document.getElementById('modalCorregir').classList.remove('hidden');
        };

        window.cerrarModalCorregir = function() {
            document.getElementById('modalCorregir').classList.add('hidden');
            document.getElementById('formCorregir').reset();
            document.getElementById('corregirMensaje').textContent = '';
        };

        document.getElementById('formCorregir').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const mensajeDiv = document.getElementById('corregirMensaje');
            mensajeDiv.textContent = '';
            
            fetch('{{ route("mesa-partes.rastrear.buscar") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success' || data.success) {
                    mensajeDiv.textContent = 'Archivo corregido exitosamente.';
                    mensajeDiv.className = 'text-green-600';
                    setTimeout(() => {
                        cerrarModalCorregir();
                        location.reload();
                    }, 1500);
                } else {
                    mensajeDiv.textContent = data.message || 'Error al corregir el trámite.';
                    mensajeDiv.className = 'text-red-600';
                }
            })
            .catch(() => {
                mensajeDiv.textContent = 'Error al enviar la corrección.';
                mensajeDiv.className = 'text-red-600';
            });
        });
    });
</script>
@endpush
@endsection
