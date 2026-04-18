{{-- Configuración del flujo por nodos (admin) --}}
@extends('layouts.admin')

@section('title', 'Flujo del procedimiento — Mesa de Partes')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.procedimientos') }}" class="text-sm text-blue-600 hover:underline">← Volver a procedimientos</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Flujo: {{ $procedimiento->nombre }}</h1>
            <p class="text-gray-600 text-sm mt-1">Nodos, transiciones (derivación), equipos y nodo de entrada.</p>
        </div>
    </div>

    <div id="flujoAlert" class="hidden mb-4 p-3 rounded border text-sm"></div>

    <section class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-3">Nodo de entrada</h2>
        <p class="text-sm text-gray-600 mb-2">Los trámites nuevos quedan pendientes hasta que un equipo del nodo de entrada acepte el ingreso.</p>
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs text-gray-500">Nodo entrada</label>
                <select id="selNodoEntrada" class="border rounded px-2 py-1 min-w-[220px]">
                    <option value="">— Sin definir —</option>
                </select>
            </div>
            <button type="button" id="btnGuardarEntrada" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Guardar</button>
        </div>
    </section>

    <section class="bg-white rounded-lg shadow p-4 mb-6">
        <h2 class="font-semibold text-gray-800 mb-3">Nuevo nodo</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
            <div><label class="text-xs text-gray-500">Nombre</label><input id="nnNombre" type="text" class="border rounded w-full px-2 py-1" placeholder="Ej. Mesa de partes"></div>
            <div><label class="text-xs text-gray-500">Orden</label><input id="nnOrden" type="number" class="border rounded w-full px-2 py-1" value="0" min="0"></div>
            <div class="flex items-center gap-2 pt-5"><input id="nnFin" type="checkbox"><label for="nnFin">Puede finalizar</label></div>
            <div class="flex items-center gap-2 pt-5"><input id="nnRech" type="checkbox"><label for="nnRech">Rechazo definitivo</label></div>
        </div>
        <button type="button" id="btnAddNodo" class="mt-3 px-3 py-1 bg-gray-800 text-white rounded text-sm">Agregar nodo</button>
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

    async function loadUsuarios() {
        const j = await fetch('{{ route('admin.flujo.usuarios-internos') }}', { headers: { 'Accept': 'application/json' } });
        const x = await j.json();
        usuariosInternos = x.usuarios || [];
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
                orden: parseInt(document.getElementById('nnOrden').value || '0', 10),
                puede_finalizar: document.getElementById('nnFin').checked,
                puede_rechazar_definitivo: document.getElementById('nnRech').checked,
            });
            document.getElementById('nnNombre').value = '';
            alertBox('Nodo creado.', true);
            await load();
        } catch (e) { alertBox(e.message, false); }
    };

    function renderNodos() {
        const c = document.getElementById('nodosContainer');
        c.innerHTML = '';
        (data.nodos || []).forEach(n => {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-lg shadow border border-gray-200 p-4';
            card.innerHTML = `
                <div class="flex flex-wrap justify-between gap-2 mb-2">
                    <div>
                        <span class="font-semibold text-gray-900">${n.nombre}</span>
                        <span class="text-xs text-gray-500 ml-2">#${n.id} · orden ${n.orden}</span>
                    </div>
                    <button type="button" class="text-red-600 text-sm hover:underline btn-del-nodo" data-id="${n.id}">Eliminar nodo</button>
                </div>
                <div class="text-xs text-gray-600 mb-3 flex flex-wrap gap-3">
                    <label><input type="checkbox" class="chk-fin" data-id="${n.id}" ${n.puede_finalizar ? 'checked' : ''}/> Finalizar</label>
                    <label><input type="checkbox" class="chk-rech" data-id="${n.id}" ${n.puede_rechazar_definitivo ? 'checked' : ''}/> Rechazo def.</label>
                    <label><input type="checkbox" class="chk-term" data-id="${n.id}" ${n.es_terminal ? 'checked' : ''}/> Terminal</label>
                </div>
                <div class="border-t pt-3 mt-2">
                    <p class="text-sm font-medium text-gray-700 mb-1">Transiciones (derivar)</p>
                    <div class="flex flex-wrap gap-2 items-end mb-2">
                        <select class="border rounded text-sm tr-hacia-${n.id} px-2 py-1"></select>
                        <label class="text-xs flex items-center gap-1"><input type="checkbox" class="tr-def-${n.id}"/> Predeterminada (AUTO)</label>
                        <button type="button" class="px-2 py-1 bg-gray-200 rounded text-sm btn-add-tr" data-desde="${n.id}">Añadir</button>
                    </div>
                    <ul class="text-sm tr-list-${n.id} list-disc ml-5 text-gray-700"></ul>
                </div>
                <div class="border-t pt-3 mt-3">
                    <p class="text-sm font-medium text-gray-700 mb-1">Equipos</p>
                    <div class="flex gap-2 mb-2">
                        <input type="text" class="border rounded px-2 py-1 text-sm eq-nom-${n.id}" placeholder="Nombre equipo"/>
                        <button type="button" class="px-2 py-1 bg-gray-200 rounded text-sm btn-add-eq" data-nodo="${n.id}">Crear equipo</button>
                    </div>
                    <div class="eq-wrap-${n.id} space-y-2"></div>
                </div>`;
            c.appendChild(card);

            const selHacia = card.querySelector('.tr-hacia-' + n.id);
            (data.nodos || []).forEach(x => {
                if (x.id === n.id) return;
                const o = document.createElement('option');
                o.value = x.id;
                o.textContent = x.nombre;
                selHacia.appendChild(o);
            });

            const ul = card.querySelector('.tr-list-' + n.id);
            (n.transiciones || []).forEach(t => {
                const li = document.createElement('li');
                li.innerHTML = `→ ${t.nombre_destino || t.hacia_nodo_id}
                    <button type="button" class="text-xs text-blue-600 ml-2 btn-toggle-tr" data-id="${t.id}" data-def="${t.es_default_auto ? '1' : '0'}">${t.es_default_auto ? 'Quitar predeterminada' : 'Marcar predeterminada'}</button>
                    <button type="button" class="text-xs text-red-600 ml-2 btn-del-tr" data-id="${t.id}">Eliminar</button>`;
                ul.appendChild(li);
            });

            const eqWrap = card.querySelector('.eq-wrap-' + n.id);
            (n.equipos || []).forEach(eq => {
                const div = document.createElement('div');
                div.className = 'border rounded p-2 bg-gray-50';
                let miembros = (eq.miembros || []).map(m => `<span class="inline-block bg-white border rounded px-1 mr-1 mb-1 text-xs">${m.nombre_completo} <button type="button" class="text-red-500 btn-det" data-eq="${eq.id}" data-u="${m.id}">×</button></span>`).join(' ');
                div.innerHTML = `<div class="font-medium text-sm">${eq.nombre} <button type="button" class="text-red-500 text-xs float-right btn-del-eq" data-eq="${eq.id}">Eliminar equipo</button></div>
                    <div class="mt-1">${miembros}</div>
                    <div class="mt-2 flex gap-1 flex-wrap items-center">
                        <select class="border rounded text-xs sel-miembro-${eq.id}"></select>
                        <button type="button" class="text-xs px-2 py-0.5 bg-blue-100 rounded btn-att" data-eq="${eq.id}">Agregar</button>
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
            if (!confirm('¿Eliminar este nodo y sus datos relacionados?')) return;
            try {
                await api('DELETE', base + '/nodos/' + b.dataset.id);
                alertBox('Nodo eliminado.', true);
                await load();
            } catch (e) { alertBox(e.message, false); }
        });

        document.querySelectorAll('.chk-fin, .chk-rech, .chk-term').forEach(chk => {
            chk.onchange = async () => {
                const id = chk.dataset.id;
                const card = chk.closest('.bg-white.rounded-lg');
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
            if (!confirm('¿Eliminar equipo?')) return;
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

    loadUsuarios().then(load).catch(e => alertBox(e.message, false));
})();
</script>
@endsection
