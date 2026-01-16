@extends('layouts.mesa-partes')

@section('title', 'Trámites Recibidos')

@section('header', 'Listado de los Trámites Recibidos')

@section('content')
        
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form id="filtrosForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Estado</label>
                    <select name="estado" id="filtro_estado" class="w-full p-2 border rounded">
                        <option value="">Todos</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="OBSERVADO">Observado</option>
                        <option value="EN_PROCESO">En Proceso</option>
                        <option value="VERIFICADO">Verificado</option>
                        <option value="FINALIZADO">Finalizado</option>
                        <option value="RECHAZADO">Rechazado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Fecha Desde</label>
                    <input type="date" name="fecha_desde" id="filtro_fecha_desde" class="w-full p-2 border rounded" max="">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Fecha Hasta</label>
                    <input type="date" name="fecha_hasta" id="filtro_fecha_hasta" class="w-full p-2 border rounded" max="" min="">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                        Filtrar
                    </button>
                </div>
            </form>
                                </div>

        <!-- Leyenda de Indicador de Tiempo -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg shadow-sm p-4 mb-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Indicador de Tiempo de Trámites</h4>
                    <p class="text-xs text-gray-700 mb-3">
                        Cada trámite tiene un límite de <strong>30 días</strong> para ser procesado. El indicador de color muestra el tiempo transcurrido desde la creación del trámite.
                    </p>
                    <div class="flex flex-wrap gap-3 text-xs">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded mr-2 border border-gray-300" style="background-color: #22c55e;"></div>
                            <span class="text-gray-700"><strong>0-9 días:</strong> Normal (21-30 días restantes)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded mr-2 border border-gray-300" style="background-color: #fde047;"></div>
                            <span class="text-gray-700"><strong>9-15 días:</strong> Atención (15-21 días restantes)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded mr-2 border border-gray-300" style="background-color: #eab308;"></div>
                            <span class="text-gray-700"><strong>15-21 días:</strong> Advertencia (9-15 días restantes)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded mr-2 border border-gray-300" style="background-color: #f97316;"></div>
                            <span class="text-gray-700"><strong>21-30 días:</strong> Urgente (0-9 días restantes)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded mr-2 border border-gray-300" style="background-color: #dc2626;"></div>
                            <span class="text-gray-700"><strong>30+ días:</strong> Vencido</span>
                        </div>
                    </div>
                </div>
            </div>
                                </div>

        <!-- Tabla de Trámites -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Procedimiento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tiempo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Voucher</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hoja de Ruta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tramitesTableBody">
                    <!-- Los trámites se cargarán dinámicamente -->
                </tbody>
            </table>

            <!-- Paginación -->
            <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button id="prevPage" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Anterior
                    </button>
                    <button id="nextPage" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Siguiente
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                        <p class="text-sm text-gray-700">
                            Mostrando <span id="inicioRegistros">0</span> a <span id="finRegistros">0</span> de <span id="totalRegistros">0</span> resultados
                        </p>
                    </div>
                                <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button id="prevPage" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Anterior
                            </button>
                            <span id="paginaActual" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                1
                            </span>
                            <button id="nextPage" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                Siguiente
                            </button>
                        </nav>
                    </div>
                </div>
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
                                <thead>
                                    <tr>
                        <th class="py-2 px-4 border bg-gray-100">Nivel</th>
                        <th class="py-2 px-4 border bg-gray-100">Nombre del Nivel</th>
                                    </tr>
                                </thead>
                <tbody id="hr-niveles-tbody">
                    <!-- Aquí se llenarán los niveles dinámicamente -->
                                </tbody>
                            </table>
                        </div>
                            </div>
                        </div>

<!-- Modal para corregir trámite -->
<div id="modalCorregir" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm relative">
        <h2 class="text-lg font-bold mb-4">Corregir Trámite</h2>
        <form id="formCorregir" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_tramite" id="corregir_id_tramite">
            <input type="file" name="archivo_adjunto" id="corregir_archivo" accept="application/pdf" class="mb-4 w-full" required />
            <div class="flex justify-end">
                <button type="button" id="cerrarModalCorregir" class="mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Enviar Corrección</button>
            </div>
            <div id="corregirMensaje" class="mt-3 text-sm"></div>
        </form>
    </div>
