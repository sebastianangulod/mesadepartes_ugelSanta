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
        <div class="text-center">
            <img src="{{ asset('img/logo-ugel.png') }}" alt="UGEL" class="mx-auto">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sistema de Mesa de Partes
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingrese con su DNI y contraseña
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            
            @if($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <span class="font-semibold">Se encontraron los siguientes errores:</span>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $msg)
                            <li>{{ $msg }}</li>
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
                        <option value="3" {{ old('tipo_persona') == '3' ? 'selected' : '' }}>Entidad Pública</option>
                    </select>
                </div>
                
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input id="dni" name="dni" type="text" required maxlength="8" pattern="[0-9]{8}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese su DNI (8 dígitos)" 
                           value="{{ old('dni') }}">
                </div>

                {{-- Campos adicionales según tipo de persona --}}
                <div id="campo_ruc" class="{{ old('tipo_persona') == '2' ? '' : 'hidden' }}">
                    <label for="ruc" class="block text-sm font-medium text-gray-700 mb-1">RUC (solo Persona Jurídica)</label>
                    <input id="ruc" name="ruc" type="text"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese su RUC"
                           value="{{ old('ruc') }}">
                </div>

                <div id="campo_nombre_entidad" class="{{ old('tipo_persona') == '3' ? '' : 'hidden' }}">
                    <label for="nombre_entidad" class="block text-sm font-medium text-gray-700 mb-1">Nombre de Entidad (solo Entidad Pública)</label>
                    <input id="nombre_entidad" name="nombre_entidad" type="text"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                           placeholder="Ingrese el nombre de la entidad"
                           value="{{ old('nombre_entidad') }}">
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
            
            @php
                $recaptchaEnabled = config('services.recaptcha.enabled', false);
                $recaptchaSiteKey = config('services.recaptcha.site_key', '');
            @endphp
            
            @if($recaptchaEnabled && !empty($recaptchaSiteKey))
            <div class="flex justify-center mt-4">
                <div class="g-recaptcha" data-sitekey="{{ $recaptchaSiteKey }}" data-callback="onRecaptchaSuccess" data-expired-callback="onRecaptchaExpired"></div>
            </div>
            <div id="recaptcha-error" class="hidden mt-2 text-center text-sm text-red-600">
                Por favor, complete el reCAPTCHA para continuar.
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

@php
    $recaptchaEnabled = config('services.recaptcha.enabled', false);
    $recaptchaSiteKey = config('services.recaptcha.site_key', '');
@endphp

@if($recaptchaEnabled && !empty($recaptchaSiteKey))
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
// Callback cuando reCAPTCHA se completa exitosamente
function onRecaptchaSuccess(token) {
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
}

// Callback cuando reCAPTCHA expira
function onRecaptchaExpired() {
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.remove('hidden');
    }
}

// Validar reCAPTCHA antes de enviar el formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('login') }}"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Esperar a que grecaptcha esté disponible
            const checkRecaptcha = setInterval(function() {
                if (typeof grecaptcha !== 'undefined') {
                    clearInterval(checkRecaptcha);
                    const recaptchaResponse = grecaptcha.getResponse();
                    if (!recaptchaResponse || recaptchaResponse.length === 0) {
                        e.preventDefault();
                        const errorDiv = document.getElementById('recaptcha-error');
                        if (errorDiv) {
                            errorDiv.classList.remove('hidden');
                        }
                        return false;
                    }
                }
            }, 100);
            
            // Timeout después de 5 segundos
            setTimeout(function() {
                clearInterval(checkRecaptcha);
            }, 5000);
        });
    }
});
</script>
@endif

<script>
// Validar formato DNI en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const dniInput = document.getElementById('dni');
    if (dniInput) {
        dniInput.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});
    }

// Mostrar/ocultar campos según tipo de persona
const tipoPersonaSelect = document.getElementById('tipo_persona');
const campoRuc = document.getElementById('campo_ruc');
const campoNombreEntidad = document.getElementById('campo_nombre_entidad');

    if (tipoPersonaSelect && campoRuc && campoNombreEntidad) {
function actualizarCamposTipoPersona() {
    const tipo = tipoPersonaSelect.value;
    campoRuc.classList.toggle('hidden', tipo !== '2');
    campoNombreEntidad.classList.toggle('hidden', tipo !== '3');
}

tipoPersonaSelect.addEventListener('change', actualizarCamposTipoPersona);
// Ejecutar al cargar para reflejar old()
actualizarCamposTipoPersona();
    }
});
</script>
@endsection