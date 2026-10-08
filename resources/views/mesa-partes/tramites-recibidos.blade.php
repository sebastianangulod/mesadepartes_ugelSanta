@extends('layouts.mesa-partes')

@section('title', 'Mis Trámites')

@section('header', 'Mis Trámites')

@section('content')
        
        <!-- Filtros -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <form id="filtrosForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
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
            <div class="overflow-x-auto">
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
            </div>

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
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
        <button id="cerrarModalCorregir" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h2 class="text-lg font-bold mb-4">Corregir Trámite</h2>
        <p class="text-sm text-gray-600 mb-4">El trámite se encuentra <span class="font-semibold text-orange-600">OBSERVADO</span>. Adjunte el documento corregido (PDF) para subsanar la observación.</p>
        <form id="formCorregir" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_tramite" id="corregir_id_tramite">
            <div class="mb-4">
                <label for="corregir_archivo" class="block text-sm font-medium text-gray-700 mb-1">
                    Documento corregido (PDF) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="file" name="archivo_adjunto" id="corregir_archivo" accept="application/pdf" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                           onchange="validarArchivoPDF(this, 'corregir_archivo_error')">
                    <div id="corregir_archivo_error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Máximo 10 MB. Solo archivos PDF.</p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="cerrarModalCorregirBtn" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-colors">Cancelar</button>
                <button type="submit" id="btnEnviarCorreccion" disabled class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors opacity-50 cursor-not-allowed">Enviar Corrección</button>
            </div>
            <div id="corregirMensaje" class="mt-3 text-sm"></div>
        </form>
    </div>
</div>

<!-- Modal para subir oficio (usuario externo) -->
<div id="modalOficio" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 w-full max-w-md shadow-lg rounded-lg bg-white">
        <button onclick="cerrarModalOficio()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h3 class="text-xl font-bold mb-4">Subir Oficio de Subsanación</h3>
        <p class="text-sm text-gray-600 mb-4">El trámite fue <span class="font-semibold text-orange-600">OBSERVADO</span> y requiere un oficio de respuesta. Adjunte el oficio (PDF) para continuar el trámite.</p>
        <form id="formOficio" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label for="archivo_oficio" class="block text-sm font-medium text-gray-700 mb-1">
                    Oficio de subsanación (PDF) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="file" name="archivo_oficio" id="archivo_oficio" accept="application/pdf" required 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                           onchange="validarArchivoPDF(this, 'oficio_archivo_error')">
                    <div id="oficio_archivo_error" class="mt-1 text-sm text-red-600 hidden"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Máximo 10 MB. Solo archivos PDF.</p>
            </div>
            <input type="hidden" name="id_tramite" id="id_tramite_oficio">
            <div class="flex justify-end gap-2">
                <button type="button" onclick="cerrarModalOficio()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-colors">Cerrar</button>
                <button type="submit" id="btnSubirOficio" disabled class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors opacity-50 cursor-not-allowed">Subir Oficio</button>
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
            <p class="text-sm text-gray-600 mb-4 bg-yellow-50 p-3 rounded">
                <strong>Nota:</strong> El oficio debe ser adjuntado por el creador del trámite después de esta observación.
                Hasta que se adjunte el oficio, no podrá realizar ninguna acción sobre este trámite.
            </p>
            <input type="hidden" name="id_tramite" id="id_tramite_observar_oficio">
            <div class="flex justify-end">
                <button type="button" onclick="cerrarModalObservarConOficio()" class="mr-2 px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cerrar</button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Observar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para ver observación (usuario externo e interno) -->
