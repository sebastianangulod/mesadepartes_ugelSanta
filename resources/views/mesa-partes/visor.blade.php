@extends('layouts.mesa-partes')

@section('title', 'Visor de documento')

@section('header', 'Visor de documento')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">{{ $nombreArchivo }}</h3>
            <p class="text-sm text-gray-500">Trámite #{{ $tramite->id }} — {{ $tramite->procedimiento->nombre ?? 'N/A' }}</p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="window.history.back()"
               class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                ← Regresar
            </button>
            <a href="{{ route('tramites.archivo.ver', ['tramite' => $tramite->id, 'nombreArchivo' => $nombreArchivo]) }}"
               target="_blank" rel="noopener"
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                Abrir en pestaña
            </a>
            <a href="{{ route('tramites.archivo.descargar', ['tramite' => $tramite->id, 'nombreArchivo' => $nombreArchivo]) }}"
               class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition-colors">
                Descargar
            </a>
        </div>
    </div>
    <iframe src="{{ route('tramites.archivo.ver', ['tramite' => $tramite->id, 'nombreArchivo' => $nombreArchivo]) }}"
            class="w-full border rounded-lg" style="height: 75vh;" title="Visor de documento"></iframe>
</div>
@endsection
