@extends('layouts.mesa-partes')

@section('title', 'Notificaciones · UGEL Santa')
@section('header', 'Notificaciones')
@section('subtitle', 'Avisos sobre sus expedientes: observaciones, avances y resultados.')

@section('actions')
    <button type="button" id="btnNotifReadAll"
        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[13px] font-bold text-slate-700 shadow-sm hover:bg-slate-50 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Marcar todas como leídas
    </button>
@endsection

@section('content')
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="inline-flex rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden self-start">
                <button type="button" data-nfiltro="todas" class="nfiltro-btn px-4 py-2 text-[13px] font-bold bg-primary-700 text-white">Todas</button>
                <button type="button" data-nfiltro="no-leidas" class="nfiltro-btn px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 border-l border-slate-200">No leídas (<span id="notifCountTop">0</span>)</button>
            </div>
            <p class="text-[12px] text-slate-500">Las más recientes aparecen primero</p>
        </div>
        <div id="notifList" class="divide-y divide-slate-100"></div>
        <div id="notifEmpty" class="hidden flex-col items-center gap-2 px-6 py-14 text-center">
            <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a24 24 0 00-5.714 0M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9M10.3 21a1.94 1.94 0 003.4 0"/></svg>
            </span>
            <p class="text-[15px] font-extrabold text-slate-700">Sin notificaciones</p>
            <p class="text-[13px] text-slate-400">Cuando haya movimientos en sus expedientes aparecerán aquí.</p>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .notif-tipo { display:inline-flex; align-items:center; justify-content:center; width:2.75rem; height:2.75rem; border-radius:0.9rem; flex-shrink:0; }
</style>
@endpush

@push('scripts')
<script>
(function() {
    let filtroActual = 'todas';

    function csrf() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function estiloTipo(tipo) {
        return {
            success: ['bg-emerald-50 text-emerald-600', 'M5 13l4 4L19 7'],
            error: ['bg-red-50 text-red-600', 'M6 18L18 6M6 6l12 12'],
            warning: ['bg-amber-50 text-amber-600', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            info: ['bg-primary-50 text-primary-700', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']
        }[tipo] || ['bg-primary-50 text-primary-700', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
    }

    function cargar() {
        const list = document.getElementById('notifList');
        const empty = document.getElementById('notifEmpty');
        list.innerHTML = '<div class="px-6 py-12 text-center"><div class="mp-spinner inline-block w-8 h-8 rounded-full border-[3px] border-slate-200 border-t-primary-600"></div><p class="mt-3 text-sm font-semibold text-slate-500">Cargando…</p></div>';
        empty.classList.add('hidden');
        empty.classList.remove('flex');

        fetch('/notificaciones/ajax?filtro=' + filtroActual + '&limite=50', { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                list.innerHTML = '';
                const items = (d && d.notificaciones) || [];
                const top = document.getElementById('notifCountTop');
                if (top) top.textContent = (d && d.no_leidas) || 0;
                if (window.app && window.app.refreshNotifBadge) window.app.refreshNotifBadge();
                if (!items.length) {
                    empty.classList.remove('hidden');
                    empty.classList.add('flex');
                    return;
                }
                items.forEach(function(n) {
                    const est = estiloTipo(n.tipo);
                    const row = document.createElement(n.url ? 'a' : 'div');
                    if (n.url) row.href = n.url;
                    row.className = 'flex items-start gap-3.5 px-5 sm:px-6 py-4 transition ' + (n.url ? 'hover:bg-slate-50 cursor-pointer' : '') + (!n.leida ? ' bg-primary-50/40' : '');
                    row.innerHTML =
                        '<span class="notif-tipo ' + est[0] + '">' +
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + est[1] + '"/></svg></span>' +
                        '<span class="min-w-0 flex-1"><span class="block text-[14px] font-bold text-slate-900"></span>' +
                        '<span class="block text-[13px] text-slate-500 mt-0.5 leading-relaxed"></span>' +
                        '<span class="block text-[11.5px] text-slate-400 mt-1"></span></span>' +
                        (!n.leida ? '<span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full bg-primary-600" title="No leída"></span>' : '');
                    const spans = row.querySelectorAll('span span');
                    spans[0].textContent = n.titulo || '';
                    spans[1].textContent = n.mensaje || '';
                    spans[2].textContent = n.fecha || '';
                    if (n.url) {
                        row.addEventListener('click', function(e) {
                            e.preventDefault();
                            fetch('/notificaciones/' + n.id + '/leer', {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
                            }).finally(function() { window.location.href = n.url; });
                        });
                    }
                    list.appendChild(row);
                });
            })
            .catch(function() {
                list.innerHTML = '<p class="px-6 py-12 text-center text-sm font-semibold text-red-500">No se pudieron cargar las notificaciones.</p>';
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nfiltro-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                filtroActual = btn.dataset.nfiltro;
                document.querySelectorAll('.nfiltro-btn').forEach(function(b) {
                    const activo = b === btn;
                    b.classList.toggle('bg-primary-700', activo);
                    b.classList.toggle('text-white', activo);
                    b.classList.toggle('text-slate-600', !activo);
                });
                cargar();
            });
        });
        const readAll = document.getElementById('btnNotifReadAll');
        if (readAll) readAll.addEventListener('click', function() {
            fetch('/notificaciones/leer-todas', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }
            }).finally(function() {
                if (window.app && window.app.toast) window.app.toast('Todas las notificaciones marcadas como leídas.', 'success');
                cargar();
            });
        });
        cargar();
    });
})();
</script>
@endpush
