{{--
    Vista de Gestión de Usuarios Internos
    Equivalente a adminUsuarios.php del PHP puro
--}}
@extends('layouts.app')

@section('title', 'Administración de Usuarios - Mesa de Partes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <x-alerts />
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Administración de Usuarios</h2>
        <button id="addUserButton" onclick="openModal('modalAddUser')" class="btn-primary">
            Agregar Usuario
                    </button>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">N°</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre Completo</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Correo</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Teléfono</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nivel</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Especialidad</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
            <tbody id="usuariosTable">
                @forelse($usuarios as $index => $usuario)
                <tr>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $index + 1 }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    {{ $usuario->nombres }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                            </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $usuario->email }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $usuario->telefono }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        @if($usuario->usuariosNiveles->isNotEmpty())
                            Nivel {{ $usuario->usuariosNiveles->first()->nivel }}
                        @else
                            Sin asignar
                        @endif
                            </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        {{ $usuario->nombre_nivel ?? 'Sin asignar' }}
                            </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <button onclick="editarUsuario({{ $usuario->id }})" 
                                class="text-green-500 hover:text-green-700 mr-2" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        @if($usuario->id != auth()->id())
                        <button onclick="eliminarUsuario({{ $usuario->id }}, '{{ $usuario->nombres }} {{ $usuario->apellido_paterno }}')" 
                                class="text-red-500 hover:text-red-700" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                        @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                    <td colspan="7" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                        No hay usuarios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
</div>

