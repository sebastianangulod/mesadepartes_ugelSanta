{{--
    Vista de Enlace Inválido
    Se muestra cuando el enlace de recuperación de contraseña es inválido, expirado o ya fue utilizado
--}}
@extends('layouts.app')

@section('title', 'Enlace Inválido - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm px-8 py-4">
                <img src="{{ asset('img/logo-ugel.png') }}" alt="UGEL Santa" class="mx-auto h-24 w-auto">
            </div>
            <div class="flex justify-center">
                <div class="rounded-full bg-red-100 p-3">
                    <svg class="h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Enlace Inválido
                </h1>
                <p class="mt-2 text-sm font-semibold text-primary-800 uppercase tracking-wide">
                    Unidad de Gestión Educativa Local · Santa
                </p>
                <p class="mt-2 text-sm text-gray-600 px-4">
                    {{ $mensaje ?? 'El enlace de recuperación no es válido o ha expirado.' }}
                </p>
            </div>
        </div>
        
        <div class="mt-8 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Información Importante
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Solo un enlace de recuperación está activo por usuario a la vez.</li>
                            <li>Si solicitó un nuevo enlace, el anterior quedó invalidado.</li>
                            <li>Los enlaces expiran después de un tiempo determinado.</li>
                            <li>Si necesita recuperar su contraseña, solicite un nuevo enlace.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 space-y-3">
            <a href="{{ route('password.request') }}" 
               class="w-full flex justify-center items-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 shadow-sm transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
                Solicitar Nuevo Enlace
            </a>
            
            <a href="{{ route('login') }}" 
               class="w-full flex justify-center items-center py-2.5 px-4 border border-gray-300 text-sm font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 transition-colors">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Inicio de Sesión
            </a>
        </div>

        <p class="text-center text-xs text-gray-500">
            Mesa de Partes · mesadepartes@ugelsanta.gob.pe · Lun a Vie 8:00 a.m. – 4:30 p.m.
        </p>
    </div>
</div>
@endsection

