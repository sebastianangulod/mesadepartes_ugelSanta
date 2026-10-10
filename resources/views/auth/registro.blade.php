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
            <div class="h-1 bg-gradient-to-r from-primary-800 via-primary-500 to-accent-600"></div>
            <div class="px-6 py-4 bg-primary-700">
                <h2 class="text-xl font-semibold text-white">Registro de Usuario</h2>
                <p class="text-primary-100">Complete todos los campos para crear su cuenta</p>
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
                                   class="focus:ring-primary-600 h-4 w-4 text-primary-700 border-gray-300"
                                   {{ old('tipo_persona', '1') == '1' ? 'checked' : '' }}>
                            <span class="ml-2">Persona Natural</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_persona" value="2" 
                                   class="focus:ring-primary-600 h-4 w-4 text-primary-700 border-gray-300"
                                   {{ old('tipo_persona') == '2' ? 'checked' : '' }}>
                            <span class="ml-2">Persona Jurídica</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_persona" value="3" 
                                   class="focus:ring-primary-600 h-4 w-4 text-primary-700 border-gray-300"
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
                               class="flex-1 border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                               value="{{ old('dni') }}" placeholder="Ingrese su DNI (8 dígitos)">
                        <button type="button" id="validarDni" 
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-200">
                            Validar DNI
                        </button>
                    </div>
                </div>

                {{-- Información Personal (se autocompleta después de validar DNI; no aplica a Entidad Pública) --}}
                <div id="datos-personales" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                        <input type="text" name="nombres" id="nombres" required maxlength="20"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                               value="{{ old('nombres') }}" readonly>
                    </div>

                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" required maxlength="20"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600 bg-gray-100"
                               value="{{ old('apellido_paterno') }}" readonly>
                    </div>

                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" required maxlength="20"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600 bg-gray-100"
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
                                       class="flex-1 border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
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
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600 bg-gray-100"
                                   value="{{ old('razon_social') }}" readonly>
                        </div>
                    </div>
                </div>

                {{-- Datos de Entidad Pública --}}
                <div id="datos-entidad" class="hidden">
                    <div>
                        <label for="nombre_entidad" class="block text-sm font-medium text-gray-700">Nombre de Entidad</label>
                        <input type="text" name="nombre_entidad" id="nombre_entidad" maxlength="45"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                               value="{{ old('nombre_entidad') }}"
                               onInput="this.value = this.value.toUpperCase()">
                    </div>
                </div>

                {{-- Información de Contacto --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required maxlength="45"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                               value="{{ old('email') }}">
                        <div id="email-feedback" class="mt-1 text-sm"></div>
                        <div id="emailRequirements" class="mt-2 space-y-1"></div>
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" required maxlength="9"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                               value="{{ old('telefono') }}" placeholder="9 dígitos">
                    </div>
                </div>

                {{-- Ubicación Geográfica --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select name="departamento_id" id="departamento" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600">
                            <option value="">Seleccione Departamento</option>
                        </select>
                    </div>

                    <div>
                        <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia</label>
                        <select name="provincia_id" id="provincia" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                                disabled>
                            <option value="">Seleccione Provincia</option>
                        </select>
                    </div>

                    <div>
                        <label for="distrito" class="block text-sm font-medium text-gray-700">Distrito</label>
                        <select name="distrito_id" id="distrito" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                                disabled>
                            <option value="">Seleccione Distrito</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección</label>
                    <input type="text" name="direccion" id="direccion" required maxlength="35"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-4 py-2.5 focus:ring-primary-600 focus:border-primary-600"
                           value="{{ old('direccion') }}">
                </div>

                {{-- Contraseña con validación en tiempo real --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required maxlength="25"
                                   class="mt-1 block w-full pl-4 pr-11 py-2.5 border-gray-300 rounded-md shadow-sm focus:ring-primary-600 focus:border-primary-600">
                            <button type="button" id="toggleRegPassword" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña"
                                    class="absolute inset-y-0 right-0 mt-1 px-3 flex items-center text-gray-400 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 rounded-r-md">
                                <svg id="iconRegEyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="iconRegEyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.948 9.948 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="passwordRequirements" class="mt-2 space-y-1"></div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="mt-1 block w-full pl-4 pr-11 py-2.5 border-gray-300 rounded-md shadow-sm focus:ring-primary-600 focus:border-primary-600">
                            <button type="button" id="toggleRegPasswordConfirm" aria-label="Mostrar contraseña" aria-pressed="false" title="Mostrar contraseña"
                                    class="absolute inset-y-0 right-0 mt-1 px-3 flex items-center text-gray-400 hover:text-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-600 rounded-r-md">
                                <svg id="iconRegConfirmEyeOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="iconRegConfirmEyeClosed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.948 9.948 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Términos y Condiciones --}}
                <div>
                    <label class="flex items-start">
                        <input type="checkbox" name="terminos" id="terminos" required
                               class="mt-1 focus:ring-primary-600 h-4 w-4 text-primary-700 border-gray-300 rounded">
                        <span class="ml-2 block text-sm text-gray-900">
                            Acepto los
                            <button type="button" id="btnVerTerminos"
                                    class="text-primary-700 hover:text-primary-500 underline font-medium">
                                Términos y Condiciones
                            </button>
                        </span>
                    </label>
                </div>

                {{-- Modal de Términos y Condiciones --}}
                <div id="modalTerminos" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" role="dialog" aria-modal="true" aria-labelledby="tituloTerminos">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative my-auto" style="max-height: 90vh; display: flex; flex-direction: column;">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 id="tituloTerminos" class="text-xl font-bold text-gray-900">Términos y Condiciones de Uso</h3>
                            <p class="text-sm text-gray-500">Mesa de Partes Digital · UGEL Santa</p>
                        </div>
                        <div class="p-6 overflow-y-auto text-sm text-gray-700 space-y-4">
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">1. Objeto del servicio</h4>
                                <p>La Mesa de Partes Digital es el canal virtual que la Unidad de Gestión Educativa Local Santa pone a disposición de la ciudadanía para presentar documentos e iniciar trámites desde cualquier lugar a través de internet, en el horario de Lun a Vie de 8:00 a.m. a 4:30 p.m.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">2. Validez de los trámites</h4>
                                <p>Los trámites iniciados por este medio tienen la misma validez y eficacia jurídica que los realizados de forma presencial. El número de voucher generado al finalizar es su comprobante oficial: consérvelo para seguimiento y consultas.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">3. Veracidad de la información</h4>
                                <p>Usted declara que los datos ingresados y los documentos adjuntos son auténticos. La presentación de información falsa puede invalidar el trámite y acarrear las responsabilidades de ley.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">4. Documentos adjuntos</h4>
                                <p>Los archivos deben ser legibles y en los formatos y tamaños indicados en cada formulario. Un trámite observado por documentación incorrecta deberá subsanarse dentro del plazo señalado; vencido el plazo, podrá ser rechazado.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">5. Datos personales</h4>
                                <p>Sus datos se utilizan únicamente para la atención de sus trámites, conforme a la Ley N.° 29733 de Protección de Datos Personales. Puede solicitar su actualización en su perfil o en mesa de partes.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">6. Seguimiento</h4>
                                <p>Puede consultar el estado de sus trámites en cualquier momento desde la opción Rastrear, con su número de trámite o voucher.</p>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">7. Contacto</h4>
                                <p>Av. los Alcatraces S/N – Nuevo Chimbote · <span class="font-medium">mesadepartes@ugelsanta.gob.pe</span> · Lun a Vie de 8:00 a.m. a 4:30 p.m.</p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                            <button type="button" id="btnCerrarTerminos"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                Cerrar
                            </button>
                            <button type="button" id="btnAceptarTerminos"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                                He leído y acepto
                            </button>
                        </div>
                    </div>
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
                    <strong>Error de configuración de reCAPTCHA</strong><br>
                    <small>Verifique que el dominio esté registrado en Google reCAPTCHA y que las claves correspondan al mismo sitio.</small>
                </div>
                @endif

                <div class="flex justify-between pt-4">
                    <button type="button" id="cancelar" 
                            class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600 transition duration-200"
                            onclick="window.location.href='{{ route('login') }}'">
                        Cancelar
                    </button>
                    <button type="submit" id="submit" 
                            class="bg-primary-600 text-white px-6 py-2 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-600"
                            style="display: none;">
                        Registrarse
                    </button>
                </div>
            </form>

            <div class="px-6 py-4 bg-primary-50 text-center rounded-b-lg">
                <a href="{{ route('login') }}" class="text-primary-500 hover:text-primary-600">
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
    const dniField = document.getElementById('dni_field');
    const datosPersonales = document.getElementById('datos-personales');
    const dniInputReg = document.getElementById('dni');
    const nombresInputReg = document.getElementById('nombres');
    const apPatInputReg = document.getElementById('apellido_paterno');
    const apMatInputReg = document.getElementById('apellido_materno');

    // Mostrar/ocultar contraseñas (helper único, sin colisión de nombres)
    function wireRegPasswordToggle(btnId, inputId, openId, closedId) {
        const btn = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        const iconOpen = document.getElementById(openId);
        const iconClosed = document.getElementById(closedId);
        if (!btn || !input) return;
        btn.addEventListener('click', function() {
            const visible = input.type === 'text';
            input.type = visible ? 'password' : 'text';
            btn.setAttribute('aria-pressed', String(!visible));
            btn.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            btn.setAttribute('title', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
            if (iconOpen) iconOpen.classList.toggle('hidden', !visible);
            if (iconClosed) iconClosed.classList.toggle('hidden', visible);
        });
    }
    wireRegPasswordToggle('toggleRegPassword', 'password', 'iconRegEyeOpen', 'iconRegEyeClosed');
    wireRegPasswordToggle('toggleRegPasswordConfirm', 'password_confirmation', 'iconRegConfirmEyeOpen', 'iconRegConfirmEyeClosed');
    
    // Inicializar validación de contraseña
    initPasswordValidation();
    
    // Inicializar validación de email
    initEmailValidation();
    
    // Manejar cambio de tipo de persona
    // Entidad Pública no usa DNI ni datos personales: solo nombre de la entidad.
    function toggleDatosTipoPersona() {
        const selectedValue = document.querySelector('input[name="tipo_persona"]:checked').value;
        const esEntidad = selectedValue === '3';

        if (dniField) dniField.classList.toggle('hidden', esEntidad);
        if (datosPersonales) datosPersonales.classList.toggle('hidden', esEntidad);
        [dniInputReg, nombresInputReg, apPatInputReg, apMatInputReg].forEach(function(el) {
            if (!el) return;
            if (esEntidad) el.removeAttribute('required');
            else el.setAttribute('required', 'required');
        });

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
        
        // Validaciones específicas por tipo (Entidad Pública no usa DNI)
        let validacionesEspecificas = false;
        if (tipoPersona === '1') {
            validacionesEspecificas = dniValidado && emailValidado;
        } else if (tipoPersona === '2') {
            validacionesEspecificas = dniValidado && rucValidado && emailValidado;
        } else if (tipoPersona === '3') {
            validacionesEspecificas = emailValidado &&
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

    // Modal de términos y condiciones
    (function() {
        const modal = document.getElementById('modalTerminos');
        const btnVer = document.getElementById('btnVerTerminos');
        const btnCerrar = document.getElementById('btnCerrarTerminos');
        const btnAceptar = document.getElementById('btnAceptarTerminos');
        const chk = document.getElementById('terminos');
        if (!modal || !btnVer) return;

        function abrir() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            btnCerrar.focus();
        }
        function cerrar() {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            btnVer.focus();
        }
        btnVer.addEventListener('click', abrir);
        btnCerrar.addEventListener('click', cerrar);
        btnAceptar.addEventListener('click', function() {
            chk.checked = true;
            chk.dispatchEvent(new Event('change'));
            cerrar();
        });
        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrar();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) cerrar();
        });
    })();
});
</script>
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

    const form = document.getElementById('registroForm');
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
    const form = document.getElementById('registroForm');
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
@endpush