</div>

<!-- Modal para subir oficio (usuario externo) -->
<div id="modalOficio" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-2xl shadow-lg rounded-md bg-white">
        <h3 class="text-xl font-bold mb-4">Subir Oficio</h3>
        <form id="formOficio" enctype="multipart/form-data">
            @csrf
            <input type="file" name="archivo_oficio" accept="application/pdf" required class="mb-4 w-full">
            <input type="hidden" name="id_tramite" id="id_tramite_oficio">
            <div class="flex justify-end">
                <button type="button" onclick="cerrarModalOficio()" class="mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cerrar</button>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Subir</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para observar con oficio (usuario interno) -->
<div id="modalObservarConOficio" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-2xl shadow-lg rounded-md bg-white">
        <h3 class="text-xl font-bold mb-4">Observar con Oficio</h3>
        <form id="formObservarConOficio" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="observacion_oficio" class="block text-gray-700 text-sm font-bold mb-2">
                    Motivo de la Observación <span class="text-red-500">*</span>
                </label>
                <textarea name="observacion" id="observacion_oficio" rows="4" required 
                          class="w-full p-2 border rounded" 
                          placeholder="Ingrese el motivo de la observación..."></textarea>
            </div>
            <div class="mb-4">
                <label for="oficio_archivo" class="block text-gray-700 text-sm font-bold mb-2">
                    Archivo de Oficio (PDF) <span class="text-red-500">*</span>
                </label>
                <input type="file" name="oficio" id="oficio_archivo" accept="application/pdf" required class="w-full">
            </div>
            <input type="hidden" name="id_tramite" id="id_tramite_observar_oficio">
            <div class="flex justify-end">
                <button type="button" onclick="cerrarModalObservarConOficio()" class="mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cerrar</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Observar</button>
            </div>
        </form>
    </div>
</div>
                    
