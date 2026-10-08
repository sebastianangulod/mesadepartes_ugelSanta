{{-- Configuración del flujo por nodos (admin) --}}
@extends('layouts.admin')

@section('title', 'Flujo del procedimiento — Mesa de Partes')

@push('styles')
<style>
    .node-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #fff;
        transition: box-shadow 0.2s, border-color 0.2s;
    }
    .node-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #d1d5db;
    }
    .node-header {
        border-bottom: 1px solid #f3f4f6;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    .badge-flag {
        font-size: 0.65rem;
        font-weight: 600;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    .badge-flag.active { background: #dcfce7; color: #166534; }
    .badge-flag.inactive { background: #f3f4f6; color: #6b7280; }
    .section-card { border: 1px solid #e5e7eb; border-radius: 0.75rem; background: #fff; }
    .section-header { border-bottom: 1px solid #f3f4f6; border-radius: 0.75rem 0.75rem 0 0; }
    .transition-row { background: #f9fafb; border-radius: 0.375rem; }
    .team-card { background: #f9fafb; border: 1px solid #f3f4f6; border-radius: 0.5rem; }
    .member-tag { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.375rem; font-size: 0.7rem; }
    .tooltipped { position: relative; cursor: help; }
    .tooltipped:hover::after {
        content: attr(data-tip);
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: #fff;
        padding: 0.4rem 0.6rem;
        border-radius: 0.375rem;
        font-size: 0.7rem;
        white-space: nowrap;
        z-index: 50;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .tooltipped:hover::before {
        content: '';
        position: absolute;
        bottom: 118%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-top-color: #1f2937;
        z-index: 50;
    }
    .input-sm { @apply border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full; }
    .select-sm { @apply border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500; }
    .btn-sm { @apply px-3 py-1.5 rounded text-sm font-medium transition-colors; }
    .btn-primary-sm { @apply btn-sm bg-blue-600 text-white hover:bg-blue-700; }
    .btn-secondary-sm { @apply btn-sm bg-gray-200 text-gray-700 hover:bg-gray-300; }
    .btn-danger-sm { @apply btn-sm bg-red-100 text-red-600 hover:bg-red-200; }
    .btn-purple-sm { @apply btn-sm bg-purple-100 text-purple-700 hover:bg-purple-200; }
    .btn-indigo-sm { @apply btn-sm bg-indigo-100 text-indigo-700 hover:bg-indigo-200; }
    .label-xs { @apply block text-xs text-gray-500 mb-1; }
    .checkbox-wrap { @apply flex items-center gap-1.5 cursor-pointer; }
    .checkbox-wrap input { @apply w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500; }
</style>
@endpush

@section('content')
<div class="p-6 max-w-7xl mx-auto">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.procedimientos') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                Volver a procedimientos
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Flujo: {{ $procedimiento->nombre }}</h1>
            <p class="text-gray-600 text-sm mt-1">Configura nodos, transiciones (derivación), equipos y nodo de entrada.</p>
        </div>
    </div>

    <div id="flujoAlert" class="hidden mb-4 p-3 rounded border text-sm"></div>

    {{-- NODO DE ENTRADA --}}
    <section class="section-card overflow-hidden mb-6">
        <div class="section-header bg-gray-50 px-5 py-3 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Nodo de entrada
            </h2>
            <span class="text-xs text-gray-400">Obligatorio para iniciar trámites</span>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-4">Los trámites nuevos quedan en PENDIENTE hasta que un equipo del nodo de entrada acepte el ingreso (cambia a EN_PROCESO).</p>
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="label-xs">Nodo de entrada</label>
                    <select id="selNodoEntrada" class="select-sm min-w-[220px]">
                        <option value="">— Sin definir —</option>
                    </select>
                </div>
                <button type="button" id="btnGuardarEntrada" class="btn-primary-sm">Guardar</button>
            </div>
        </div>
    </section>

    {{-- NUEVO NODO --}}
    <section class="section-card overflow-hidden mb-6">
        <div class="section-header bg-gray-50 px-5 py-3">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Crear nodo manual
            </h2>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-sm mb-4">
                <div>
                    <label class="label-xs">Nombre *</label>
                    <input id="nnNombre" type="text" class="input-sm" placeholder="Ej. Mesa de partes" required>
                </div>
                <div>
                    <label class="label-xs">Slug</label>
                    <input id="nnSlug" type="text" class="input-sm" placeholder="auto si vacío">
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input id="nnFin" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <label for="nnFin" class="cursor-pointer text-sm font-medium text-gray-700 tooltipped" data-tip="Permite procesar y finalizar trámites (genera voucher). Solo en nodo final.">Finalizar</label>
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <input id="nnRech" type="checkbox" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <label for="nnRech" class="cursor-pointer text-sm font-medium text-gray-700 tooltipped" data-tip="Permite rechazar definitivamente trámites (estado RECHAZADO). Solo en nodo final.">Rechazo def.</label>
                </div>
            </div>
            <p class="text-xs text-gray-500 mb-2">El orden se asigna automáticamente según la jerarquía (Nivel 1=1, 2=2, 3=3, 4=4).</p>
            <button type="button" id="btnAddNodo" class="btn-secondary-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Agregar nodo
            </button>
        </div>
    </section>

    {{-- ACCIONES MASIVAS --}}
    <section class="section-card overflow-hidden mb-6">
        <div class="section-header bg-gray-50 px-5 py-3">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Acciones masivas
            </h2>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-600 mb-4">Regenera el flujo completo desde la jerarquía del procedimiento o sincroniza usuarios asignados a los niveles.</p>
            <div class="flex flex-wrap gap-3">
                <button type="button" id="btnRegenerarJerarquia" class="btn-purple-sm flex items-center gap-2" data-tip="Elimina nodos/transiciones/equipos actuales y los recrea en secuencia según Nivel 1 → 2 → 3 → 4 del procedimiento.">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Regenerar desde jerarquía
                </button>
                <button type="button" id="btnSincronizarUsuarios" class="btn-indigo-sm flex items-center gap-2" data-tip="Lee la tabla de asignación de usuarios a niveles y los agrega a los equipos correspondientes del flujo.">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Sincronizar usuarios de niveles
                </button>
            </div>
        </div>
    </section>

    <div id="nodosContainer" class="space-y-4"></div>
</div>

<script>
(function() {
    const procId = {{ (int) $procedimiento->id }};
    const base = '{{ url('/admin/procedimientos') }}/' + procId + '/flujo';
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    function alertBox(msg, ok) {
        const el = document.getElementById('flujoAlert');
        el.textContent = msg;
        el.className = 'mb-4 p-3 rounded border text-sm ' + (ok ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800');
        el.classList.remove('hidden');
        setTimeout(() => el.classList.add('hidden'), 6000);
    }

    async function api(method, url, body) {
        const opt = { method, headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' } };
        if (body) opt.body = JSON.stringify(body);
        const r = await fetch(url, opt);
        const j = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(j.message || j.error || ('Error HTTP ' + r.status));
        return j;
    }

    let data = { nodos: [], procedimiento: {} };
    let usuariosInternos = [];
    let nodosGlobalesPorNivel = { 1: [], 2: [], 3: [], 4: [] };

    async function loadUsuarios() {
        const j = await fetch('{{ route('admin.flujo.usuarios-internos') }}', { headers: { 'Accept': 'application/json' } });
        const x = await j.json();
        usuariosInternos = x.usuarios || [];
    }

    async function loadNodosGlobales() {
        const j = await fetch('{{ route('flujo.nodos-globales') }}', { headers: { 'Accept': 'application/json' } });
        const x = await j.json();
        nodosGlobalesPorNivel = x.niveles || { 1: [], 2: [], 3: [], 4: [] };
    }

    async function load() {
        const j = await fetch(base + '/api', { headers: { 'Accept': 'application/json' } });
        data = await j.json();
        renderEntrada();
        renderNodos();
    }

    function renderEntrada() {
        const sel = document.getElementById('selNodoEntrada');
        sel.innerHTML = '<option value="">— Sin definir —</option>';
        (data.nodos || []).forEach(n => {
            const o = document.createElement('option');
            o.value = n.id;
            o.textContent = n.nombre + ' (#' + n.id + ')';
            if (String(data.procedimiento.id_nodo_entrada) === String(n.id)) o.selected = true;
            sel.appendChild(o);
        });
    }

    document.getElementById('btnGuardarEntrada').onclick = async () => {
        try {
            const v = document.getElementById('selNodoEntrada').value;
            await api('POST', '{{ url('/admin/procedimientos') }}/' + procId + '/flujo/nodo-entrada', { id_nodo_entrada: v ? parseInt(v, 10) : null });
            alertBox('Nodo de entrada actualizado.', true);
            await load();
        } catch (e) { alertBox(e.message, false); }
    };

    document.getElementById('btnAddNodo').onclick = async () => {
        try {
            await api('POST', base + '/nodos', {
                nombre: document.getElementById('nnNombre').value.trim(),
                slug: document.getElementById('nnSlug').value.trim() || null,
                puede_finalizar: document.getElementById('nnFin').checked,
                puede_rechazar_definitivo: document.getElementById('nnRech').checked,
            });
            document.getElementById('nnNombre').value = '';
            document.getElementById('nnSlug').value = '';
            alertBox('Nodo creado.', true);
            await load();
        } catch (e) { alertBox(e.message, false); }
    };

    document.getElementById('btnRegenerarJerarquia').onclick = async () => {
        if (!confirm('¿Regenerar el flujo completo desde la jerarquía del procedimiento? Se perderán cambios manuales en nodos/transiciones/equipos.')) return;
        try {
            await api('POST', base + '/regenerar-jerarquia', {});
            alertBox('Flujo regenerado desde jerarquía.', true);
            await load();
        } catch (e) { alertBox(e.message, false); }
    };

    document.getElementById('btnSincronizarUsuarios').onclick = async () => {
        if (!confirm('¿Sincronizar usuarios asignados a los niveles del procedimiento con los equipos del flujo?')) return;
        try {
            await api('POST', base + '/sincronizar-usuarios', {});
            alertBox('Usuarios sincronizados.', true);
            await load();
        } catch (e) { alertBox(e.message, false); }
    };

    function badgeFlag(label, active, tip) {
        return `<span class="badge-flag ${active ? 'active' : 'inactive'} tooltipped" data-tip="${tip}">${label} ${active ? '✓' : '✗'}</span>`;
    }

    function renderNodos() {
        const c = document.getElementById('nodosContainer');
        c.innerHTML = '';
        (data.nodos || []).forEach(n => {
            const card = document.createElement('div');
            card.className = 'node-card overflow-hidden';
            const isEntry = String(data.procedimiento?.id_nodo_entrada) === String(n.id);
            card.innerHTML = `
                <div class="node-header bg-gray-50 px-5 py-3 flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="font-semibold text-gray-900">${n.nombre}</span>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">#${n.id}</span>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded">orden ${n.orden}</span>
                        ${isEntry ? '<span class="text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded font-medium">NODO ENTRADA</span>' : ''}
                    </div>
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium btn-del-nodo" data-id="${n.id}">Eliminar</button>
                </div>
                <div class="p-5 space-y-5">
                    <div class="flex flex-wrap gap-3">
                        <div class="checkbox-wrap tooltipped" data-tip="Permite procesar y finalizar trámites (genera voucher). Solo en nodo final del flujo.">
                            <input type="checkbox" class="chk-fin" data-id="${n.id}" ${n.puede_finalizar ? 'checked' : ''}>
                            <label for="" class="text-sm font-medium text-gray-700 cursor-pointer">Finalizar</label>
                        </div>
                        <div class="checkbox-wrap tooltipped" data-tip="Permite rechazar definitivamente trámites (estado RECHAZADO). Solo en nodo final.">
                            <input type="checkbox" class="chk-rech" data-id="${n.id}" ${n.puede_rechazar_definitivo ? 'checked' : ''}>
                            <label for="" class="text-sm font-medium text-gray-700 cursor-pointer">Rechazo def.</label>
                        </div>
                        <div class="checkbox-wrap tooltipped" data-tip="Marca el nodo como terminal del grafo. En modo AUTO, desde aquí el flujo se reinicia al nodo de entrada.">
                            <input type="checkbox" class="chk-term" data-id="${n.id}" ${n.es_terminal ? 'checked' : ''}>
                            <label for="" class="text-sm font-medium text-gray-700 cursor-pointer">Terminal</label>
                        </div>
                        ${badgeFlag('FINALIZAR', n.puede_finalizar, 'Nodo puede finalizar trámites')}
                        ${badgeFlag('RECHAZO DEF.', n.puede_rechazar_definitivo, 'Nodo puede rechazar definitivamente')}
                        ${badgeFlag('TERMINAL', n.es_terminal, 'Nodo terminal del grafo')}
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Transiciones (derivar)
                        </p>
                        <div class="flex flex-wrap gap-2 items-end mb-3">
                            <div class="flex-1 min-w-[140px]">
                                <label class="label-xs">Nivel</label>
                                <select class="select-sm tr-nivel-${n.id} w-full" onchange="actualizarNodosDestino(${n.id})">
                                    <option value="">— Seleccione nivel —</option>
                                    <option value="1">Nivel 1</option>
                                    <option value="2">Nivel 2</option>
                                    <option value="3">Nivel 3</option>
                                    <option value="4">Nivel 4</option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[200px]">
                                <label class="label-xs">Nodo destino</label>
                                <select class="select-sm tr-hacia-${n.id} w-full" disabled>
                                    <option value="">— Primero seleccione nivel —</option>
                                </select>
                            </div>
                            <div class="checkbox-wrap">
                                <input type="checkbox" class="tr-def-${n.id}">
                                <label class="text-sm font-medium text-gray-700 cursor-pointer tooltipped" data-tip="Esta transición se usará automáticamente en modo AUTO">Predeterminada (AUTO)</label>
                            </div>
                            <button type="button" class="btn-secondary-sm btn-add-tr" data-desde="${n.id}">Añadir</button>
                        </div>
                        <ul class="tr-list-${n.id} space-y-2"></ul>
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Equipos
                        </p>
                        <div class="flex gap-2 mb-3">
                            <div class="flex-1 min-w-[180px]">
                                <label class="label-xs">Nombre del equipo</label>
                                <input type="text" class="input-sm eq-nom-${n.id}" placeholder="Ej. Mesa de partes">
                            </div>
                            <button type="button" class="btn-secondary-sm btn-add-eq" data-nodo="${n.id}">Crear</button>
                        </div>
                        <div class="eq-wrap-${n.id} space-y-3"></div>
                    </div>
                </div>`;
            c.appendChild(card);

            // NUEVO: Dropdown cascada para nodos destino (nodos globales por nivel)
            const selNivel = card.querySelector('.tr-nivel-' + n.id);
            const selHacia = card.querySelector('.tr-hacia-' + n.id);

            // Función para actualizar nodos destino según nivel seleccionado
            window.actualizarNodosDestino = function (nodoId) {
                const selNivel = document.querySelector('.tr-nivel-' + nodoId);
                const selHacia = document.querySelector('.tr-hacia-' + nodoId);
                const nivel = parseInt(selNivel.value);
                selHacia.innerHTML = '<option value="">— Seleccione nodo —</option>';
                if (!nivel || !nodosGlobalesPorNivel[nivel]?.length) {
                    selHacia.disabled = true;
                    return;
                }
                selHacia.disabled = false;
                nodosGlobalesPorNivel[nivel].forEach(nd => {
                    const o = document.createElement('option');
                    o.value = nd.id;
                    const badge = nd.puede_finalizar ? ' 🎯' : '';
                    const badgeTerm = nd.es_terminal ? ' 🏁' : '';
                    o.textContent = `${nd.nombre} (${nd.procedimiento})${badge}${badgeTerm}`;
                    selHacia.appendChild(o);
                });
            };

            // Inicializar si ya hay nivel seleccionado (para transiciones existentes)
            if (selNivel.value) {
                actualizarNodosDestino(n.id);
            }

            const ul = card.querySelector('.tr-list-' + n.id);
            (n.transiciones || []).forEach(t => {
                const li = document.createElement('li');
                li.className = 'transition-row px-3 py-2 flex flex-wrap items-center justify-between gap-2';
                const isDef = t.es_default_auto;
                li.innerHTML = `
                    <span class="text-sm text-gray-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        ${t.nombre_destino || '#' + t.hacia_nodo_id}
                        ${isDef ? '<span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">AUTO</span>' : ''}
                    </span>
                    <div class="flex items-center gap-1">
                        <button type="button" class="text-xs text-blue-600 hover:underline btn-toggle-tr" data-id="${t.id}" data-def="${isDef ? '1' : '0'}">${isDef ? 'Quitar AUTO' : 'Marcar AUTO'}</button>
                        <button type="button" class="text-xs text-red-600 hover:underline btn-del-tr" data-id="${t.id}">Eliminar</button>
                    </div>`;
                ul.appendChild(li);
            });

            const eqWrap = card.querySelector('.eq-wrap-' + n.id);
            (n.equipos || []).forEach(eq => {
                const div = document.createElement('div');
                div.className = 'team-card p-3';
                let miembros = (eq.miembros || []).map(m => `<span class="member-tag px-2 py-1 flex items-center gap-1">${m.nombre_completo} <button type="button" class="text-red-500 hover:text-red-700 btn-det" data-eq="${eq.id}" data-u="${m.id}" title="Quitar">×</button></span>`).join('');
                div.innerHTML = `
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-sm text-gray-900">${eq.nombre}</span>
                        <button type="button" class="text-red-500 hover:text-red-700 text-sm btn-del-eq" data-eq="${eq.id}">Eliminar equipo</button>
                    </div>
                    <div class="mb-2 flex flex-wrap gap-1">${miembros || '<span class="text-xs text-gray-400">Sin miembros</span>'}</div>
                    <div class="flex gap-1 flex-wrap items-center">
                        <div class="flex-1 min-w-[150px]">
                            <select class="select-sm sel-miembro-${eq.id} text-xs"></select>
                        </div>
                        <button type="button" class="btn-secondary-sm text-xs btn-att" data-eq="${eq.id}">Agregar</button>
                    </div>`;
                eqWrap.appendChild(div);
                const s = div.querySelector('.sel-miembro-' + eq.id);
                usuariosInternos.forEach(u => {
                    const o = document.createElement('option');
                    o.value = u.id;
                    o.textContent = u.nombre_completo + ' — ' + u.email;
                    s.appendChild(o);
                });
            });
        });

        document.querySelectorAll('.btn-del-nodo').forEach(b => b.onclick = async () => {
            if (!confirm('¿Eliminar este nodo y sus datos relacionados (transiciones, equipos)?')) return;
            try {
                await api('DELETE', base + '/nodos/' + b.dataset.id);
                alertBox('Nodo eliminado.', true);
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.chk-fin, .chk-rech, .chk-term').forEach(chk => {
            chk.onchange = async () => {
                const id = chk.dataset.id;
                const card = chk.closest('.node-card');
                try {
                    await api('PUT', base + '/nodos/' + id, {
                        puede_finalizar: card.querySelector('.chk-fin[data-id="' + id + '"]').checked,
                        puede_rechazar_definitivo: card.querySelector('.chk-rech[data-id="' + id + '"]').checked,
                        es_terminal: card.querySelector('.chk-term[data-id="' + id + '"]').checked,
                    });
                } catch (e) { alertBox(e.message, false); await load(); }
            };
        });

        document.querySelectorAll('.btn-add-tr').forEach(b => b.onclick = async () => {
            const desde = b.dataset.desde;
            const sel = document.querySelector('.tr-hacia-' + desde);
            const hacia = sel.value;
            const def = document.querySelector('.tr-def-' + desde).checked;
            if (!hacia) { alertBox('Seleccione nodo destino.', false); return; }
            try {
                await api('POST', base + '/transiciones', {
                    desde_nodo_id: parseInt(desde, 10),
                    hacia_nodo_id: parseInt(hacia, 10),
                    es_default_auto: def,
                });
                alertBox('Transición creada.', true);
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.btn-toggle-tr').forEach(b => b.onclick = async () => {
            try {
                const nuevo = b.dataset.def === '1' ? false : true;
                await api('PUT', base + '/transiciones/' + b.dataset.id, { es_default_auto: nuevo });
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.btn-del-tr').forEach(b => b.onclick = async () => {
            if (!confirm('¿Eliminar transición?')) return;
            try {
                await api('DELETE', base + '/transiciones/' + b.dataset.id);
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.btn-add-eq').forEach(b => b.onclick = async () => {
            const nid = b.dataset.nodo;
            const nom = document.querySelector('.eq-nom-' + nid).value.trim();
            if (!nom) return;
            try {
                await api('POST', base + '/equipos', { nodo_id: parseInt(nid, 10), nombre: nom });
                document.querySelector('.eq-nom-' + nid).value = '';
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.btn-del-eq').forEach(b => b.onclick = async () => {
            if (!confirm('¿Eliminar equipo y sus miembros?')) return;
            try {
                await api('DELETE', base + '/equipos/' + b.dataset.eq);
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.btn-att').forEach(b => b.onclick = async () => {
            const eq = b.dataset.eq;
            const sel = document.querySelector('.sel-miembro-' + eq);
            const uid = sel.value;
            if (!uid) return;
            try {
                await api('POST', base + '/equipos/' + eq + '/miembros', { usuario_id: parseInt(uid, 10) });
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.btn-det').forEach(b => b.onclick = async () => {
            try {
                await api('DELETE', base + '/equipos/' + b.dataset.eq + '/miembros/' + b.dataset.u);
                await load();
            } catch (e) { alertBox(e.message, false); }
        });
    }

    loadUsuarios().then(loadNodosGlobales).then(load).catch(e => alertBox(e.message, false));
})();
</script>
@endsection
