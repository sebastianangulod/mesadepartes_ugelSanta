@extends('layouts.mesa-partes')

@section('title', 'Mesa de Partes - Dashboard')

@section('header', 'Dashboard - Mesa de Partes')

@section('content')
<!-- Estadísticas rápidas -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="card border-l-4 border-blue-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Trámites Realizados</p>
                <p class="text-3xl font-bold text-gray-900">{{ $estadisticas['tramites_realizados'] ?? 0 }}</p>
            </div>
            <div class="bg-blue-100 rounded-full p-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="card border-l-4 border-green-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Trámites Completados</p>
                <p class="text-3xl font-bold text-gray-900">{{ $estadisticas['tramites_completados'] ?? 0 }}</p>
            </div>
            <div class="bg-green-100 rounded-full p-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="card border-l-4 border-yellow-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Trámites en Proceso</p>
                <p class="text-3xl font-bold text-gray-900">{{ $estadisticas['tramites_proceso'] ?? 0 }}</p>
            </div>
            <div class="bg-yellow-100 rounded-full p-3">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <div class="card border-l-4 border-red-500 hover:shadow-lg transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-2 font-semibold">Trámites Observados</p>
                <p class="text-3xl font-bold text-gray-900">{{ $estadisticas['tramites_observados'] ?? 0 }}</p>
            </div>
            <div class="bg-red-100 rounded-full p-3">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Acciones rápidas e información -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="card">
        <h3 class="text-lg font-bold mb-6 text-gray-800">Acciones Rápidas</h3>
        <div class="space-y-4">
            <a href="{{ route('mesa-partes.nuevo') }}" class="flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-6 rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Iniciar Nuevo Trámite
            </a>
            <a href="{{ route('mesa-partes.rastrear') }}" class="flex items-center justify-center bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3.5 px-6 rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Rastrear un Trámite
            </a>
            <a href="{{ route('mesa-partes.documentos-enviados') }}" class="flex items-center justify-center bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3.5 px-6 rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ver Mis Documentos
            </a>
        </div>
    </div>

    <div class="card">
        <h3 class="text-lg font-bold mb-4 text-gray-800">Información Importante</h3>
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg p-5 shadow-sm">
            <div class="flex items-start mb-3">
                <svg class="w-6 h-6 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h6 class="font-bold text-blue-900 text-base">Consejos útiles:</h6>
            </div>
            <ul class="text-sm space-y-2.5 text-gray-700 ml-8">
                <li class="flex items-start">
                    <span class="text-blue-600 mr-2">•</span>
                    <span>Verifica todos los datos antes de enviar un trámite</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-600 mr-2">•</span>
                    <span>Guarda el número de voucher para futuras consultas</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-600 mr-2">•</span>
                    <span>Los trámites observados deben corregirse dentro de 5 días</span>
                </li>
                <li class="flex items-start">
                    <span class="text-blue-600 mr-2">•</span>
                    <span>Puedes rastrear tu trámite en cualquier momento</span>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Trámites recientes -->
<div class="card">
    <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
        <h3 class="text-xl font-bold text-gray-800">Tus Trámites Recientes</h3>
        <a href="{{ route('mesa-partes.documentos-enviados') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors shadow-md hover:shadow-lg">
            Ver Todos
        </a>
    </div>
    
    @if(isset($tramitesRecientes) && $tramitesRecientes->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N° de Trámite</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tipo de Documento</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Asunto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($tramitesRecientes as $tramite)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $tramite->numero_tramite ?? $tramite->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $tramite->tipoDocumento->nombre ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($tramite->asunto ?? 'N/A', 50) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                @if($tramite->fecha_creacion)
                                    {{ $tramite->fecha_creacion->format('d/m/Y H:i') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $tramite->estado_nombre ?? $tramite->estado ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('mesa-partes.rastrear', ['codigo' => $tramite->codigo_seguimiento ?? $tramite->id]) }}" 
                                       class="text-blue-600 hover:text-blue-800 font-medium">
                                        Rastrear
                                    </a>
                                    @if(isset($tramite->voucher) && $tramite->voucher)
                                        <span class="text-gray-300">|</span>
                                        <a href="{{ route('mesa-partes.voucher', ['id' => $tramite->id]) }}" 
                                           class="text-green-600 hover:text-green-800 font-medium"
                                           target="_blank">
                                            Voucher
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600 text-lg font-medium mb-2">No tienes trámites recientes</p>
            <p class="text-gray-500 text-sm mb-6">Comienza creando tu primer trámite</p>
            <a href="{{ route('mesa-partes.nuevo') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3.5 px-6 rounded-lg transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Crear tu primer trámite
            </a>
        </div>
    @endif
</div>
@endsection
