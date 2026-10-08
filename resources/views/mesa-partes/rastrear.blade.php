{{--
    Vista de Rastrear Trámites
    Equivalente a rastrearTramites.php del PHP puro
    Para usuarios externos e internos
--}}
@extends('layouts.mesa-partes')

@section('title', 'Rastrear Trámites - Mesa de Partes')

@section('header', 'Rastrear Trámites')

@section('content')
        
        <div class="bg-white rounded-lg shadow p-6">
            <form id="searchForm" class="mb-6">
                @csrf
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Buscar por</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                    <div>
                        <label for="numeroTramite" class="block text-sm font-medium text-gray-700 mb-1">N° de Trámite</label>
                        <input type="number" id="numeroTramite" name="numero_tramite" min="1"
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ej. 4">
                    </div>
                    <div>
                        <label for="numeroVoucher" class="block text-sm font-medium text-gray-700 mb-1">Número de Voucher</label>
                        <input type="text" id="numeroVoucher" name="numero_voucher"
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Ingrese el número de voucher">
                    </div>
                    <div>
                        <label for="dniRemitente" class="block text-sm font-medium text-gray-700 mb-1">DNI del Remitente</label>
                        <input type="text" id="dniRemitente" name="dni_remitente" maxlength="8" inputmode="numeric"
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="8 dígitos">
                    </div>
                </div>
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Filtrar por</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="tipoProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Procedimiento</label>
                        <select id="tipoProcedimiento" name="tipo_procedimiento"
                                class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            @foreach($procedimientos as $procedimiento)
                                <option value="{{ $procedimiento->id }}">
                                    {{ $procedimiento->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="estado" name="estado" class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <option value="PENDIENTE">Pendiente</option>
                            <option value="EN_PROCESO">En Proceso</option>
                            <option value="OBSERVADO">Observado</option>
                            <option value="VERIFICADO">Verificado</option>
                            <option value="FINALIZADO">Finalizado</option>
                            <option value="RECHAZADO">Rechazado</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="fechaDesde" class="block text-sm font-medium text-gray-700 mb-1">Fecha Desde</label>
                            <input type="date" id="fechaDesde" name="fecha_desde"
                                   class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" max="">
                        </div>
                        <div>
                            <label for="fechaHasta" class="block text-sm font-medium text-gray-700 mb-1">Fecha Hasta</label>
                            <input type="date" id="fechaHasta" name="fecha_hasta"
                                   class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" max="" min="">
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                    <button type="submit"
                            class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                        Buscar
                    </button>
                    <button type="button" id="btnLimpiar"
                            class="w-full bg-gray-200 text-gray-800 py-3 px-4 rounded-lg hover:bg-gray-300 transition-colors">
                        Limpiar
                    </button>
                </div>
            </form>

            <!-- Resultados de búsqueda -->
            <div id="resultadosBusqueda" class="mt-4">
                <div class="text-center py-4 text-gray-500">
                    Use los filtros para buscar trámites.
                </div>
            </div>

            <!-- Indicador de carga -->
            <div id="loadingIndicator" class="hidden text-center py-4">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                <p class="mt-2 text-gray-600">Buscando trámites...</p>
            </div>
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
                <tbody id="hr-niveles-tbody">
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

        // Configurar límites de fecha al cargar la página
        const fechaDesdeInput = document.getElementById('fechaDesde');
        const fechaHastaInput = document.getElementById('fechaHasta');
        const fechaActual = new Date().toISOString().split('T')[0];
        
        // Establecer fecha máxima (hoy) para ambos campos
        if (fechaDesdeInput) fechaDesdeInput.setAttribute('max', fechaActual);
        if (fechaHastaInput) fechaHastaInput.setAttribute('max', fechaActual);
        
        // Validar cuando cambia la fecha "desde"
        if (fechaDesdeInput) {
            fechaDesdeInput.addEventListener('change', function() {
                const fechaDesde = this.value;
                if (fechaDesde) {
                    // Establecer fecha mínima de "hasta" igual a "desde"
                    if (fechaHastaInput) {
                        fechaHastaInput.setAttribute('min', fechaDesde);
                        
                        // Si "hasta" es menor que "desde", actualizarla
                        if (fechaHastaInput.value && fechaHastaInput.value < fechaDesde) {
                            fechaHastaInput.value = fechaDesde;
                        }
                    }
                } else {
                    // Si no hay fecha "desde", quitar el mínimo de "hasta"
                    if (fechaHastaInput) fechaHastaInput.removeAttribute('min');
                }
            });
        }
        
        // Validar cuando cambia la fecha "hasta"
        if (fechaHastaInput) {
            fechaHastaInput.addEventListener('change', function() {
                const fechaHasta = this.value;
                const fechaDesde = fechaDesdeInput ? fechaDesdeInput.value : '';
                
                if (fechaDesde && fechaHasta && fechaHasta < fechaDesde) {
                    alert('La fecha "Hasta" debe ser igual o posterior a la fecha "Desde"');
                    this.value = fechaDesde;
                }
            });
        }
        
        // Validar fechas antes de enviar el formulario
        function validarFechas() {
            const fechaDesde = fechaDesdeInput ? fechaDesdeInput.value : '';
            const fechaHasta = fechaHastaInput ? fechaHastaInput.value : '';
            
            // Validar que no sean fechas futuras
            if (fechaDesde && fechaDesde > fechaActual) {
                alert('La fecha "Desde" no puede ser superior a la fecha actual');
                if (fechaDesdeInput) fechaDesdeInput.value = '';
                return false;
            }
            
            if (fechaHasta && fechaHasta > fechaActual) {
                alert('La fecha "Hasta" no puede ser superior a la fecha actual');
                if (fechaHastaInput) fechaHastaInput.value = '';
                return false;
            }
            
            // Validar que "hasta" sea >= "desde"
            if (fechaDesde && fechaHasta && fechaHasta < fechaDesde) {
                alert('La fecha "Hasta" debe ser igual o posterior a la fecha "Desde"');
                if (fechaHastaInput) fechaHastaInput.value = fechaDesde;
                return false;
            }
            
            return true;
        }

        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar fechas antes de buscar
            if (!validarFechas()) {
                return;
            }
            
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
                            ${formatearEstado(tramite.estado)}
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
                'EN_PROCESO': 'bg-blue-100 text-blue-800',
                'OBSERVADO': 'bg-orange-100 text-orange-800',
                'VERIFICADO': 'bg-purple-100 text-purple-800',
                'FINALIZADO': 'bg-green-100 text-green-800',
                'RECHAZADO': 'bg-red-100 text-red-800'
            };
            return clases[estado] || 'bg-gray-100 text-gray-800';
        }

        function formatearEstado(estado) {
            if (!estado) return '';
            
            const etiquetas = {
                'PENDIENTE': 'Pendiente',
                'OBSERVADO': 'Observado',
                'EN_PROCESO': 'En Proceso',
                'VERIFICADO': 'Verificado',
                'FINALIZADO': 'Finalizado',
                'RECHAZADO': 'Rechazado'
            };
            
            if (etiquetas[estado]) {
                return etiquetas[estado];
            }
            
            return estado
                .replace(/_/g, ' ')
                .toLowerCase()
                .split(' ')
                .map(palabra => palabra.charAt(0).toUpperCase() + palabra.slice(1))
                .join(' ');
        }

        window.verHojaRuta = function(idTramite) {
            const tbody = document.getElementById('hr-niveles-tbody');
            tbody.innerHTML = '';
            document.getElementById('hojaRutaModal').classList.remove('hidden');

            fetch(`/mesa-partes/hoja-ruta/${idTramite}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-500">' + (data.message || 'Error al cargar datos') + '</td></tr>';
                    return;
                }

                // 1. Posición actual
                if (data.posicion_actual) {
                    const pos = data.posicion_actual;
                    tbody.innerHTML += `
                        <tr class="bg-blue-50 font-semibold">
                            <td class="py-2 px-4 border" colspan="4">
                                📍 <strong>Posición Actual:</strong> ${pos.nodo_nombre} (Orden: ${pos.nodo_orden})
                                <span class="ml-2 px-2 py-0.5 text-xs rounded ${pos.puede_finalizar ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'}">
                                    ${pos.puede_finalizar ? 'Nodo Final' : 'Nodo Intermedio'}
                                </span>
                                <span class="ml-2 px-2 py-0.5 text-xs rounded ${pos.modo_derivacion === 'MANUAL_FORZADO' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'}">
                                    ${pos.modo_derivacion === 'MANUAL_FORZADO' ? 'Manual' : 'Automático'}
                                </span>
                            </td>
                        </tr>
                    `;
                }

                // 2. Historial de movimientos (ruta real)
                if (data.historial && data.historial.length > 0) {
                    tbody.innerHTML += `
                        <tr class="bg-gray-100 font-semibold">
                            <td class="py-2 px-4 border" colspan="4">📋 Historial de Movimientos (${data.historial.length})</td>
                        </tr>
                    `;
                    data.historial.forEach((mov, index) => {
                        const desde = mov.nodo_desde ? `${mov.nodo_desde.nombre} (Ord: ${mov.nodo_desde.orden})` : '—';
                        const hacia = mov.nodo_hacia ? `${mov.nodo_hacia.nombre} (Ord: ${mov.nodo_hacia.orden})` : '—';
                        const usuario = mov.usuario ? `${mov.usuario.nombre} (${mov.usuario.email})` : 'Sistema';

                        tbody.innerHTML += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}">
                                <td class="py-2 px-4 border text-xs text-gray-600">${mov.fecha}</td>
                                <td class="py-2 px-4 border">
                                    <span class="px-2 py-0.5 rounded text-xs ${mov.accion.includes('DERIVAR') ? 'bg-blue-100 text-blue-800' : mov.accion.includes('OBSERVAR') ? 'bg-yellow-100 text-yellow-800' : mov.accion.includes('VERIFICAR') ? 'bg-green-100 text-green-800' : mov.accion.includes('RECHAZAR') ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'}">
                                        ${mov.accion_legible}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border text-sm">
                                    <div><strong>De:</strong> ${desde}</div>
                                    <div><strong>A:</strong> ${hacia}</div>
                                </td>
                                <td class="py-2 px-4 border text-xs text-gray-600">${usuario}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML += `
                        <tr class="bg-gray-50">
                            <td class="py-2 px-4 border text-center text-gray-500" colspan="4">Sin historial de movimientos</td>
                        </tr>
                    `;
                }

                // 3. Ruta esperada del procedimiento
                if (data.niveles_estaticos && data.niveles_estaticos.length > 0) {
                    tbody.innerHTML += `
                        <tr class="bg-gray-100 font-semibold">
                            <td class="py-2 px-4 border" colspan="4">🗺️ Ruta Esperada (Procedimiento)</td>
                        </tr>
                    `;
                    data.niveles_estaticos.forEach(nivel => {
                        const esActual = data.posicion_actual && data.posicion_actual.nodo_orden === nivel.orden;
                        tbody.innerHTML += `
                            <tr class="${esActual ? 'bg-blue-50 font-semibold' : ''}">
                                <td class="py-2 px-4 border text-center font-bold">${nivel.nivel}</td>
                                <td class="py-2 px-4 border">${nivel.nombre}</td>
                                <td class="py-2 px-4 border text-center">
                                    <span class="px-2 py-0.5 rounded text-xs ${nivel.tipo === 'estatico' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-800'}">
                                        ${nivel.tipo === 'estatico' ? 'Estático' : 'Dinámico'}
                                    </span>
                                </td>
                                <td class="py-2 px-4 border text-center">
                                    ${esActual ? '<span class="px-2 py-0.5 rounded text-xs bg-blue-200 text-blue-800">📍 ACTUAL</span>' : ''}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML += '<tr><td colspan="4" class="text-center py-4">No disponible</td></tr>';
                }
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-500">Error al cargar datos</td></tr>';
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

            const idTramite = document.getElementById('corregir_id_tramite').value;
            const archivoInput = document.getElementById('corregir_archivo');
            if (!archivoInput.files || archivoInput.files.length === 0) {
                mensajeDiv.textContent = 'Debe seleccionar un archivo PDF.';
                mensajeDiv.className = 'text-red-600';
                return;
            }

            fetch(`/tramites/${idTramite}/corregir`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mensajeDiv.textContent = 'Archivo corregido exitosamente.';
                    mensajeDiv.className = 'text-green-600';
                    setTimeout(() => {
                        cerrarModalCorregir();
                        buscarTramites();
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

        document.getElementById('btnLimpiar').addEventListener('click', function() {
            searchForm.reset();
            resultadosBusqueda.innerHTML = `
                <div class="text-center py-4 text-gray-500">
                    Use los filtros para buscar trámites.
                </div>
            `;
        });
    });
</script>
@endpush
@endsection
