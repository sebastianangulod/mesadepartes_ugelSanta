@extends('layouts.mesa-partes')

@php
    $esInterno = Auth::check() && Auth::user()->tipo_user == 2;
@endphp

@section('title', $esInterno ? 'Recepción de Trámites · UGEL Santa' : 'Mis Trámites · UGEL Santa')
@section('header', $esInterno ? 'Recepción de Trámites' : 'Mis Trámites')
@section('subtitle', $esInterno
    ? 'Bandeja de trabajo del personal interno: acepta, observa, deriva y finaliza los expedientes asignados a tu nivel.'
    : 'Consulta el estado de tus expedientes, subsana observaciones y descarga tus documentos.')

@section('actions')
    <a href="{{ route('mesa-partes.nuevo') }}"
        class="inline-flex items-center gap-2 rounded-xl bg-primary-700 hover:bg-primary-800 px-4 py-2.5 text-[13px] font-bold text-white shadow-md shadow-primary-700/20 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
        {{ $esInterno ? 'Trámite presencial' : 'Nuevo trámite' }}
    </a>
@endsection

@section('content')

    {{-- ══════ KPIs (datos del controlador: $resumen) ══════ --}}
    @isset($resumen)
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </span>
            <div class="min-w-0"><p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $resumen['urgentes'] ?? 0 }}</p><p class="mt-1 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">Urgentes +7 días</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0"><p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $resumen['por_vencer'] ?? 0 }}</p><p class="mt-1 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">Por vencer</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl bg-blue-50 text-primary-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </span>
            <div class="min-w-0"><p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $resumen['en_proceso'] ?? 0 }}</p><p class="mt-1 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">En proceso</p></div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
            <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="min-w-0"><p class="text-2xl font-extrabold text-slate-900 leading-none">{{ $resumen['atendidos_hoy'] ?? 0 }}</p><p class="mt-1 text-[11.5px] font-semibold text-slate-500 uppercase tracking-wide">Ingresados hoy</p></div>
        </div>
    </div>
    @endisset

    {{-- ══════ Filtros ══════ --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-5 overflow-hidden">
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </span>
                <div>
                    <h3 class="text-[15px] font-extrabold text-slate-900 leading-tight">Filtros de búsqueda</h3>
                    <p class="text-[12px] text-slate-500">Afina la bandeja por estado y rango de fechas</p>
                </div>
            </div>
            <button type="button" id="btnLimpiarFiltros" class="text-[12.5px] font-bold text-slate-500 hover:text-primary-700 transition">Limpiar</button>
        </div>
        <form id="filtrosForm" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_auto] gap-4 px-5 sm:px-6 py-5">
            @csrf
            <div>
                <label for="filtro_estado" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Estado</label>
                <select name="estado" id="filtro_estado"
                    class="w-full rounded-xl border-slate-200 border px-3.5 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <option value="">Todos los estados</option>
                    <option value="PENDIENTE">Pendiente</option>
                    <option value="OBSERVADO">Observado</option>
                    <option value="EN_PROCESO">En proceso</option>
                    <option value="VERIFICADO">Verificado</option>
                    <option value="FINALIZADO">Finalizado</option>
                    <option value="RECHAZADO">Rechazado</option>
                </select>
            </div>
            <div>
                <label for="filtro_fecha_desde" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Fecha desde</label>
                <input type="date" name="fecha_desde" id="filtro_fecha_desde"
                    class="w-full rounded-xl border-slate-200 border px-3.5 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" max="">
            </div>
            <div>
                <label for="filtro_fecha_hasta" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Fecha hasta</label>
                <input type="date" name="fecha_hasta" id="filtro_fecha_hasta"
                    class="w-full rounded-xl border-slate-200 border px-3.5 py-2.5 text-sm font-medium text-slate-700 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" max="" min="">
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full xl:w-auto inline-flex justify-center items-center gap-2 rounded-xl bg-primary-700 hover:bg-primary-800 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-primary-700/20 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Filtrar
                </button>
            </div>
        </form>
    </section>

    {{-- ══════ Leyenda de tiempos ══════ --}}
    <section class="rounded-2xl border border-primary-100 bg-gradient-to-r from-primary-50/80 via-white to-white shadow-sm p-4 sm:p-5 mb-5">
        <div class="flex items-start gap-3">
            <span class="w-9 h-9 rounded-xl bg-primary-700 text-white flex items-center justify-center shrink-0 shadow">
                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <h4 class="text-[13.5px] font-extrabold text-slate-900">Semáforo de atención · límite 30 días</h4>
                    <span class="text-[11.5px] font-medium text-slate-500">El color indica los días transcurridos desde la creación</span>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600"><i class="w-2.5 h-2.5 rounded-full inline-block" style="background:#22c55e"></i>0–9 d · Normal</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600"><i class="w-2.5 h-2.5 rounded-full inline-block border border-slate-300" style="background:#fde047"></i>9–15 d · Atención</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600"><i class="w-2.5 h-2.5 rounded-full inline-block" style="background:#eab308"></i>15–21 d · Advertencia</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 px-2.5 py-1 text-[11.5px] font-semibold text-slate-600"><i class="w-2.5 h-2.5 rounded-full inline-block" style="background:#f97316"></i>21–30 d · Urgente</span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white border border-red-200 px-2.5 py-1 text-[11.5px] font-bold text-red-700"><i class="w-2.5 h-2.5 rounded-full inline-block" style="background:#dc2626"></i>30+ d · Vencido</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════ Tabla ══════ --}}
    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div>
                <h3 class="text-[15px] font-extrabold text-slate-900">{{ $esInterno ? 'Expedientes en bandeja' : 'Mis expedientes' }}</h3>
                <p class="text-[12px] text-slate-500"><span id="totalRegistrosTop" class="font-bold text-slate-700">0</span> registros</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <label for="porPagina" class="text-[12.5px] font-bold text-slate-500">Mostrar</label>
                <select id="porPagina"
                    class="rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-[12.5px] font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
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
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider whitespace-nowrap">Exp.</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Documento</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider min-w-[180px]">Procedimiento</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-center" title="Días transcurridos desde la creación">Tiempo</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider whitespace-nowrap">Fecha</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Voucher</th>
                        <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Hoja de ruta</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tramitesTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="mp-spinner w-8 h-8 rounded-full border-[3px] border-slate-200 border-t-primary-600"></div>
                                <p class="text-sm font-semibold text-slate-500">Cargando…</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-5 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60">
            <p class="text-[13px] text-slate-500 order-2 sm:order-1">
                Mostrando <span id="inicioRegistros" class="font-bold text-slate-800">0</span> a <span id="finRegistros" class="font-bold text-slate-800">0</span> de <span id="totalRegistros" class="font-bold text-slate-800">0</span> resultados
            </p>
            <div class="order-1 sm:order-2 flex items-center gap-2">
                <div class="sm:hidden flex items-center gap-2">
                    <button data-prev class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition">Anterior</button>
                    <button data-next class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 transition">Siguiente</button>
                </div>
                <nav class="hidden sm:inline-flex items-center rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden" aria-label="Paginación">
                    <button id="prevPage" class="px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition border-r border-slate-200">← Anterior</button>
                    <span class="px-4 py-2 text-[13px] font-extrabold text-primary-800 bg-primary-50">Pág. <span id="paginaActual">1</span></span>
                    <button id="nextPage" class="px-4 py-2 text-[13px] font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition border-l border-slate-200">Siguiente →</button>
                </nav>
            </div>
        </div>
    </section>

{{-- ═════════ MODALES (IDs originales preservados) ═════════ --}}

<!-- Hoja de Ruta -->
<div id="hojaRutaModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[88vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/70">
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-primary-700 text-white flex items-center justify-center">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </span>
                <div><h3 class="text-[16px] font-extrabold text-slate-900 leading-tight">Hoja de ruta</h3><p class="text-[12px] text-slate-500">Recorrido del expediente por niveles</p></div>
            </div>
            <button onclick="cerrarHojaRuta()" class="w-9 h-9 rounded-xl hover:bg-slate-200/70 text-slate-500 hover:text-slate-800 flex items-center justify-center transition" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="overflow-y-auto mp-scroll p-5 sm:p-6">
            <table class="min-w-full text-sm bg-white border border-slate-200 rounded-xl overflow-hidden">
                <thead><tr class="bg-slate-50"><th class="py-2.5 px-4 text-left text-[11px] font-extrabold uppercase tracking-wide text-slate-500">Nivel</th><th class="py-2.5 px-4 text-left text-[11px] font-extrabold uppercase tracking-wide text-slate-500">Detalle</th></tr></thead>
                <tbody id="hr-niveles-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Corregir -->
<div id="modalCorregir" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md relative">
        <button id="cerrarModalCorregir" class="absolute top-4 right-4 w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xl leading-none" aria-label="Cerrar">×</button>
        <h2 class="text-[17px] font-extrabold text-slate-900">Corregir trámite</h2>
        <p class="text-[13px] text-slate-500 mt-1 mb-4">El expediente está <span class="font-bold text-amber-600">OBSERVADO</span>. Adjunte el documento corregido en PDF.</p>
        <form id="formCorregir" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id_tramite" id="corregir_id_tramite">
            <label for="corregir_archivo" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Documento corregido (PDF) <span class="text-red-500">*</span></label>
            <input type="file" name="archivo_adjunto" id="corregir_archivo" accept="application/pdf" required
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500"
                onchange="validarArchivoPDF(this, 'corregir_archivo_error')">
            <div id="corregir_archivo_error" class="mt-1.5 text-[12.5px] font-semibold text-red-600 hidden"></div>
            <p class="mt-1.5 text-[11.5px] text-slate-400">Máximo 25 MB · Solo PDF</p>
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" id="cerrarModalCorregirBtn" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition">Cancelar</button>
                <button type="submit" id="btnEnviarCorreccion" disabled class="px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition opacity-50 cursor-not-allowed">Enviar corrección</button>
            </div>
            <div id="corregirMensaje" class="mt-3 text-sm"></div>
        </form>
    </div>
</div>

<!-- Subir oficio -->
<div id="modalOficio" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md relative">
        <button onclick="cerrarModalOficio()" class="absolute top-4 right-4 w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xl leading-none" aria-label="Cerrar">×</button>
        <h3 class="text-[17px] font-extrabold text-slate-900">Subir oficio de subsanación</h3>
        <p class="text-[13px] text-slate-500 mt-1 mb-4">El trámite fue <span class="font-bold text-amber-600">OBSERVADO</span> y requiere oficio de respuesta en PDF.</p>
        <form id="formOficio" enctype="multipart/form-data">
            @csrf
            <label for="archivo_oficio" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Oficio (PDF) <span class="text-red-500">*</span></label>
            <input type="file" name="archivo_oficio" id="archivo_oficio" accept="application/pdf" required
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                onchange="validarArchivoPDF(this, 'oficio_archivo_error')">
            <div id="oficio_archivo_error" class="mt-1.5 text-[12.5px] font-semibold text-red-600 hidden"></div>
            <p class="mt-1.5 text-[11.5px] text-slate-400">Máximo 25 MB · Solo PDF</p>
            <input type="hidden" name="id_tramite" id="id_tramite_oficio">
            <div class="flex justify-end gap-2 mt-5">
                <button type="button" onclick="cerrarModalOficio()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition">Cerrar</button>
                <button type="submit" id="btnSubirOficio" disabled class="px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-700 transition opacity-50 cursor-not-allowed">Subir oficio</button>
            </div>
        </form>
    </div>
</div>

<!-- Observar con oficio -->
<div id="modalObservarConOficio" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-xl">
        <h3 class="text-[17px] font-extrabold text-slate-900">Observar con oficio</h3>
        <form id="formObservarConOficio" enctype="multipart/form-data" class="mt-4">
            @csrf
            <label for="observacion_oficio" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Motivo de la observación <span class="text-red-500">*</span></label>
            <textarea name="observacion" id="observacion_oficio" rows="4" required
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                placeholder="Describa con precisión qué debe subsanar el administrado…"></textarea>
            <p class="text-[12.5px] text-amber-800 mt-3 bg-amber-50 border border-amber-200 rounded-xl p-3"><strong>Nota:</strong> el creador del trámite deberá adjuntar el oficio antes de continuar. Hasta entonces no podrá realizar acciones.</p>
            <input type="hidden" name="id_tramite" id="id_tramite_observar_oficio">
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" onclick="cerrarModalObservarConOficio()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition">Cerrar</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition">Observar</button>
            </div>
        </form>
    </div>
</div>

<!-- Ver observación -->
<div id="modalObservacion" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md relative">
        <button onclick="cerrarModalObservacion()" class="absolute top-4 right-4 w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xl leading-none" aria-label="Cerrar">×</button>
        <h3 class="text-[17px] font-extrabold text-slate-900">Motivo de la observación</h3>
        <div class="mt-4">
            <label class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Observación</label>
            <div id="observacionTexto" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm min-h-[90px] whitespace-pre-wrap leading-relaxed"></div>
        </div>
        <div id="oficioSection" class="mt-4 hidden rounded-xl border border-purple-200 bg-purple-50 p-3.5">
            <label class="block text-[12px] font-bold uppercase tracking-wide text-purple-700 mb-2">Oficio adjunto</label>
            <div class="flex items-center gap-2.5">
                <button id="btnVerOficioObservacion" type="button" class="px-3.5 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-[13px] font-bold transition">Ver oficio</button>
                <span class="text-[12px] text-purple-700/70 font-medium">Documento oficial de la observación</span>
            </div>
        </div>
        <div class="flex justify-end mt-5">
            <button type="button" onclick="cerrarModalObservacion()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition">Cerrar</button>
        </div>
    </div>
</div>

<!-- Acciones -->
<div id="modalAccionesTramite" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
    <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm relative">
        <button id="cerrarModalAcciones" class="absolute top-4 right-4 w-8 h-8 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-700 flex items-center justify-center text-xl leading-none" aria-label="Cerrar">×</button>
        <h3 id="modalAccionesTitulo" class="text-[16px] font-extrabold text-slate-900 pr-8">Acciones del trámite</h3>
        <p class="text-[12px] text-slate-500 mt-0.5 mb-4">Seleccione la acción a ejecutar</p>
        <div id="modalAccionesBtns" class="flex flex-col gap-2"></div>
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
    #tramitesTableBody tr { transition: background .15s ease; }
    .icon-btn { display:inline-flex; align-items:center; justify-content:center; gap:4px; font-size:11.5px; font-weight:700; padding:6px 10px; border-radius:10px; transition:all .15s ease; white-space:nowrap; }
</style>
@endpush

@push('scripts')
<script>
    console.log('=== tramites-recibidos script loaded ===');
    window.TIPO_USER = {{ Auth::user()->tipo_user }};
    window.USER_ID = {{ Auth::user()->id }};
    window.NIVEL_USUARIO = {{ Auth::user()->usuariosNiveles->first()->nivel ?? 1 }};

    let tramiteIdActual;
    let paginaActual = 1;
    let totalPaginas = 1;
    let porPagina = 10;
    let filtrosActuales = {};
    let tramitesCache = [];

    // Helper: modales centrados (los contenedores usan hidden + flex)
    function openModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closeModal(id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.add('hidden');
        m.classList.remove('flex');
    }

    window.usuarioEsExterno = function() { return window.TIPO_USER === 1; }
    function usuarioEsInterno() { return window.TIPO_USER == 2; }

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

    function formatearEstado(estado) {
        if (!estado) return '';
        const etiquetas = {
            'PENDIENTE': 'Pendiente', 'OBSERVADO': 'Observado', 'EN_PROCESO': 'En proceso',
            'VERIFICADO': 'Verificado', 'FINALIZADO': 'Finalizado', 'RECHAZADO': 'Rechazado'
        };
        if (etiquetas[estado]) return etiquetas[estado];
        return estado.replace(/_/g, ' ').toLowerCase().split(' ').map(p => p.charAt(0).toUpperCase() + p.slice(1)).join(' ');
    }

    function formatearFecha(fecha) {
        if (!fecha) return '—';
        const date = new Date(fecha);
        return isNaN(date) ? '—' : date.toLocaleDateString('es-PE', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
    }

    function semaforoMeta(dias) {
        if (dias >= 30) return { color: '#dc2626', label: dias + ' días · Vencido' };
        if (dias >= 21) return { color: '#f97316', label: dias + ' días · Urgente' };
        if (dias >= 15) return { color: '#eab308', label: dias + ' días · Advertencia' };
        if (dias >= 9)  return { color: '#eab308', label: dias + ' días · Atención', ring: true };
        return { color: '#22c55e', label: dias + ' días · Normal' };
    }

    function obtenerIndicadorDias(diasTranscurridos, fechaCreacion) {
        const LIMITE_DIAS = 30;
        if (diasTranscurridos === undefined || diasTranscurridos === null) {
            if (fechaCreacion) {
                const diff = new Date().getTime() - new Date(fechaCreacion).getTime();
                diasTranscurridos = Math.floor(diff / (1000 * 60 * 60 * 24));
                if (diasTranscurridos < 0 || isNaN(diasTranscurridos)) diasTranscurridos = 0;
            } else diasTranscurridos = 0;
        }
        diasTranscurridos = Math.floor(Math.abs(diasTranscurridos || 0));
        const meta = semaforoMeta(Math.min(diasTranscurridos, 99));
        return `<span class="inline-flex items-center justify-center w-7 h-7 rounded-lg border border-slate-300 shadow-sm" style="background-color:${meta.color}" title="${meta.label} (límite ${LIMITE_DIAS} días)"><span class="text-[10px] font-extrabold text-white drop-shadow">${diasTranscurridos > 99 ? '+99' : diasTranscurridos}</span></span>`;
    }

    function cargarTramites(pagina = 1) {
        const formData = new FormData(document.getElementById('filtrosForm'));
        filtrosActuales = {
            estado: formData.get('estado'),
            fecha_desde: formData.get('fecha_desde'),
            fecha_hasta: formData.get('fecha_hasta'),
        };
        const payload = new FormData();
        payload.append('pagina', pagina);
        payload.append('por_pagina', porPagina);
        payload.append('filtros', JSON.stringify(filtrosActuales));

        fetch('{{ route("mesa-partes.tramites-recibidos.ajax") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: payload
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                renderTramites(data.tramites || []);
                paginaActual = data.pagina_actual || 1;
                totalPaginas = data.paginas || 1;
                actualizarPaginacion(data);
            } else {
                document.getElementById('tramitesTableBody').innerHTML =
                    `<tr><td colspan="9" class="px-6 py-12 text-center"><p class="text-sm font-semibold text-slate-500">${(data.message || 'No hay trámites disponibles')}</p></td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('tramitesTableBody').innerHTML =
                `<tr><td colspan="9" class="px-6 py-12 text-center"><p class="text-sm font-semibold text-red-500">Error al cargar los trámites: ${error.message}</p></td></tr>`;
        });
    }

    function renderTramites(tramites) {
        const tbody = document.getElementById('tramitesTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (!tramites || tramites.length === 0) {
            tbody.innerHTML = `<tr><td colspan="9" class="px-6 py-14 text-center">
                <div class="flex flex-col items-center gap-2">
                    <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <p class="text-[15px] font-extrabold text-slate-700">Sin resultados</p>
                    <p class="text-[13px] text-slate-400">No hay expedientes para los filtros seleccionados.</p>
                </div></td></tr>`;
            return;
        }

        tramites.forEach(tramite => {
            tramitesCache[tramite.id] = tramite;
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/80 bg-white';
            tr.innerHTML = `
                <td class="px-5 py-3.5 whitespace-nowrap"><span class="inline-flex items-center rounded-lg bg-slate-900 text-white text-[12px] font-extrabold px-2.5 py-1">#${tramite.id}</span></td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    ${tramite.archivo_adjunto ? `
                        <div class="flex items-center gap-1.5">
                            <a href="/tramites/${tramite.id}/archivo/${tramite.archivo_adjunto}" download target="_blank" title="Descargar documento"
                               class="icon-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                PDF</a>
                            <a href="/tramites/${tramite.id}/visor/${tramite.archivo_adjunto}" title="Ver documento"
                               class="icon-btn bg-primary-50 text-primary-700 hover:bg-primary-100 border border-primary-100">Ver</a>
                        </div>` : '<span class="text-[12px] font-medium text-slate-400">Sin archivo</span>'}
                </td>
                <td class="px-4 py-3.5"><span class="block max-w-[220px] truncate text-[13px] font-semibold text-slate-700" title="${(tramite.procedimiento_nombre || '').replace(/"/g, '&quot;')}">${tramite.procedimiento_nombre || '—'}</span></td>
                <td class="px-4 py-3.5 whitespace-nowrap"><span class="estado-pill ${obtenerClaseEstado(tramite.estado)}">${formatearEstado(tramite.estado)}</span></td>
                <td class="px-4 py-3.5 whitespace-nowrap text-center">${obtenerIndicadorDias(tramite.dias_transcurridos, tramite.fecha_creacion)}</td>
                <td class="px-4 py-3.5 whitespace-nowrap text-[12.5px] font-medium text-slate-500">${formatearFecha(tramite.fecha_creacion)}</td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    ${tramite.voucher ? `<a href="/tramites/${tramite.id}/archivo/${tramite.voucher}" download target="_blank" class="icon-btn bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200">Voucher</a>`
                        : '<span class="text-[12px] font-medium text-slate-300">—</span>'}
                </td>
                <td class="px-4 py-3.5 whitespace-nowrap">
                    <button onclick="verHojaRuta(${tramite.id})" class="icon-btn bg-white border border-slate-200 text-slate-600 hover:border-primary-300 hover:text-primary-700 hover:bg-primary-50">Hoja de ruta</button>
                </td>
                <td class="px-5 py-3.5 whitespace-nowrap text-right"><div class="flex items-center justify-end gap-1.5 flex-wrap">${generarBotonesAccion(tramite)}</div></td>
            `;
            tbody.appendChild(tr);
        });
    }

    window.generarBotonesAccion = function(tramite) {
        let botones = '';
        if (tramite.estado === 'RECHAZADO') return '<span class="text-[11.5px] font-semibold text-slate-400">—</span>';

        const esUsuarioExterno = window.TIPO_USER === 1;
        if (esUsuarioExterno && tramite.estado === 'OBSERVADO') {
            botones += `<button onclick="verObservacion(${tramite.id})" class="icon-btn bg-amber-500 hover:bg-amber-600 text-white" title="Ver motivo de la observación">Ver observación</button>`;
        }
        if (tramite.oficio && !tramite.requiere_oficio) {
            botones += `<button onclick="verOficio('${tramite.oficio}', ${tramite.id})" class="icon-btn bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200">Ver oficio</button>`;
        }
        if (tramite.accionesPermitidas && tramite.accionesPermitidas.length > 0) {
            botones += `<button onclick="abrirModalAcciones(${tramite.id})" class="icon-btn bg-slate-900 hover:bg-slate-700 text-white">Acciones ▾</button>`;
        } else {
            const tooltip = tramite.requiere_oficio ? 'Requiere oficio del creador para habilitar acciones' : 'Sin acciones disponibles';
            botones += `<span class="icon-btn bg-slate-100 text-slate-400 cursor-not-allowed border border-slate-200" title="${tooltip}">${tramite.requiere_oficio ? 'Requiere oficio' : 'Sin acciones'}</span>`;
        }
        return botones;
    }

    function actualizarPaginacion(data) {
        document.getElementById('paginaActual').textContent = data.pagina_actual || 1;
        document.getElementById('totalRegistros').textContent = data.total || 0;
        const top = document.getElementById('totalRegistrosTop');
        if (top) top.textContent = data.total || 0;
        const inicio = data.tramites && data.tramites.length > 0 ? ((data.pagina_actual - 1) * porPagina + 1) : 0;
        const fin = data.tramites && data.tramites.length > 0 ? ((data.pagina_actual - 1) * porPagina + data.tramites.length) : 0;
        document.getElementById('inicioRegistros').textContent = inicio;
        document.getElementById('finRegistros').textContent = fin;

        const setState = (btn) => {
            if (!btn) return;
            const isPrev = btn.id === 'prevPage' || btn.hasAttribute('data-prev');
            const disabled = isPrev ? (data.pagina_actual <= 1) : (data.pagina_actual >= data.paginas || data.paginas === 0);
            btn.disabled = disabled;
            btn.classList.toggle('opacity-40', disabled);
            btn.classList.toggle('cursor-not-allowed', disabled);
        };
        setState(document.getElementById('prevPage'));
        setState(document.getElementById('nextPage'));
        document.querySelectorAll('[data-prev]').forEach(setState);
        document.querySelectorAll('[data-next]').forEach(setState);
    }

    window.abrirModalAcciones = async function(idTramite) {
        const tramite = tramitesCache[idTramite];
        if (!tramite) return alert('No se encontró el trámite.');

        const modal = document.getElementById('modalAccionesTramite');
        const cont = document.getElementById('modalAccionesBtns');
        cont.innerHTML = '';

        if (tramite.accionesPermitidas && tramite.accionesPermitidas.length > 0) {
            const etiquetasAccion = {
                'ACEPTAR': 'Aceptar', 'OBSERVAR': 'Observar', 'OBSERVAR_CON_OFICIO': 'Observar con oficio',
                'RECHAZAR': 'Rechazar', 'DERIVAR': 'Derivar', 'DERIVAR_ESPECIALISTA': 'Derivar a especialista',
                'MODO_MANUAL': 'Activar derivación manual', 'MODO_AUTO': 'Restaurar derivación automática',
                'RETROCEDER': 'Retroceder', 'VERIFICAR': 'Verificar', 'PROCESAR': 'Procesar',
                'CORREGIR': 'Corregir', 'SUBIR_OFICIO': 'Subir oficio',
            };

            const esManual = tramite.id_nodo_actual && (tramite.modo_derivacion === 'MANUAL_FORZADO' || tramite.modo_derivacion === true);
            const nodoPuedeFinalizar = tramite.nodo_puede_finalizar === true || tramite.nodo_puede_finalizar === 'true';
            const esNodoFinal = tramite.id_nodo_actual && nodoPuedeFinalizar;
            const debeCargarOpciones = (esManual || esNodoFinal) && tramite.accionesPermitidas.includes('DERIVAR');

            let opcionesDerivacion = [];
            if (debeCargarOpciones) {
                try {
                    const rOp = await fetch(`/tramites/${idTramite}/flujo/derivacion-opciones`, { headers: { 'Accept': 'application/json' } });
                    const op = await rOp.json();
                    opcionesDerivacion = op.transiciones || [];
                } catch (e) { console.warn('No se pudieron cargar opciones de derivación', e); }
            }

            tramite.accionesPermitidas.forEach(acc => {
                if (acc === 'DERIVAR' && debeCargarOpciones && opcionesDerivacion.length > 0) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'w-full rounded-xl border border-slate-200 bg-slate-50 p-3';

                    const label = document.createElement('label');
                    label.className = 'block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5';
                    label.textContent = 'Derivar a:';
                    wrapper.appendChild(label);

                    const esManualForzado = tramite.modo_derivacion === 'MANUAL_FORZADO';

                    if (esManualForzado && opcionesDerivacion.length > 1) {
                        const nivelSelect = document.createElement('select');
                        nivelSelect.className = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm mb-2 focus:ring-2 focus:ring-primary-500 focus:outline-none';
                        nivelSelect.innerHTML = `<option value="" disabled selected>Seleccionar nivel...</option><option value="1">Nivel 1</option><option value="2">Nivel 2</option><option value="3">Nivel 3</option><option value="4">Nivel 4</option>`;
                        wrapper.appendChild(nivelSelect);

                        const select = document.createElement('select');
                        select.className = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm mb-2 focus:ring-2 focus:ring-primary-500 focus:outline-none';
                        select.id = 'derivarDestinoSelect';
                        select.disabled = true;
                        select.innerHTML = '<option value="" disabled selected>Primero seleccione un nivel...</option>';
                        wrapper.appendChild(select);

                        nivelSelect.onchange = async function() {
                            const nivel = this.value;
                            if (!nivel) return;
                            select.innerHTML = '<option value="">Cargando…</option>';
                            select.disabled = true;
                            try {
                                const resp = await fetch('{{ route("flujo.nodos-globales") }}', { headers: { 'Accept': 'application/json' } });
                                const data = await resp.json();
                                const nodos = data.niveles?.[parseInt(nivel)] || [];
                                select.innerHTML = '<option value="" disabled selected>Seleccionar nodo...</option>';
                                if (nodos.length === 0) { select.innerHTML += '<option value="" disabled>No hay nodos en este nivel</option>'; return; }
                                nodos.forEach(n => {
                                    const opt = document.createElement('option');
                                    opt.value = n.id;
                                    opt.textContent = `${n.nombre} (${n.procedimiento})${n.puede_finalizar ? ' 🎯' : ''}`;
                                    select.appendChild(opt);
                                });
                                select.disabled = false;
                            } catch (e) { select.innerHTML = '<option value="" disabled>Error al cargar nodos</option>'; }
                        };
                    } else {
                        const select = document.createElement('select');
                        select.className = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm mb-2 focus:ring-2 focus:ring-primary-500 focus:outline-none';
                        select.id = 'derivarDestinoSelect';
                        if (opcionesDerivacion.length === 1) {
                            const opt = document.createElement('option');
                            opt.value = opcionesDerivacion[0].hacia_nodo_id;
                            opt.textContent = opcionesDerivacion[0].nombre_destino + ' (auto)';
                            opt.selected = true;
                            select.appendChild(opt);
                            select.disabled = true;
                        } else {
                            select.innerHTML = '<option value="" disabled selected>Seleccionar destino...</option>';
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
                    btn.className = 'w-full rounded-xl bg-primary-700 hover:bg-primary-800 text-white font-bold py-2.5 px-4 text-sm transition';
                    btn.textContent = 'Derivar';
                    btn.onclick = function() {
                        const destinoId = document.getElementById('derivarDestinoSelect').value;
                        if (!destinoId) return alert('Seleccione un destino');
                        closeModal('modalAccionesTramite');
                        derivarTramiteConDestino(idTramite, parseInt(destinoId));
                    };
                    wrapper.appendChild(btn);
                    cont.appendChild(wrapper);
                } else {
                    const btn = document.createElement('button');
                    if (acc === 'MODO_AUTO') {
                        fetch(`/tramites/${idTramite}/puede-reactivar-auto`, { headers: { 'Accept': 'application/json' } })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success && data.puede) {
                                    btn.className = 'w-full rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2.5 px-4 text-sm transition text-left';
                                    btn.textContent = etiquetasAccion[acc] || acc;
                                    btn.onclick = function() { closeModal('modalAccionesTramite'); ejecutarAccion(acc, idTramite); };
                                } else {
                                    btn.className = 'w-full rounded-xl bg-slate-50 text-slate-400 font-bold py-2.5 px-4 text-sm cursor-not-allowed text-left border border-slate-200';
                                    btn.textContent = etiquetasAccion[acc] || acc;
                                    btn.disabled = true;
                                    btn.title = data.message || 'No se puede reactivar.';
                                }
                                cont.appendChild(btn);
                            })
                            .catch(() => {
                                btn.className = 'w-full rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold py-2.5 px-4 text-sm transition text-left';
                                btn.textContent = etiquetasAccion[acc] || acc;
                                btn.onclick = function() { closeModal('modalAccionesTramite'); ejecutarAccion(acc, idTramite); };
                                cont.appendChild(btn);
                            });
                    } else {
                        const danger = (acc === 'RECHAZAR');
                        btn.className = danger
                            ? 'w-full rounded-xl bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold py-2.5 px-4 text-sm transition text-left'
                            : 'w-full rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-800 font-bold py-2.5 px-4 text-sm transition text-left';
                        btn.textContent = etiquetasAccion[acc] || acc;
                        btn.onclick = function() { closeModal('modalAccionesTramite'); ejecutarAccion(acc, idTramite); };
                        cont.appendChild(btn);
                    }
                }
            });
        } else {
            cont.innerHTML = '<p class="text-sm text-slate-400 font-medium text-center py-4">No hay acciones disponibles</p>';
        }

        document.getElementById('modalAccionesTitulo').textContent = `Acciones · Expediente #${tramite.id}`;
        openModal('modalAccionesTramite');
    }

    function ejecutarAccion(accion, idTramite) {
        switch(accion) {
            case 'CORREGIR': mostrarModalCorregir(idTramite); break;
            case 'OBSERVAR': observarTramite(idTramite); break;
            case 'OBSERVAR_CON_OFICIO': observarConOficioTramite(idTramite); break;
            case 'DERIVAR': derivarTramite(idTramite); break;
            case 'RETROCEDER': retrocederTramite(idTramite); break;
            case 'ACEPTAR': aceptarTramite(idTramite); break;
            case 'MODO_MANUAL': activarModoManualTramite(idTramite); break;
            case 'MODO_AUTO': desactivarModoManualTramite(idTramite); break;
            case 'RECHAZAR': rechazarTramite(idTramite); break;
            case 'VERIFICAR': verificarTramite(idTramite); break;
            case 'DERIVAR_ESPECIALISTA': derivarEspecialistaTramite(idTramite); break;
            case 'PROCESAR': procesarTramite(idTramite); break;
            case 'SUBIR_OFICIO': mostrarModalOficio(idTramite); break;
        }
    }

    window.mostrarModalCorregir = function(idTramite) {
        document.getElementById('corregir_id_tramite').value = idTramite;
        openModal('modalCorregir');
    }

    window.observarTramite = function(id) {
        const observacion = prompt('Ingrese el motivo de la observación:');
        if (!observacion) return;
        fetch(`/tramites/${id}/observar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ observacion: observacion })
        }).then(res => res.json()).then(data => {
            if (data.success) { alert('Trámite observado correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al observar el trámite.');
        }).catch(() => alert('Error de red al intentar observar el trámite.'));
    }

    window.derivarTramite = async function(id) {
        const tInfo = tramitesCache[id];
        const reiniciaFlujo = tInfo && tInfo.id_nodo_actual && tInfo.nodo_puede_finalizar && tInfo.modo_derivacion !== 'MANUAL_FORZADO';
        if (!confirm(reiniciaFlujo ? '¿Derivar este trámite? Al estar en el nivel final, la derivación automática lo enviará al inicio del flujo.' : '¿Está seguro de derivar este trámite al siguiente nivel?')) return;
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const headersJson = { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' };
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
                    if (isNaN(idx) || idx < 0 || idx >= tr.length) { alert('Opción no válida.'); return; }
                    body = JSON.stringify({ hacia_nodo_id: tr[idx].hacia_nodo_id });
                } else if (tr.length === 1) body = JSON.stringify({ hacia_nodo_id: tr[0].hacia_nodo_id });
            } catch (e) { alert('No se pudieron cargar las opciones de derivación.'); return; }
        }
        fetch(`/tramites/${id}/derivar`, { method: 'POST', headers: body ? headersJson : { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: body || undefined })
        .then(res => res.json()).then(data => {
            if (data.success) { alert(data.message || 'Trámite derivado correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al derivar el trámite.');
        }).catch(() => alert('Error de red al intentar derivar el trámite.'));
    }

    window.derivarTramiteConDestino = async function(id, destinoId) {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        fetch(`/tramites/${id}/derivar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ hacia_nodo_id: destinoId }),
        }).then(res => res.json()).then(data => {
            if (data.success) { alert(data.message || 'Trámite derivado correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al derivar el trámite.');
        }).catch(() => alert('Error de red al intentar derivar el trámite.'));
    }

    window.retrocederTramite = function(id) {
        if (!confirm('¿Retroceder el trámite al paso anterior del flujo (derivación o ingreso)?')) return;
        fetch(`/tramites/${id}/flujo/retroceder`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert(data.message || 'Retroceso aplicado.'); cargarTramites(paginaActual); }
            else alert(data.message || 'No se pudo retroceder.');
        }).catch(() => alert('Error de red al retroceder el trámite.'));
    };

    window.activarModoManualTramite = function(id) {
        if (!confirm('¿Activar derivación manual permanente para este trámite? Ya no se usará derivación automática.')) return;
        fetch(`/tramites/${id}/flujo/modo-manual`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert(data.message || 'Modo manual activado.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al activar el modo manual.');
        }).catch(() => alert('Error de red al activar el modo manual.'));
    };

    window.desactivarModoManualTramite = function(id) {
        if (!confirm('¿Restaurar derivación automática para este trámite?')) return;
        fetch(`/tramites/${id}/flujo/modo-auto`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert(data.message || 'Derivación automática restaurada.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al restaurar el modo automático.');
        }).catch(() => alert('Error de red al restaurar el modo automático.'));
    };

    window.aceptarTramite = function(id) {
        if (!confirm('¿Está seguro de aceptar este trámite?')) return;
        fetch(`/tramites/${id}/aceptar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert('Trámite aceptado correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al aceptar el trámite.');
        }).catch(() => alert('Error de red al intentar aceptar el trámite.'));
    }

    window.rechazarTramite = function(id) {
        const motivo = prompt('Ingrese el motivo del rechazo:');
        if (!motivo) return;
        fetch(`/tramites/${id}/rechazar`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ motivo: motivo })
        }).then(res => res.json()).then(data => {
            if (data.success) { alert('Trámite rechazado correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al rechazar el trámite.');
        }).catch(() => alert('Error de red al intentar rechazar el trámite.'));
    }

    window.verificarTramite = function(id) {
        if (!confirm('¿Está seguro de verificar este trámite?')) return;
        fetch(`/tramites/${id}/verificar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert('Trámite verificado correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al verificar el trámite.');
        }).catch(() => alert('Error de red al intentar verificar el trámite.'));
    }

    window.derivarEspecialistaTramite = function(id) {
        if (!confirm('¿Está seguro de derivar este trámite al especialista?')) return;
        fetch(`/tramites/${id}/derivar-especialista`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert('Trámite derivado al especialista correctamente.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al derivar al especialista.');
        }).catch(() => alert('Error de red al intentar derivar al especialista.'));
    }

    window.observarConOficioTramite = function(id) {
        document.getElementById('id_tramite_observar_oficio').value = id;
        openModal('modalObservarConOficio');
    }
    window.cerrarModalObservarConOficio = function() {
        closeModal('modalObservarConOficio');
        document.getElementById('formObservarConOficio').reset();
    }

    window.procesarTramite = function(id) {
        if (!confirm('¿Está seguro de que desea finalizar este trámite y generar el voucher?')) return;
        fetch(`/tramites/${id}/procesar`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            if (data.success) { alert('Trámite finalizado correctamente. El voucher ha sido generado.'); cargarTramites(paginaActual); }
            else alert(data.message || 'Error al finalizar el trámite.');
        }).catch(() => alert('Error de red al intentar procesar el trámite.'));
    }

    window.verHojaRuta = function(idTramite) {
        const tbody = document.getElementById('hr-niveles-tbody');
        tbody.innerHTML = '<tr><td colspan="2" class="py-8 text-center text-sm text-slate-400">Cargando…</td></tr>';
        openModal('hojaRutaModal');
        fetch(`/mesa-partes/hoja-ruta/${idTramite}`, { method: 'GET', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } })
        .then(res => res.json()).then(data => {
            tbody.innerHTML = '';
            if (!data.success) { tbody.innerHTML = `<tr><td colspan="2" class="py-6 text-center text-sm font-semibold text-red-500">${(data.message || 'Error al cargar datos')}</td></tr>`; return; }
            if (data.posicion_actual) {
                const pos = data.posicion_actual;
                tbody.innerHTML += `<tr class="bg-primary-50"><td class="py-3 px-4 text-[13px]" colspan="2">📍 <strong>Posición actual:</strong> ${pos.nodo_nombre} (Orden ${pos.nodo_orden})
                    <span class="ml-2 px-2 py-0.5 text-[11px] font-bold rounded-full ${pos.puede_finalizar ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'}">${pos.puede_finalizar ? 'Nodo final' : 'Nodo intermedio'}</span>
                    <span class="ml-1 px-2 py-0.5 text-[11px] font-bold rounded-full ${pos.modo_derivacion === 'MANUAL_FORZADO' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800'}">${pos.modo_derivacion === 'MANUAL_FORZADO' ? 'Manual' : 'Automático'}</span></td></tr>`;
            }
            if (data.historial && data.historial.length > 0) {
                tbody.innerHTML += `<tr class="bg-slate-100"><td class="py-2 px-4 text-[12px] font-extrabold uppercase tracking-wide text-slate-500" colspan="2">Historial de movimientos (${data.historial.length})</td></tr>`;
                data.historial.forEach((mov, index) => {
                    const desde = mov.nodo_desde ? `${mov.nodo_desde.nombre} (Ord: ${mov.nodo_desde.orden})` : '—';
                    const hacia = mov.nodo_hacia ? `${mov.nodo_hacia.nombre} (Ord: ${mov.nodo_hacia.orden})` : '—';
                    const usuario = mov.usuario ? `${mov.usuario.nombre} (${mov.usuario.email})` : 'Sistema';
                    tbody.innerHTML += `<tr class="${index % 2 === 0 ? 'bg-white' : 'bg-slate-50'}">
                        <td class="py-2.5 px-4 text-[12px] text-slate-500 whitespace-nowrap">${mov.fecha}<br><span class="inline-block mt-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">${mov.accion_legible}</span></td>
                        <td class="py-2.5 px-4 text-[13px]"><div><strong>De:</strong> ${desde}</div><div><strong>A:</strong> ${hacia}</div><div class="text-[12px] text-slate-500 mt-0.5">${usuario}</div></td></tr>`;
                });
            } else {
                tbody.innerHTML += `<tr><td class="py-4 px-4 text-center text-[13px] text-slate-400" colspan="2">Sin historial de movimientos</td></tr>`;
            }
            if (data.niveles_estaticos && data.niveles_estaticos.length > 0) {
                tbody.innerHTML += `<tr class="bg-slate-100"><td class="py-2 px-4 text-[12px] font-extrabold uppercase tracking-wide text-slate-500" colspan="2">Ruta esperada</td></tr>`;
                data.niveles_estaticos.forEach(nivel => {
                    const esActual = data.posicion_actual && data.posicion_actual.nodo_id && nivel.id_nodo && data.posicion_actual.nodo_id === nivel.id_nodo;
                    tbody.innerHTML += `<tr class="${esActual ? 'bg-primary-50 font-semibold' : ''}">
                        <td class="py-2 px-4 text-center font-extrabold text-slate-700">${nivel.nivel}</td>
                        <td class="py-2 px-4 text-[13px]">${nivel.nombre} ${esActual ? '<span class="ml-2 px-2 py-0.5 rounded-full text-[11px] font-bold bg-primary-600 text-white">ACTUAL</span>' : ''}</td></tr>`;
                });
            }
        }).catch(() => { tbody.innerHTML = '<tr><td colspan="2" class="py-6 text-center text-sm font-semibold text-red-500">Error al cargar datos</td></tr>'; });
    }
    window.cerrarHojaRuta = function() { closeModal('hojaRutaModal'); }

    window.verOficio = function(nombreOficio, tramiteId) {
        if (!nombreOficio || !tramiteId) { alert('No hay oficio adjunto'); return; }
        window.location.href = `/tramites/${tramiteId}/visor/${nombreOficio}`;
    }
    window.verDocumento = function(nombreDocumento, tramiteId) {
        if (!nombreDocumento || !tramiteId) { alert('No hay documento adjunto'); return; }
        window.location.href = `/tramites/${tramiteId}/visor/${nombreDocumento}`;
    }

    window.verObservacion = function(idTramite) {
        const tramite = tramitesCache[idTramite];
        if (!tramite) return alert('No se encontró el trámite.');
        document.getElementById('observacionTexto').textContent = tramite.observaciones || 'Sin observación registrada.';
        const oficioSection = document.getElementById('oficioSection');
        if (tramite.oficio && !tramite.requiere_oficio) {
            oficioSection.classList.remove('hidden');
            document.getElementById('btnVerOficioObservacion').onclick = () => verOficio(tramite.oficio, idTramite);
        } else oficioSection.classList.add('hidden');
        openModal('modalObservacion');
    }
    window.cerrarModalObservacion = function() { closeModal('modalObservacion'); }

    function validarArchivoPDF(input, errorId) {
        const errorDiv = document.getElementById(errorId);
        const submitBtn = input.id === 'corregir_archivo' ? document.getElementById('btnEnviarCorreccion') : document.getElementById('btnSubirOficio');
        const setInvalid = (msg) => {
            if (errorDiv) { errorDiv.textContent = msg; errorDiv.classList.remove('hidden'); }
            if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('opacity-50', 'cursor-not-allowed'); }
            return false;
        };
        if (!input.files || input.files.length === 0) return setInvalid('Debe seleccionar un archivo');
        const file = input.files[0];
        if (file.type !== 'application/pdf') { input.value = ''; return setInvalid('Solo se permiten archivos PDF'); }
        if (file.size > 25 * 1024 * 1024) { input.value = ''; return setInvalid('El archivo no debe superar los 25 MB'); }
        if (errorDiv) errorDiv.classList.add('hidden');
        if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('opacity-50', 'cursor-not-allowed'); }
        return true;
    }

    window.mostrarModalOficio = function(idTramite) {
        document.getElementById('id_tramite_oficio').value = idTramite;
        openModal('modalOficio');
        document.getElementById('formOficio').reset();
        document.getElementById('oficio_archivo_error').classList.add('hidden');
        const btn = document.getElementById('btnSubirOficio');
        btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed');
    }
    window.cerrarModalOficio = function() {
        closeModal('modalOficio');
        document.getElementById('formOficio').reset();
        document.getElementById('oficio_archivo_error').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('cerrarModalAcciones').onclick = function() { closeModal('modalAccionesTramite'); };

        const closeCorr = () => {
            closeModal('modalCorregir');
            document.getElementById('formCorregir').reset();
            document.getElementById('corregirMensaje').textContent = '';
            document.getElementById('corregir_archivo_error').classList.add('hidden');
            const btn = document.getElementById('btnEnviarCorreccion');
            btn.disabled = true; btn.classList.add('opacity-50', 'cursor-not-allowed');
        };
        document.getElementById('cerrarModalCorregir').onclick = closeCorr;
        document.getElementById('cerrarModalCorregirBtn').onclick = closeCorr;

        // Cerrar modales con clic fuera / Escape (bloqueado durante mutaciones)
        ['hojaRutaModal','modalCorregir','modalOficio','modalObservarConOficio','modalObservacion','modalAccionesTramite'].forEach(id => {
            const m = document.getElementById(id);
            if (m) m.addEventListener('click', function(e) { if (e.target === m && !(window.app && window.app.mutationsInFlight > 0)) closeModal(id); });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !(window.app && window.app.mutationsInFlight > 0)) ['hojaRutaModal','modalCorregir','modalOficio','modalObservarConOficio','modalObservacion','modalAccionesTramite'].forEach(closeModal);
        });

        document.getElementById('formCorregir').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const mensajeDiv = document.getElementById('corregirMensaje');
            if (!validarArchivoPDF(document.getElementById('corregir_archivo'), 'corregir_archivo_error')) return;
            mensajeDiv.textContent = 'Enviando...';
            mensajeDiv.className = 'mt-3 text-sm text-blue-600';
            fetch(`/tramites/${formData.get('id_tramite')}/corregir`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: formData
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    mensajeDiv.textContent = 'Corrección enviada correctamente.';
                    mensajeDiv.className = 'mt-3 text-sm text-green-600';
                    setTimeout(() => { closeCorr(); cargarTramites(paginaActual); }, 1200);
                } else { mensajeDiv.textContent = data.message || 'Error al corregir el trámite.'; mensajeDiv.className = 'mt-3 text-sm text-red-600'; }
            }).catch(() => { mensajeDiv.textContent = 'Error al enviar la corrección.'; mensajeDiv.className = 'mt-3 text-sm text-red-600'; });
        };

        document.getElementById('formOficio').onsubmit = function(e) {
            e.preventDefault();
            if (!validarArchivoPDF(document.getElementById('archivo_oficio'), 'oficio_archivo_error')) return;
            const formData = new FormData(this);
            const btn = document.getElementById('btnSubirOficio');
            const originalText = btn.textContent;
            btn.textContent = 'Subiendo...'; btn.disabled = true;
            fetch(`/tramites/${formData.get('id_tramite')}/subir-oficio`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                body: formData
            }).then(res => res.json()).then(data => {
                if (data.success) { alert('Oficio subido correctamente'); cerrarModalOficio(); cargarTramites(paginaActual); }
                else alert(data.message || 'Error al subir el oficio');
            }).catch(() => alert('Error de red al intentar subir el oficio'))
            .finally(() => { btn.textContent = originalText; });
        };

        document.getElementById('formObservarConOficio').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const observacion = formData.get('observacion');
            const idTramite = formData.get('id_tramite');
            if (!observacion || observacion.trim() === '') { alert('Por favor ingrese el motivo de la observación'); return; }
            fetch(`/tramites/${idTramite}/observar-con-oficio`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ observacion: observacion })
            }).then(res => res.json()).then(data => {
                if (data.success) { alert('Trámite observado con oficio correctamente.'); cerrarModalObservarConOficio(); cargarTramites(paginaActual); }
                else alert(data.message || 'Error al observar el trámite con oficio.');
            }).catch(() => alert('Error de red al intentar observar el trámite.'));
        };

        const fechaDesdeInput = document.getElementById('filtro_fecha_desde');
        const fechaHastaInput = document.getElementById('filtro_fecha_hasta');
        const fechaActual = new Date().toISOString().split('T')[0];
        if (fechaDesdeInput) fechaDesdeInput.setAttribute('max', fechaActual);
        if (fechaHastaInput) fechaHastaInput.setAttribute('max', fechaActual);
        if (fechaDesdeInput) fechaDesdeInput.addEventListener('change', function() {
            const fechaDesde = this.value;
            if (fechaHastaInput) {
                if (fechaDesde) {
                    fechaHastaInput.setAttribute('min', fechaDesde);
                    if (fechaHastaInput.value && fechaHastaInput.value < fechaDesde) fechaHastaInput.value = fechaDesde;
                } else fechaHastaInput.removeAttribute('min');
            }
        });
        if (fechaHastaInput) fechaHastaInput.addEventListener('change', function() {
            const fechaHasta = this.value;
            const fechaDesde = fechaDesdeInput ? fechaDesdeInput.value : '';
            if (fechaDesde && fechaHasta && fechaHasta < fechaDesde) { alert('La fecha "Hasta" debe ser igual o posterior a la fecha "Desde"'); this.value = fechaDesde; }
        });
        function validarFechas() {
            const fechaDesde = fechaDesdeInput ? fechaDesdeInput.value : '';
            const fechaHasta = fechaHastaInput ? fechaHastaInput.value : '';
            if (fechaDesde && fechaDesde > fechaActual) { alert('La fecha "Desde" no puede ser superior a la fecha actual'); if (fechaDesdeInput) fechaDesdeInput.value = ''; return false; }
            if (fechaHasta && fechaHasta > fechaActual) { alert('La fecha "Hasta" no puede ser superior a la fecha actual'); if (fechaHastaInput) fechaHastaInput.value = ''; return false; }
            if (fechaDesde && fechaHasta && fechaHasta < fechaDesde) { alert('La fecha "Hasta" debe ser igual o posterior a la fecha "Desde"'); if (fechaHastaInput) fechaHastaInput.value = fechaDesde; return false; }
            return true;
        }

        document.getElementById('filtrosForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!validarFechas()) return;
            paginaActual = 1;
            cargarTramites(1);
        });

        // Cantidad de registros por página (10 / 25 / 50 / 100)
        const porPaginaSelect = document.getElementById('porPagina');
        if (porPaginaSelect) porPaginaSelect.addEventListener('change', function() {
            porPagina = parseInt(this.value, 10) || 10;
            paginaActual = 1;
            cargarTramites(1);
        });

        const btnLimpiar = document.getElementById('btnLimpiarFiltros');
        if (btnLimpiar) btnLimpiar.addEventListener('click', function() {
            document.getElementById('filtrosForm').reset();
            if (fechaHastaInput) fechaHastaInput.removeAttribute('min');
            paginaActual = 1;
            cargarTramites(1);
        });

        const goPrev = () => { if (paginaActual > 1) { paginaActual--; cargarTramites(paginaActual); } };
        const goNext = () => { if (paginaActual < totalPaginas) { paginaActual++; cargarTramites(paginaActual); } };
        const prevMain = document.getElementById('prevPage');
        const nextMain = document.getElementById('nextPage');
        if (prevMain) prevMain.addEventListener('click', goPrev);
        if (nextMain) nextMain.addEventListener('click', goNext);
        document.querySelectorAll('[data-prev]').forEach(btn => btn.addEventListener('click', goPrev));
        document.querySelectorAll('[data-next]').forEach(btn => btn.addEventListener('click', goNext));

        cargarTramites(1);
    });
</script>
@endpush