<!-- Modal Agregar Usuario -->
<div id="modalAddUser" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white p-6 rounded-lg w-full max-w-4xl my-8">
        <div class="modal-header mb-4">
            <h3 class="text-xl font-bold">Agregar Usuario</h3>
        </div>
        
        <form id="formCrearUsuario" class="grid grid-cols-2 gap-4" autocomplete="off" onsubmit="crearUsuario(event)">
            @csrf
            <!-- DNI y Nombres -->
            <div class="col-span-2">
                <label for="dniUsuario" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                <div class="flex gap-2">
                    <input type="text" id="dniUsuario" name="dniUsuario" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ingrese el DNI" maxlength="8" required>
                    <button type="button" onclick="obtenerDNI()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Buscar
                    </button>
                </div>
                <div id="dni-error" class="text-red-500 text-sm mt-1"></div>
            </div>
            
            <div>
                <label for="nombreUsuario" class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                <input type="text" id="nombreUsuario" name="nombreUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       readonly required>
            </div>

            <div>
                <label for="apellidoPaternoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno</label>
                <input type="text" id="apellidoPaternoUsuario" name="apellidoPaternoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       readonly required>
            </div>

            <div>
                <label for="apellidoMaternoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno</label>
                <input type="text" id="apellidoMaternoUsuario" name="apellidoMaternoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       readonly required>
            </div>

            <!-- Correo y Contraseña -->
            <div>
                <label for="correo_electronico" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" id="correo_electronico" name="correoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Ingrese el correo electrónico" required>
            </div>

            <div>
                <label for="contrasena" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <div class="relative">
                    <input type="password" id="contrasena" name="contrasenaUsuario" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ingrese la contraseña" required>
                    <button type="button" onclick="togglePassword('contrasena')" class="absolute right-2 top-2 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Dirección y Teléfono -->
            <div class="col-span-2">
                <label for="direccionUsuario" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input type="text" id="direccionUsuario" name="direccionUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required>
            </div>

            <div>
                <label for="telefonoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" id="telefonoUsuario" name="telefonoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required maxlength="9" pattern="\d{9}">
            </div>

            <!-- Ubicación -->
            <div>
                <label for="departamentoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select id="departamentoUsuario" name="departamentoUsuario" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarProvincias(this.value)">
                    <option value="">Seleccione un departamento</option>
                </select>
            </div>

            <div>
                <label for="provinciaUsuario" class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                <select id="provinciaUsuario" name="provinciaUsuario" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled onchange="cargarDistritos(this.value)">
                    <option value="">Seleccione una provincia</option>
                </select>
            </div>

            <div>
                <label for="distritoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
                <select id="distritoUsuario" name="distritoUsuario" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled required>
                    <option value="">Seleccione un distrito</option>
                </select>
            </div>

            <!-- Niveles en cascada -->
            <div>
                <label for="nivel1" class="block text-sm font-medium text-gray-700 mb-1">Nivel 1</label>
                <select id="nivel1" name="nivel1" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarNivel2(this.value)">
                    <option value="">Seleccione Nivel 1</option>
                </select>
            </div>

            <div>
                <label for="nivel2" class="block text-sm font-medium text-gray-700 mb-1">Nivel 2</label>
                <select id="nivel2" name="nivel2" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled onchange="cargarNivel3(this.value)">
                    <option value="">Seleccione Nivel 2</option>
                </select>
            </div>

            <div>
                <label for="nivel3" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3</label>
                <select id="nivel3" name="nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled onchange="cargarNivel4(this.value)">
                    <option value="">Seleccione Nivel 3</option>
                </select>
            </div>

            <div>
                <label for="nivel4" class="block text-sm font-medium text-gray-700 mb-1">Nivel 4</label>
                <select id="nivel4" name="nivel4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled>
                    <option value="">Seleccione Nivel 4</option>
                </select>
            </div>

            <div class="col-span-2 flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closeModal('modalAddUser')" class="btn-secondary">Cancelar</button>
                <button type="submit" id="btnGuardarUsuario" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div id="modalEditUser" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white p-6 rounded-lg w-full max-w-4xl my-8">
        <div class="modal-header mb-4">
            <h3 class="text-xl font-bold">Editar Usuario</h3>
        </div>
        
        <form id="formEditarUsuario" class="grid grid-cols-2 gap-4">
            @csrf
            @method('PUT')
            <input type="hidden" id="editUserId" name="id">
            
            <div>
                <label for="editNombreUsuario" class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                <input type="text" id="editNombreUsuario" name="nombres" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="editApellidoPaternoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno</label>
                <input type="text" id="editApellidoPaternoUsuario" name="apellido_paterno" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="editApellidoMaternoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno</label>
                <input type="text" id="editApellidoMaternoUsuario" name="apellido_materno" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="editCorreoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="email" id="editCorreoUsuario" name="email" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="col-span-2">
                <label for="editDireccionUsuario" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input type="text" id="editDireccionUsuario" name="direccion" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="editTelefonoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="text" id="editTelefonoUsuario" name="telefono" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="editDepartamentoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select id="editDepartamentoUsuario" name="departamento_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarProvinciasEdit(this.value)">
                    <option value="">Seleccione un departamento</option>
                </select>
            </div>

            <div>
                <label for="editProvinciaUsuario" class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                <select id="editProvinciaUsuario" name="provincia_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarDistritosEdit(this.value)">
                    <option value="">Seleccione una provincia</option>
                </select>
            </div>

            <div>
                <label for="editDistritoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Distrito</label>
                <select id="editDistritoUsuario" name="distrito_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione un distrito</option>
                </select>
            </div>

            <!-- Niveles en cascada -->
            <div>
                <label for="editNivel1" class="block text-sm font-medium text-gray-700 mb-1">Nivel 1</label>
                <select id="editNivel1" name="nivel1" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarEditNivel2(this.value)">
                    <option value="">Seleccione Nivel 1</option>
                </select>
            </div>

            <div>
                <label for="editNivel2" class="block text-sm font-medium text-gray-700 mb-1">Nivel 2</label>
                <select id="editNivel2" name="nivel2" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled onchange="cargarEditNivel3(this.value)">
                    <option value="">Seleccione Nivel 2</option>
                </select>
            </div>

            <div>
                <label for="editNivel3" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3</label>
                <select id="editNivel3" name="nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled onchange="cargarEditNivel4(this.value)">
                    <option value="">Seleccione Nivel 3</option>
                </select>
            </div>

            <div>
                <label for="editNivel4" class="block text-sm font-medium text-gray-700 mb-1">Nivel 4</label>
                <select id="editNivel4" name="nivel4" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled>
                    <option value="">Seleccione Nivel 4</option>
                </select>
            </div>

            <div class="col-span-2 flex justify-end space-x-2 mt-4">
                <button type="button" onclick="closeModal('modalEditUser')" class="btn-secondary">Cancelar</button>
                <button type="button" onclick="actualizarUsuario()" class="btn-primary" id="btnEditarUsuario">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar Usuario -->
