{{--
    Vista para Actualizar Datos Personales
    Permite a los usuarios actualizar su información personal
--}}
@extends($layout ?? (Auth::user()->esAdministrador() ? 'layouts.admin' : 'layouts.mesa-partes'))

@section('title', 'Actualizar Datos - Mesa de Partes')

@section('header', 'Actualizar Datos Personales')

@section('content')
<div class="max-w-4xl mx-auto">
    <p class="text-gray-600 mb-6">Modifique su información personal. Los campos marcados con * son obligatorios.</p>

    <!-- Mensaje de éxito -->
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

    <!-- Formulario -->
    <form action="{{ route('perfil.actualizar-datos.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- DNI (solo lectura) -->
                <div class="md:col-span-2">
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input type="text" id="dni" name="dni" value="{{ $usuario->dni }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed" 
                           readonly disabled>
                    <p class="mt-1 text-xs text-gray-500">El DNI no puede ser modificado</p>
                </div>

                <!-- Nombres -->
                <div>
                    <label for="nombres" class="block text-sm font-medium text-gray-700 mb-1">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nombres" name="nombres" 
                           value="{{ old('nombres', $usuario->nombres) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           maxlength="20" required>
                    @error('nombres')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido Paterno -->
                <div>
                    <label for="apellido_paterno" class="block text-sm font-medium text-gray-700 mb-1">
                        Apellido Paterno <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="apellido_paterno" name="apellido_paterno" 
                           value="{{ old('apellido_paterno', $usuario->apellido_paterno) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           maxlength="20" required>
                    @error('apellido_paterno')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Apellido Materno -->
                <div>
                    <label for="apellido_materno" class="block text-sm font-medium text-gray-700 mb-1">
                        Apellido Materno <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="apellido_materno" name="apellido_materno" 
                           value="{{ old('apellido_materno', $usuario->apellido_materno) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           maxlength="20" required>
                    @error('apellido_materno')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" 
                           value="{{ old('email', $usuario->email) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           maxlength="50" required>
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-700 mb-1">
                        Teléfono <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" id="telefono" name="telefono" 
                           value="{{ old('telefono', $usuario->telefono) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="9 dígitos" maxlength="9" pattern="[0-9]{9}" required>
                    @error('telefono')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dirección -->
                <div class="md:col-span-2">
                    <label for="direccion" class="block text-sm font-medium text-gray-700 mb-1">
                        Dirección <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="direccion" name="direccion" 
                           value="{{ old('direccion', $usuario->direccion) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                           maxlength="35" required>
                    @error('direccion')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ubicación: Departamento -->
                <div>
                    <label for="departamento" class="block text-sm font-medium text-gray-700 mb-1">
                        Departamento <span class="text-red-500">*</span>
                    </label>
                    <select id="departamento" name="departamento" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="cargarProvincias(this.value)" required>
                        <option value="">Seleccione un departamento</option>
                        @foreach($departamentos as $departamento)
                            <option value="{{ $departamento->id }}" 
                                    {{ old('departamento', $usuario->distrito && $usuario->distrito->provincia ? $usuario->distrito->provincia->departamento_id : '') == $departamento->id ? 'selected' : '' }}>
                                {{ $departamento->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('departamento')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Provincia -->
                <div>
                    <label for="provincia" class="block text-sm font-medium text-gray-700 mb-1">
                        Provincia <span class="text-red-500">*</span>
                    </label>
                    <select id="provincia" name="provincia" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            onchange="cargarDistritos(this.value)" 
                            @if(!($usuario->distrito && $usuario->distrito->provincia)) disabled @endif
                            data-provincia-id="{{ $usuario->distrito && $usuario->distrito->provincia ? $usuario->distrito->provincia_id : '' }}"
                            required>
                        <option value="">Seleccione una provincia</option>
                        @if($usuario->distrito && $usuario->distrito->provincia)
                            @foreach($provincias as $provincia)
                                <option value="{{ $provincia->id }}" 
                                        {{ old('provincia', $usuario->distrito->provincia_id) == $provincia->id ? 'selected' : '' }}>
                                    {{ $provincia->nombre }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('provincia')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Distrito -->
                <div>
                    <label for="distrito_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Distrito <span class="text-red-500">*</span>
                    </label>
                    <select id="distrito_id" name="distrito_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            data-distrito-id="{{ $usuario->distrito ? $usuario->distrito_id : '' }}"
                            {{ $usuario->distrito ? '' : 'disabled' }} required>
                        <option value="">Seleccione un distrito</option>
                        @if($usuario->distrito)
                            @foreach($distritos as $distrito)
                                <option value="{{ $distrito->id }}" 
                                        {{ old('distrito_id', $usuario->distrito_id) == $distrito->id ? 'selected' : '' }}>
                                    {{ $distrito->nombre }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('distrito_id')
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
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors font-medium">
                    Actualizar Datos
                </button>
            </div>
        </form>
</div>

<script>
// Cargar provincias
function cargarProvincias(departamentoId) {
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito_id');
    
    if (!departamentoId) {
        provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
        provinciaSelect.setAttribute('disabled', 'disabled');
        distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';
        distritoSelect.setAttribute('disabled', 'disabled');
        return;
    }
    
    fetch(`/api/provincias/${departamentoId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data && Array.isArray(result.data)) {
                const provinciaActual = provinciaSelect.value || provinciaSelect.dataset.provinciaId || null;
                provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
                result.data.forEach(provincia => {
                    const option = document.createElement('option');
                    option.value = provincia.id;
                    option.textContent = provincia.nombre;
                    if (provincia.id == provinciaActual) {
                        option.selected = true;
                    }
                    provinciaSelect.appendChild(option);
                });
                // Asegurar que el select esté habilitado
                provinciaSelect.removeAttribute('disabled');
                provinciaSelect.disabled = false;
                
                // Si hay una provincia seleccionada, cargar distritos
                if (provinciaActual) {
                    cargarDistritos(provinciaActual);
                } else {
                    distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';
                    distritoSelect.setAttribute('disabled', 'disabled');
                }
            } else {
                console.error('Error: La respuesta de la API no tiene el formato esperado', result);
            }
        })
        .catch(error => {
            console.error('Error al cargar provincias:', error);
        });
}

// Cargar distritos
function cargarDistritos(provinciaId) {
    const distritoSelect = document.getElementById('distrito_id');
    
    if (!provinciaId) {
        distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';
        distritoSelect.setAttribute('disabled', 'disabled');
        return;
    }
    
    fetch(`/api/distritos/${provinciaId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data && Array.isArray(result.data)) {
                const distritoActual = distritoSelect.value || distritoSelect.dataset.distritoId || null;
                distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';
                result.data.forEach(distrito => {
                    const option = document.createElement('option');
                    option.value = distrito.id;
                    option.textContent = distrito.nombre;
                    if (distrito.id == distritoActual) {
                        option.selected = true;
                    }
                    distritoSelect.appendChild(option);
                });
                // Asegurar que el select esté habilitado
                distritoSelect.removeAttribute('disabled');
                distritoSelect.disabled = false;
            } else {
                console.error('Error: La respuesta de la API no tiene el formato esperado', result);
            }
        })
        .catch(error => {
            console.error('Error al cargar distritos:', error);
        });
}

// Validar teléfono
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    if (this.value.length > 9) {
        this.value = this.value.slice(0, 9);
    }
});

// Cargar provincias y distritos al cargar la página si hay un departamento seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const departamentoSelect = document.getElementById('departamento');
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito_id');
    
    // Si hay un departamento seleccionado, cargar provincias
    if (departamentoSelect && departamentoSelect.value) {
        // Obtener el valor de provincia desde el data attribute o del option seleccionado
        const provinciaActual = provinciaSelect.dataset.provinciaId || (provinciaSelect.querySelector('option[selected]') ? provinciaSelect.querySelector('option[selected]').value : null) || null;
        const distritoActual = distritoSelect.dataset.distritoId || (distritoSelect.querySelector('option[selected]') ? distritoSelect.querySelector('option[selected]').value : null) || null;
        
        // Cargar provincias
        fetch(`/api/provincias/${departamentoSelect.value}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data && Array.isArray(result.data)) {
                    provinciaSelect.innerHTML = '<option value="">Seleccione una provincia</option>';
                    result.data.forEach(provincia => {
                        const option = document.createElement('option');
                        option.value = provincia.id;
                        option.textContent = provincia.nombre;
                        if (provincia.id == provinciaActual) {
                            option.selected = true;
                        }
                        provinciaSelect.appendChild(option);
                    });
                    // Asegurar que el select esté habilitado
                    provinciaSelect.removeAttribute('disabled');
                    provinciaSelect.disabled = false;
                    
                    // Si hay una provincia seleccionada, cargar distritos
                    if (provinciaActual) {
                        fetch(`/api/distritos/${provinciaActual}`)
                            .then(response => response.json())
                            .then(result => {
                                if (result.success && result.data && Array.isArray(result.data)) {
                                    distritoSelect.innerHTML = '<option value="">Seleccione un distrito</option>';
                                    result.data.forEach(distrito => {
                                        const option = document.createElement('option');
                                        option.value = distrito.id;
                                        option.textContent = distrito.nombre;
                                        if (distrito.id == distritoActual) {
                                            option.selected = true;
                                        }
                                        distritoSelect.appendChild(option);
                                    });
                                    // Asegurar que el select esté habilitado
                                    distritoSelect.removeAttribute('disabled');
                                    distritoSelect.disabled = false;
                                }
                            })
                            .catch(error => {
                                console.error('Error al cargar distritos:', error);
                            });
                    }
                } else {
                    console.error('Error: La respuesta de la API no tiene el formato esperado', result);
                }
            })
            .catch(error => {
                console.error('Error al cargar provincias:', error);
            });
    }
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
    const form = document.querySelector('form[action="{{ route('perfil.actualizar-datos.store') }}"]');
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

