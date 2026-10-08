{{--
    Vista de Documentos Enviados
    Equivalente a documentosEnviados.php del PHP puro
    Muestra los documentos enviados por el usuario autenticado
--}}
@extends('layouts.mesa-partes')

@section('title', 'Documentos Enviados - Mesa de Partes')

@section('header', 'Documentos Enviados')

@section('content')
        
        <div class="bg-white rounded-lg shadow p-6">
            @if($documentos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    N° Trámite
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Procedimiento
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Archivo
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th class="px-6 py-3 border-b-2 border-gray-300 text-left text-sm leading-4 font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentos as $documento)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    {{ $documento->numero_voucher ?? $documento->id }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    {{ $documento->fecha_creacion->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    {{ $documento->procedimiento->nombre ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    {{ $documento->archivo_adjunto ? basename($documento->archivo_adjunto) : 'Sin archivo' }}
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $documento->estado_clases_css ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $documento->estado_etiqueta ?? $documento->estado }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-300 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if($documento->archivo_adjunto)
                                        <a href="{{ route('tramites.archivo.visor', ['tramite' => $documento->id, 'nombreArchivo' => basename($documento->archivo_adjunto)]) }}"
                                           class="text-blue-600 hover:text-blue-900"
                                           title="Ver documento">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                        <a href="{{ route('tramites.archivo.descargar', ['tramite' => $documento->id, 'nombreArchivo' => basename($documento->archivo_adjunto)]) }}"
                                           class="text-gray-600 hover:text-gray-900"
                                           download
                                           title="Descargar archivo">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                        </a>
                                        @endif
                                        <a href="{{ route('mesa-partes.voucher', $documento->id) }}" 
                                           class="text-green-600 hover:text-green-900"
                                           title="Ver voucher">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No hay documentos enviados.</p>
            @endif
        </div>
@endsection
