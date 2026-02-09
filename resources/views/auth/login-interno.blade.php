{{--
    Vista de Login Interno
    Formulario de autenticación para personal interno/administrativo
    Credenciales diferentes a usuarios externos
--}}
@extends('layouts.app')

@section('title', 'Login Personal Interno - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <img class="mx-auto h-16 w-auto" src="{{ asset('img/logo-ugel.png') }}" alt="UGEL" style="max-height: 80px;">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Acceso Personal Interno
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Ingrese sus credenciales administrativas
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('login.interno') }}" method="POST">
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

            <div class="text-center border-t pt-4">
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    Soy usuario externo
                </a>
            </div>
            
            @php
                $recaptchaEnabled = config('services.recaptcha.enabled', false);
                $recaptchaSiteKey = config('services.recaptcha.site_key', '');
            @endphp
            
            @if($recaptchaEnabled && !empty($recaptchaSiteKey))
            <div class="flex justify-center mt-4">
                <div id="recaptcha-widget" 
                     class="g-recaptcha" 
                     data-sitekey="{{ $recaptchaSiteKey }}" 
                     data-callback="onRecaptchaSuccess" 
                     data-expired-callback="onRecaptchaExpired"
                     data-size="normal"
                     data-theme="light"
                     data-type="image"></div>
            </div>
            <div id="recaptcha-error" class="hidden mt-2 text-center text-sm text-red-600">
                Por favor, complete el reCAPTCHA para continuar.
            </div>
            <div id="recaptcha-config-error" class="hidden mt-2 text-center text-sm text-red-600 bg-red-50 p-3 rounded border border-red-200">
                <strong>⚠️ Error de configuración de reCAPTCHA</strong><br>
                <small>Verifique que:<br>
                1. El dominio (localhost, 127.0.0.1) esté registrado en Google reCAPTCHA<br>
                2. Las claves Site Key y Secret Key sean del mismo sitio<br>
                3. El tipo de reCAPTCHA sea "v2 - No soy un robot" (no v3)</small>
            </div>
            @endif
        </form>
    </div>
</div>

@php
    $recaptchaEnabled = config('services.recaptcha.enabled', false);
    $recaptchaSiteKey = config('services.recaptcha.site_key', '');
@endphp

@if($recaptchaEnabled && !empty($recaptchaSiteKey))
@once
<script src="https://www.google.com/recaptcha/api.js?hl=es" async defer></script>
@endonce

<script>
// Variable global para almacenar el widget ID
let recaptchaWidgetId = null;

// Callback cuando reCAPTCHA se completa exitosamente
function onRecaptchaSuccess(token) {
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
    
    // Guardar el token en una variable global para verificación
    window.recaptchaToken = token;
    
    // Verificar si Google creó el textarea automáticamente
    const form = document.querySelector('form[action="{{ route('login.interno') }}"]');
    if (form) {
        // Esperar un momento para que Google cree el textarea
        setTimeout(function() {
            const textarea = form.querySelector('textarea[name="g-recaptcha-response"]');
            if (textarea && !textarea.value && token) {
                textarea.value = token;
            } else if (!textarea) {
                const hiddenInput = document.createElement('textarea');
                hiddenInput.name = 'g-recaptcha-response';
                hiddenInput.style.display = 'none';
                hiddenInput.value = token;
                form.appendChild(hiddenInput);
            }
        }, 100);
    }
}

// Callback cuando reCAPTCHA expira
function onRecaptchaExpired() {
    window.recaptchaToken = null;
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.remove('hidden');
    }
}

// Callback cuando hay un error en reCAPTCHA
function onRecaptchaError(error) {
    window.recaptchaToken = null;
    
    // Mostrar error de configuración
    const configErrorDiv = document.getElementById('recaptcha-config-error');
    if (configErrorDiv) {
        configErrorDiv.classList.remove('hidden');
    }
    
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.remove('hidden');
        errorDiv.textContent = 'Error de configuración de reCAPTCHA. Verifique las claves y el dominio en Google reCAPTCHA.';
    }
}

// Validar reCAPTCHA antes de enviar el formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ route('login.interno') }}"]');
    const widget = document.getElementById('recaptcha-widget');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Buscar específicamente el textarea de reCAPTCHA
            const existingTextarea = form.querySelector('textarea[name="g-recaptcha-response"]');
            let recaptchaResponse = null;
            
            if (existingTextarea && existingTextarea.value) {
                recaptchaResponse = existingTextarea.value;
            } else if (window.recaptchaToken) {
                recaptchaResponse = window.recaptchaToken;
            } else if (typeof grecaptcha !== 'undefined') {
                // Intentar obtener el token usando grecaptcha.getResponse()
                try {
                    recaptchaResponse = grecaptcha.getResponse();
                    if (!recaptchaResponse) {
                        recaptchaResponse = grecaptcha.getResponse(0);
                    }
                } catch (err) {
                    console.error('Error al obtener respuesta de grecaptcha:', err);
                }
            }
            
            if (!recaptchaResponse || recaptchaResponse.length === 0) {
                e.preventDefault();
                const errorDiv = document.getElementById('recaptcha-error');
                if (errorDiv) {
                    errorDiv.classList.remove('hidden');
                }
                alert('Por favor, complete el reCAPTCHA antes de continuar.');
                return false;
            }
            
            // Asegurar que el token esté en el formulario
            if (!existingTextarea) {
                const hiddenInput = document.createElement('textarea');
                hiddenInput.name = 'g-recaptcha-response';
                hiddenInput.style.display = 'none';
                hiddenInput.value = recaptchaResponse;
                form.appendChild(hiddenInput);
            } else if (!existingTextarea.value) {
                existingTextarea.value = recaptchaResponse;
            }
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
});
</script>
@endsection