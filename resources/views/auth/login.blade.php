{{--
    Vista de Login Principal
    Formulario de autenticación para usuarios externos (DNI)
    Incluye enlace a registro y recuperación de contraseña
--}}
@extends('layouts.app')

@section('title', 'Iniciar Sesión - Mesa de Partes')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm px-8 py-4">
                <img src="{{ asset('img/logo-ugel.png') }}" alt="UGEL Santa" class="mx-auto h-24 w-auto">
            </div>
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Mesa de Partes Digital
                </h1>
                <p class="mt-2 text-sm font-semibold text-primary-800 uppercase tracking-wide">
                    Unidad de Gestión Educativa Local · Santa
                </p>
                <p class="mt-2 text-sm text-gray-600">
                    Ingrese con su DNI y contraseña
                </p>
            </div>
        </div>

        <form class="space-y-5" action="{{ route('login') }}" method="POST">
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

            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-primary-800 via-primary-500 to-accent-600"></div>
                <div class="px-6 py-5 space-y-4">
                <div>
                    <label for="tipo_persona" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Persona</label>
                    <select id="tipo_persona" name="tipo_persona" required autocomplete="off"
                            class="block w-full px-3 py-2 border border-gray-300 text-gray-900 rounded-md focus:outline-none focus:ring-primary-600 focus:border-primary-600 text-base sm:text-sm">
                        <option value="">Seleccione...</option>
                        <option value="1" {{ old('tipo_persona') == '1' ? 'selected' : '' }}>Persona Natural</option>
                        <option value="2" {{ old('tipo_persona') == '2' ? 'selected' : '' }}>Persona Jurídica</option>
                        <option value="3" {{ old('tipo_persona') == '3' ? 'selected' : '' }}>Entidad Pública</option>
                    </select>
                </div>

                <div id="campo_dni">
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input id="dni" name="dni" type="text" required maxlength="8" pattern="[0-9]{8}" autocomplete="username" inputmode="numeric"
                           class="block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-600 focus:border-primary-600 text-base sm:text-sm"
                           placeholder="Ingrese su DNI (8 dígitos)"
                           value="{{ old('dni') }}">
                </div>

                {{-- Campos adicionales según tipo de persona --}}
                <div id="campo_ruc" class="{{ old('tipo_persona') == '2' ? '' : 'hidden' }}">
                    <label for="ruc" class="block text-sm font-medium text-gray-700 mb-1">RUC (solo Persona Jurídica)</label>
                    <input id="ruc" name="ruc" type="text" autocomplete="off" inputmode="numeric"
                           class="block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-600 focus:border-primary-600 text-base sm:text-sm"
                           placeholder="Ingrese su RUC"
                           value="{{ old('ruc') }}">
                </div>

                <div id="campo_nombre_entidad" class="{{ old('tipo_persona') == '3' ? '' : 'hidden' }}">
                    <label for="nombre_entidad" class="block text-sm font-medium text-gray-700 mb-1">Nombre de Entidad (solo Entidad Pública)</label>
                    <input id="nombre_entidad" name="nombre_entidad" type="text" autocomplete="off" maxlength="50"
                           class="block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-600 focus:border-primary-600 text-base sm:text-sm"
                           placeholder="Ingrese el nombre de la entidad (máx. 50 caracteres)"
                           value="{{ old('nombre_entidad') }}">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="block w-full pl-3 pr-11 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-primary-600 focus:border-primary-600 text-base sm:text-sm"
                               placeholder="Ingrese su contraseña">
                        <button type="button" id="togglePassword" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña"
                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 rounded-r-md">
                            <svg id="iconEyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <svg id="iconEyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.948 9.948 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 text-primary-700 focus:ring-primary-600 border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">
                        Recordarme
                    </label>
                </div>
            </div>
        </div>

            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-semibold rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 shadow-sm">
                    Iniciar Sesión
                </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-y-2 text-sm font-medium text-primary-700">
                <a href="{{ route('register') }}" class="inline-flex items-center hover:text-primary-500 hover:underline">
                    ¿No tienes cuenta? Regístrate
                </a>
                <a href="{{ route('password.request') }}" class="inline-flex items-center hover:text-primary-500 hover:underline">
                    ¿Olvidaste tu contraseña?
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

            <div class="text-center border-t border-gray-200 pt-5">
                <a href="{{ route('login.interno') }}" class="inline-flex items-center justify-center px-5 py-2 rounded-md border border-primary-200 bg-white text-sm font-semibold text-primary-700 hover:bg-primary-50 shadow-sm tracking-wide">
                    Soy personal interno
                </a>
            </div>

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

    // Asegurar que el token quede en el formulario
    const form = document.querySelector('form[action="{{ route('login') }}"]');
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
    const form = document.querySelector('form[action="{{ route('login') }}"]');
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
// Mostrar/ocultar contraseña
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const iconOpen = document.getElementById('iconEyeOpen');
    const iconClosed = document.getElementById('iconEyeClosed');
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const visible = passwordInput.type === 'text';
            passwordInput.type = visible ? 'password' : 'text';
            toggleBtn.setAttribute('aria-pressed', String(!visible));
            toggleBtn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            toggleBtn.setAttribute('title', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            if (iconOpen) iconOpen.classList.toggle('hidden', !visible);
            if (iconClosed) iconClosed.classList.toggle('hidden', visible);
        });
    }
});
</script>

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
// Entidad Pública entra con el nombre de la entidad: se oculta el DNI.
const tipoPersonaSelect = document.getElementById('tipo_persona');
const campoDni = document.getElementById('campo_dni');
const dniLoginInput = document.getElementById('dni');
const campoRuc = document.getElementById('campo_ruc');
const campoNombreEntidad = document.getElementById('campo_nombre_entidad');
const nombreEntidadInput = document.getElementById('nombre_entidad');

    if (tipoPersonaSelect && campoRuc && campoNombreEntidad) {
function actualizarCamposTipoPersona() {
    const tipo = tipoPersonaSelect.value;
    const esEntidad = tipo === '3';
    if (campoDni) campoDni.classList.toggle('hidden', esEntidad);
    if (dniLoginInput) {
        if (esEntidad) dniLoginInput.removeAttribute('required');
        else dniLoginInput.setAttribute('required', 'required');
    }
    campoRuc.classList.toggle('hidden', tipo !== '2');
    campoNombreEntidad.classList.toggle('hidden', !esEntidad);
    if (nombreEntidadInput) {
        if (esEntidad) nombreEntidadInput.setAttribute('required', 'required');
        else nombreEntidadInput.removeAttribute('required');
    }
}

tipoPersonaSelect.addEventListener('change', actualizarCamposTipoPersona);
// Ejecutar al cargar para reflejar old()
actualizarCamposTipoPersona();
    }
});
</script>
@endsection
