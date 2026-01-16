{{--
    Vista de Registro de Usuario
    Formulario para nuevos usuarios externos
    Incluye validación de DNI/RUC con APIs, validación en tiempo real de contraseña y email
--}}
@extends('layouts.app')

@section('title', 'Registro de Usuario - Mesa de Partes')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-6 py-4 bg-indigo-600">
                <h2 class="text-xl font-semibold text-white">Registro de Usuario</h2>
                <p class="text-indigo-200">Complete todos los campos para crear su cuenta</p>
            </div>

            <form id="registroForm" action="{{ route('register') }}" method="POST" class="p-6 space-y-6" enctype="multipart/form-data">
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

                <!-- Campos ocultos para controlar validaciones -->
                <input type="hidden" id="dniValidado" value="false">
                <input type="hidden" id="rucValidado" value="false">
                <input type="hidden" id="emailValidado" value="false">

                {{-- Tipo de Persona --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Persona</label>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_persona" value="1" 
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                   {{ old('tipo_persona', '1') == '1' ? 'checked' : '' }}>
                            <span class="ml-2">Persona Natural</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_persona" value="2" 
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                   {{ old('tipo_persona') == '2' ? 'checked' : '' }}>
                            <span class="ml-2">Persona Jurídica</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_persona" value="3" 
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                   {{ old('tipo_persona') == '3' ? 'checked' : '' }}>
                            <span class="ml-2">Entidad Pública</span>
                        </label>
                    </div>
                </div>

                {{-- DNI con botón de validación --}}
                <div id="dni_field">
                    <label for="dni" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <div class="flex gap-2">
                        <input type="text" name="dni" id="dni" required maxlength="8" pattern="[0-9]{8}"
                               class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('dni') }}" placeholder="Ingrese su DNI (8 dígitos)">
                        <button type="button" id="validarDni" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-200">
                            Validar DNI
                        </button>
                    </div>
                </div>

                {{-- Información Personal (se autocompleta después de validar DNI) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                        <input type="text" name="nombres" id="nombres" required maxlength="20"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('nombres') }}" readonly>
                    </div>

                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" required maxlength="20"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-100"
                               value="{{ old('apellido_paterno') }}" readonly>
                    </div>

                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" required maxlength="20"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-100"
                               value="{{ old('apellido_materno') }}" readonly>
                    </div>
                </div>

                {{-- Datos de Persona Jurídica --}}
                <div id="datos-juridica" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="ruc" class="block text-sm font-medium text-gray-700 mb-1">RUC</label>
                            <div class="flex gap-2">
                                <input type="text" name="ruc" id="ruc" maxlength="11" pattern="[0-9]{11}"
                                       class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('ruc') }}" placeholder="Ingrese su RUC (11 dígitos)">
                                <button type="button" id="validarRuc" 
                                        class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-200">
                                    Validar RUC
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="razon_social" class="block text-sm font-medium text-gray-700">Razón Social</label>
                            <input type="text" name="razon_social" id="razon_social" maxlength="45"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-gray-100"
                                   value="{{ old('razon_social') }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- Datos de Entidad Pública --}}
                <div id="datos-entidad" class="hidden">
                    <div>
                        <label for="nombre_entidad" class="block text-sm font-medium text-gray-700">Nombre de Entidad</label>
                        <input type="text" name="nombre_entidad" id="nombre_entidad" maxlength="45"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('nombre_entidad') }}"
                               onInput="this.value = this.value.toUpperCase()">
                    </div>
                </div>

                {{-- Información de Contacto --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required maxlength="45"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('email') }}">
                        <div id="email-feedback" class="mt-1 text-sm"></div>
                        <div id="emailRequirements" class="mt-2 space-y-1"></div>
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" required maxlength="9"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('telefono') }}" placeholder="9 dígitos">
                    </div>
                </div>

                {{-- Ubicación Geográfica --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select name="departamento_id" id="departamento" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccione Departamento</option>
                        </select>
                    </div>

                    <div>
                        <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia</label>
                        <select name="provincia_id" id="provincia" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                disabled>
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>

                    <div>
                        <label for="distrito" class="block text-sm font-medium text-gray-700">Distrito</label>
                        <select name="distrito_id" id="distrito" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                disabled>
                            <option value="">Seleccione Distrito</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion" required maxlength="35"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('direccion') }}">
                </div>

                {{-- Contraseña con validación en tiempo real --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password" required maxlength="25"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <div id="passwordRequirements" class="mt-2 space-y-1"></div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                {{-- Términos y Condiciones --}}
                <div>
                    <label class="flex items-start">
                        <input type="checkbox" name="terminos" id="terminos" required
                               class="mt-1 focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        <span class="ml-2 block text-sm text-gray-900">
                            Acepto los Términos y Condiciones:
                        </span>
                    </label>
                    <textarea readonly 
                              class="mt-2 w-full px-3 py-2 text-gray-700 border border-gray-300 rounded-lg focus:outline-none focus:border-indigo-500 bg-gray-50"
                              rows="5">TÉRMINOS Y CONDICIONES DE USO DEL SISTEMA DE MESA DE PARTES DIGITAL DE LA UGEL SANTA

El Sistema de Mesa de Partes Digital es un canal que la Unidad de Gestión Educativa Local (UGEL) Santa pone a disposición de la comunidad educativa y usuarios interesados, para presentar documentos e iniciar trámites desde cualquier lugar a través de internet.

Los trámites y procedimientos iniciados mediante el Sistema de Mesa de Partes Digital de la UGEL Santa tienen la misma validez y eficacia jurídica que los realizados a través de medios convencionales.</textarea>
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

                <div class="flex justify-between pt-4">
                    <button type="button" id="cancelar" 
                            class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-md transition duration-200"
                            onclick="window.location.href='{{ route('login') }}'">
                        Cancelar
                    </button>
                    <button type="submit" id="submit" 
                            class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            style="display: none;">
                        Registrarse
                    </button>
                </div>
            </form>

            <div class="px-6 py-4 bg-gray-50 text-center">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">
                    ¿Ya tienes cuenta? Inicia sesión
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .requirement, .email-requirement {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        margin: 0.25rem 0;
    }
    .requirement .icon, .email-requirement .icon {
        margin-right: 0.5rem;
        font-weight: bold;
    }
    .requirement.valid, .email-requirement.valid {
        color: #10b981;
    }
    .requirement.invalid, .email-requirement.invalid {
        color: #ef4444;
    }
    input[readonly] {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    const tipoPersonaRadios = document.querySelectorAll('input[name="tipo_persona"]');
    const datosJuridica = document.getElementById('datos-juridica');
    const datosEntidad = document.getElementById('datos-entidad');
    
    // Inicializar validación de contraseña
    initPasswordValidation();
    
    // Inicializar validación de email
    initEmailValidation();
    
    // Manejar cambio de tipo de persona
    function toggleDatosTipoPersona() {
        const selectedValue = document.querySelector('input[name="tipo_persona"]:checked').value;
        
        if (selectedValue === '2') {
            datosJuridica.classList.remove('hidden');
            datosEntidad.classList.add('hidden');
            document.getElementById('ruc').setAttribute('required', 'required');
            document.getElementById('razon_social').setAttribute('required', 'required');
            document.getElementById('nombre_entidad').removeAttribute('required');
        } else if (selectedValue === '3') {
            datosJuridica.classList.add('hidden');
            datosEntidad.classList.remove('hidden');
            document.getElementById('ruc').removeAttribute('required');
            document.getElementById('razon_social').removeAttribute('required');
            document.getElementById('nombre_entidad').setAttribute('required', 'required');
        } else {
            datosJuridica.classList.add('hidden');
            datosEntidad.classList.add('hidden');
            document.getElementById('ruc').removeAttribute('required');
            document.getElementById('razon_social').removeAttribute('required');
            document.getElementById('nombre_entidad').removeAttribute('required');
        }
        resetValidations(selectedValue === '2');
        checkValidations();
    }
    
    tipoPersonaRadios.forEach(radio => {
        radio.addEventListener('change', toggleDatosTipoPersona);
    });
    toggleDatosTipoPersona();

    // Validar DNI
    document.getElementById('validarDni').addEventListener('click', function() {
        const dni = document.getElementById('dni').value;
        if (dni.length !== 8) {
            alert('El DNI debe tener 8 dígitos');
            return;
        }
        
        this.disabled = true;
        this.textContent = 'Validando...';
        
        fetch('{{ route("api.validar-dni") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ dni: dni })
        })
        .then(response => response.json())
        .then(data => {
            console.log('Respuesta del servidor DNI:', data);
            if (data.success) {
                console.log('Nombres recibido:', data.nombres);
                console.log('Apellido Paterno recibido:', data.apellidoPaterno);
                console.log('Apellido Materno recibido:', data.apellidoMaterno);
                
                document.getElementById('nombres').value = data.nombres || '';
                document.getElementById('apellido_paterno').value = data.apellidoPaterno || '';
                document.getElementById('apellido_materno').value = data.apellidoMaterno || '';
                document.getElementById('dniValidado').value = 'true';
                document.getElementById('dni').readOnly = true;
                document.getElementById('nombres').readOnly = true;
                document.getElementById('apellido_paterno').readOnly = true;
                document.getElementById('apellido_materno').readOnly = true;
                this.textContent = '✓ Validado';
                this.classList.remove('bg-green-500', 'hover:bg-green-600');
                this.classList.add('bg-green-600');
                checkValidations();
            } else {
                console.error('Error en validación DNI:', data.message);
                alert(data.message || 'No se pudo validar el DNI');
                this.disabled = false;
                this.textContent = 'Validar DNI';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al validar el DNI. Por favor, inténtelo nuevamente.');
            this.disabled = false;
            this.textContent = 'Validar DNI';
        });
    });

    // Validar RUC
    document.getElementById('validarRuc').addEventListener('click', function() {
        const ruc = document.getElementById('ruc').value;
        if (ruc.length !== 11) {
            alert('El RUC debe tener 11 dígitos');
            return;
        }
        
        this.disabled = true;
        this.textContent = 'Validando...';
        
        fetch('{{ route("api.validar-ruc") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ruc: ruc })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('razon_social').value = data.razonSocial || '';
                document.getElementById('rucValidado').value = 'true';
                document.getElementById('ruc').readOnly = true;
                document.getElementById('razon_social').readOnly = true;
                this.textContent = '✓ Validado';
                this.classList.remove('bg-green-500', 'hover:bg-green-600');
                this.classList.add('bg-green-600');
                checkValidations();
            } else {
                alert(data.message || 'No se pudo validar el RUC');
                this.disabled = false;
                this.textContent = 'Validar RUC';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al validar el RUC. Por favor, inténtelo nuevamente.');
            this.disabled = false;
            this.textContent = 'Validar RUC';
        });
    });

    // Resetear validaciones
    function resetValidations(resetRuc = false) {
        if (resetRuc) {
            document.getElementById('rucValidado').value = 'false';
            document.getElementById('ruc').readOnly = false;
            document.getElementById('razon_social').readOnly = false;
            document.getElementById('ruc').value = '';
            document.getElementById('razon_social').value = '';
            const btnRuc = document.getElementById('validarRuc');
            btnRuc.disabled = false;
            btnRuc.textContent = 'Validar RUC';
            btnRuc.classList.remove('bg-green-600');
            btnRuc.classList.add('bg-green-500', 'hover:bg-green-600');
        }
    }

    // Validar formato DNI
    document.getElementById('dni').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
    });

    // Validar formato teléfono
    document.getElementById('telefono').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 9) {
            this.value = this.value.slice(0, 9);
        }
        checkValidations();
    });

    // Validar formato RUC
    document.getElementById('ruc').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
    });

    // Función para verificar todas las validaciones
    function checkValidations() {
        const tipoPersona = document.querySelector('input[name="tipo_persona"]:checked').value;
        const dniValidado = document.getElementById('dniValidado').value === 'true';
        const rucValidado = document.getElementById('rucValidado').value === 'true';
        const emailValidado = document.getElementById('emailValidado').value === 'true';
        const terminosAceptados = document.getElementById('terminos').checked;
        
        // Validar campos requeridos
        const camposRequeridos = document.querySelectorAll('#registroForm [required]:not([disabled])');
        let camposCompletos = true;
        camposRequeridos.forEach(campo => {
            if (campo.offsetParent !== null && !campo.value) {
                camposCompletos = false;
            }
        });
        
        // Validar ubicación
        const ubicacionValida = document.getElementById('departamento').value && 
                                document.getElementById('provincia').value && 
                                document.getElementById('distrito').value;
        
        // Validar contraseña
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;
        const passwordValid = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$/.test(password) && 
                             password === passwordConfirmation;
        
        // Validar teléfono
        const telefono = document.getElementById('telefono').value;
        const telefonoValido = /^\d{9}$/.test(telefono);
        
        // Validaciones específicas por tipo
        let validacionesEspecificas = false;
        if (tipoPersona === '1') {
            validacionesEspecificas = dniValidado && emailValidado;
        } else if (tipoPersona === '2') {
            validacionesEspecificas = dniValidado && rucValidado && emailValidado;
        } else if (tipoPersona === '3') {
            validacionesEspecificas = dniValidado && emailValidado && 
                                     document.getElementById('nombre_entidad').value.trim() !== '';
        }
        
        const canSubmit = camposCompletos && validacionesEspecificas && terminosAceptados && 
                         telefonoValido && ubicacionValida && passwordValid;
        
        document.getElementById('submit').style.display = canSubmit ? 'block' : 'none';
    }

    // Event listeners para checkValidations
    ['departamento', 'provincia', 'distrito', 'terminos', 'password', 'password_confirmation'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', checkValidations);
            element.addEventListener('input', checkValidations);
        }
    });

    // Cargar departamentos
    cargarDepartamentos();
    
    document.getElementById('departamento').addEventListener('change', function() {
        const departamentoId = this.value;
        if (departamentoId) {
            cargarProvincias(departamentoId);
        } else {
            limpiarProvincias();
            limpiarDistritos();
        }
        checkValidations();
    });

    document.getElementById('provincia').addEventListener('change', function() {
        const provinciaId = this.value;
        if (provinciaId) {
            cargarDistritos(provinciaId);
        } else {
            limpiarDistritos();
        }
        checkValidations();
    });

    // Funciones para cargar ubicaciones
    function cargarDepartamentos() {
        fetch('{{ route("api.departamentos") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('departamento');
                    select.innerHTML = '<option value="">Seleccione Departamento</option>';
                    data.data.forEach(depto => {
                        const option = document.createElement('option');
                        option.value = depto.id;
                        option.textContent = depto.nombre;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error al cargar departamentos:', error));
    }

    function cargarProvincias(departamentoId) {
        fetch(`{{ route("api.provincias", ":id") }}`.replace(':id', departamentoId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('provincia');
                    select.innerHTML = '<option value="">Seleccione Provincia</option>';
                    select.disabled = false;
                    data.data.forEach(prov => {
                        const option = document.createElement('option');
                        option.value = prov.id;
                        option.textContent = prov.nombre;
                        select.appendChild(option);
                    });
                    limpiarDistritos();
                }
            })
            .catch(error => console.error('Error al cargar provincias:', error));
    }

    function cargarDistritos(provinciaId) {
        fetch(`{{ route("api.distritos", ":id") }}`.replace(':id', provinciaId))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('distrito');
                    select.innerHTML = '<option value="">Seleccione Distrito</option>';
                    select.disabled = false;
                    data.data.forEach(dist => {
                        const option = document.createElement('option');
                        option.value = dist.id;
                        option.textContent = dist.nombre;
                        select.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error al cargar distritos:', error));
    }

    function limpiarProvincias() {
        const select = document.getElementById('provincia');
        select.innerHTML = '<option value="">Seleccione Provincia</option>';
        select.disabled = true;
    }

    function limpiarDistritos() {
        const select = document.getElementById('distrito');
        select.innerHTML = '<option value="">Seleccione Distrito</option>';
        select.disabled = true;
    }

    // Validación de contraseña en tiempo real
    function initPasswordValidation() {
        const passwordInput = document.getElementById('password');
        const passwordRequirements = document.getElementById('passwordRequirements');
        
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

            Object.entries(validations).forEach(([key, isValid]) => {
                const req = requirements[key];
                req.element.className = `requirement ${isValid ? 'valid' : 'invalid'}`;
                req.element.innerHTML = `<span class="icon">${isValid ? '✓' : '✕'}</span> ${req.text}`;
            });

            return Object.values(validations).every(v => v);
        }

        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
            checkValidations();
        });
    }

    // Validación de email en tiempo real
    function initEmailValidation() {
        const emailInput = document.getElementById('email');
        const emailRequirements = document.getElementById('emailRequirements');
        const emailFeedback = document.getElementById('email-feedback');
        
        const requirements = {
            format: { text: 'Formato válido (ejemplo@dominio.com)', element: document.createElement('div') },
            length: { text: 'Máximo 45 caracteres', element: document.createElement('div') },
            unique: { text: 'Email disponible', element: document.createElement('div') }
        };

        Object.values(requirements).forEach(req => {
            req.element.className = 'email-requirement invalid';
            req.element.innerHTML = `<span class="icon">✕</span> ${req.text}`;
            emailRequirements.appendChild(req.element);
        });

        let timeoutId;
        const debounceTime = 500;

        async function validateEmail(email) {
            const validations = {
                format: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email),
                length: email.length <= 45,
                unique: false
            };

            // Actualizar formato y longitud inmediatamente
            requirements.format.element.className = `email-requirement ${validations.format ? 'valid' : 'invalid'}`;
            requirements.format.element.innerHTML = `<span class="icon">${validations.format ? '✓' : '✕'}</span> ${requirements.format.text}`;
            
            requirements.length.element.className = `email-requirement ${validations.length ? 'valid' : 'invalid'}`;
            requirements.length.element.innerHTML = `<span class="icon">${validations.length ? '✓' : '✕'}</span> ${requirements.length.text}`;

            // Verificar disponibilidad si formato y longitud son válidos
            if (validations.format && validations.length && email.length > 0) {
                try {
                    const response = await fetch('{{ route("api.validar-email") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email: email })
                    });
                    const data = await response.json();
                    validations.unique = !data.exists;
                    document.getElementById('emailValidado').value = validations.unique ? 'true' : 'false';
                    
                    requirements.unique.element.className = `email-requirement ${validations.unique ? 'valid' : 'invalid'}`;
                    requirements.unique.element.innerHTML = `<span class="icon">${validations.unique ? '✓' : '✕'}</span> ${requirements.unique.text}`;
                    
                    if (data.exists) {
                        emailFeedback.textContent = 'El correo electrónico ya está registrado';
                        emailFeedback.className = 'text-red-600';
                    } else {
                        emailFeedback.textContent = 'Correo electrónico disponible';
                        emailFeedback.className = 'text-green-600';
                    }
                } catch (error) {
                    console.error('Error:', error);
                    validations.unique = false;
                    document.getElementById('emailValidado').value = 'false';
                }
            } else {
                document.getElementById('emailValidado').value = 'false';
                requirements.unique.element.className = 'email-requirement invalid';
                requirements.unique.element.innerHTML = `<span class="icon">✕</span> ${requirements.unique.text}`;
                emailFeedback.textContent = '';
            }

            checkValidations();
        }

        emailInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const email = this.value;
            validateEmail(email);
            timeoutId = setTimeout(() => validateEmail(email), debounceTime);
        });
    }
});
</script>
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
    const form = document.getElementById('registroForm');
    if (form && {{ $recaptcha_enabled ? 'true' : 'false' }}) {
        form.addEventListener('submit', function(e) {
            if (typeof grecaptcha !== 'undefined') {
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
        });
    }
});
</script>
@endif
@endpush
