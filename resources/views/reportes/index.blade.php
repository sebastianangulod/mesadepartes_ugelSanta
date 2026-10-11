@extends('layouts.mesa-partes')

@section('title', 'Reportes de Trámites · UGEL Santa')
@section('header', 'Reportes de Trámites')
@section('subtitle', 'Estadísticas de solo lectura sobre los trámites ingresados en el periodo.')

@section('actions')
    <span id="acciones-reporte" class="contents">
    <button type="button" onclick="window.print()"
        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[13px] font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Imprimir
    </button>
    <a id="btnExportarCsv" href="#"
        class="inline-flex items-center gap-2 rounded-xl bg-primary-700 hover:bg-primary-800 px-4 py-2.5 text-[13px] font-bold text-white shadow-md shadow-primary-700/20 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Exportar CSV
    </a>
    </span>
@endsection

@section('content')
    {{-- ══════ Selector de periodo ══════ --}}
    <section id="filtros-reporte" class="bg-white rounded-2xl border border-slate-200 shadow-sm px-5 sm:px-6 py-4 mb-5 flex flex-col md:flex-row md:items-center gap-4">
        <div class="inline-flex rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden self-start">
            <button type="button" data-rtipo="semana" class="rtipo-btn px-5 py-2 text-[13px] font-bold bg-primary-700 text-white">Semana</button>
            <button type="button" data-rtipo="mes" class="rtipo-btn px-5 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 border-l border-slate-200">Mes</button>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="repPrev" class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 font-extrabold hover:bg-slate-50 transition" aria-label="Periodo anterior">‹</button>
            <button type="button" id="repToday" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-[12.5px] font-bold text-slate-600 hover:bg-slate-50 transition">Actual</button>
            <button type="button" id="repNext" class="w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-600 font-extrabold hover:bg-slate-50 transition" aria-label="Periodo siguiente">›</button>
        </div>
        <p class="md:ml-auto text-[13.5px] font-extrabold text-slate-800">Periodo: <span id="repEtiqueta" class="text-primary-700">—</span></p>
    </section>

    {{-- ══════ KPIs ══════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Ingresados</p>
            <p id="kpiIngresados" class="mt-1 text-[26px] leading-none font-extrabold text-slate-900">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Finalizados</p>
            <p id="kpiFinalizados" class="mt-1 text-[26px] leading-none font-extrabold text-emerald-600">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">% Finalización</p>
            <p id="kpiPorcentaje" class="mt-1 text-[26px] leading-none font-extrabold text-primary-700">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Observados</p>
            <p id="kpiObservados" class="mt-1 text-[26px] leading-none font-extrabold text-amber-600">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Tiempo medio (días)</p>
            <p id="kpiTiempo" class="mt-1 text-[26px] leading-none font-extrabold text-slate-900">—</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Vencidos en bandeja</p>
            <p id="kpiVencidos" class="mt-1 text-[26px] leading-none font-extrabold text-red-600">—</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">
        {{-- ══════ Por estado ══════ --}}
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
            <h3 class="text-[15px] font-extrabold text-slate-900">Por estado</h3>
            <p class="text-[12px] text-slate-500 mb-4">Distribución de los ingresados en el periodo</p>
            <div id="barsEstado" class="space-y-3"></div>
        </section>

        <div class="space-y-5">
            {{-- ══════ Presencial / Virtual ══════ --}}
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900">Por modalidad</h3>
                <p class="text-[12px] text-slate-500 mb-4">Presenciales frente a virtuales</p>
                <div id="barsTipo" class="space-y-3"></div>
            </section>

            {{-- ══════ Top procedimientos ══════ --}}
            <section class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900">Top procedimientos</h3>
                <p class="text-[12px] text-slate-500 mb-4">Los 5 con más ingresos del periodo</p>
                <div id="barsProc" class="space-y-3"></div>
            </section>
        </div>
    </div>

    {{-- ══════ Detalle ══════ --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div>
                <h3 class="text-[15px] font-extrabold text-slate-900">Detalle de expedientes</h3>
                <p class="text-[12px] text-slate-500"><span id="repTotalTop" class="font-bold text-slate-700">0</span> registros en el periodo</p>
            </div>
            <div class="flex items-center gap-2">
                <label for="repPorPagina" class="text-[12.5px] font-bold text-slate-500">Mostrar</label>
                <select id="repPorPagina" class="rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-[12.5px] font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-[12.5px] font-bold text-slate-500">por página</span>
            </div>
        </div>

        <div class="overflow-x-auto mp-scroll">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Exp.</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Procedimiento</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Titular</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-center">Días</th>
                    </tr>
                </thead>
                <tbody id="repTableBody" class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        <div id="repPaginacion" class="px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60">
            <p class="text-[13px] text-slate-500 order-2 sm:order-1">
                Mostrando <span id="repInicio" class="font-bold text-slate-800">0</span> a <span id="repFin" class="font-bold text-slate-800">0</span> de <span id="repTotal" class="font-bold text-slate-800">0</span> resultados
            </p>
            <nav class="order-1 sm:order-2 inline-flex items-center rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden" aria-label="Paginación">
                <button id="repPrevPage" class="px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition border-r border-slate-200">← Anterior</button>
                <span class="px-4 py-2 text-[13px] font-extrabold text-primary-800 bg-primary-50">Pág. <span id="repPagina">1</span></span>
                <button id="repNextPage" class="px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition border-l border-slate-200">Siguiente →</button>
            </nav>
        </div>
    </section>
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
    .rep-bar { height: 10px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .rep-bar > i { display: block; height: 100%; border-radius: 999px; }
    @media print {
        #filtros-reporte, #acciones-reporte, #repPaginacion, .mp-topbar, footer.mp-footer { display: none !important; }
        main { padding: 0 !important; }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    let tipo = 'semana';
    let offset = 0;
    let pagina = 1;
    let porPagina = 10;
    let totalPaginas = 1;

    const COLORES_ESTADO = {
        'PENDIENTE': ['#1d4ed8', 'Pendiente'],
        'EN_PROCESO': ['#047857', 'En proceso'],
        'OBSERVADO': ['#b45309', 'Observado'],
        'VERIFICADO': ['#7e22ce', 'Verificado'],
        'FINALIZADO': ['#4338ca', 'Finalizado'],
        'RECHAZADO': ['#b91c1c', 'Rechazado']
    };
    const CLASES_ESTADO = {
        'PENDIENTE': 'estado-pendiente', 'EN_PROCESO': 'estado-proceso', 'OBSERVADO': 'estado-observado',
        'VERIFICADO': 'estado-verificado', 'FINALIZADO': 'estado-finalizado', 'RECHAZADO': 'estado-rechazado'
    };
    const ORDEN_ESTADOS = ['PENDIENTE', 'EN_PROCESO', 'OBSERVADO', 'VERIFICADO', 'FINALIZADO', 'RECHAZADO'];

    function barra(contenedor, nombre, total, maximo, color) {
        const pct = maximo > 0 ? Math.round(total * 100 / maximo) : 0;
        const fila = document.createElement('div');
        const head = document.createElement('div');
        head.className = 'flex items-center justify-between gap-3 mb-1.5';
        const n = document.createElement('span');
        n.className = 'text-[13px] font-semibold text-slate-700 truncate';
        n.textContent = nombre;
        n.title = nombre;
        const t = document.createElement('span');
        t.className = 'text-[13px] font-extrabold text-slate-900 tabular-nums';
        t.textContent = total;
        head.appendChild(n);
        head.appendChild(t);
        const bar = document.createElement('div');
        bar.className = 'rep-bar';
        const fill = document.createElement('i');
        fill.style.width = pct + '%';
        fill.style.backgroundColor = color;
        bar.appendChild(fill);
        fila.appendChild(head);
        fila.appendChild(bar);
        contenedor.appendChild(fila);
    }

    function vacio(contenedor, texto) {
        contenedor.innerHTML = '<p class="text-[13px] font-medium text-slate-400 py-2">' + texto + '</p>';
    }

    function cargar() {
        const params = new URLSearchParams({ tipo: tipo, offset: offset, pagina: pagina, por_pagina: porPagina });
        fetch('/mesa-partes/reportes/ajax?' + params.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d || d.status !== 'success') {
                    window.app.toast('No se pudo cargar el reporte.', 'error');
                    return;
                }
                document.getElementById('repEtiqueta').textContent = d.etiqueta || '';
                const k = d.kpis || {};
                document.getElementById('kpiIngresados').textContent = k.ingresados ?? 0;
                document.getElementById('kpiFinalizados').textContent = k.finalizados ?? 0;
                document.getElementById('kpiPorcentaje').textContent = (k.porcentaje ?? 0) + '%';
                document.getElementById('kpiObservados').textContent = k.observados ?? 0;
                document.getElementById('kpiTiempo').textContent = k.tiempo_medio === null || k.tiempo_medio === undefined ? '—' : k.tiempo_medio;
                document.getElementById('kpiVencidos').textContent = k.vencidos ?? 0;

                // Por estado
                const be = document.getElementById('barsEstado');
                be.innerHTML = '';
                const estados = d.por_estado || {};
                const maxE = Math.max.apply(null, [0].concat(Object.values(estados)));
                if (!Object.keys(estados).length) vacio(be, 'Sin movimientos en el periodo.');
                ORDEN_ESTADOS.forEach(function(e) {
                    if (estados[e] === undefined) return;
                    barra(be, (COLORES_ESTADO[e] ? COLORES_ESTADO[e][1] : e), estados[e], maxE, COLORES_ESTADO[e][0]);
                });

                // Por modalidad
                const bt = document.getElementById('barsTipo');
                bt.innerHTML = '';
                const tipos = d.por_tipo || {};
                const maxT = Math.max.apply(null, [0].concat(Object.values(tipos)));
                if (!Object.keys(tipos).length) vacio(bt, 'Sin movimientos en el periodo.');
                Object.keys(tipos).forEach(function(t) {
                    barra(bt, t === 'PRESENCIAL' ? 'Presencial' : 'Virtual', tipos[t], maxT, t === 'PRESENCIAL' ? '#b45309' : '#1d4ed8');
                });

                // Top procedimientos
                const bp = document.getElementById('barsProc');
                bp.innerHTML = '';
                const procs = d.por_procedimiento || [];
                if (!procs.length) vacio(bp, 'Sin movimientos en el periodo.');
                const maxP = procs.length ? Math.max.apply(null, procs.map(function(p) { return p.total; })) : 0;
                procs.forEach(function(p) { barra(bp, p.nombre, p.total, maxP, '#1a4078'); });

                // Detalle
                const det = (d.detalle) || {};
                const items = det.items || [];
                const tbody = document.getElementById('repTableBody');
                tbody.innerHTML = '';
                if (!items.length) {
                    tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center"><p class="text-sm font-extrabold text-slate-700">Sin expedientes en el periodo</p><p class="text-[13px] text-slate-400 mt-1">Pruebe con otro periodo.</p></td></tr>';
                }
                items.forEach(function(t) {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50/80 bg-white';
                    const tdExp = document.createElement('td');
                    tdExp.className = 'px-5 py-3.5 whitespace-nowrap';
                    const badge = document.createElement('span');
                    badge.className = 'inline-flex items-center rounded-lg bg-slate-900 text-white text-[12px] font-extrabold px-2.5 py-1';
                    badge.textContent = '#' + t.id;
                    tdExp.appendChild(badge);
                    tr.appendChild(tdExp);

                    const tdFecha = document.createElement('td');
                    tdFecha.className = 'px-4 py-3.5 whitespace-nowrap text-[12.5px] font-medium text-slate-500';
                    tdFecha.textContent = t.fecha;
                    tr.appendChild(tdFecha);

                    const tdProc = document.createElement('td');
                    tdProc.className = 'px-4 py-3.5';
                    const sp = document.createElement('span');
                    sp.className = 'block max-w-[220px] truncate text-[13px] font-semibold text-slate-700';
                    sp.textContent = t.procedimiento;
                    sp.title = t.procedimiento;
                    tdProc.appendChild(sp);
                    tr.appendChild(tdProc);

                    const tdTit = document.createElement('td');
                    tdTit.className = 'px-4 py-3.5';
                    const tn = document.createElement('span');
                    tn.className = 'block max-w-[200px] truncate text-[13px] font-semibold text-slate-700';
                    tn.textContent = t.titular;
                    tdTit.appendChild(tn);
                    if (t.documento && t.documento !== '—') {
                        const td = document.createElement('span');
                        td.className = 'block max-w-[200px] truncate text-[11px] font-medium text-slate-400';
                        td.textContent = 'Doc: ' + t.documento;
                        tdTit.appendChild(td);
                    }
                    tdTit.title = t.titular;
                    tr.appendChild(tdTit);

                    const tdEst = document.createElement('td');
                    tdEst.className = 'px-4 py-3.5 whitespace-nowrap';
                    const pill = document.createElement('span');
                    pill.className = 'estado-pill ' + (CLASES_ESTADO[t.estado] || 'estado-pendiente');
                    pill.textContent = (COLORES_ESTADO[t.estado] ? COLORES_ESTADO[t.estado][1] : t.estado);
                    tdEst.appendChild(pill);
                    tr.appendChild(tdEst);

                    const tdDias = document.createElement('td');
                    tdDias.className = 'px-4 py-3.5 whitespace-nowrap text-center text-[13px] font-extrabold ' + (t.dias >= 30 ? 'text-red-600' : 'text-slate-700');
                    tdDias.textContent = t.dias;
                    tr.appendChild(tdDias);

                    tbody.appendChild(tr);
                });

                pagina = det.pagina_actual || 1;
                totalPaginas = det.paginas || 1;
                const total = det.total || 0;
                document.getElementById('repPagina').textContent = pagina;
                document.getElementById('repTotal').textContent = total;
                document.getElementById('repTotalTop').textContent = total;
                document.getElementById('repInicio').textContent = items.length ? ((pagina - 1) * porPagina + 1) : 0;
                document.getElementById('repFin').textContent = items.length ? ((pagina - 1) * porPagina + items.length) : 0;
                document.getElementById('repPrevPage').disabled = pagina <= 1;
                document.getElementById('repNextPage').disabled = pagina >= totalPaginas || totalPaginas === 0;

                const exp = document.getElementById('btnExportarCsv');
                if (exp) exp.href = '/mesa-partes/reportes/exportar?' + new URLSearchParams({ tipo: tipo, offset: offset }).toString();
            })
            .catch(function() {
                window.app.toast('Error al cargar el reporte.', 'error');
            });
    }

    function setTipo(nt) {
        tipo = nt;
        offset = 0;
        pagina = 1;
        document.querySelectorAll('.rtipo-btn').forEach(function(b) {
            const activo = b.dataset.rtipo === nt;
            b.classList.toggle('bg-primary-700', activo);
            b.classList.toggle('text-white', activo);
            b.classList.toggle('text-slate-600', !activo);
        });
        cargar();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.rtipo-btn').forEach(function(b) {
            b.addEventListener('click', function() { setTipo(b.dataset.rtipo); });
        });
        document.getElementById('repPrev').addEventListener('click', function() { offset--; pagina = 1; cargar(); });
        document.getElementById('repNext').addEventListener('click', function() { offset++; pagina = 1; cargar(); });
        document.getElementById('repToday').addEventListener('click', function() { offset = 0; pagina = 1; cargar(); });
        document.getElementById('repPorPagina').addEventListener('change', function() {
            porPagina = parseInt(this.value, 10) || 10;
            pagina = 1;
            cargar();
        });
        document.getElementById('repPrevPage').addEventListener('click', function() {
            if (pagina > 1) { pagina--; cargar(); }
        });
        document.getElementById('repNextPage').addEventListener('click', function() {
            if (pagina < totalPaginas) { pagina++; cargar(); }
        });
        cargar();
    });
})();
</script>
@endpush
