{{--
    Vista de Dashboard de Administración
    Panel principal para usuarios administradores
    Muestra estadísticas y accesos rápidos
--}}
@extends('layouts.admin')

@section('title', 'Panel de Administración · UGEL Santa')

@section('header', 'Panel de Administración')

@section('subtitle')
    Bienvenido, {{ strtok(Auth::user()->nombres ?? 'administrador', ' ') }}. Resumen general del sistema y accesos directos.
@endsection

@section('content')
    {{-- Estadísticas Generales --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-5">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary-600"></div>
            <div class="flex items-start justify-between gap-3">
                <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Total Usuarios</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $totalUsuarios ?? 0 }}</p></div>
                <span class="w-11 h-11 rounded-xl bg-primary-50 text-primary-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path></svg>
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Trámites Activos</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $tramitesActivos ?? 0 }}</p></div>
                <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-amber-400"></div>
            <div class="flex items-start justify-between gap-3">
                <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Pendientes</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $tramitesPendientes ?? 0 }}</p></div>
                <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </span>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 relative overflow-hidden hover:shadow-md transition">
            <div class="absolute left-0 top-0 bottom-0 w-1 bg-red-500"></div>
            <div class="flex items-start justify-between gap-3">
                <div><p class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Rechazados</p><p class="mt-1 text-[28px] leading-none font-extrabold text-slate-900">{{ $tramitesRechazados ?? 0 }}</p></div>
                <span class="w-11 h-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </span>
            </div>
        </div>
    </div>

    {{-- Trámites Recientes --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
            <div><h3 class="text-[15px] font-extrabold text-slate-900">Trámites Recientes</h3><p class="text-[12px] text-slate-500">Últimos expedientes del sistema</p></div>
            <a href="{{ route('mesa-partes.tramites-recibidos') }}" class="rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-[12.5px] font-bold px-3.5 py-2 transition shadow">Ver bandeja</a>
        </div>
        <div class="px-5 sm:px-6 py-5">
            @if(isset($tramitesRecientes) && $tramitesRecientes->count() > 0)
                <div class="overflow-x-auto mp-scroll">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left">
                                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">N° Expediente</th>
                                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Asunto</th>
                                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-4 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tramitesRecientes as $tramite)
                                <tr class="hover:bg-slate-50/80 bg-white transition">
                                    <td class="px-4 py-3.5 font-extrabold text-slate-900 whitespace-nowrap">
                                        {{ $tramite->numero_expediente ?? ($tramite->numero_voucher ?? 'MP-' . str_pad($tramite->id, 6, '0', STR_PAD_LEFT)) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 font-medium max-w-[220px] truncate">
                                        {{ Str::limit($tramite->asunto ?? ($tramite->procedimiento->nombre ?? 'Sin asunto'), 50) }}
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 text-[12.5px] whitespace-nowrap">
                                        {{ $tramite->usuario ? ($tramite->usuario->nombres . ' ' . $tramite->usuario->apellido_paterno) : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @php
                                            $estadoClases = [
                                                'PENDIENTE' => 'estado-pendiente',
                                                'EN_PROCESO' => 'estado-proceso',
                                                'OBSERVADO' => 'estado-observado',
                                                'VERIFICADO' => 'estado-verificado',
                                                'FINALIZADO' => 'estado-finalizado',
                                                'RECHAZADO' => 'estado-rechazado'
                                            ];
                                            $estadoEtiquetas = [
                                                'PENDIENTE' => 'Pendiente',
                                                'EN_PROCESO' => 'En proceso',
                                                'OBSERVADO' => 'Observado',
                                                'VERIFICADO' => 'Verificado',
                                                'FINALIZADO' => 'Finalizado',
                                                'RECHAZADO' => 'Rechazado'
                                            ];
                                            $clase = $estadoClases[$tramite->estado] ?? 'estado-pendiente';
                                            $etiqueta = $estadoEtiquetas[$tramite->estado] ?? $tramite->estado;
                                        @endphp
                                        <span class="estado-pill {{ $clase }}">
                                            {{ $etiqueta }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 text-[12.5px] whitespace-nowrap">
                                        {{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('mesa-partes.tramites-recibidos') }}?tramite_id={{ $tramite->id }}" class="text-[12.5px] font-bold text-primary-700 hover:text-primary-900">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center gap-2 py-8">
                    <span class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <p class="text-[14px] font-extrabold text-slate-700">Sin trámites recientes</p>
                    <p class="text-[13px] text-slate-400">No hay expedientes para mostrar.</p>
                </div>
            @endif
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
</style>
@endpush
