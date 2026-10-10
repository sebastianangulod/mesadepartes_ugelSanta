@extends('layouts.mesa-partes')

@section('title', 'Panel · Mesa de Partes UGEL Santa')
@section('header', 'Panel general')
@section('subtitle', 'Resumen de tu actividad en la Mesa de Partes Digital de la UGEL Santa.')

@section('content')
<!-- Bienvenida institucional -->
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0b1c34] via-[#173763] to-[#1e4d8b] text-white shadow-lg mb-5">
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(rgba(255,255,255,.5) 1px, transparent 1px); background-size: 18px 18px;"></div>
    <div class="relative flex flex-col md:flex-row md:items-center gap-5 p-6 sm:p-8">
        <span class="w-16 h-16 rounded-2xl bg-white p-2 shadow-lg shrink-0">
            <img src="{{ asset('img/logo-ugel.png') }}" alt="UGEL Santa" class="w-full h-full object-contain" onerror="this.style.display='none'">
        </span>
        <div class="flex-1 min-w-0">
            <p class="text-[11px] font-extrabold uppercase tracking-[.18em] text-white/60">Unidad de Gestión Educativa Local Santa</p>
            <h3 class="mt-1 text-xl sm:text-2xl font-extrabold tracking-tight">Hola, {{ strtok(Auth::user()->nombres ?? 'usuario', ' ') }}</h3>
            <p class="mt-1 text-[13.5px] text-white/75 max-w-2xl">Gestiona tus expedientes de forma ágil y segura. Todos los movimientos quedan registrados con trazabilidad completa.</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 border border-white/20 px-3.5 py-2 text-[12px] font-semibold text-white/85">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Trazabilidad completa
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 border border-white/20 px-3.5 py-2 text-[12px] font-semibold text-white/85">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Seguimiento en tiempo real
            </span>
        </div>
    </div>
    <div class="h-1 bg-gradient-to-r from-primary-400 via-white/70 to-accent-600"></div>
</div>

<!-- Estadísticas -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden group hover:shadow-md transition">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary-600"></div>
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Realizados</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $estadisticas['tramites_realizados'] ?? 0 }}</p></div>
            <span class="w-11 h-11 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Completados</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $estadisticas['tramites_completados'] ?? 0 }}</p></div>
            <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">En proceso</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $estadisticas['tramites_proceso'] ?? 0 }}</p></div>
            <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
        </div>
    </div>
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
        <div class="flex items-start justify-between gap-3">
            <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Observados</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $estadisticas['tramites_observados'] ?? 0 }}</p></div>
            <span class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <!-- Recientes -->
    <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
            <div><h3 class="text-[15px] font-extrabold text-slate-900">Tus trámites recientes</h3><p class="text-[12px] text-slate-500">Últimos 5 expedientes registrados</p></div>
            <a href="{{ route('mesa-partes.tramites-recibidos') }}" class="rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-[12.5px] font-bold px-3.5 py-2 transition shadow">Ver todos</a>
        </div>
        @if(isset($tramitesRecientes) && $tramitesRecientes->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead><tr class="bg-slate-50">
                        <th class="px-5 py-3 text-left text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">N°</th>
                        <th class="px-4 py-3 text-left text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Procedimiento</th>
                        <th class="px-4 py-3 text-left text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-4 py-3 text-left text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-3 text-right text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Acción</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tramitesRecientes as $tramite)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-5 py-3.5 font-extrabold text-slate-900">#{{ $tramite->numero_tramite ?? $tramite->id }}</td>
                                <td class="px-4 py-3.5 text-slate-600 font-medium max-w-[220px] truncate">{{ $tramite->procedimiento->nombre ?? $tramite->tipoDocumento->nombre ?? '—' }}</td>
                                <td class="px-4 py-3.5 text-slate-500 text-[12.5px] whitespace-nowrap">{{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y H:i') : '—' }}</td>
                                <td class="px-4 py-3.5"><span class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 border border-primary-100 text-primary-800 text-[11.5px] font-bold px-2.5 py-1">{{ $tramite->estado_nombre ?? $tramite->estado ?? '—' }}</span></td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('mesa-partes.rastrear', ['codigo' => $tramite->codigo_seguimiento ?? $tramite->id]) }}" class="text-[12.5px] font-bold text-primary-700 hover:text-primary-900">Rastrear</a>
                                    @if(!empty($tramite->voucher))
                                        <span class="text-slate-200 mx-1">|</span>
                                        <a href="{{ route('mesa-partes.voucher', ['id' => $tramite->id]) }}" class="text-[12.5px] font-bold text-emerald-600 hover:text-emerald-800" target="_blank">Voucher</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center px-6 py-14">
                <span class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
                <p class="text-slate-700 font-extrabold">Aún no tienes trámites</p>
                <p class="text-slate-500 text-sm mt-1">Cuando registres expedientes aparecerán aquí con su estado actualizado.</p>
                <p class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-2 text-[12.5px] font-semibold text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Usa la opción «Trámite nuevo» del menú lateral para crear tu primer expediente
                </p>
            </div>
        @endif
    </div>

    <!-- Ayuda -->
    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-2.5 mb-4">
                <span class="w-9 h-9 rounded-xl bg-primary-700 text-white flex items-center justify-center"><svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                <h3 class="text-[15px] font-extrabold text-slate-900">Guía rápida</h3>
            </div>
            <ul class="space-y-3 text-[13px] text-slate-600">
                <li class="flex gap-2.5"><span class="w-6 h-6 rounded-lg bg-primary-50 text-primary-700 text-[12px] font-extrabold flex items-center justify-center shrink-0">1</span>Verifica tus datos y el PDF antes de enviar el trámite.</li>
                <li class="flex gap-2.5"><span class="w-6 h-6 rounded-lg bg-primary-50 text-primary-700 text-[12px] font-extrabold flex items-center justify-center shrink-0">2</span>Guarda el número de expediente y el voucher.</li>
                <li class="flex gap-2.5"><span class="w-6 h-6 rounded-lg bg-primary-50 text-primary-700 text-[12px] font-extrabold flex items-center justify-center shrink-0">3</span>Subsanar observaciones dentro del plazo indicado.</li>
                <li class="flex gap-2.5"><span class="w-6 h-6 rounded-lg bg-primary-50 text-primary-700 text-[12px] font-extrabold flex items-center justify-center shrink-0">4</span>Rastrea tu expediente en cualquier momento.</li>
            </ul>
        </div>
        <div class="rounded-2xl border border-primary-100 bg-gradient-to-br from-primary-50 to-white p-6 shadow-sm">
            <p class="text-[11px] font-extrabold uppercase tracking-[.14em] text-primary-600">Información institucional</p>
            <p class="mt-1 text-[14px] font-extrabold text-slate-900">Horario de mesa de partes</p>
            <ul class="mt-2 space-y-1.5 text-[13px] text-slate-500">
                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>Lunes a viernes · 8:00 — 16:30</li>
                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>Sede Chimbote · Santa — Áncash</li>
                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>Conserve su número de expediente y voucher</li>
            </ul>
        </div>
    </div>
</div>
@endsection