<!-- Modal de Acciones -->
<div id="modalAccionesTramite" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm relative">
        <button id="cerrarModalAcciones" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h3 id="modalAccionesTitulo" class="text-lg font-bold mb-3">Acciones del Trámite</h3>
        <div id="modalAccionesBtns" class="flex flex-col gap-3"></div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .estado-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.85em;
        font-weight: 500;
    }
    .estado-pendiente { background-color: #e3f2fd; color: #1976d2; }
    .estado-observado { background-color: #fff3e0; color: #f57c00; }
    .estado-proceso { background-color: #e8f5e9; color: #388e3c; }
    .estado-verificado { background-color: #f3e5f5; color: #7b1fa2; }
    .estado-finalizado { background-color: #e8eaf6; color: #3f51b5; }
    .estado-rechazado { background-color: #ffebee; color: #d32f2f; }
    
    .acciones-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .acciones-buttons button {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.9em;
        transition: all 0.3s ease;
    }
    .acciones-buttons button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    // Variables globales
    window.TIPO_USER = {{ Auth::user()->tipo_user }};
    window.USER_ID = {{ Auth::user()->id }};
    window.NIVEL_USUARIO = {{ Auth::user()->usuariosNiveles->first()->nivel ?? 1 }};
    
    let tramiteIdActual;
    let paginaActual = 1;
    let totalPaginas = 1;
    let filtrosActuales = {};
    let tramitesCache = [];

    // Función para verificar si el usuario es externo
    window.usuarioEsExterno = function() {
        return window.TIPO_USER === 1;
    }

    // Función para verificar si el usuario es interno
    function usuarioEsInterno() {
        return window.TIPO_USER == 2;
    }

    // Función para obtener la clase CSS según el estado
    function obtenerClaseEstado(estado) {
        const clases = {
            'PENDIENTE': 'estado-pendiente',
            'OBSERVADO': 'estado-observado',
            'EN_PROCESO': 'estado-proceso',
            'VERIFICADO': 'estado-verificado',
            'FINALIZADO': 'estado-finalizado',
            'RECHAZADO': 'estado-rechazado'
        };
        return clases[estado] || 'estado-pendiente';
    }

    // Función para formatear el estado (reemplazar guiones bajos con espacios y capitalizar)
    function formatearEstado(estado) {
        if (!estado) return '';
        
        // Mapeo de estados a etiquetas formateadas
        const etiquetas = {
            'PENDIENTE': 'Pendiente',
            'OBSERVADO': 'Observado',
            'EN_PROCESO': 'En Proceso',
            'VERIFICADO': 'Verificado',
            'FINALIZADO': 'Finalizado',
            'RECHAZADO': 'Rechazado'
        };
        
        // Si existe en el mapeo, usar la etiqueta formateada
        if (etiquetas[estado]) {
            return etiquetas[estado];
        }
        
        // Si no existe, reemplazar guiones bajos con espacios y capitalizar
        return estado
            .replace(/_/g, ' ')
            .toLowerCase()
            .split(' ')
            .map(palabra => palabra.charAt(0).toUpperCase() + palabra.slice(1))
            .join(' ');
    }

    // Formatear fecha
    function formatearFecha(fecha) {
        if (!fecha) return '';
        const date = new Date(fecha);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Función para obtener el indicador de días transcurridos (cuadrado de color)
    function obtenerIndicadorDias(diasTranscurridos, fechaCreacion, tramiteId) {
        const LIMITE_DIAS = 30;
        
        // Si no se proporcionan los días transcurridos, calcularlos desde la fecha
        if (diasTranscurridos === undefined || diasTranscurridos === null) {
            if (fechaCreacion) {
                const fecha = new Date(fechaCreacion);
                const ahora = new Date();
                // Calcular diferencia en días (entero) desde fecha hasta ahora
                const diffTime = ahora.getTime() - fecha.getTime();
                diasTranscurridos = Math.floor(diffTime / (1000 * 60 * 60 * 24));
                // Si es negativo (fecha futura), establecer a 0
                if (diasTranscurridos < 0) {
                    diasTranscurridos = 0;
                }
            } else {
                diasTranscurridos = 0;
            }
        }
        
        // Asegurar que sea un entero (redondear hacia abajo)
        diasTranscurridos = Math.floor(Math.abs(diasTranscurridos));
        
        // Determinar el color según los días transcurridos usando colores específicos con estilos inline
        let colorFondo = '';
        
        if (diasTranscurridos >= LIMITE_DIAS) {
            // Vencido (rojo)
            colorFondo = '#dc2626'; // red-600
        } else if (diasTranscurridos >= 21) {
            // Urgente (naranja) - 21-30 días - usando naranja más saturado
            colorFondo = '#f97316'; // orange-500
        } else if (diasTranscurridos >= 15) {
            // Advertencia (amarillo) - 15-21 días - usando amarillo más saturado
            colorFondo = '#eab308'; // yellow-500
        } else if (diasTranscurridos >= 9) {
            // Atención (amarillo claro) - 9-15 días - usando amarillo claro más visible
            colorFondo = '#fde047'; // yellow-300
        } else {
            // Normal (verde) - 0-9 días
            colorFondo = '#22c55e'; // green-500
        }
        
        // Asegurar que el estilo se aplique correctamente sin clases que puedan interferir
        return `<div style="width: 1.25rem; height: 1.25rem; border-radius: 0.25rem; border: 1px solid #9ca3af; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); background-color: ${colorFondo};"></div>`;
    }

    // Cargar trámites dinámicamente
    function cargarTramites(pagina = 1) {
        const formData = new FormData(document.getElementById('filtrosForm'));
        filtrosActuales = {
            estado: formData.get('estado'),
            fecha_desde: formData.get('fecha_desde'),
            fecha_hasta: formData.get('fecha_hasta'),
        };

        const payload = new FormData();
        payload.append('pagina', pagina);
        payload.append('por_pagina', 10);
        payload.append('filtros', JSON.stringify(filtrosActuales));

        console.log('Enviando petición AJAX', {
            url: '{{ route("mesa-partes.tramites-recibidos.ajax") }}',
            pagina: pagina,
            filtros: filtrosActuales
        });

        fetch('{{ route("mesa-partes.tramites-recibidos.ajax") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: payload
        })
        .then(res => {
            console.log('Respuesta recibida', res.status, res.statusText);
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            if (data.status === 'success') {
                renderTramites(data.tramites || []);
                paginaActual = data.pagina_actual || 1;
                totalPaginas = data.paginas || 1;
                actualizarPaginacion(data);
            } else {
                console.error('Error en respuesta:', data.message || 'Estado desconocido');
                document.getElementById('tramitesTableBody').innerHTML = 
                    '<tr><td colspan="9" class="text-center py-4">' + (data.message || 'No hay trámites disponibles') + '</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            document.getElementById('tramitesTableBody').innerHTML = 
                '<tr><td colspan="9" class="text-center py-4 text-red-500">Error al cargar los trámites: ' + error.message + '</td></tr>';
        });
    }

    // Renderizar trámites en la tabla
    function renderTramites(tramites) {
        const tbody = document.getElementById('tramitesTableBody');
        tbody.innerHTML = '';
        
        if (!tramites || tramites.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-4">No hay trámites para mostrar</td></tr>';
            return;
        }
        
        tramites.forEach(tramite => {
            tramitesCache[tramite.id] = tramite;
            const tr = document.createElement('tr');
            tr.className = 'bg-gray-50 hover:bg-gray-100';
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tramite.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${tramite.archivo_adjunto ? `
                        <div class="flex flex-col gap-1 items-center">
                            <a href="/tramites/${tramite.id}/archivo/${tramite.archivo_adjunto}" download 
                               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded text-xs text-center" target="_blank">
                                Descargar
                            </a>
                            <a href="/tramites/${tramite.id}/archivo/${tramite.archivo_adjunto}" 
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs text-center" target="_blank">
                                Ver
                            </a>
                        </div>
                    ` : 'Sin archivo'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${tramite.procedimiento_nombre || ''}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${obtenerClaseEstado(tramite.estado)}">
                        ${formatearEstado(tramite.estado)}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    ${obtenerIndicadorDias(tramite.dias_transcurridos, tramite.fecha_creacion, tramite.id)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${formatearFecha(tramite.fecha_creacion)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    ${tramite.voucher ? `
                        <a href="/tramites/${tramite.id}/archivo/${tramite.voucher}" download 
                           class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded text-xs" target="_blank">
                            Descargar Voucher
                        </a>
                    ` : 'Sin voucher'}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <button onclick="verHojaRuta(${tramite.id})" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded text-xs mb-1 mr-1">
                        Hoja de Ruta
                    </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 acciones-buttons">
                    ${generarBotonesAccion(tramite)}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Generar botones de acción según estado y nivel
    window.generarBotonesAccion = function(tramite) {
        let botones = '';
        
        // Si está rechazado, no mostrar botones de acción (solo se muestra en la columna "Hoja de Ruta")
        if (tramite.estado === 'RECHAZADO') {
            return botones;
        }
        
        // Ver oficio si existe
        if (tramite.oficio) {
            botones += `<button onclick="verOficio('${tramite.oficio}', ${tramite.id})" 
                        class="bg-purple-500 hover:bg-purple-600 text-white font-bold py-1 px-2 rounded text-xs mb-1 mr-1">
                        Ver Oficio</button>`;
        }
        
        // Botón de acciones si hay acciones permitidas
        if (tramite.accionesPermitidas && tramite.accionesPermitidas.length > 0) {
            botones += `<button onclick="abrirModalAcciones(${tramite.id})" 
                        class="bg-gray-700 hover:bg-gray-900 text-white font-bold py-1 px-2 rounded text-xs mb-1">
                        Acciones</button>`;
        } else {
            botones += `<button class="bg-gray-300 text-gray-500 cursor-not-allowed px-2 py-1 rounded text-xs mb-1" disabled>
                        Sin acciones</button>`;
        }
        
        return botones;
    }

    // Actualizar paginación
    function actualizarPaginacion(data) {
        document.getElementById('paginaActual').textContent = data.pagina_actual || 1;
        document.getElementById('totalRegistros').textContent = data.total || 0;
        const inicio = data.tramites && data.tramites.length > 0 ? ((data.pagina_actual - 1) * 10 + 1) : 0;
        const fin = data.tramites && data.tramites.length > 0 ? ((data.pagina_actual - 1) * 10 + data.tramites.length) : 0;
        document.getElementById('inicioRegistros').textContent = inicio;
        document.getElementById('finRegistros').textContent = fin;
        
        const prevBtns = document.querySelectorAll('#prevPage');
        const nextBtns = document.querySelectorAll('#nextPage');
        prevBtns.forEach(btn => btn.disabled = data.pagina_actual === 1);
        nextBtns.forEach(btn => btn.disabled = data.pagina_actual === data.paginas);
    }

    // Modal de acciones
    window.abrirModalAcciones = function(idTramite) {
        const tramite = tramitesCache[idTramite];
        if (!tramite) return alert('No se encontró el trámite.');
        
        const modal = document.getElementById('modalAccionesTramite');
        const cont = document.getElementById('modalAccionesBtns');
        cont.innerHTML = '';
        
        if (tramite.accionesPermitidas && tramite.accionesPermitidas.length > 0) {
            tramite.accionesPermitidas.forEach(acc => {
                const btn = document.createElement('button');
                btn.className = 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded';
                btn.textContent = acc;
                btn.onclick = function() {
                    modal.classList.add('hidden');
                    ejecutarAccion(acc, idTramite);
                };
                cont.appendChild(btn);
            });
        } else {
            cont.innerHTML = '<p class="text-gray-500">No hay acciones disponibles</p>';
        }
        
        document.getElementById('modalAccionesTitulo').textContent = `Acciones para Trámite #${tramite.id}`;
        modal.classList.remove('hidden');
    }

    // Ejecutar acción
    function ejecutarAccion(accion, idTramite) {
        switch(accion) {
            case 'CORREGIR':
                mostrarModalCorregir(idTramite);
                break;
            case 'OBSERVAR':
                observarTramite(idTramite);
                break;
            case 'OBSERVAR_CON_OFICIO':
                observarConOficioTramite(idTramite);
                break;
            case 'DERIVAR':
                derivarTramite(idTramite);
                break;
            case 'ACEPTAR':
                aceptarTramite(idTramite);
                break;
            case 'RECHAZAR':
                rechazarTramite(idTramite);
                break;
            case 'VERIFICAR':
                verificarTramite(idTramite);
                break;
            case 'DERIVAR_ESPECIALISTA':
                derivarEspecialistaTramite(idTramite);
                break;
            case 'PROCESAR':
                procesarTramite(idTramite);
                break;
        }
    }

    // Funciones de acciones
    window.mostrarModalCorregir = function(idTramite) {
        document.getElementById('corregir_id_tramite').value = idTramite;
        document.getElementById('modalCorregir').classList.remove('hidden');
    }

    window.observarTramite = function(id) {
        const observacion = prompt('Ingrese el motivo de la observación:');
        if (!observacion) return;
        
        fetch(`/tramites/${id}/observar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ observacion: observacion })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Trámite observado correctamente.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al observar el trámite.');
            }
        })
        .catch(() => alert('Error de red al intentar observar el trámite.'));
    }

    window.derivarTramite = function(id) {
        if (!confirm('¿Está seguro de derivar este trámite al siguiente nivel?')) return;
        
        fetch(`/tramites/${id}/derivar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Trámite derivado correctamente.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al derivar el trámite.');
            }
        })
        .catch(() => alert('Error de red al intentar derivar el trámite.'));
    }

    window.aceptarTramite = function(id) {
        if (!confirm('¿Está seguro de aceptar este trámite?')) return;
        
        fetch(`/tramites/${id}/aceptar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Trámite aceptado correctamente.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al aceptar el trámite.');
            }
        })
        .catch(() => alert('Error de red al intentar aceptar el trámite.'));
    }

    window.rechazarTramite = function(id) {
        const motivo = prompt('Ingrese el motivo del rechazo:');
        if (!motivo) return;
        
        fetch(`/tramites/${id}/rechazar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ motivo: motivo })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Trámite rechazado correctamente.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al rechazar el trámite.');
            }
        })
        .catch(() => alert('Error de red al intentar rechazar el trámite.'));
    }

    window.verificarTramite = function(id) {
        if (!confirm('¿Está seguro de verificar este trámite?')) return;
        
        fetch(`/tramites/${id}/verificar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Trámite verificado correctamente.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al verificar el trámite.');
            }
        })
        .catch(() => alert('Error de red al intentar verificar el trámite.'));
    }

    window.derivarEspecialistaTramite = function(id) {
        if (!confirm('¿Está seguro de derivar este trámite al especialista?')) return;
        
        fetch(`/tramites/${id}/derivar-especialista`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Trámite derivado al especialista correctamente.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al derivar al especialista.');
            }
        })
        .catch(() => alert('Error de red al intentar derivar al especialista.'));
    }

    window.observarConOficioTramite = function(id) {
        document.getElementById('id_tramite_observar_oficio').value = id;
        document.getElementById('modalObservarConOficio').classList.remove('hidden');
    }

    window.cerrarModalObservarConOficio = function() {
        document.getElementById('modalObservarConOficio').classList.add('hidden');
        document.getElementById('formObservarConOficio').reset();
    }

    window.procesarTramite = function(id) {
        if (!confirm('¿Está seguro de que desea finalizar este trámite y generar el voucher?')) return;
        
        fetch(`/tramites/${id}/procesar`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Trámite finalizado correctamente. El voucher ha sido generado.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al finalizar el trámite.');
            }
        })
        .catch(() => alert('Error de red al intentar procesar el trámite.'));
    }

    window.verHojaRuta = function(idTramite) {
        const modal = document.getElementById('hojaRutaModal');
        const tbody = document.getElementById('hr-niveles-tbody');
        tbody.innerHTML = '';
        modal.classList.remove('hidden');

        fetch(`/mesa-partes/hoja-ruta/${idTramite}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.niveles && data.niveles.length > 0) {
                data.niveles.forEach(nivel => {
                    tbody.innerHTML += `
                        <tr>
                            <td class="py-2 px-4 border bg-gray-100 font-bold">Nivel ${nivel.nivel}</td>
                            <td class="py-2 px-4 border">${nivel.nombre || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="2" class="text-center py-4">No disponible</td></tr>';
            }
        })
        .catch(() => {
            tbody.innerHTML = '<tr><td colspan="2" class="text-center py-4 text-red-500">Error al cargar datos</td></tr>';
        });
    }

    window.cerrarHojaRuta = function() {
        document.getElementById('hojaRutaModal').classList.add('hidden');
    }

    window.verOficio = function(nombreOficio, tramiteId) {
        if (!nombreOficio || !tramiteId) {
            alert('No hay oficio adjunto');
            return;
        }
        window.open(`/tramites/${tramiteId}/archivo/${nombreOficio}`, '_blank');
    }

    window.verDocumento = function(nombreDocumento, tramiteId) {
        if (!nombreDocumento || !tramiteId) {
            alert('No hay documento adjunto');
            return;
        }
        window.open(`/tramites/${tramiteId}/archivo/${nombreDocumento}`, '_blank');
    }

    window.abrirModalOficio = function(idTramite) {
        document.getElementById('id_tramite_oficio').value = idTramite;
        document.getElementById('modalOficio').classList.remove('hidden');
    }

    window.cerrarModalOficio = function() {
        document.getElementById('modalOficio').classList.add('hidden');
        document.getElementById('formOficio').reset();
    }

    // Event Listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modal de acciones
        document.getElementById('cerrarModalAcciones').onclick = function() {
            document.getElementById('modalAccionesTramite').classList.add('hidden');
        };

        // Cerrar modal de corregir
        document.getElementById('cerrarModalCorregir').onclick = function() {
            document.getElementById('modalCorregir').classList.add('hidden');
            document.getElementById('formCorregir').reset();
            document.getElementById('corregirMensaje').textContent = '';
        };

        // Formulario de corregir
        document.getElementById('formCorregir').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const mensajeDiv = document.getElementById('corregirMensaje');
            mensajeDiv.textContent = 'Enviando...';
            
            fetch(`/tramites/${formData.get('id_tramite')}/corregir`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mensajeDiv.textContent = 'Corrección enviada correctamente.';
                    setTimeout(() => {
                        document.getElementById('modalCorregir').classList.add('hidden');
                        this.reset();
                        mensajeDiv.textContent = '';
                        cargarTramites(paginaActual);
                    }, 1200);
                } else {
                    mensajeDiv.textContent = data.message || 'Error al corregir el trámite.';
                }
            })
            .catch(() => {
                mensajeDiv.textContent = 'Error al enviar la corrección.';
            });
        };

        // Formulario de oficio (usuario externo)
        document.getElementById('formOficio').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const archivoInput = formData.get('archivo_oficio');
            
            if (!archivoInput || archivoInput.size === 0) {
                alert('Por favor seleccione un archivo PDF');
                return;
            }
            
            if (archivoInput.type !== 'application/pdf') {
                alert('Solo se permiten archivos PDF');
                return;
            }
            
            if (archivoInput.size > 10 * 1024 * 1024) {
                alert('El archivo no debe superar los 10MB');
                return;
            }
            
            fetch(`/tramites/${formData.get('id_tramite')}/subir-oficio`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Oficio subido correctamente');
                    cerrarModalOficio();
                    cargarTramites(paginaActual);
                } else {
                    alert(data.message || 'Error al subir el oficio');
                }
            })
            .catch(() => alert('Error de red al intentar subir el oficio'));
        };

        // Formulario de observar con oficio (usuario interno)
        document.getElementById('formObservarConOficio').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const archivoInput = formData.get('oficio');
            
            if (!archivoInput || archivoInput.size === 0) {
                alert('Por favor seleccione un archivo PDF');
                return;
            }
            
            if (archivoInput.type !== 'application/pdf') {
                alert('Solo se permiten archivos PDF');
                return;
            }
            
            if (archivoInput.size > 10 * 1024 * 1024) {
                alert('El archivo no debe superar los 10MB');
                return;
            }
            
            fetch(`/tramites/${formData.get('id_tramite')}/observar-con-oficio`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Trámite observado correctamente. El usuario externo deberá adjuntar un oficio de subsanación.');
                    cerrarModalObservarConOficio();
                    cargarTramites(paginaActual);
                } else {
                    alert(data.message || 'Error al observar el trámite con oficio.');
                }
            })
            .catch(() => alert('Error de red al intentar observar el trámite.'));
        };

        // Configurar límites de fecha al cargar la página
        const fechaDesdeInput = document.getElementById('filtro_fecha_desde');
        const fechaHastaInput = document.getElementById('filtro_fecha_hasta');
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

        // Formulario de filtros
        document.getElementById('filtrosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar fechas antes de filtrar
            if (!validarFechas()) {
                return;
            }
            
            paginaActual = 1;
            cargarTramites(1);
        });

        // Paginación
        document.querySelectorAll('#prevPage').forEach(btn => {
            btn.addEventListener('click', function() {
                if (paginaActual > 1) {
                    paginaActual--;
                    cargarTramites(paginaActual);
                }
            });
        });

        document.querySelectorAll('#nextPage').forEach(btn => {
            btn.addEventListener('click', function() {
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    cargarTramites(paginaActual);
                }
            });
        });

        // Cargar trámites inicialmente
        cargarTramites(1);
    });
</script>
@endpush
