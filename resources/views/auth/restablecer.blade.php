{{--
    Vista de Restablecimiento de Contraseña
    Formulario para establecer nueva contraseña usando token
--}}
@extends('layouts.app')

@section('title', 'Restablecer Contraseña - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <img class="mx-auto h-12 w-auto" src="{{ asset('img/logo-ugel.jpg') }}" alt="UGEL">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Restablecer Contraseña
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingrese su nueva contraseña
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
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
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" name="email" type="email" required autofocus
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese su email" 
                           value="{{ old('email', request()->email) }}">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Mínimo 8 caracteres">
                    <p class="mt-1 text-xs text-gray-500">
                        La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números.
                    </p>
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required 
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Confirme su contraseña">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Restablecer Contraseña
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
// Validar que las contraseñas coincidan
document.getElementById('password_confirmation').addEventListener('input', function(e) {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    
    if (password !== confirmation && confirmation.length > 0) {
        this.setCustomValidity('Las contraseñas no coinciden');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endsection