<div id="modalObservacion" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
        <button onclick="cerrarModalObservacion()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800 text-2xl">&times;</button>
        <h3 class="text-xl font-bold mb-4">Motivo de la Observación</h3>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Observación</label>
            <div id="observacionTexto" class="w-full px-3 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 min-h-[80px] whitespace-pre-wrap"></div>
        </div>
        <div id="oficioSection" class="mb-4 hidden">
            <label class="block text-sm font-medium text-gray-700 mb-1">Oficio adjunto</label>
            <div class="flex items-center gap-2">
                <button id="btnVerOficioObservacion" type="button" 
                        class="px-3 py-2 bg-purple-500 hover:bg-purple-600 text-white text-sm rounded-lg font-medium transition-colors">
                    Ver Oficio
                </button>
                <span class="text-sm text-gray-500">Documento oficial de la observación</span>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="cerrarModalObservacion()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium transition-colors">Cerrar</button>
        </div>
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
        gap: 6px;
        flex-wrap: nowrap;
        align-items: center;
    }
    .acciones-buttons button {
        height: 28px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@push('scripts')
<script>
    console.log('=== tramites-recibidos script loaded ===');
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
        console.log('=== cargarTramites called ===', { pagina });
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
            console.log('=== Response received ===', res.status, res.statusText);
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            console.log('=== Data received ===', data);
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
            console.error('=== Error completo ===', error);
            document.getElementById('tramitesTableBody').innerHTML = 
                '<tr><td colspan="9" class="text-center py-4 text-red-500">Error al cargar los trámites: ' + error.message + '</td></tr>';
        });
    }

    // Renderizar trámites en la tabla
    function renderTramites(tramites) {
        console.log('=== renderTramites called ===', { count: tramites?.length });
        const tbody = document.getElementById('tramitesTableBody');
        if (!tbody) {
            console.error('=== tbody NOT FOUND ===', 'tramitesTableBody');
            return;
        }
        tbody.innerHTML = '';
        
        if (!tramites || tramites.length === 0) {
            console.log('=== No tramites to render ===');
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
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-middle">
                    <button onclick="verHojaRuta(${tramite.id})" 
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold px-2 rounded text-xs">
                        Hoja de Ruta
                    </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 align-middle">
                    <div class="acciones-buttons">
                        ${generarBotonesAccion(tramite)}
                    </div>
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
        
        // Botón "Ver observación" SOLO para usuarios externos (tipo_user = 1) en trámites observados
        const esUsuarioExterno = window.TIPO_USER === 1;
        if (esUsuarioExterno && tramite.estado === 'OBSERVADO') {
            botones += `<button onclick="verObservacion(${tramite.id})" 
                        class="bg-amber-500 hover:bg-amber-600 text-white font-bold px-2 rounded text-xs mr-1"
                        title="Ver motivo de la observación">
                        Ver observación</button>`;
        }
        
        // Ver oficio solo si existe y NO requiere oficio pendiente
        if (tramite.oficio && !tramite.requiere_oficio) {
            botones += `<button onclick="verOficio('${tramite.oficio}', ${tramite.id})" 
                        class="bg-purple-500 hover:bg-purple-600 text-white font-bold px-2 rounded text-xs mr-1">
                        Ver Oficio</button>`;
        }
        // Si requiere oficio o no hay oficio, NO mostrar el botón "Ver Oficio" (oculto)
        
        // Botón de acciones si hay acciones permitidas
        if (tramite.accionesPermitidas && tramite.accionesPermitidas.length > 0) {
            botones += `<button onclick="abrirModalAcciones(${tramite.id})" 
                        class="bg-gray-700 hover:bg-gray-900 text-white font-bold px-2 rounded text-xs">
                        Acciones</button>`;
        } else {
            // Mostrar estado de acciones: sin acciones o requiere oficio
            const tooltip = tramite.requiere_oficio 
                ? 'Requiere oficio del creador para habilitar acciones' 
                : 'Sin acciones disponibles';
            botones += `<button class="bg-gray-300 text-gray-500 cursor-not-allowed px-2 rounded text-xs" disabled title="${tooltip}">
                        ${tramite.requiere_oficio ? 'Requiere oficio' : 'Sin acciones'}</button>`;
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
    window.abrirModalAcciones = async function(idTramite) {
        const tramite = tramitesCache[idTramite];
        if (!tramite) return alert('No se encontró el trámite.');
        
        const modal = document.getElementById('modalAccionesTramite');
        const cont = document.getElementById('modalAccionesBtns');
        cont.innerHTML = '';
        
        if (tramite.accionesPermitidas && tramite.accionesPermitidas.length > 0) {
            const etiquetasAccion = {
                'ACEPTAR': 'Aceptar',
                'OBSERVAR': 'Observar',
                'OBSERVAR_CON_OFICIO': 'Observar con oficio',
                'RECHAZAR': 'Rechazar',
                'DERIVAR': 'Derivar',
                'DERIVAR_ESPECIALISTA': 'Derivar a especialista',
                'MODO_MANUAL': 'Activar derivación manual',
                'MODO_AUTO': 'Restaurar derivación automática',
                'RETROCEDER': 'Retroceder',
                'VERIFICAR': 'Verificar',
                'PROCESAR': 'Procesar',
                'CORREGIR': 'Corregir',
                'SUBIR_OFICIO': 'Subir oficio',
            };
            
            // DEBUG: Log trámite data
            console.log('=== Tramite data for modal ===', {
                id: tramite.id,
                modo_derivacion: tramite.modo_derivacion,
                id_nodo_actual: tramite.id_nodo_actual,
                nodo_puede_finalizar: tramite.nodo_puede_finalizar,
                accionesPermitidas: tramite.accionesPermitidas
            });
            
            // En modo manual O en nodos finales (puede_finalizar), cargar opciones de derivación para DERIVAR
            const esManual = tramite.id_nodo_actual && (tramite.modo_derivacion === 'MANUAL_FORZADO' || tramite.modo_derivacion === true);
            // También cargar opciones si el nodo actual tiene puede_finalizar (para permitir elegir destino en AUTO)
            const nodoPuedeFinalizar = tramite.nodo_puede_finalizar === true || tramite.nodo_puede_finalizar === 'true';
            const esNodoFinal = tramite.id_nodo_actual && nodoPuedeFinalizar;
            const debeCargarOpciones = (esManual || esNodoFinal) && tramite.accionesPermitidas.includes('DERIVAR');
            
            console.log('=== DEBUG CONDITIONS ===', {
                id_nodo_actual: tramite.id_nodo_actual,
                modo_derivacion: tramite.modo_derivacion,
                nodo_puede_finalizar: tramite.nodo_puede_finalizar,
                nodoPuedeFinalizar: nodoPuedeFinalizar,
                esManual: esManual,
                esNodoFinal: esNodoFinal,
                tieneDERIVAR: tramite.accionesPermitidas.includes('DERIVAR'),
                debeCargarOpciones: debeCargarOpciones,
                accionesPermitidas: tramite.accionesPermitidas
            });
            
            let opcionesDerivacion = [];
            
            if (debeCargarOpciones) {
                try {
                    console.log('=== Fetching derivacion-opciones for ===', idTramite);
                    const rOp = await fetch(`/tramites/${idTramite}/flujo/derivacion-opciones`, { 
                        headers: { 'Accept': 'application/json' } 
                    });
                    console.log('=== Response status ===', rOp.status);
                    const op = await rOp.json();
                    console.log('=== Response JSON ===', op);
                    opcionesDerivacion = op.transiciones || [];
                    console.log('=== Derivacion opciones loaded ===', opcionesDerivacion);
                } catch (e) {
                    console.warn('No se pudieron cargar opciones de derivación', e);
                }
            } else {
                console.log('=== NOT loading derivacion-opciones ===', { debeCargarOpciones, esManual, esNodoFinal, tieneDERIVAR: tramite.accionesPermitidas.includes('DERIVAR') });
            }
            
            console.log('=== Final Dropdown conditions ===', { esManual, esNodoFinal, debeCargarOpciones, opcionesCount: opcionesDerivacion.length });
            
            tramite.accionesPermitidas.forEach(acc => {
                // En modo manual o nodo final con DERIVAR, mostrar dropdown + botón
                if (acc === 'DERIVAR' && debeCargarOpciones && opcionesDerivacion.length > 0) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'w-full';
                    
                    const label = document.createElement('label');
                    label.className = 'block text-sm font-medium text-gray-700 mb-1';
                    label.textContent = 'Derivar a:';
                    wrapper.appendChild(label);
                    
                    // En modo manual: dropdown cascada Nivel → Nodo
                    const esManual = tramite.modo_derivacion === 'MANUAL_FORZADO';
                    
                    if (esManual && opcionesDerivacion.length > 1) {
                        // Modo manual: dropdown cascada Nivel → Nodo
                        const nivelWrapper = document.createElement('div');
                        nivelWrapper.className = 'mb-2';
                        
                        const nivelLabel = document.createElement('label');
                        nivelLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
                        nivelLabel.textContent = 'Nivel de destino:';
                        nivelWrapper.appendChild(nivelLabel);
                        
                        const nivelSelect = document.createElement('select');
                        nivelSelect.className = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-2';
                        nivelSelect.id = 'derivarNivelSelect';
                        nivelSelect.innerHTML = `
                            <option value="" disabled selected>Seleccionar nivel...</option>
                            <option value="1">Nivel 1</option>
                            <option value="2">Nivel 2</option>
                            <option value="3">Nivel 3</option>
                            <option value="4">Nivel 4</option>
                        `;
                        nivelWrapper.appendChild(nivelSelect);
                        wrapper.appendChild(nivelWrapper);
                        
                        // Dropdown de nodos (se llena según nivel seleccionado)
                        const nodoLabel = document.createElement('label');
                        nodoLabel.className = 'block text-sm font-medium text-gray-700 mb-1';
                        nodoLabel.textContent = 'Nodo de destino:';
                        wrapper.appendChild(nodoLabel);
                        
                        const select = document.createElement('select');
                        select.className = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-2';
                        select.id = 'derivarDestinoSelect';
                        select.disabled = true;
                        select.innerHTML = '<option value="" disabled selected>Primero seleccione un nivel...</option>';
                        wrapper.appendChild(select);
                        
                        // Función para actualizar nodos según nivel
                        const actualizarNodosPorNivel = async (nivel) => {
                            select.innerHTML = '<option value="">Cargando nodos...</option>';
                            select.disabled = true;
                            
                            try {
                                const resp = await fetch('{{ route("flujo.nodos-globales") }}', { 
                                    headers: { 'Accept': 'application/json' } 
                                });
                                const data = await resp.json();
                                const nodos = data.niveles?.[parseInt(nivel)] || [];
                                
                                select.innerHTML = '<option value="" disabled selected>Seleccionar nodo...</option>';
                                if (nodos.length === 0) {
                                    select.innerHTML += '<option value="" disabled>No hay nodos en este nivel</option>';
                                    select.disabled = true;
                                    return;
                                }
                                
                                nodos.forEach(n => {
                                    const opt = document.createElement('option');
                                    opt.value = n.id;
                                    const badges = [];
                                    if (n.puede_finalizar) badges.push('🎯');
                                    opt.textContent = `${n.nombre} (${n.procedimiento}) ${badges.join(' ')}`.trim();
                                    select.appendChild(opt);
                                });
                                select.disabled = false;
                            } catch (e) {
                                select.innerHTML = '<option value="" disabled>Error al cargar nodos</option>';
                                select.disabled = true;
                                console.error(e);
                            }
                        };
                        
                        nivelSelect.onchange = function() {
                            const nivel = this.value;
                            if (nivel) {
                                actualizarNodosPorNivel(nivel);
                            } else {
                                select.innerHTML = '<option value="" disabled selected>Primero seleccione un nivel...</option>';
                                select.disabled = true;
                            }
                        };
                        
                    } else {
                        // Modo AUTO o nodo final: dropdown simple (comportamiento original)
                        const label = document.createElement('label');
                        label.className = 'block text-sm font-medium text-gray-700 mb-1';
                        label.textContent = 'Derivar a:';
                        wrapper.appendChild(label);
                        
                        const select = document.createElement('select');
                        select.className = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 mb-2';
                        select.id = 'derivarDestinoSelect';
                        
                        if (opcionesDerivacion.length === 1) {
                            const opt = document.createElement('option');
                            opt.value = opcionesDerivacion[0].hacia_nodo_id;
                            opt.textContent = opcionesDerivacion[0].nombre_destino + ' (auto)';
                            opt.selected = true;
                            select.appendChild(opt);
                            select.disabled = true;
                        } else {
                            const opt0 = document.createElement('option');
                            opt0.value = '';
                            opt0.textContent = 'Seleccionar destino...';
                            opt0.disabled = true;
                            opt0.selected = true;
                            select.appendChild(opt0);
                            opcionesDerivacion.forEach(op => {
                                const opt = document.createElement('option');
                                opt.value = op.hacia_nodo_id;
                                opt.textContent = op.nombre_destino + (op.es_default_auto ? ' (predeterminado)' : '');
                                select.appendChild(opt);
                            });
                        }
                        wrapper.appendChild(select);
                    }
                    
                    const btn = document.createElement('button');
                    btn.className = 'w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded';
                    btn.textContent = 'Derivar';
                    btn.onclick = function() {
                        const destinoId = document.getElementById('derivarDestinoSelect').value;
                        if (!destinoId) return alert('Seleccione un destino');
                        modal.classList.add('hidden');
                        derivarTramiteConDestino(idTramite, parseInt(destinoId));
                    };
                    wrapper.appendChild(btn);
                    cont.appendChild(wrapper);
                } else {
                    const btn = document.createElement('button');
                    
                    // Para MODO_AUTO, verificar si se puede reactivar
                    if (acc === 'MODO_AUTO') {
                        const checkUrl = `/tramites/${idTramite}/puede-reactivar-auto`;
                        fetch(checkUrl, { headers: { 'Accept': 'application/json' } })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.puede) {
                                    // Puede reactivar: botón normal
                                    btn.className = 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded w-full mb-2';
                                    btn.textContent = etiquetasAccion[acc] || acc;
                                    btn.onclick = function() {
                                        modal.classList.add('hidden');
                                        ejecutarAccion(acc, idTramite);
                                    };
                                } else {
                                    // No puede reactivar: botón deshabilitado con tooltip
                                    btn.className = 'bg-gray-200 text-gray-400 font-bold py-2 px-4 rounded w-full mb-2 cursor-not-allowed';
                                    btn.textContent = etiquetasAccion[acc] || acc;
                                    btn.disabled = true;
                                    btn.title = data.message || 'No se puede reactivar: el trámite está fuera de su ruta original o el nodo no tiene derivación automática configurada.';
                                }
                                cont.appendChild(btn);
                            })
                            .catch(() => {
                                // En caso de error de red, mostrar botón normal pero con advertencia
                                btn.className = 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded w-full mb-2';
                                btn.textContent = etiquetasAccion[acc] || acc;
                                btn.onclick = function() {
                                    modal.classList.add('hidden');
                                    ejecutarAccion(acc, idTramite);
                                };
                                cont.appendChild(btn);
                            });
                    } else {
                        // Otras acciones: botón normal
                        btn.className = 'bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded w-full mb-2';
                        btn.textContent = etiquetasAccion[acc] || acc;
                        btn.onclick = function() {
                            modal.classList.add('hidden');
                            ejecutarAccion(acc, idTramite);
                        };
                        cont.appendChild(btn);
                    }
                }
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
            case 'RETROCEDER':
                retrocederTramite(idTramite);
                break;
            case 'ACEPTAR':
                aceptarTramite(idTramite);
                break;
            case 'MODO_MANUAL':
                activarModoManualTramite(idTramite);
                break;
            case 'MODO_AUTO':
                desactivarModoManualTramite(idTramite);
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
            case 'SUBIR_OFICIO':
                mostrarModalOficio(idTramite);
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

    window.derivarTramite = async function(id) {
        const tInfo = tramitesCache[id];
        const reiniciaFlujo = tInfo && tInfo.id_nodo_actual && tInfo.nodo_puede_finalizar && tInfo.modo_derivacion !== 'MANUAL_FORZADO';
        const mensajeConfirm = reiniciaFlujo
            ? '¿Derivar este trámite? Al estar en el nivel final, la derivación automática lo enviará al inicio del flujo.'
            : '¿Está seguro de derivar este trámite al siguiente nivel?';
        if (!confirm(mensajeConfirm)) return;

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const headersJson = {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        let body = null;
        const t = tramitesCache[id];
        const necesitaElegir = t && t.id_nodo_actual && t.modo_derivacion === 'MANUAL_FORZADO';

        if (necesitaElegir) {
            try {
                const rOp = await fetch(`/tramites/${id}/flujo/derivacion-opciones`, { headers: { 'Accept': 'application/json' } });
                const op = await rOp.json();
                const tr = op.transiciones || [];
                if (tr.length > 1) {
                    const lineas = tr.map((x, i) => `${i + 1}) ${x.nombre_destino} (id destino ${x.hacia_nodo_id})`).join('\n');
                    const sel = prompt(`Seleccione destino (modo manual). Escriba el número de opción:\n\n${lineas}`);
                    if (!sel) return;
                    const idx = parseInt(sel, 10) - 1;
                    if (isNaN(idx) || idx < 0 || idx >= tr.length) {
                        alert('Opción no válida.');
                        return;
                    }
                    body = JSON.stringify({ hacia_nodo_id: tr[idx].hacia_nodo_id });
                } else if (tr.length === 1) {
                    body = JSON.stringify({ hacia_nodo_id: tr[0].hacia_nodo_id });
                }
            } catch (e) {
                alert('No se pudieron cargar las opciones de derivación.');
                return;
            }
        }

        fetch(`/tramites/${id}/derivar`, {
            method: 'POST',
            headers: body ? headersJson : {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
            },
            body: body || undefined,
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

    // Derivar en modo manual con destino específico (desde dropdown en modal)
    window.derivarTramiteConDestino = async function(id, destinoId) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const headersJson = {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        };

        fetch(`/tramites/${id}/derivar`, {
            method: 'POST',
            headers: headersJson,
            body: JSON.stringify({ hacia_nodo_id: destinoId }),
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

    window.retrocederTramite = function(id) {
        if (!confirm('¿Retroceder el trámite al paso anterior del flujo (derivación o ingreso)?')) return;
        fetch(`/tramites/${id}/flujo/retroceder`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Retroceso aplicado.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'No se pudo retroceder.');
            }
        })
        .catch(() => alert('Error de red al retroceder el trámite.'));
    };

    window.activarModoManualTramite = function(id) {
        if (!confirm('¿Activar derivación manual permanente para este trámite? Ya no se usará derivación automática.')) return;

        fetch(`/tramites/${id}/flujo/modo-manual`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Modo manual activado.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al activar el modo manual.');
            }
        })
        .catch(() => alert('Error de red al activar el modo manual.'));
    };

    window.desactivarModoManualTramite = function(id) {
        if (!confirm('¿Restaurar derivación automática para este trámite?')) return;

        fetch(`/tramites/${id}/flujo/modo-auto`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Derivación automática restaurada.');
                cargarTramites(paginaActual);
            } else {
                alert(data.message || 'Error al restaurar el modo automático.');
            }
        })
        .catch(() => alert('Error de red al restaurar el modo automático.'));
    };

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
            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-red-500">' + (data.message || 'Error al cargar datos') + '</td></tr>';
                return;
            }

            // 1. Mostrar posición actual
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

            // 2. Mostrar historial de movimientos (ruta real)
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

            // 3. Mostrar niveles estáticos (ruta esperada)
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

    // Ver observación modal
    window.verObservacion = function(idTramite) {
        const tramite = tramitesCache[idTramite];
        if (!tramite) return alert('No se encontró el trámite.');

        const modal = document.getElementById('modalObservacion');
        const textoDiv = document.getElementById('observacionTexto');
        const oficioSection = document.getElementById('oficioSection');
        const btnVerOficio = document.getElementById('btnVerOficioObservacion');

        // Mostrar el texto de la observación
        textoDiv.textContent = tramite.observaciones || 'Sin observación registrada.';

        // Mostrar/ocultar sección de oficio
        if (tramite.oficio && !tramite.requiere_oficio) {
            oficioSection.classList.remove('hidden');
            btnVerOficio.onclick = () => verOficio(tramite.oficio, idTramite);
        } else {
            oficioSection.classList.add('hidden');
        }

        modal.classList.remove('hidden');
    }

    window.cerrarModalObservacion = function() {
        document.getElementById('modalObservacion').classList.add('hidden');
    }

    // Validar archivo PDF en el cliente
    function validarArchivoPDF(input, errorId) {
        const errorDiv = document.getElementById(errorId);
        const submitBtn = input.id === 'corregir_archivo' ? document.getElementById('btnEnviarCorreccion') : document.getElementById('btnSubirOficio');
        
        if (!input.files || input.files.length === 0) {
            if (errorDiv) {
                errorDiv.textContent = 'Debe seleccionar un archivo';
                errorDiv.classList.remove('hidden');
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            return false;
        }

        const file = input.files[0];
        
        if (file.type !== 'application/pdf') {
            if (errorDiv) {
                errorDiv.textContent = 'Solo se permiten archivos PDF';
                errorDiv.classList.remove('hidden');
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            input.value = '';
            return false;
        }

        if (file.size > 10 * 1024 * 1024) {
            if (errorDiv) {
                errorDiv.textContent = 'El archivo no debe superar los 10MB';
                errorDiv.classList.remove('hidden');
            }
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            input.value = '';
            return false;
        }

        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        return true;
    }

    window.mostrarModalOficio = function(idTramite) {
        document.getElementById('id_tramite_oficio').value = idTramite;
        document.getElementById('modalOficio').classList.remove('hidden');
        // Reset form state
        document.getElementById('formOficio').reset();
        document.getElementById('oficio_archivo_error').classList.add('hidden');
        const btn = document.getElementById('btnSubirOficio');
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
    }

    window.cerrarModalOficio = function() {
        document.getElementById('modalOficio').classList.add('hidden');
        document.getElementById('formOficio').reset();
        document.getElementById('oficio_archivo_error').classList.add('hidden');
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
            document.getElementById('corregir_archivo_error').classList.add('hidden');
            const btn = document.getElementById('btnEnviarCorreccion');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        };
        
        document.getElementById('cerrarModalCorregirBtn').onclick = function() {
            document.getElementById('modalCorregir').classList.add('hidden');
            document.getElementById('formCorregir').reset();
            document.getElementById('corregirMensaje').textContent = '';
            document.getElementById('corregir_archivo_error').classList.add('hidden');
            const btn = document.getElementById('btnEnviarCorreccion');
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        };

        // Formulario de corregir
        document.getElementById('formCorregir').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const mensajeDiv = document.getElementById('corregirMensaje');
            
            // Validar archivo
            const archivoInput = document.getElementById('corregir_archivo');
            if (!validarArchivoPDF(archivoInput, 'corregir_archivo_error')) {
                return;
            }
            
            mensajeDiv.textContent = 'Enviando...';
            mensajeDiv.className = 'mt-3 text-sm text-blue-600';
            
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
                    mensajeDiv.className = 'mt-3 text-sm text-green-600';
                    setTimeout(() => {
                        document.getElementById('modalCorregir').classList.add('hidden');
                        this.reset();
                        mensajeDiv.textContent = '';
                        document.getElementById('corregir_archivo_error').classList.add('hidden');
                        const btn = document.getElementById('btnEnviarCorreccion');
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                        cargarTramites(paginaActual);
                    }, 1200);
                } else {
                    mensajeDiv.textContent = data.message || 'Error al corregir el trámite.';
                    mensajeDiv.className = 'mt-3 text-sm text-red-600';
                }
            })
            .catch(() => {
                mensajeDiv.textContent = 'Error al enviar la corrección.';
                mensajeDiv.className = 'mt-3 text-sm text-red-600';
            });
        };

        // Formulario de oficio (usuario externo)
        document.getElementById('formOficio').onsubmit = function(e) {
            e.preventDefault();
            
            const archivoInput = document.getElementById('archivo_oficio');
            if (!validarArchivoPDF(archivoInput, 'oficio_archivo_error')) {
                return;
            }
            
            const formData = new FormData(this);
            const btn = document.getElementById('btnSubirOficio');
            const originalText = btn.textContent;
            btn.textContent = 'Subiendo...';
            btn.disabled = true;
            
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
            .catch(() => alert('Error de red al intentar subir el oficio'))
            .finally(() => {
                btn.textContent = originalText;
            });
        };

        // Formulario de observar con oficio (usuario interno)
        document.getElementById('formObservarConOficio').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const observacion = formData.get('observacion');
            const idTramite = formData.get('id_tramite');
            
            if (!observacion || observacion.trim() === '') {
                alert('Por favor ingrese el motivo de la observación');
                return;
            }
            
            fetch(`/tramites/${idTramite}/observar-con-oficio`, {
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
                    alert('Trámite observado con oficio correctamente.');
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

        console.log('=== Calling cargarTramites(1) ===');
        // Cargar trámites inicialmente
        cargarTramites(1);
    });
</script>
@endpush
