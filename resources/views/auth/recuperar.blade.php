{{--
    Vista de Recuperación de Contraseña
    Formulario para solicitar recuperación de contraseña
--}}
@extends('layouts.app')

@section('title', 'Recuperar Contraseña - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ asset('img/logo-ugel.jpg') }}" alt="UGEL">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Recuperar Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingrese su email y le enviaremos un enlace para restablecer su contraseña
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('password.request') }}" method="POST">
            @csrf
            
            <x-alerts :errors-bag="$errors" />

            <div class="rounded-md shadow-sm">
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input id="dni" name="dni" type="text" required autofocus maxlength="8" pattern="[0-9]{8}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese su DNI (8 dígitos)" 
                           value="{{ old('dni') }}">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Enviar Enlace de Recuperación
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Volver al inicio de sesión
                </a>
            </div>
            
            @if($recaptcha_enabled ?? false)
            <div class="flex justify-center">
                <div class="g-recaptcha" data-sitekey="{{ $recaptcha_site_key }}"></div>
            </div>
            @endif
        </form>
    </div>
</div>

@if($recaptcha_enabled ?? false)
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif

<script>
// Validar formato DNI en tiempo real
document.getElementById('dni').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});
</script>
@endsection

