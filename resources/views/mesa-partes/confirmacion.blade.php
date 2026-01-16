@extends('layouts.mesa-partes')

@section('title', 'Confirmación de Trámite - Mesa de Partes')

@section('header', 'Confirmación de Trámite')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <!-- Mensaje de éxito -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-white">¡Trámite creado correctamente!</h2>
            </div>
        </div>
        
        <div class="p-6">
            <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                <p class="text-gray-700 mb-2">Su trámite ha sido registrado en el sistema con el siguiente número:</p>
                <p class="text-2xl font-bold text-green-700">
                    N° de Trámite: {{ $tramite->id }}-{{ $tramite->periodo ?? date('Y') }}
                </p>
                @if($tramite->numero_voucher)
                    <p class="text-lg text-gray-600 mt-2">
                        <strong>N° de Voucher:</strong> {{ $tramite->numero_voucher }}
                    </p>
                @endif
            </div>

            <!-- Información del Trámite y Solicitante -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Información del Trámite -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-300">
                        Información del Trámite
                    </h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Procedimiento:</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tramite->procedimiento->nombre ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Fecha de Registro:</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($tramite->fecha_creacion)
                                    {{ $tramite->fecha_creacion->format('d/m/Y H:i:s') }}
                                @else
                                    {{ now()->format('d/m/Y H:i:s') }}
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Estado:</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $tramite->estado }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tipo de Trámite:</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tramite->tipo_tramite ?? 'VIRTUAL' }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Información del Solicitante -->
                <div class="bg-gray-50 rounded-lg p-5">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-300">
                        Información del Solicitante
                    </h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre:</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $tramite->usuario->nombres }} {{ $tramite->usuario->apellido_paterno }} {{ $tramite->usuario->apellido_materno }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">DNI:</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tramite->usuario->dni }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email:</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $tramite->usuario->email }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('mesa-partes.voucher', $tramite->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                   target="_blank">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Descargar Voucher
                </a>
                <a href="{{ route('mesa-partes.nuevo') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Crear Otro Trámite
                </a>
                <a href="{{ route('mesa-partes.tramites-recibidos') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Ver Mis Documentos
                </a>
            </div>
        </div>
    </div>

    <!-- Mensaje informativo -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Importante:</strong> Guarde el número de trámite para realizar el seguimiento. 
                    Puede descargar el voucher como comprobante de su solicitud.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

