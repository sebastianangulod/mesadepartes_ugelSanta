{{-- Configuración del flujo por nodos (admin) --}}
@extends('layouts.admin')

@section('title', 'Flujo del procedimiento · UGEL Santa')

@section('header', 'Flujo: ' . $procedimiento->nombre)

@section('subtitle', 'Configure nodos, equipos y nodo de entrada. La derivación AUTO avanza al siguiente nodo por orden; en MANUAL se elige cualquier destino.')

@section('actions')
    <a href="{{ route('admin.procedimientos') }}"
        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[13px] font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Volver a procedimientos
    </a>
@endsection

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
    /* Utilidades del editor (CSS real: @apply no funciona en <style> inline) */
    .input-sm { border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.25rem 0.5rem; font-size: 0.875rem; width: 100%; }
    .input-sm:focus { outline: none; box-shadow: 0 0 0 2px rgba(43,109,192,.35); border-color: #2b6dc0; }
    .select-sm { border: 1px solid #d1d5db; border-radius: 0.625rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 500; color: #1f2937; background: #fff; }
    .select-sm:focus { outline: none; box-shadow: 0 0 0 2px rgba(43,109,192,.35); border-color: #2b6dc0; }
    .btn-sm { padding: 0.375rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; transition: background-color .15s, color .15s; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 0.5rem; }
    .btn-primary-sm { background: #1a4078; color: #fff; font-weight: 700; padding: 0.5rem 1.25rem; border-radius: 0.625rem; box-shadow: 0 1px 2px rgba(17,42,77,.25); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-primary-sm:hover { background: #173763; }
    .btn-secondary-sm { background: #e5e7eb; color: #374151; }
    .btn-secondary-sm:hover { background: #d1d5db; }
    .btn-danger-sm { background: #fee2e2; color: #b91c1c; }
    .btn-danger-sm:hover { background: #fecaca; }
    .btn-purple-sm { background: #6d28d9; color: #fff; font-weight: 700; padding: 0.5rem 1.25rem; border-radius: 0.625rem; box-shadow: 0 1px 2px rgba(109,40,217,.25); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .btn-purple-sm:hover { background: #5b21b6; }
    .btn-indigo-sm { background: #eef2ff; color: #4338ca; }
    .btn-indigo-sm:hover { background: #e0e7ff; }
    .label-xs { display: block; font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem; }
    .checkbox-wrap { display: flex; align-items: center; gap: 0.375rem; cursor: pointer; }
    .checkbox-wrap input { width: 1rem; height: 1rem; accent-color: #1a4078; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <div id="flujoAlert" class="hidden mb-4 p-3 rounded-xl border text-sm"></div>

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
            <p class="text-sm text-gray-600 mb-4">Los trámites nuevos quedan en PENDIENTE hasta que un equipo del nodo de entrada acepte el ingreso (cambia a EN_PROCESO). Cambiar la entrada con trámites en curso pide confirmación y queda registrado.</p>
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="label-xs">Nodo de entrada</label>
                    <select id="selNodoEntrada" class="select-sm min-w-[220px]">
                        <option value="">— Sin definir —</option>
                    </select>
                </div>
                <button type="button" id="btnGuardarEntrada" class="btn-primary-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Guardar
                </button>
            </div>
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
            <p class="text-sm text-gray-600 mb-4">Regenera los nodos del flujo desde la jerarquía de niveles del procedimiento. Los permisos salen directo de la asignación usuarios-niveles.</p>
            <div class="flex flex-wrap gap-3">
                <button type="button" id="btnRegenerarJerarquia" class="btn-purple-sm flex items-center gap-2" data-tip="Elimina los nodos actuales y los recrea en secuencia según Nivel 1 → 2 → 3 → 4 del procedimiento.">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Regenerar desde jerarquía
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
        if (!r.ok) {
            const err = new Error(j.message || j.error || ('Error HTTP ' + r.status));
            err.status = r.status;
            err.body = j;
            throw err;
        }
        return j;
    }

    let data = { nodos: [], procedimiento: {} };

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
            const destinoId = v ? parseInt(v, 10) : null;

            // Vaciar la entrada pausa la recepción: confirmación dedicada.
            if (destinoId === null) {
                if (!confirm('¿Dejar el procedimiento sin nodo de entrada? Mientras tanto no podrá recibir trámites nuevos.')) return;
                await guardarEntrada(destinoId, true);
                return;
            }

            // Si no hay cambio, nada que guardar.
            if (String(data.procedimiento.id_nodo_entrada ?? '') === String(destinoId)) {
                alertBox('El nodo de entrada ya es ese. Sin cambios.', true);
                return;
            }

            await guardarEntrada(destinoId, false);
        } catch (e) { alertBox(e.message, false); }
    };

    async function guardarEntrada(destinoId, confirmado) {
        let r;
        try {
            r = await api('POST', '{{ url('/admin/procedimientos') }}/' + procId + '/flujo/nodo-entrada', { id_nodo_entrada: destinoId, confirmado: confirmado });
        } catch (e) {
            r = (e && e.body) ? e.body : null;
            if (!r || !r.needs_confirm) {
                alertBox((e && e.message) || 'No se pudo guardar.', false);
                return;
            }
        }
        if (r && r.success) {
            alertBox('Nodo de entrada actualizado.', true);
            await load();
            return;
        }
        // El backend pide confirmación cuando hay trámites en curso.
        if (r && r.needs_confirm) {
            const n = r.sensibles || 0;
            if (confirm(`Hay ${n} trámite(s) en curso. Cambiar el nodo de entrada moverá su aceptación, bandeja y retroceso al nuevo nodo. ¿Continuar?`)) {
                await guardarEntrada(destinoId, true);
            }
            return;
        }
        alertBox((r && r.message) || 'No se pudo guardar.', false);
    }

    document.getElementById('btnRegenerarJerarquia').onclick = async () => {
        try {
            await regenerarDesdeJerarquia(false);
        } catch (e) { alertBox(e.message, false); }
    };

    async function regenerarDesdeJerarquia(confirmado) {
        let r;
        try {
            r = await api('POST', base + '/regenerar-jerarquia', { confirmado: confirmado });
        } catch (e) {
            r = (e && e.body) ? e.body : null;
            if (!r || !r.needs_confirm) {
                alertBox((e && e.message) || 'No se pudo regenerar.', false);
                return;
            }
        }
        if (r && r.success) {
            alertBox('Flujo regenerado desde jerarquía.', true);
            await load();
            return;
        }
        // El backend pide confirmación cuando hay trámites en curso.
        if (r && r.needs_confirm) {
            const n = r.sensibles || 0;
            if (confirm(`Hay ${n} trámite(s) en curso. Regenerar restablecerá nombres, banderas y nodo de entrada del flujo (los trámites existentes conservan su nodo actual). ¿Continuar?`)) {
                await regenerarDesdeJerarquia(true);
            }
            return;
        }
        alertBox((r && r.message) || 'No se pudo regenerar.', false);
    }

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
                </div>
                <div class="p-5 space-y-5">
                    <div class="flex flex-wrap gap-3">
                        ${badgeFlag('FINALIZAR', n.puede_finalizar, 'Nodo puede finalizar trámites')}
                        ${badgeFlag('RECHAZO DEF.', n.puede_rechazar_definitivo, 'Nodo puede rechazar definitivamente')}
                    </div>

                    <div class="border-t pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Miembros (desde asignación a niveles)
                        </p>
                        <div class="flex flex-wrap gap-1">${(n.miembros || []).map(m => `<span class="member-tag px-2 py-1">${m.nombre_completo}</span>`).join('') || '<span class="text-xs text-gray-400">Sin miembros: asigne usuarios al nivel en Gestión de Niveles/Usuarios</span>'}</div>
                    </div>
                </div>`;
            c.appendChild(card);
        });
    }

    load().catch(e => alertBox(e.message, false));
})();
</script>
@endsection
