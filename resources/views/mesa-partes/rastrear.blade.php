{{--
    Vista de Rastrear Trámites
    Para usuarios externos e internos
--}}
@extends('layouts.mesa-partes')

@section('title', 'Rastrear Trámites · UGEL Santa')

@section('header', 'Rastrear Trámites')

@section('subtitle', 'Busque expedientes por número, voucher o DNI del remitente, y afine con procedimiento, estado y fechas.')

@section('content')

    {{-- ══════ Filtros ══════ --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-5 overflow-hidden">
        <div class="flex items-center gap-2.5 px-5 sm:px-6 py-4 border-b border-slate-100">
            <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
            <div>
                <h3 class="text-[15px] font-extrabold text-slate-900 leading-tight">Buscar expediente</h3>
                <p class="text-[12px] text-slate-500">Combine criterios para localizar un trámite</p>
            </div>
        </div>

        <form id="searchForm" class="px-5 sm:px-6 py-5">
            @csrf
            <p class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Buscar por</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                <div>
                    <label for="numeroTramite" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">N° de Trámite</label>
                    <input type="number" id="numeroTramite" name="numero_tramite" min="1"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                           placeholder="Ej. 4">
                </div>
                <div>
                    <label for="numeroVoucher" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Número de Voucher</label>
                    <input type="text" id="numeroVoucher" name="numero_voucher"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                           placeholder="Voucher del expediente">
                </div>
                <div>
                    <label for="dniRemitente" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">DNI del Remitente</label>
                    <input type="text" id="dniRemitente" name="dni_remitente" maxlength="8" inputmode="numeric"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                           placeholder="8 dígitos">
                </div>
            </div>

            <p class="text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-3">Filtrar por</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="tipoProcedimiento" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Procedimiento</label>
                    <select id="tipoProcedimiento" name="tipo_procedimiento"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                        <option value="">Todos</option>
                        @foreach($procedimientos as $procedimiento)
                            <option value="{{ $procedimiento->id }}">
                                {{ $procedimiento->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="estado" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Estado</label>
                    <select id="estado" name="estado" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                        <option value="">Todos</option>
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="EN_PROCESO">En proceso</option>
                        <option value="OBSERVADO">Observado</option>
                        <option value="VERIFICADO">Verificado</option>
                        <option value="FINALIZADO">Finalizado</option>
                        <option value="RECHAZADO">Rechazado</option>
                    </select>
                </div>
                <div>
                    <label for="fechaDesde" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Fecha Desde</label>
                    <input type="date" id="fechaDesde" name="fecha_desde"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" max="">
                </div>
                <div>
                    <label for="fechaHasta" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Fecha Hasta</label>
                    <input type="date" id="fechaHasta" name="fecha_hasta"
                           class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" max="" min="">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-5">
                <button type="submit"
                        class="inline-flex justify-center items-center gap-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-sm font-bold py-2.5 px-6 shadow-md shadow-primary-700/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Buscar
                </button>
                <button type="button" id="btnLimpiar"
                        class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-bold py-2.5 px-6 transition">
                    Limpiar
                </button>
            </div>
        </form>
    </section>

    {{-- ══════ Resultados ══════ --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-[15px] font-extrabold text-slate-900">Resultados de búsqueda</h3>
            <p class="text-[12px] text-slate-500">Los expedientes que coincidan aparecerán aquí</p>
        </div>

        <div id="resultadosBusqueda" class="px-5 sm:px-6 py-6">
            <div class="flex flex-col items-center gap-2 py-6">
                <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <p class="text-[14px] font-extrabold text-slate-700">Sin búsqueda activa</p>
                <p class="text-[13px] text-slate-400">Use los filtros de arriba para localizar un trámite.</p>
            </div>
        </div>

        <div id="loadingIndicator" class="hidden px-6 py-10 text-center">
            <div class="mp-spinner inline-block w-8 h-8 rounded-full border-[3px] border-slate-200 border-t-primary-600"></div>
            <p class="mt-3 text-sm font-semibold text-slate-500">Cargando…</p>
        </div>
    </section>

<!-- Modal Hoja de Ruta -->
<div id="hojaRutaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[88vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
            <div>
                <h3 class="text-[16px] font-extrabold text-slate-900 leading-tight">Hoja de ruta</h3>
                <p class="text-[12px] text-slate-500">Recorrido del expediente por niveles</p>
            </div>
            <button onclick="cerrarHojaRuta()" class="w-9 h-9 rounded-xl hover:bg-slate-200/70 text-slate-500 hover:text-slate-800 flex items-center justify-center transition" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto mp-scroll p-5 sm:p-6">
            <table class="min-w-full text-sm bg-white border border-slate-200 rounded-xl overflow-hidden">
                <tbody id="hr-niveles-tbody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Corregir Trámite -->
<div id="modalCorregir" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md relative">
        <button onclick="cerrarModalCorregir()" class="absolute top-4 right-4 w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xl leading-none" aria-label="Cerrar">×</button>
        <h3 class="text-[17px] font-extrabold text-slate-900">Corregir trámite</h3>
        <p class="text-[13px] text-slate-500 mt-1 mb-4">Adjunte el documento corregido en PDF para subsanar la observación.</p>
        <form id="formCorregir" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_tramite" id="corregir_id_tramite">
            <label for="corregir_archivo" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Archivo corregido (PDF) <span class="text-red-500">*</span></label>
            <input type="file" name="archivo_adjunto" id="corregir_archivo" accept="application/pdf"
                   class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500" required />
            <div id="corregirMensaje" class="mt-3 text-sm"></div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="cerrarModalCorregir()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition">
                    Enviar Corrección
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    .estado-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 11px; border-radius:999px; font-size:11.5px; font-weight:700; white-space:nowrap; border:1px solid transparent; }
    .estado-pill::before { content:''; width:6px; height:6px; border-radius:999px; background:currentColor; }
    .estado-pendiente { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .estado-observado { background:#fffbeb; color:#b45309; border-color:#fde68a; }
    .estado-proceso { background:#ecfdf5; color:#047857; border-color:#a7f3d0; }
    .estado-verificado { background:#faf5ff; color:#7e22ce; border-color:#e9d5ff; }
    .estado-finalizado { background:#eef2ff; color:#4338ca; border-color:#c7d2fe; }
    .estado-rechazado { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .icon-btn { display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:11.5px; font-weight:700; padding:6px 10px; border-radius:10px; transition:all .15s ease; white-space:nowrap; }
</style>
@endpush

@push('scripts')
<script>
    function openRastrearModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeRastrearModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

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
                    window.app.toast('La fecha "Hasta" debe ser igual o posterior a la fecha "Desde".', 'warning');
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
                window.app.toast('La fecha "Desde" no puede ser superior a la fecha actual.', 'warning');
                if (fechaDesdeInput) fechaDesdeInput.value = '';
                return false;
            }

            if (fechaHasta && fechaHasta > fechaActual) {
                window.app.toast('La fecha "Hasta" no puede ser superior a la fecha actual.', 'warning');
                if (fechaHastaInput) fechaHastaInput.value = '';
                return false;
            }

            // Validar que "hasta" sea >= "desde"
            if (fechaDesde && fechaHasta && fechaHasta < fechaDesde) {
                window.app.toast('La fecha "Hasta" debe ser igual o posterior a la fecha "Desde".', 'warning');
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
                    <div class="flex flex-col items-center gap-2 py-8">
                        <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <p class="text-[14px] font-extrabold text-slate-700">Sin coincidencias</p>
                        <p class="text-[13px] text-slate-400">No hay trámites que coincidan con los criterios de búsqueda.</p>
                    </div>
                `;
                return;
            }

            const wrapper = document.createElement('div');
            wrapper.className = 'overflow-x-auto mp-scroll -mx-5 sm:-mx-6 px-5 sm:px-6';
            const tabla = document.createElement('table');
            tabla.className = 'min-w-full text-sm';
            tabla.innerHTML = `
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Voucher</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Procedimiento</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                </tbody>
            `;

            const tbody = tabla.querySelector('tbody');
            tramites.forEach(tramite => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50/80 bg-white';
                tr.innerHTML = `
                    <td class="px-4 py-3.5 whitespace-nowrap text-[13px] font-extrabold text-slate-900">${tramite.numero_voucher}</td>
                    <td class="px-4 py-3.5"><span class="block max-w-[220px] truncate text-[13px] font-semibold text-slate-700">${tramite.procedimiento_nombre}</span></td>
                    <td class="px-4 py-3.5 whitespace-nowrap">
                        <span class="estado-pill ${obtenerClaseEstado(tramite.estado)}">
                            ${formatearEstado(tramite.estado)}
                        </span>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-[12.5px] font-medium text-slate-500">${tramite.fecha_formateada}</td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button onclick="verHojaRuta(${tramite.id})" class="icon-btn bg-white border border-slate-200 text-slate-600 hover:border-primary-300 hover:text-primary-700 hover:bg-primary-50">
                                Hoja de ruta
                            </button>
                            ${tramite.estado === 'OBSERVADO' ? `<button onclick="mostrarModalCorregir(${tramite.id})" class="icon-btn bg-amber-500 hover:bg-amber-600 text-white">Corregir</button>` : ''}
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });

            resultadosBusqueda.innerHTML = '';
            const count = document.createElement('p');
            count.className = 'text-[12.5px] font-semibold text-slate-500 mb-3';
            count.innerHTML = `<span class="font-extrabold text-slate-800">${tramites.length}</span> resultado(s) encontrado(s)`;
            resultadosBusqueda.appendChild(count);
            resultadosBusqueda.appendChild(wrapper);
            wrapper.appendChild(tabla);
        }

        function mostrarError(mensaje) {
            resultadosBusqueda.innerHTML = `
                <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-sm text-red-800 flex items-start gap-2.5" role="alert">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>${mensaje}</span>
                </div>
            `;
        }

        function obtenerClaseEstado(estado) {
            const clases = {
                'PENDIENTE': 'estado-pendiente',
                'EN_PROCESO': 'estado-proceso',
                'OBSERVADO': 'estado-observado',
                'VERIFICADO': 'estado-verificado',
                'FINALIZADO': 'estado-finalizado',
                'RECHAZADO': 'estado-rechazado'
            };
            return clases[estado] || 'estado-pendiente';
        }

        function formatearEstado(estado) {
            if (!estado) return '';

            const etiquetas = {
                'PENDIENTE': 'Pendiente',
                'OBSERVADO': 'Observado',
                'EN_PROCESO': 'En proceso',
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
            tbody.innerHTML = '<tr><td class="py-8 text-center text-sm text-slate-400">Cargando…</td></tr>';
            openRastrearModal('hojaRutaModal');

            fetch(`/mesa-partes/hoja-ruta/${idTramite}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                tbody.innerHTML = '';
                if (!data.success) {
                    tbody.innerHTML = '<tr><td class="py-6 text-center text-sm font-semibold text-red-500">' + (data.message || 'Error al cargar datos') + '</td></tr>';
                    return;
                }

                // 1. Posición actual
                if (data.posicion_actual) {
                    const pos = data.posicion_actual;
                    tbody.innerHTML += `
                        <tr class="bg-primary-50">
                            <td class="py-3 px-4 text-[13px]" colspan="2">📍 <strong>Posición actual:</strong> ${pos.nodo_nombre} (Orden ${pos.nodo_orden})
                                <span class="ml-2 px-2 py-0.5 text-[11px] font-bold rounded-full ${pos.puede_finalizar ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'}">
                                    ${pos.puede_finalizar ? 'Nodo final' : 'Nodo intermedio'}
                                </span>
                                <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full ${pos.modo_derivacion === 'MANUAL_FORZADO' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">
                                    ${pos.modo_derivacion === 'MANUAL_FORZADO' ? 'Manual' : 'Automático'}
                                </span>
                            </td>
                        </tr>
                    `;
                }

                // 2. Historial de movimientos (ruta real)
                if (data.historial && data.historial.length > 0) {
                    tbody.innerHTML += `
                        <tr class="bg-slate-100">
                            <td class="py-2 px-4 text-[12px] font-extrabold uppercase tracking-wide text-slate-500" colspan="2">Historial de movimientos (${data.historial.length})</td>
                        </tr>
                    `;
                    data.historial.forEach((mov, index) => {
                        const desde = mov.nodo_desde ? `${mov.nodo_desde.nombre} (Ord: ${mov.nodo_desde.orden})` : '—';
                        const hacia = mov.nodo_hacia ? `${mov.nodo_hacia.nombre} (Ord: ${mov.nodo_hacia.orden})` : '—';
                        const usuario = mov.usuario ? `${mov.usuario.nombre} (${mov.usuario.email})` : 'Sistema';

                        tbody.innerHTML += `
                            <tr class="${index % 2 === 0 ? 'bg-white' : 'bg-slate-50'}">
                                <td class="py-2.5 px-4 text-[12px] text-slate-500 whitespace-nowrap">${mov.fecha}<br><span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">${mov.accion_legible}</span></td>
                                <td class="py-2.5 px-4 text-[13px]"><div><strong>De:</strong> ${desde}</div><div><strong>A:</strong> ${hacia}</div><div class="text-[12px] text-slate-500 mt-0.5">${usuario}</div></td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML += `
                        <tr>
                            <td class="py-4 px-4 text-center text-[13px] text-slate-400" colspan="2">Sin historial de movimientos</td>
                        </tr>
                    `;
                }

                // 3. Ruta esperada del procedimiento
                if (data.niveles_estaticos && data.niveles_estaticos.length > 0) {
                    tbody.innerHTML += `
                        <tr class="bg-slate-100">
                            <td class="py-2 px-4 text-[12px] font-extrabold uppercase tracking-wide text-slate-500" colspan="2">Ruta esperada</td>
                        </tr>
                    `;
                    data.niveles_estaticos.forEach(nivel => {
                        const esActual = data.posicion_actual && data.posicion_actual.nodo_id
                            && nivel.id_nodo && data.posicion_actual.nodo_id === nivel.id_nodo;
                        tbody.innerHTML += `
                            <tr class="${esActual ? 'bg-primary-50 font-semibold' : ''}">
                                <td class="py-2 px-4 text-center font-extrabold text-slate-700">${nivel.nivel}</td>
                                <td class="py-2 px-4 text-[13px]">${nivel.nombre} ${esActual ? '<span class="ml-2 px-2 py-0.5 rounded-full text-[11px] font-bold bg-primary-600 text-white">ACTUAL</span>' : ''}</td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML += '<tr><td colspan="2" class="py-6 text-center text-[13px] text-slate-400">No disponible</td></tr>';
                }
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="2" class="py-6 text-center text-sm font-semibold text-red-500">Error al cargar datos</td></tr>';
            });
        };

        window.cerrarHojaRuta = function() {
            closeRastrearModal('hojaRutaModal');
        };

        window.mostrarModalCorregir = function(idTramite) {
            document.getElementById('corregir_id_tramite').value = idTramite;
            openRastrearModal('modalCorregir');
        };

        window.cerrarModalCorregir = function() {
            closeRastrearModal('modalCorregir');
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
                mensajeDiv.className = 'mt-3 text-sm text-red-600';
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
                    mensajeDiv.className = 'mt-3 text-sm text-green-600';
                    setTimeout(() => {
                        cerrarModalCorregir();
                        buscarTramites();
                    }, 1500);
                } else {
                    mensajeDiv.textContent = data.message || 'Error al corregir el trámite.';
                    mensajeDiv.className = 'mt-3 text-sm text-red-600';
                }
            })
            .catch(() => {
                mensajeDiv.textContent = 'Error al enviar la corrección.';
                mensajeDiv.className = 'mt-3 text-sm text-red-600';
            });
        });

        document.getElementById('btnLimpiar').addEventListener('click', function() {
            searchForm.reset();
            resultadosBusqueda.innerHTML = `
                <div class="flex flex-col items-center gap-2 py-6">
                    <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <p class="text-[14px] font-extrabold text-slate-700">Sin búsqueda activa</p>
                    <p class="text-[13px] text-slate-400">Use los filtros de arriba para localizar un trámite.</p>
                </div>
            `;
        });

        // Cerrar modales con clic fuera / Escape (bloqueado durante mutaciones)
        ['hojaRutaModal', 'modalCorregir'].forEach(id => {
            const m = document.getElementById(id);
            if (m) m.addEventListener('click', function(e) { if (e.target === m && !(window.app && window.app.mutationsInFlight > 0)) closeRastrearModal(id); });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !(window.app && window.app.mutationsInFlight > 0)) ['hojaRutaModal', 'modalCorregir'].forEach(closeRastrearModal);
        });

        // Búsqueda directa desde enlaces internos (?codigo=N): prellena y ejecuta.
        try {
            const codigo = new URLSearchParams(window.location.search).get('codigo');
            const campoNumero = document.getElementById('numeroTramite');
            if (codigo && campoNumero) {
                campoNumero.value = codigo;
                buscarTramites();
            }
        } catch (e) { /* sin soporte de URLSearchParams: búsqueda manual */ }
    });
</script>
@endpush
