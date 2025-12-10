{{--
    Vista de Registro de Usuario
    Formulario para nuevos usuarios externos
    Incluye selector de ubicación geográfica (departamento/provincia/distrito)
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

            <form action="{{ route('register') }}" method="POST" class="p-6 space-y-6" enctype="multipart/form-data">
                @csrf

                <x-alerts :errors-bag="$errors" />

                {{-- Información Personal --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nombres" class="block text-sm font-medium text-gray-700">Nombres</label>
                        <input type="text" name="nombres" id="nombres" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('nombres') }}">
                    </div>

                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input type="text" name="apellido_paterno" id="apellido_paterno" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('apellido_paterno') }}">
                    </div>

                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                        <input type="text" name="apellido_materno" id="apellido_materno" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('apellido_materno') }}">
                    </div>

                    <div>
                        <label for="dni" class="block text-sm font-medium text-gray-700">DNI</label>
                        <input type="text" name="dni" id="dni" required maxlength="8" pattern="[0-9]{8}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('dni') }}">
                    </div>
                </div>

                {{-- Información de Contacto --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('email') }}">
                    </div>

                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="tel" name="telefono" id="telefono" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('telefono') }}">
                    </div>
                </div>

                {{-- Ubicación Geográfica --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="departamento" class="block text-sm font-medium text-gray-700">Departamento</label>
                        <select name="departamento" id="departamento" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Seleccione Departamento</option>
                            {{-- Opciones cargadas dinámicamente --}}
                        </select>
                    </div>

                    <div>
                        <label for="provincia" class="block text-sm font-medium text-gray-700">Provincia</label>
                        <select name="provincia" id="provincia" required
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
                    <input type="text" name="direccion" id="direccion" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('direccion') }}">
                </div>

                {{-- Tipo de Persona --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipo de Persona</label>
                    <div class="mt-2 space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_persona" value="1" 
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                   {{ old('tipo_persona', '1') == '1' ? 'checked' : '' }}>
                            <span class="ml-2">Persona Natural</span>
                        </label>
                        <label class="inline-flex items-center ml-6">
                            <input type="radio" name="tipo_persona" value="2" 
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                   {{ old('tipo_persona') == '2' ? 'checked' : '' }}>
                            <span class="ml-2">Persona Jurídica</span>
                        </label>
                        <label class="inline-flex items-center ml-6">
                            <input type="radio" name="tipo_persona" value="3" 
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                   {{ old('tipo_persona') == '3' ? 'checked' : '' }}>
                            <span class="ml-2">Entidad Pública</span>
                        </label>
                    </div>
                </div>

                {{-- Datos de Persona Jurídica --}}
                <div id="datos-juridica" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="ruc" class="block text-sm font-medium text-gray-700">RUC</label>
                            <input type="text" name="ruc" id="ruc" maxlength="45"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('ruc') }}">
                        </div>

                        <div>
                            <label for="razon_social" class="block text-sm font-medium text-gray-700">Razón Social</label>
                            <input type="text" name="razon_social" id="razon_social" maxlength="45"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('razon_social') }}">
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

                {{-- Contraseña --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="terminos" id="terminos" required
                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    <label for="terminos" class="ml-2 block text-sm text-gray-900">
                        Acepto los <a href="#" class="text-indigo-600 hover:text-indigo-500">términos y condiciones</a>
                    </label>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
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

@push('scripts')
<script>
    // Script para manejar el cambio de tipo de persona
    document.addEventListener('DOMContentLoaded', function() {
        const tipoPersonaRadios = document.querySelectorAll('input[name="tipo_persona"]');
        const datosJuridica = document.getElementById('datos-juridica');
        const datosEntidad = document.getElementById('datos-entidad');
        
        function toggleDatosTipoPersona() {
            const selectedValue = document.querySelector('input[name="tipo_persona"]:checked').value;
            
            // Persona Jurídica (tipo 2)
            if (selectedValue === '2') {
                datosJuridica.classList.remove('hidden');
                datosEntidad.classList.add('hidden');
                document.getElementById('ruc').setAttribute('required', 'required');
                document.getElementById('razon_social').setAttribute('required', 'required');
                document.getElementById('nombre_entidad').removeAttribute('required');
            } 
            // Entidad Pública (tipo 3)
            else if (selectedValue === '3') {
                datosJuridica.classList.add('hidden');
                datosEntidad.classList.remove('hidden');
                document.getElementById('ruc').removeAttribute('required');
                document.getElementById('razon_social').removeAttribute('required');
                document.getElementById('nombre_entidad').setAttribute('required', 'required');
            } 
            // Persona Natural (tipo 1)
            else {
                datosJuridica.classList.add('hidden');
                datosEntidad.classList.add('hidden');
                document.getElementById('ruc').removeAttribute('required');
                document.getElementById('razon_social').removeAttribute('required');
                document.getElementById('nombre_entidad').removeAttribute('required');
            }
        }
        
        tipoPersonaRadios.forEach(radio => {
            radio.addEventListener('change', toggleDatosTipoPersona);
        });
        
        // Inicializar estado
        toggleDatosTipoPersona();

        // Cargar departamentos al iniciar
        cargarDepartamentos();

        // Manejar cambio de departamento
        document.getElementById('departamento').addEventListener('change', function() {
            const departamentoId = this.value;
            if (departamentoId) {
                cargarProvincias(departamentoId);
            } else {
                limpiarProvincias();
                limpiarDistritos();
            }
        });

        // Manejar cambio de provincia
        document.getElementById('provincia').addEventListener('change', function() {
            const provinciaId = this.value;
            if (provinciaId) {
                cargarDistritos(provinciaId);
            } else {
                limpiarDistritos();
            }
        });
    });

    // Función para cargar departamentos
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
            .catch(error => {
                console.error('Error al cargar departamentos:', error);
            });
    }

    // Función para cargar provincias
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
            .catch(error => {
                console.error('Error al cargar provincias:', error);
            });
    }

    // Función para cargar distritos
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
            .catch(error => {
                console.error('Error al cargar distritos:', error);
            });
    }

    // Función para limpiar provincias
    function limpiarProvincias() {
        const select = document.getElementById('provincia');
        select.innerHTML = '<option value="">Seleccione Provincia</option>';
        select.disabled = true;
    }

    // Función para limpiar distritos
    function limpiarDistritos() {
        const select = document.getElementById('distrito');
        select.innerHTML = '<option value="">Seleccione Distrito</option>';
        select.disabled = true;
    }

    // Validar formato DNI en tiempo real
    document.getElementById('dni').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 8) {
            this.value = this.value.slice(0, 8);
        }
    });

    // Validar formato teléfono en tiempo real
    document.getElementById('telefono').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 9) {
            this.value = this.value.slice(0, 9);
        }
    });
</script>
@endpush