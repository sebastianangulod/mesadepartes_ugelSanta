{{--
    Vista de Login Principal
    Formulario de autenticación para usuarios externos (DNI)
    Incluye enlace a registro y recuperación de contraseña
--}}
@extends('layouts.app')

@section('title', 'Iniciar Sesión - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ asset('img/logo-ugel.jpg') }}" alt="UGEL">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sistema de Mesa de Partes
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingrese con su DNI y contraseña
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('info'))
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative">
                    {{ session('info') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="tipo_persona" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Persona</label>
                    <select id="tipo_persona" name="tipo_persona" required 
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm">
                        <option value="">Seleccione...</option>
                        <option value="1" {{ old('tipo_persona') == '1' ? 'selected' : '' }}>Persona Natural</option>
                        <option value="2" {{ old('tipo_persona') == '2' ? 'selected' : '' }}>Persona Jurídica</option>
                    </select>
                </div>
                
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input id="dni" name="dni" type="text" required maxlength="8" pattern="[0-9]{8}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese su DNI (8 dígitos)" 
                           value="{{ old('dni') }}">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese su contraseña">
                </div>
                
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Recordarme
                    </label>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Iniciar Sesión
                </button>
            </div>

            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        ¿No tienes cuenta? Regístrate
                    </a>
                </div>
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
            </div>
            
            @if($recaptcha_enabled ?? false)
            <div class="flex justify-center">
                <div class="g-recaptcha" data-sitekey="{{ $recaptcha_site_key }}"></div>
            </div>
            @endif

            <div class="text-center border-t pt-4">
                <a href="{{ route('login.interno') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Soy personal interno
                </a>
            </div>
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