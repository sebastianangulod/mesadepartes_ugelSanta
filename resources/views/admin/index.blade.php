{{--
    Vista de Dashboard de Administración
    Panel principal para usuarios administradores
    Muestra estadísticas y accesos rápidos
--}}
@extends('layouts.admin')

@section('title', 'Panel de Administración - Mesa de Partes')

@section('content')
    <div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-900">Panel de Administración</h2>
        <p class="text-gray-600">Bienvenido, {{ Auth::user()->nombres }}</p>
    </div>

    {{-- Estadísticas Generales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Total Usuarios</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalUsuarios ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Trámites Activos</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $tramitesActivos ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Pendientes</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $tramitesPendientes ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">Rechazados</h3>
                    <p class="text-2xl font-bold text-red-600">{{ $tramitesRechazados ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Trámites Recientes --}}
    <div class="mt-8 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Trámites Recientes</h3>
        </div>
        <div class="p-6">
            @if(isset($tramitesRecientes) && $tramitesRecientes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Expediente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asunto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tramitesRecientes as $tramite)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $tramite->numero_expediente ?? ($tramite->numero_voucher ?? 'MP-' . str_pad($tramite->id, 6, '0', STR_PAD_LEFT)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ Str::limit($tramite->asunto ?? ($tramite->procedimiento->nombre ?? 'Sin asunto'), 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tramite->usuario ? ($tramite->usuario->nombres . ' ' . $tramite->usuario->apellido_paterno) : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $estadoClases = [
                                                'PENDIENTE' => 'bg-yellow-100 text-yellow-800',
                                                'EN_PROCESO' => 'bg-blue-100 text-blue-800',
                                                'OBSERVADO' => 'bg-orange-100 text-orange-800',
                                                'VERIFICADO' => 'bg-purple-100 text-purple-800',
                                                'FINALIZADO' => 'bg-green-100 text-green-800',
                                                'RECHAZADO' => 'bg-red-100 text-red-800'
                                            ];
                                            $estadoEtiquetas = [
                                                'PENDIENTE' => 'Pendiente',
                                                'EN_PROCESO' => 'En Proceso',
                                                'OBSERVADO' => 'Observado',
                                                'VERIFICADO' => 'Verificado',
                                                'FINALIZADO' => 'Finalizado',
                                                'RECHAZADO' => 'Rechazado'
                                            ];
                                            $clase = $estadoClases[$tramite->estado] ?? 'bg-gray-100 text-gray-800';
                                            $etiqueta = $estadoEtiquetas[$tramite->estado] ?? $tramite->estado;
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $clase }}">
                                            {{ $etiqueta }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $tramite->fecha_creacion ? $tramite->fecha_creacion->format('d/m/Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('mesa-partes.tramites-recibidos') }}?tramite_id={{ $tramite->id }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-4">No hay trámites recientes para mostrar.</p>
            @endif
        </div>
    </div>
</div>
@endsection