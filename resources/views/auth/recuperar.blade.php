{{--
    Vista de Recuperación de Contraseña
    Formulario para solicitar recuperación de contraseña
--}}
@extends('layouts.app')

@section('title', 'Recuperar Contraseña - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm px-8 py-4">
                <img src="{{ asset('img/logo-ugel.png') }}" alt="UGEL Santa" class="mx-auto h-24 w-auto">
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Recuperar Contraseña
                </h1>
                <p class="mt-2 text-sm font-semibold text-primary-800 uppercase tracking-wide">
                    Unidad de Gestión Educativa Local · Santa
                </p>
                <p class="mt-2 text-sm text-gray-600">
                    Ingrese su DNI y le enviaremos un enlace para restablecer su contraseña
                </p>
            </div>
        </div>

        <form class="space-y-5" action="{{ url('/recuperar') }}" method="POST">
            @csrf

            @if($errors->any())
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800" role="alert">
                    <span class="font-semibold">Se encontraron los siguientes errores:</span>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $msg)
                            <li>{{ $msg }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-primary-800 via-primary-500 to-accent-600"></div>
                <div class="px-6 py-5 space-y-4">
                <div>
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input id="dni" name="dni" type="text" required autofocus maxlength="8" pattern="[0-9]{8}" autocomplete="username" inputmode="numeric"
                           class="block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-600 focus:border-primary-600 text-base sm:text-sm"
                           placeholder="Ingrese su DNI (8 dígitos)"
                           value="{{ old('dni') }}">
                </div>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 shadow-sm">
                    Enviar Enlace de Recuperación
                </button>
            </div>

            <div class="text-center border-t border-gray-200 pt-5">
                <a href="{{ route('login') }}" class="font-medium text-primary-700 hover:text-primary-500">
                    Volver al inicio de sesión
                </a>
            </div>

            @php
                $recaptchaEnabled = config('services.recaptcha.enabled', false);
                $recaptchaSiteKey = config('services.recaptcha.site_key', '');
            @endphp

            @if($recaptchaEnabled && !empty($recaptchaSiteKey))
            <div class="flex justify-center">
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
                <strong>Error de configuración de reCAPTCHA</strong><br>
                <small>Verifique que el dominio esté registrado en Google reCAPTCHA y que las claves correspondan al mismo sitio.</small>
            </div>
            @endif
        </form>

        <p class="text-center text-xs text-gray-500">
            Mesa de Partes · mesadepartes@ugelsanta.gob.pe · Lun a Vie 8:00 a.m. – 4:30 p.m.
        </p>
    </div>
</div>

@php
    $recaptchaEnabled = config('services.recaptcha.enabled', false);
    $recaptchaSiteKey = config('services.recaptcha.site_key', '');
@endphp

@if($recaptchaEnabled && !empty($recaptchaSiteKey))
<script src="https://www.google.com/recaptcha/api.js?hl=es" async defer></script>
<script>
// Variable global para almacenar el token
window.recaptchaToken = null;

// Callback cuando reCAPTCHA se completa exitosamente
function onRecaptchaSuccess(token) {
    window.recaptchaToken = token;
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }

    const form = document.querySelector('form[action="{{ url('/recuperar') }}"]');
    if (form) {
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

// Validar reCAPTCHA antes de enviar el formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[action="{{ url('/recuperar') }}"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const existingTextarea = form.querySelector('textarea[name="g-recaptcha-response"]');
            let recaptchaResponse = null;

            if (existingTextarea && existingTextarea.value) {
                recaptchaResponse = existingTextarea.value;
            } else if (window.recaptchaToken) {
                recaptchaResponse = window.recaptchaToken;
            } else if (typeof grecaptcha !== 'undefined') {
                try {
                    recaptchaResponse = grecaptcha.getResponse() || grecaptcha.getResponse(0);
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
                return false;
            }

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