<div id="modalDeleteUser" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Eliminar Usuario</h2>
        <input type="hidden" id="deleteUserId">
        <p class="text-lg text-gray-700 mb-4">
            ¿Está seguro que desea eliminar el usuario <strong id="deleteUserName"></strong>?
        </p>
        <p class="text-sm text-gray-500 mb-4">Esta acción no se puede deshacer.</p>
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="closeModal('modalDeleteUser')" class="btn-secondary">Cancelar</button>
            <button type="button" onclick="confirmarEliminarUsuario()" class="btn-danger">Eliminar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Funciones de modal
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
        } else {
            input.type = "password";
        }
    }

    // Cargar departamentos al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        cargarDepartamentos();
        cargarEditDepartamentos();
        cargarNivel1();
        cargarEditNivel1();
    });

    // Funciones para cargar ubicación
    function cargarDepartamentos() {
        fetch('/api/departamentos', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('departamentoUsuario');
            data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.nombre;
                select.appendChild(option);
            });
        });
    }

    function cargarProvincias(departamentoId) {
        const select = document.getElementById('provinciaUsuario');
        select.innerHTML = '<option value="">Seleccione una provincia</option>';
        select.disabled = true;
        
        if (!departamentoId) return;
        
        fetch(`/api/provincias/${departamentoId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.nombre;
                select.appendChild(option);
            });
            select.disabled = false;
        });
    }

    function cargarDistritos(provinciaId) {
        const select = document.getElementById('distritoUsuario');
        select.innerHTML = '<option value="">Seleccione un distrito</option>';
        select.disabled = true;
        
        if (!provinciaId) return;
        
        fetch(`/api/distritos/${provinciaId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(dist => {
                const option = document.createElement('option');
                option.value = dist.id;
                option.textContent = dist.nombre;
                select.appendChild(option);
            });
            select.disabled = false;
        });
    }

    // Funciones para cargar niveles
    function cargarNivel1() {
        fetch('/api/areas', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const select = document.getElementById('nivel1');
                data.data.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.nombre;
                    select.appendChild(option);
                });
            }
        });
    }

    function cargarNivel2(nivel1Id) {
        const select = document.getElementById('nivel2');
        select.innerHTML = '<option value="">Seleccione Nivel 2</option>';
        select.disabled = true;
        
        if (!nivel1Id) return;
        
        fetch(`/api/niveles/nivel2/${nivel1Id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel2 => {
                    const option = document.createElement('option');
                    option.value = nivel2.id;
                    option.textContent = nivel2.nombre;
                    select.appendChild(option);
                });
                select.disabled = false;
            }
        });
    }

    function cargarNivel3(nivel2Id) {
        const select = document.getElementById('nivel3');
        select.innerHTML = '<option value="">Seleccione Nivel 3</option>';
        select.disabled = true;
        
        if (!nivel2Id) return;
        
        fetch(`/api/niveles/nivel3/${nivel2Id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel3 => {
                    const option = document.createElement('option');
                    option.value = nivel3.id;
                    option.textContent = nivel3.nombre;
                    select.appendChild(option);
                });
                select.disabled = false;
            }
        });
    }

    function cargarNivel4(nivel3Id) {
        const select = document.getElementById('nivel4');
        select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
        select.disabled = true;
        
        if (!nivel3Id) return;
        
        fetch(`/api/niveles/nivel4/${nivel3Id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel4 => {
                    const option = document.createElement('option');
                    option.value = nivel4.id;
                    option.textContent = nivel4.nombre;
                    select.appendChild(option);
                });
                select.disabled = false;
            }
        });
    }

    // Funciones para edición (similar pero con prefijo edit)
    function cargarEditDepartamentos() {
        fetch('/api/departamentos', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('editDepartamentoUsuario');
            data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.nombre;
                select.appendChild(option);
            });
        });
    }

    function cargarProvinciasEdit(departamentoId) {
        const select = document.getElementById('editProvinciaUsuario');
        select.innerHTML = '<option value="">Seleccione una provincia</option>';
        
        if (!departamentoId) return;
        
        fetch(`/api/provincias/${departamentoId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.nombre;
                select.appendChild(option);
            });
        });
    }

    function cargarDistritosEdit(provinciaId) {
        const select = document.getElementById('editDistritoUsuario');
        select.innerHTML = '<option value="">Seleccione un distrito</option>';
        
        if (!provinciaId) return;
        
        fetch(`/api/distritos/${provinciaId}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            data.forEach(dist => {
                const option = document.createElement('option');
                option.value = dist.id;
                option.textContent = dist.nombre;
                select.appendChild(option);
            });
        });
    }

    function cargarEditNivel1() {
        fetch('/api/areas', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const select = document.getElementById('editNivel1');
                data.data.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area.id;
                    option.textContent = area.nombre;
                    select.appendChild(option);
                });
            }
        });
    }

    function cargarEditNivel2(nivel1Id) {
        const select = document.getElementById('editNivel2');
        select.innerHTML = '<option value="">Seleccione Nivel 2</option>';
        select.disabled = true;
        
        if (!nivel1Id) return;
        
        fetch(`/api/niveles/nivel2/${nivel1Id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel2 => {
                    const option = document.createElement('option');
                    option.value = nivel2.id;
                    option.textContent = nivel2.nombre;
                    select.appendChild(option);
                });
                select.disabled = false;
            }
        });
    }

    function cargarEditNivel3(nivel2Id) {
        const select = document.getElementById('editNivel3');
        select.innerHTML = '<option value="">Seleccione Nivel 3</option>';
        select.disabled = true;
        
        if (!nivel2Id) return;
        
        fetch(`/api/niveles/nivel3/${nivel2Id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel3 => {
                    const option = document.createElement('option');
                    option.value = nivel3.id;
                    option.textContent = nivel3.nombre;
                    select.appendChild(option);
                });
                select.disabled = false;
            }
        });
    }

    function cargarEditNivel4(nivel3Id) {
        const select = document.getElementById('editNivel4');
        select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
        select.disabled = true;
        
        if (!nivel3Id) return;
        
        fetch(`/api/niveles/nivel4/${nivel3Id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel4 => {
                    const option = document.createElement('option');
                    option.value = nivel4.id;
                    option.textContent = nivel4.nombre;
                    select.appendChild(option);
                });
                select.disabled = false;
            }
        });
    }

    // Función para obtener DNI (simulada - necesitarías implementar la API real)
    function obtenerDNI() {
        const dni = document.getElementById('dniUsuario').value;
        if (!/^\d{8}$/.test(dni)) {
            document.getElementById('dni-error').textContent = 'El DNI debe tener 8 dígitos';
            return;
        }
        
        // Aquí deberías llamar a una API para obtener datos del DNI
        // Por ahora, solo limpiamos el error
        document.getElementById('dni-error').textContent = '';
        // Nota: En producción, esto debería llamar a una API externa o servicio
    }

    // Función para crear usuario
    function crearUsuario(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        // Determinar nivel y id_nivel según la selección
        let nivel = null;
        let idNivel = null;
        
        if (formData.get('nivel4')) {
            nivel = 4;
            idNivel = formData.get('nivel4');
        } else if (formData.get('nivel3')) {
            nivel = 3;
            idNivel = formData.get('nivel3');
        } else if (formData.get('nivel2')) {
            nivel = 2;
            idNivel = formData.get('nivel2');
        } else if (formData.get('nivel1')) {
            nivel = 1;
            idNivel = formData.get('nivel1');
        }
        
        if (!nivel || !idNivel) {
            alert('Debe seleccionar al menos el Nivel 1');
            return;
        }
        
        const data = {
            dniUsuario: formData.get('dniUsuario'),
            nombreUsuario: formData.get('nombreUsuario'),
            apellidoPaternoUsuario: formData.get('apellidoPaternoUsuario'),
            apellidoMaternoUsuario: formData.get('apellidoMaternoUsuario'),
            correoUsuario: formData.get('correoUsuario'),
            contrasenaUsuario: formData.get('contrasenaUsuario'),
            direccionUsuario: formData.get('direccionUsuario'),
            telefonoUsuario: formData.get('telefonoUsuario'),
            distritoUsuario: formData.get('distritoUsuario'),
            nivel: nivel,
            id_nivel: idNivel
        };
        
        fetch('{{ route("admin.usuarios.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al crear el usuario');
        });
    }

    // Función para editar usuario
    function editarUsuario(id) {
        fetch(`/admin/usuarios/${id}/obtener`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                document.getElementById('editUserId').value = id;
                document.getElementById('editNombreUsuario').value = user.nombres || '';
                document.getElementById('editApellidoPaternoUsuario').value = user.apellido_paterno || '';
                document.getElementById('editApellidoMaternoUsuario').value = user.apellido_materno || '';
                document.getElementById('editCorreoUsuario').value = user.email || '';
                document.getElementById('editDireccionUsuario').value = user.direccion || '';
                document.getElementById('editTelefonoUsuario').value = user.telefono || '';
                
                // Cargar ubicación si existe
                if (user.departamento_id) {
                    document.getElementById('editDepartamentoUsuario').value = user.departamento_id;
                    cargarProvinciasEdit(user.departamento_id);
                    if (user.provincia_id) {
                        setTimeout(() => {
                            document.getElementById('editProvinciaUsuario').value = user.provincia_id;
                            cargarDistritosEdit(user.provincia_id);
                            if (user.distrito_id) {
                                setTimeout(() => {
                                    document.getElementById('editDistritoUsuario').value = user.distrito_id;
                                }, 500);
                            }
                        }, 500);
                    }
                }
                
                // Cargar niveles si existen
                if (user.nivel1_id) {
                    document.getElementById('editNivel1').value = user.nivel1_id;
                    cargarEditNivel2(user.nivel1_id);
                    if (user.nivel2_id) {
                        setTimeout(() => {
                            document.getElementById('editNivel2').value = user.nivel2_id;
                            cargarEditNivel3(user.nivel2_id);
                            if (user.nivel3_id) {
                                setTimeout(() => {
                                    document.getElementById('editNivel3').value = user.nivel3_id;
                                    cargarEditNivel4(user.nivel3_id);
                                    if (user.nivel4_id) {
                                        setTimeout(() => {
                                            document.getElementById('editNivel4').value = user.nivel4_id;
                                        }, 500);
                                    }
                                }, 500);
                            }
                        }, 500);
                    }
                }
                
                openModal('modalEditUser');
            }
        });
    }

    // Función para actualizar usuario
    function actualizarUsuario() {
        const id = document.getElementById('editUserId').value;
        if (!id) return;
        
        // Determinar nivel y id_nivel
        let nivel = null;
        let idNivel = null;
        
        const nivel4 = document.getElementById('editNivel4').value;
        const nivel3 = document.getElementById('editNivel3').value;
        const nivel2 = document.getElementById('editNivel2').value;
        const nivel1 = document.getElementById('editNivel1').value;
        
        if (nivel4) {
            nivel = 4;
            idNivel = nivel4;
        } else if (nivel3) {
            nivel = 3;
            idNivel = nivel3;
        } else if (nivel2) {
            nivel = 2;
            idNivel = nivel2;
        } else if (nivel1) {
            nivel = 1;
            idNivel = nivel1;
        }
        
        if (!nivel || !idNivel) {
            alert('Debe seleccionar al menos el Nivel 1');
            return;
        }
        
        const data = {
            nombres: document.getElementById('editNombreUsuario').value,
            apellido_paterno: document.getElementById('editApellidoPaternoUsuario').value,
            apellido_materno: document.getElementById('editApellidoMaternoUsuario').value,
            email: document.getElementById('editCorreoUsuario').value,
            direccion: document.getElementById('editDireccionUsuario').value,
            telefono: document.getElementById('editTelefonoUsuario').value,
            distrito_id: document.getElementById('editDistritoUsuario').value,
            nivel: nivel,
            id_nivel: idNivel
        };
        
        fetch(`/admin/usuarios/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el usuario');
        });
    }

    // Función para eliminar usuario
    function eliminarUsuario(id, nombre) {
        document.getElementById('deleteUserId').value = id;
        document.getElementById('deleteUserName').textContent = nombre;
        openModal('modalDeleteUser');
    }

    function confirmarEliminarUsuario() {
        const id = document.getElementById('deleteUserId').value;
        
        fetch(`/admin/usuarios/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al eliminar el usuario');
        });
    }
</script>
@endpush
@endsection
