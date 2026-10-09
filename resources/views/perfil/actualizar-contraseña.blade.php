{{--
    Vista para Actualizar Contraseña
    Permite a los usuarios cambiar su contraseña con validaciones completas
--}}
@extends($layout ?? (Auth::user()->esAdministrador() ? 'layouts.admin' : 'layouts.mesa-partes'))

@section('title', 'Actualizar Contraseña - Mesa de Partes')

@section('header', 'Actualizar Contraseña')

@push('styles')
<style>
    .requirement {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        margin: 0.25rem 0;
    }
    .requirement .icon {
        margin-right: 0.5rem;
        font-weight: bold;
    }
    .requirement.valid {
        color: #10b981;
    }
    .requirement.invalid {
        color: #ef4444;
    }
    .password-strength {
        height: 4px;
        border-radius: 2px;
        margin-top: 0.5rem;
        transition: all 0.3s;
    }
    .password-strength.weak {
        background-color: #ef4444;
        width: 33%;
    }
    .password-strength.medium {
        background-color: #f59e0b;
        width: 66%;
    }
    .password-strength.strong {
        background-color: #10b981;
        width: 100%;
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <p class="text-gray-600 mb-6">Cambie su contraseña de forma segura. Asegúrese de usar una contraseña fuerte.</p>

        <!-- Mensajes de éxito/error -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <div class="font-semibold mb-2">Se encontraron los siguientes errores:</div>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de cambio de contraseña -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <form id="formCambiarContraseña" action="{{ route('perfil.actualizar-contraseña.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <!-- Contraseña Actual -->
                    <div>
                        <label for="password_actual" class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña Actual <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_actual" name="password_actual" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Ingrese su contraseña actual" required autocomplete="current-password">
                            <button type="button" onclick="togglePasswordVisibility('password_actual', 'togglePasswordActual')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg id="togglePasswordActual" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password_actual')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nueva Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                            Nueva Contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Ingrese su nueva contraseña" required autocomplete="new-password">
                            <button type="button" onclick="togglePasswordVisibility('password', 'togglePassword')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg id="togglePassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <!-- Indicador de fortaleza de contraseña -->
                        <div id="passwordStrength" class="password-strength"></div>
                        <!-- Requisitos de contraseña -->
                        <div id="passwordRequirements" class="mt-2"></div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirmar Nueva Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                            Confirmar Nueva Contraseña <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Confirme su nueva contraseña" required autocomplete="new-password">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'togglePasswordConfirmation')" 
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                                <svg id="togglePasswordConfirmation" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="passwordMatch" class="mt-1 text-xs"></div>
                        @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @php
                    $recaptchaEnabled = config('services.recaptcha.enabled', false);
                    $recaptchaSiteKey = config('services.recaptcha.site_key', '');
                @endphp
                
                @if($recaptchaEnabled && !empty($recaptchaSiteKey))
                <div class="flex justify-center mt-6">
                    <div id="recaptcha-widget" 
                         class="g-recaptcha" 
                         data-sitekey="{{ $recaptchaSiteKey }}" 
                         data-callback="onRecaptchaSuccess" 
                         data-expired-callback="onRecaptchaExpired"
                         data-size="normal"
                         data-theme="light"></div>
                </div>
                <div id="recaptcha-error" class="hidden mt-2 text-center text-sm text-red-600">
                    Por favor, complete el reCAPTCHA para continuar.
                </div>
                @endif

                <!-- Botones -->
                <div class="mt-8 flex justify-end gap-4">
                    <a href="{{ Auth::user()->esAdmin() ? route('admin.index') : route('mesa-partes.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" id="submitBtn"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                        Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>

        <!-- Sección de recuperación de contraseña -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2">¿No recuerdas tu contraseña actual?</h3>
                    <p class="text-yellow-700 mb-4">Si no recuerdas tu contraseña actual, puedes solicitar un enlace de recuperación por correo electrónico.</p>
                    <button type="button" id="toggleRecuperacion" 
                            class="text-yellow-800 hover:text-yellow-900 font-medium underline focus:outline-none">
                        Solicitar enlace de recuperación
                    </button>
                </div>
            </div>

            <!-- Formulario de recuperación (oculto por defecto) -->
            <div id="formRecuperacion" class="mt-4 hidden">
                <div class="bg-white rounded-lg p-4 border border-yellow-300">
                    <form action="{{ url('/recuperar') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="dni_recuperacion" class="block text-sm font-medium text-gray-700 mb-1">
                                DNI <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="dni_recuperacion" name="dni" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   placeholder="Ingrese su DNI (8 dígitos)" 
                                   maxlength="8" pattern="[0-9]{8}" required>
                            <p class="mt-1 text-xs text-gray-500">
                                Se enviará un enlace de recuperación a su correo electrónico registrado.
                            </p>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" 
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors">
                                Enviar Enlace de Recuperación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
</div>

<script>
// Toggle visibilidad de contraseña
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
        `;
    }
}

// Toggle formulario de recuperación
document.getElementById('toggleRecuperacion').addEventListener('click', function() {
    const form = document.getElementById('formRecuperacion');
    form.classList.toggle('hidden');
});

// Validación de contraseña en tiempo real
function initPasswordValidation() {
    const passwordInput = document.getElementById('password');
    const passwordRequirements = document.getElementById('passwordRequirements');
    const passwordStrength = document.getElementById('passwordStrength');
    const submitBtn = document.getElementById('submitBtn');
    
    const requirements = {
        length: { text: 'Mínimo 8 caracteres', element: document.createElement('div') },
        uppercase: { text: 'Al menos una mayúscula', element: document.createElement('div') },
        number: { text: 'Al menos un número', element: document.createElement('div') },
        special: { text: 'Al menos un carácter especial (@$!%*?&_)', element: document.createElement('div') }
    };

    Object.values(requirements).forEach(req => {
        req.element.className = 'requirement invalid';
        req.element.innerHTML = `<span class="icon">✕</span> ${req.text}`;
        passwordRequirements.appendChild(req.element);
    });

    function validatePassword(password) {
        const validations = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[@$!%*?&_]/.test(password)
        };

        // Actualizar requisitos
        Object.entries(validations).forEach(([key, isValid]) => {
            const req = requirements[key];
            req.element.className = `requirement ${isValid ? 'valid' : 'invalid'}`;
            req.element.innerHTML = `<span class="icon">${isValid ? '✓' : '✕'}</span> ${req.text}`;
        });

        // Calcular fortaleza
        const validCount = Object.values(validations).filter(v => v).length;
        passwordStrength.className = 'password-strength';
        if (validCount <= 2) {
            passwordStrength.classList.add('weak');
        } else if (validCount === 3) {
            passwordStrength.classList.add('medium');
        } else {
            passwordStrength.classList.add('strong');
        }

        // Habilitar/deshabilitar botón
        const allValid = Object.values(validations).every(v => v);
        submitBtn.disabled = !allValid;

        return allValid;
    }

    passwordInput.addEventListener('input', function() {
        validatePassword(this.value);
    });
}

// Validar coincidencia de contraseñas
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;
    const matchDiv = document.getElementById('passwordMatch');
    
    if (confirmation.length === 0) {
        matchDiv.textContent = '';
        return;
    }
    
    if (password === confirmation) {
        matchDiv.textContent = '✓ Las contraseñas coinciden';
        matchDiv.className = 'mt-1 text-xs text-green-600';
        this.setCustomValidity('');
    } else {
        matchDiv.textContent = '✕ Las contraseñas no coinciden';
        matchDiv.className = 'mt-1 text-xs text-red-600';
        this.setCustomValidity('Las contraseñas no coinciden');
    }
});

// Validar DNI en formulario de recuperación
document.getElementById('dni_recuperacion')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 8) {
        this.value = this.value.slice(0, 8);
    }
});

// Inicializar validación de contraseña
document.addEventListener('DOMContentLoaded', function() {
    initPasswordValidation();
});
</script>

@php
    $recaptchaEnabled = config('services.recaptcha.enabled', false);
    $recaptchaSiteKey = config('services.recaptcha.site_key', '');
@endphp

@if($recaptchaEnabled && !empty($recaptchaSiteKey))
@once
<script src="https://www.google.com/recaptcha/api.js?hl=es" async defer></script>
@endonce

<script>
let recaptchaToken = null;

function onRecaptchaSuccess(token) {
    recaptchaToken = token;
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
    }
}

function onRecaptchaExpired() {
    recaptchaToken = null;
    const errorDiv = document.getElementById('recaptcha-error');
    if (errorDiv) {
        errorDiv.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCambiarContraseña');
    if (form) {
        form.addEventListener('submit', function(e) {
            const existingTextarea = form.querySelector('textarea[name="g-recaptcha-response"]');
            let recaptchaResponse = null;
            
            if (existingTextarea && existingTextarea.value) {
                recaptchaResponse = existingTextarea.value;
            } else if (recaptchaToken) {
                recaptchaResponse = recaptchaToken;
            } else if (typeof grecaptcha !== 'undefined') {
                try {
                    recaptchaResponse = grecaptcha.getResponse();
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
@endsection

