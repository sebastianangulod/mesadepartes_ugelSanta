{{--
    Vista de Gestión de Usuarios Internos
    Equivalente a adminUsuarios.php del PHP puro
--}}
@extends('layouts.admin')

@section('title', 'Administración de Usuarios - Mesa de Partes')

@push('styles')
<style>
    /* Estilos para modales - centrados en el área de contenido (sin sidebar) */
    #modalAddUser,
    #modalEditUser,
    #modalDeleteUser {
        backdrop-filter: blur(4px);
        /* Centrar solo en el área de contenido, considerando el sidebar de 250px */
        left: 250px;
        width: calc(100% - 250px);
    }
    
    /* Resaltar bordes del modal para mejor visibilidad */
    #modalAddUser > div,
    #modalEditUser > div,
    #modalDeleteUser > div {
        border: 2px solid #e5e7eb;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Borde más visible en hover o focus */
    #modalAddUser > div:focus-within,
    #modalEditUser > div:focus-within,
    #modalDeleteUser > div:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
    }
    
    /* Asegurar que el contenido del modal sea scrollable */
    .modal-content {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
    }
    
    .modal-content::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-content::-webkit-scrollbar-track {
        background: #f7fafc;
    }
    
    .modal-content::-webkit-scrollbar-thumb {
        background-color: #cbd5e0;
        border-radius: 4px;
    }
    
    .modal-content::-webkit-scrollbar-thumb:hover {
        background-color: #a0aec0;
    }
    
    /* En pantallas pequeñas, el modal debe ocupar toda la pantalla */
    @media (max-width: 768px) {
        #modalAddUser,
        #modalEditUser,
        #modalDeleteUser {
            left: 0;
            width: 100%;
        }
    }
    
    /* Estilos para validación de email */
    .email-requirement {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        margin: 0.25rem 0;
    }
    .email-requirement .icon {
        margin-right: 0.5rem;
        font-weight: bold;
    }
    .email-requirement.valid {
        color: #10b981;
    }
    .email-requirement.invalid {
        color: #ef4444;
    }
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
    input[readonly], input[disabled] {
        background-color: #f3f4f6;
        cursor: not-allowed;
    }
</style>
@endpush

@section('content')
    
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
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Niveles Asignados</th>
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
                            <div class="flex flex-wrap gap-1">
                                @foreach($usuario->usuariosNiveles as $un)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Nivel {{ $un->nivel }}
                                        @if($un->nivelModelo)
                                            : {{ $un->nivelModelo->nombre }}
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-500 text-sm">Sin asignar</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <button onclick="editarUsuario({{ $usuario->id }})" 
                                class="text-green-500 hover:text-green-700 mr-2" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="abrirModalNiveles({{ $usuario->id }}, '{{ $usuario->nombres }} {{ $usuario->apellido_paterno }}')" 
                                class="text-purple-500 hover:text-purple-700 mr-2" title="Asignar Niveles">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 002-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </button>
                        @if($usuario->id != auth()->id())
                        @if($usuario->administrador)
                        <button onclick="toggleAdmin({{ $usuario->id }}, true, '{{ $usuario->nombres }} {{ $usuario->apellido_paterno }}')"
                                class="text-amber-500 hover:text-amber-700 mr-2" title="Quitar administrador">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l2.9 6.26L21.5 9.3l-4.75 4.87L17.8 21 12 17.77 6.2 21l1.05-6.83L2.5 9.3l6.6-1.04L12 2z"></path>
                            </svg>
                        </button>
                        @else
                        <button onclick="toggleAdmin({{ $usuario->id }}, false, '{{ $usuario->nombres }} {{ $usuario->apellido_paterno }}')"
                                class="text-gray-400 hover:text-amber-600 mr-2" title="Hacer administrador">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2l2.9 6.26L21.5 9.3l-4.75 4.87L17.8 21 12 17.77 6.2 21l1.05-6.83L2.5 9.3l6.6-1.04L12 2z"></path>
                            </svg>
                        </button>
                        @endif
                        <button onclick="resetearPassword({{ $usuario->id }}, '{{ $usuario->nombres }} {{ $usuario->apellido_paterno }}')"
                                class="text-blue-500 hover:text-blue-700 mr-2" title="Resetear contraseña">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </button>
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
                    <td colspan="6" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                        No hay usuarios registrados
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
</div>

<!-- Modal Agregar Usuario -->
<div id="modalAddUser" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl relative my-auto modal-content border-2 border-gray-200" style="max-height: 90vh; overflow-y: auto;">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-900">Agregar Usuario</h3>
        </div>
        <div class="p-6">
        
        <form id="formCrearUsuario" class="grid grid-cols-2 gap-4" autocomplete="off" onsubmit="crearUsuario(event)">
            @csrf
            <!-- DNI y Nombres -->
            <div class="col-span-2">
                <label for="dniUsuario" class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                <div class="flex gap-2">
                    <input type="text" id="dniUsuario" name="dniUsuario" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ingrese el DNI" maxlength="8" pattern="[0-9]{8}" required>
                    <button type="button" id="btnBuscarDNI" onclick="obtenerDNI()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200">
                        Buscar
                    </button>
                </div>
                <div id="dni-error" class="text-red-500 text-sm mt-1"></div>
            </div>
            
            <div>
                <label for="nombreUsuario" class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                <input type="text" id="nombreUsuario" name="nombreUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" 
                       readonly disabled maxlength="20" required>
            </div>

            <div>
                <label for="apellidoPaternoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno</label>
                <input type="text" id="apellidoPaternoUsuario" name="apellidoPaternoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" 
                       readonly disabled maxlength="20" required>
            </div>

            <div>
                <label for="apellidoMaternoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno</label>
                <input type="text" id="apellidoMaternoUsuario" name="apellidoMaternoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-100 cursor-not-allowed" 
                       readonly disabled maxlength="20" required>
            </div>

            <!-- Correo y Contraseña -->
            <div>
                <label for="correo_electronico" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input type="hidden" id="emailValidado" value="false">
                <input type="email" id="correo_electronico" name="correoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="Ingrese el correo electrónico" maxlength="45" required>
                <div id="email-feedback" class="mt-1 text-sm"></div>
                <div id="emailRequirements" class="mt-2 space-y-1"></div>
            </div>

            <div>
                <label for="contrasena" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <div class="relative">
                    <input type="password" id="contrasena" name="contrasenaUsuario" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Ingrese la contraseña" maxlength="25" required>
                    <button type="button" onclick="togglePassword('contrasena')" class="absolute right-2 top-2 text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </button>
                </div>
                <div id="passwordRequirements" class="mt-2 space-y-1"></div>
            </div>

            <!-- Dirección y Teléfono -->
            <div class="col-span-2">
                <label for="direccionUsuario" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input type="text" id="direccionUsuario" name="direccionUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       maxlength="35" required>
            </div>

            <div>
                <label for="telefonoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input type="tel" id="telefonoUsuario" name="telefonoUsuario" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       placeholder="9 dígitos" maxlength="9" pattern="[0-9]{9}" required>
            </div>

            <!-- Ubicación -->
            <div>
                <label for="departamentoUsuario" class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                <select id="departamentoUsuario" name="departamentoUsuario" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione un departamento</option>
                </select>
            </div>

            <div>
                <label for="provinciaUsuario" class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                <select id="provinciaUsuario" name="provinciaUsuario" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled>
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 1</option>
                </select>
            </div>

            <div>
                <label for="nivel2" class="block text-sm font-medium text-gray-700 mb-1">Nivel 2</label>
                <select id="nivel2" name="nivel2" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled>
                    <option value="">Seleccione Nivel 2</option>
                </select>
            </div>

            <div>
                <label for="nivel3" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3</label>
                <select id="nivel3" name="nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        disabled>
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

            <div class="col-span-2 flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalAddUser')" class="btn-secondary">Cancelar</button>
                <button type="submit" id="btnGuardarUsuario" class="btn-primary">Guardar</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div id="modalEditUser" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl relative my-auto modal-content border-2 border-gray-200" style="max-height: 90vh; overflow-y: auto;">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-900">Editar Usuario</h3>
        </div>
        <div class="p-6">
        
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

            <div class="col-span-2 flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalEditUser')" class="btn-secondary">Cancelar</button>
                <button type="button" onclick="actualizarUsuario()" class="btn-primary" id="btnEditarUsuario">Guardar Cambios</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Usuario -->
<div id="modalDeleteUser" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="px-6 py-4 border-b-2 border-gray-300">
            <h2 class="text-2xl font-bold text-red-600">Eliminar Usuario</h2>
        </div>
        <div class="px-6 py-4">
        <input type="hidden" id="deleteUserId">
        <p class="text-lg text-gray-700 mb-4">
            ¿Está seguro que desea eliminar el usuario <strong id="deleteUserName"></strong>?
        </p>
        
        <!-- Información de elementos relacionados -->
        <div id="deleteUserDependencias" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
            <p class="text-sm font-semibold text-yellow-800 mb-2">
                <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Se eliminarán también los siguientes elementos relacionados:
            </p>
            <ul id="deleteUserDependenciasList" class="text-sm text-yellow-700 list-disc list-inside space-y-1">
            </ul>
        </div>
        
        <p class="text-sm text-gray-500 mb-4">Esta acción no se puede deshacer.</p>
        </div>
        <div class="px-6 py-4 border-t-2 border-gray-300 flex justify-end space-x-2">
            <button type="button" onclick="closeModal('modalDeleteUser')" class="btn-secondary">Cancelar</button>
            <button type="button" onclick="confirmarEliminarUsuario()" class="btn-danger">Eliminar</button>
        </div>
    </div>
</div>

<!-- Modal Asignar Niveles Múltiples -->
<div id="modalAsignarNiveles" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative my-auto modal-content border-2 border-gray-200" style="max-height: 90vh; overflow-y: auto;">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h3 class="text-xl font-bold text-gray-900">Asignar Niveles</h3>
            <p class="text-sm text-gray-600 mt-1" id="modalNivelesUsuarioNombre"></p>
        </div>
        <div class="p-6">
            <input type="hidden" id="nivelesUsuarioId" name="id">
            
            <!-- Niveles asignados actualmente -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Niveles asignados actualmente</h4>
                <div id="nivelesAsignadosContainer" class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-4 bg-gray-50">
                    <div class="text-center text-gray-500 py-4" id="nivelesAsignadosEmpty">
                        <p>No hay niveles asignados</p>
                    </div>
                </div>
            </div>
            
            <!-- Agregar nuevo nivel -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">Agregar nivel</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label for="nuevoNivel" class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                        <select id="nuevoNivel" name="nivel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="cargarOpcionesNivel(this.value)">
                            <option value="">Seleccione nivel</option>
                            <option value="1">Nivel 1</option>
                            <option value="2">Nivel 2</option>
                            <option value="3">Nivel 3</option>
                            <option value="4">Nivel 4</option>
                        </select>
                    </div>
                    <div>
                        <label for="nuevoIdNivel" class="block text-sm font-medium text-gray-700 mb-1">Unidad</label>
                        <select id="nuevoIdNivel" name="id_nivel" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
                            <option value="">Primero seleccione nivel</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" onclick="agregarNivelUsuario()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200" disabled id="btnAgregarNivel">
                            Agregar
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalAsignarNiveles')" class="btn-secondary">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Funciones de modal
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.remove('hidden');
        // Prevenir scroll del body cuando el modal está abierto
        document.body.style.overflow = 'hidden';
        
        // Si es el modal de agregar usuario, limpiar el formulario
        if (modalId === 'modalAddUser') {
            limpiarFormularioAgregar();
        }
    }
    
    function limpiarFormularioAgregar() {
        // Limpiar todos los campos
        document.getElementById('formCrearUsuario').reset();
        
        // Limpiar campos readonly y restaurar botón
        const dniInput = document.getElementById('dniUsuario');
        const nombreInput = document.getElementById('nombreUsuario');
        const apellidoPaternoInput = document.getElementById('apellidoPaternoUsuario');
        const apellidoMaternoInput = document.getElementById('apellidoMaternoUsuario');
        const btnBuscar = document.querySelector('button[onclick="obtenerDNI()"]');
        
        dniInput.readOnly = false;
        dniInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        
        // Los campos de nombres y apellidos deben estar siempre disabled hasta que se valide el DNI
        nombreInput.readOnly = true;
        nombreInput.disabled = true;
        apellidoPaternoInput.readOnly = true;
        apellidoPaternoInput.disabled = true;
        apellidoMaternoInput.readOnly = true;
        apellidoMaternoInput.disabled = true;
        
        // Limpiar valores
        nombreInput.value = '';
        apellidoPaternoInput.value = '';
        apellidoMaternoInput.value = '';
        
        // Aplicar estilos de readonly
        nombreInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        apellidoPaternoInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        apellidoMaternoInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        
        if (btnBuscar) {
            btnBuscar.disabled = false;
            btnBuscar.textContent = 'Buscar';
            btnBuscar.classList.remove('bg-green-600', 'bg-gray-400');
            btnBuscar.classList.add('bg-blue-600', 'hover:bg-blue-700', 'transition', 'duration-200');
        }
        
        // Limpiar mensajes de error
        document.getElementById('dni-error').textContent = '';
        
        // Limpiar y resetear dropdowns de ubicación
        document.getElementById('provinciaUsuario').innerHTML = '<option value="">Seleccione una provincia</option>';
        document.getElementById('provinciaUsuario').disabled = true;
        document.getElementById('distritoUsuario').innerHTML = '<option value="">Seleccione un distrito</option>';
        document.getElementById('distritoUsuario').disabled = true;
        
        // Limpiar y resetear dropdowns de niveles
        document.getElementById('nivel2').innerHTML = '<option value="">Seleccione Nivel 2</option>';
        document.getElementById('nivel2').disabled = true;
        document.getElementById('nivel3').innerHTML = '<option value="">Seleccione Nivel 3</option>';
        document.getElementById('nivel3').disabled = true;
        document.getElementById('nivel4').innerHTML = '<option value="">Seleccione Nivel 4</option>';
        document.getElementById('nivel4').disabled = true;
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        // Restaurar scroll del body
        document.body.style.overflow = 'auto';
    }
    
    // Cerrar modal al hacer clic en el backdrop
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('bg-gray-900') || e.target.classList.contains('bg-opacity-75')) {
            const modal = e.target.closest('[id^="modal"]');
            if (modal) {
                closeModal(modal.id);
            }
        }
    });
    
    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = ['modalAddUser', 'modalEditUser', 'modalDeleteUser'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });

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
        
        // Event listeners para selects en cascada (ubicación)
        const deptoSelect = document.getElementById('departamentoUsuario');
        const provSelect = document.getElementById('provinciaUsuario');
        const distSelect = document.getElementById('distritoUsuario');
        
        if (deptoSelect) {
            deptoSelect.addEventListener('change', function() {
                cargarProvincias(this.value);
            });
        }
        if (provSelect) {
            provSelect.addEventListener('change', function() {
                cargarDistritos(this.value);
            });
        }
        
        // Event listeners para selects en cascada (niveles)
        const n1Select = document.getElementById('nivel1');
        const n2Select = document.getElementById('nivel2');
        const n3Select = document.getElementById('nivel3');
        
        if (n1Select) {
            n1Select.addEventListener('change', function() {
                cargarNivel2(this.value);
            });
        }
        if (n2Select) {
            n2Select.addEventListener('change', function() {
                cargarNivel3(this.value);
            });
        }
        if (n3Select) {
            n3Select.addEventListener('change', function() {
                cargarNivel4(this.value);
            });
        }
        
        // Validar formato DNI (solo números)
        const dniInput = document.getElementById('dniUsuario');
        if (dniInput) {
            dniInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 8) {
                    this.value = this.value.slice(0, 8);
                }
            });
        }
        
        // Validar formato teléfono (solo números)
        const telefonoInput = document.getElementById('telefonoUsuario');
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 9) {
                    this.value = this.value.slice(0, 9);
                }
            });
        }
        
        // Prevenir edición de nombres y apellidos (doble protección)
        const nombreInput = document.getElementById('nombreUsuario');
        const apellidoPaternoInput = document.getElementById('apellidoPaternoUsuario');
        const apellidoMaternoInput = document.getElementById('apellidoMaternoUsuario');
        
        [nombreInput, apellidoPaternoInput, apellidoMaternoInput].forEach(input => {
            if (input) {
                // Asegurar que siempre estén deshabilitados y readonly
                input.readOnly = true;
                input.disabled = true;
                input.classList.add('bg-gray-100', 'cursor-not-allowed');
                
                // Prevenir cualquier intento de edición
                input.addEventListener('keydown', function(e) {
                    e.preventDefault();
                    return false;
                });
                input.addEventListener('paste', function(e) {
                    e.preventDefault();
                    return false;
                });
                input.addEventListener('cut', function(e) {
                    e.preventDefault();
                    return false;
                });
                input.addEventListener('focus', function(e) {
                    this.blur();
                });
            }
        });
        
        // Inicializar validación de email
        initEmailValidation();
        
        // Inicializar validación de contraseña
        initPasswordValidation();
    });

    // Funciones para cargar ubicación (igual que en el registro)
    function cargarDepartamentos() {
        fetch('{{ route("api.departamentos") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
            const select = document.getElementById('departamentoUsuario');
                select.innerHTML = '<option value="">Seleccione un departamento</option>';
                data.data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.nombre;
                select.appendChild(option);
            });
            }
        })
        .catch(error => {
            console.error('Error al cargar departamentos:', error);
        });
    }

    function cargarProvincias(departamentoId) {
        const select = document.getElementById('provinciaUsuario');
        select.innerHTML = '<option value="">Seleccione una provincia</option>';
        select.disabled = true;
        
        if (!departamentoId) {
            limpiarDistritos();
            return;
        }
        
        fetch(`{{ route("api.provincias", ":id") }}`.replace(':id', departamentoId), {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.nombre;
                select.appendChild(option);
            });
            select.disabled = false;
            }
            limpiarDistritos();
        })
        .catch(error => {
            console.error('Error al cargar provincias:', error);
        });
    }

    function cargarDistritos(provinciaId) {
        const select = document.getElementById('distritoUsuario');
        select.innerHTML = '<option value="">Seleccione un distrito</option>';
        select.disabled = true;
        
        if (!provinciaId) return;
        
        fetch(`{{ route("api.distritos", ":id") }}`.replace(':id', provinciaId), {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(dist => {
                const option = document.createElement('option');
                option.value = dist.id;
                option.textContent = dist.nombre;
                select.appendChild(option);
            });
            select.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error al cargar distritos:', error);
        });
    }
    
    function limpiarDistritos() {
        const select = document.getElementById('distritoUsuario');
        select.innerHTML = '<option value="">Seleccione un distrito</option>';
        select.disabled = true;
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
        return fetch('{{ route("api.departamentos") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
            const select = document.getElementById('editDepartamentoUsuario');
                select.innerHTML = '<option value="">Seleccione un departamento</option>';
                data.data.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.nombre;
                select.appendChild(option);
            });
            }
            return data;
        })
        .catch(error => {
            console.error('Error al cargar departamentos:', error);
            throw error;
        });
    }

    function cargarProvinciasEdit(departamentoId) {
        const select = document.getElementById('editProvinciaUsuario');
        select.innerHTML = '<option value="">Seleccione una provincia</option>';
        select.disabled = true;
        
        if (!departamentoId) {
            limpiarDistritosEdit();
            return Promise.resolve();
        }
        
        return fetch(`{{ route("api.provincias", ":id") }}`.replace(':id', departamentoId), {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(prov => {
                const option = document.createElement('option');
                option.value = prov.id;
                option.textContent = prov.nombre;
                select.appendChild(option);
            });
                select.disabled = false;
            }
            limpiarDistritosEdit();
        })
        .catch(error => {
            console.error('Error al cargar provincias:', error);
            throw error;
        });
    }

    function cargarDistritosEdit(provinciaId) {
        const select = document.getElementById('editDistritoUsuario');
        select.innerHTML = '<option value="">Seleccione un distrito</option>';
        select.disabled = true;
        
        if (!provinciaId) {
            return Promise.resolve();
        }
        
        return fetch(`{{ route("api.distritos", ":id") }}`.replace(':id', provinciaId), {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(dist => {
                const option = document.createElement('option');
                option.value = dist.id;
                option.textContent = dist.nombre;
                select.appendChild(option);
            });
                select.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error al cargar distritos:', error);
            throw error;
        });
    }
    
    function limpiarDistritosEdit() {
        const select = document.getElementById('editDistritoUsuario');
        select.innerHTML = '<option value="">Seleccione un distrito</option>';
        select.disabled = true;
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

    // Función para obtener DNI (igual que en el registro)
    function obtenerDNI() {
        const dni = document.getElementById('dniUsuario').value;
        const btnBuscar = document.getElementById('btnBuscarDNI');
        
        if (!/^\d{8}$/.test(dni)) {
            document.getElementById('dni-error').textContent = 'El DNI debe tener 8 dígitos';
            return;
        }
        
        document.getElementById('dni-error').textContent = '';
        btnBuscar.disabled = true;
        btnBuscar.textContent = 'Validando...';
        
        fetch('{{ route("api.validar-dni") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ dni: dni })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Habilitar temporalmente los campos para poder establecer los valores
                const nombreInput = document.getElementById('nombreUsuario');
                const apellidoPaternoInput = document.getElementById('apellidoPaternoUsuario');
                const apellidoMaternoInput = document.getElementById('apellidoMaternoUsuario');
                
                nombreInput.disabled = false;
                apellidoPaternoInput.disabled = false;
                apellidoMaternoInput.disabled = false;
                
                // Llenar los campos con los datos obtenidos
                nombreInput.value = data.nombres || '';
                apellidoPaternoInput.value = data.apellidoPaterno || '';
                apellidoMaternoInput.value = data.apellidoMaterno || '';
                
                // Hacer los campos readonly y disabled como en el registro (no editables)
                document.getElementById('dniUsuario').readOnly = true;
                nombreInput.readOnly = true;
                nombreInput.disabled = true;
                apellidoPaternoInput.readOnly = true;
                apellidoPaternoInput.disabled = true;
                apellidoMaternoInput.readOnly = true;
                apellidoMaternoInput.disabled = true;
                
                // Aplicar estilo de readonly (bg-gray-100 y cursor-not-allowed)
                nombreInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                apellidoPaternoInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                apellidoMaternoInput.classList.add('bg-gray-100', 'cursor-not-allowed');
                
                btnBuscar.disabled = false;
                btnBuscar.textContent = '✓ Validado';
                btnBuscar.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                btnBuscar.classList.add('bg-green-600');
            } else {
                document.getElementById('dni-error').textContent = data.message || 'No se pudo validar el DNI';
                btnBuscar.disabled = false;
                btnBuscar.textContent = 'Buscar';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('dni-error').textContent = 'Error al validar el DNI. Por favor, inténtelo nuevamente.';
            btnBuscar.disabled = false;
            btnBuscar.textContent = 'Buscar';
        });
    }

    // Función para crear usuario
    function crearUsuario(event) {
        event.preventDefault();
        const form = event.target;
        
        // Obtener valores directamente del DOM porque los campos disabled no se envían en FormData
        const dniUsuario = document.getElementById('dniUsuario').value;
        const nombreUsuario = document.getElementById('nombreUsuario').value;
        const apellidoPaternoUsuario = document.getElementById('apellidoPaternoUsuario').value;
        const apellidoMaternoUsuario = document.getElementById('apellidoMaternoUsuario').value;
        const correoUsuario = document.getElementById('correo_electronico').value;
        const contrasenaUsuario = document.getElementById('contrasena').value;
        const direccionUsuario = document.getElementById('direccionUsuario').value;
        const telefonoUsuario = document.getElementById('telefonoUsuario').value;
        const distritoUsuario = document.getElementById('distritoUsuario').value;
        
        // Validar que los campos requeridos estén llenos
        if (!dniUsuario || !nombreUsuario || !apellidoPaternoUsuario || !apellidoMaternoUsuario) {
            alert('Debe validar el DNI primero para obtener los datos del usuario');
            return;
        }
        
        // Validar contraseña
        const passwordValid = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$/.test(contrasenaUsuario);
        if (!passwordValid) {
            alert('La contraseña no cumple con los requisitos. Debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial (@$!%*?&_)');
            return;
        }
        
        // Validar email
        const emailValidado = document.getElementById('emailValidado').value === 'true';
        if (!emailValidado) {
            alert('El correo electrónico no es válido o ya está registrado');
            return;
        }
        
        // Validar que se haya seleccionado al menos el Nivel 1
        const nivel1 = document.getElementById('nivel1').value;
        if (!nivel1 || nivel1 === '') {
            alert('Debe seleccionar al menos el Nivel 1 para el usuario');
            document.getElementById('nivel1').focus();
            return;
        }
        
        // Determinar nivel y id_nivel según la selección
        let nivel = null;
        let idNivel = null;
        
        const nivel4 = document.getElementById('nivel4').value;
        const nivel3 = document.getElementById('nivel3').value;
        const nivel2 = document.getElementById('nivel2').value;
        
        if (nivel4) {
            nivel = 4;
            idNivel = nivel4;
        } else if (nivel3) {
            nivel = 3;
            idNivel = nivel3;
        } else if (nivel2) {
            nivel = 2;
            idNivel = nivel2;
        } else {
            // Si solo hay nivel1, usar ese
            nivel = 1;
            idNivel = nivel1;
        }
        
        const data = {
            dniUsuario: dniUsuario,
            nombreUsuario: nombreUsuario,
            apellidoPaternoUsuario: apellidoPaternoUsuario,
            apellidoMaternoUsuario: apellidoMaternoUsuario,
            correoUsuario: correoUsuario,
            contrasenaUsuario: contrasenaUsuario,
            direccionUsuario: direccionUsuario,
            telefonoUsuario: telefonoUsuario,
            distritoUsuario: distritoUsuario,
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
                
                // Cargar ubicación si existe (usando promesas para asegurar que se carguen correctamente)
                console.log('Datos del usuario:', user); // Debug
                console.log('departamento_id:', user.departamento_id); // Debug
                console.log('provincia_id:', user.provincia_id); // Debug
                console.log('distrito_id:', user.distrito_id); // Debug
                
                if (user.departamento_id) {
                    // Asegurar que los departamentos estén cargados primero
                    cargarEditDepartamentos().then(() => {
                        const departamentoSelect = document.getElementById('editDepartamentoUsuario');
                        departamentoSelect.value = user.departamento_id;
                        console.log('Departamento seleccionado:', departamentoSelect.value); // Debug
                        return cargarProvinciasEdit(user.departamento_id);
                    }).then(() => {
                    if (user.provincia_id) {
                            const provinciaSelect = document.getElementById('editProvinciaUsuario');
                            provinciaSelect.value = user.provincia_id;
                            console.log('Provincia seleccionada:', provinciaSelect.value); // Debug
                            return cargarDistritosEdit(user.provincia_id);
                        }
                    }).then(() => {
                            if (user.distrito_id) {
                            const distritoSelect = document.getElementById('editDistritoUsuario');
                            distritoSelect.value = user.distrito_id;
                            console.log('Distrito seleccionado:', distritoSelect.value); // Debug
                            }
                    }).catch(error => {
                        console.error('Error al cargar ubicación:', error);
                    });
                } else {
                    console.warn('El usuario no tiene departamento_id asignado');
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
        
        // Ocultar el div de dependencias inicialmente
        const dependenciasDiv = document.getElementById('deleteUserDependencias');
        const dependenciasList = document.getElementById('deleteUserDependenciasList');
        
        if (dependenciasDiv) {
            dependenciasDiv.classList.add('hidden');
        }
        if (dependenciasList) {
            dependenciasList.innerHTML = '';
        }
        
        // Abrir el modal primero
        openModal('modalDeleteUser');
        
        // Cargar información de dependencias después de abrir el modal
        cargarDependenciasUsuario(id);
    }
    
    // Función para cargar dependencias del usuario
    function cargarDependenciasUsuario(id) {
        fetch(`/admin/usuarios/${id}/dependencias`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const dependenciasDiv = document.getElementById('deleteUserDependencias');
                const dependenciasList = document.getElementById('deleteUserDependenciasList');
                
                if (!dependenciasDiv || !dependenciasList) {
                    return;
                }
                
                if (data.success && data.data && data.data.tiene_dependencias && data.data.asignaciones_especialista > 0) {
                    dependenciasList.innerHTML = '';
                    const li = document.createElement('li');
                    li.textContent = `Asignaciones como especialista en procedimientos: ${data.data.asignaciones_especialista}`;
                    dependenciasList.appendChild(li);
                    dependenciasDiv.classList.remove('hidden');
                } else {
                    dependenciasDiv.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error al cargar dependencias:', error);
                const dependenciasDiv = document.getElementById('deleteUserDependencias');
                if (dependenciasDiv) {
                    dependenciasDiv.classList.add('hidden');
                }
            });
    }

    function toggleAdmin(id, esAdmin, nombre) {
        const accion = esAdmin ? 'quitar-admin' : 'hacer-admin';
        const mensaje = esAdmin
            ? `¿Quitar el rol de administrador a ${nombre}?`
            : `¿Hacer administrador a ${nombre}?`;
        if (!confirm(mensaje)) return;

        fetch(`/admin/usuarios/${id}/${accion}`, {
            method: 'POST',
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
            alert('Error al cambiar el rol de administrador');
        });
    }

    function resetearPassword(id, nombre) {
        if (!confirm(`¿Resetear la contraseña de ${nombre}? Se generará una nueva clave temporal.`)) return;

        fetch(`/admin/usuarios/${id}/resetear-password`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Nueva contraseña temporal para ' + nombre + ':\n\n' + data.data.nueva_contrasena + '\n\nEntréguela al usuario por un canal seguro.');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al resetear la contraseña');
        });
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
    
    // Validación de email en tiempo real (igual que en el registro)
    function initEmailValidation() {
        const emailInput = document.getElementById('correo_electronico');
        const emailRequirements = document.getElementById('emailRequirements');
        const emailFeedback = document.getElementById('email-feedback');
        
        if (!emailInput || !emailRequirements || !emailFeedback) {
            return; // Los elementos no existen aún
        }
        
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
        }

        emailInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const email = this.value;
            validateEmail(email);
            timeoutId = setTimeout(() => validateEmail(email), debounceTime);
        });
    }
    
    // Validación de contraseña en tiempo real (igual que en el registro)
    function initPasswordValidation() {
        const passwordInput = document.getElementById('contrasena');
        const passwordRequirements = document.getElementById('passwordRequirements');
        
        if (!passwordInput || !passwordRequirements) {
            return; // Los elementos no existen aún
        }
        
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
        });
    }

    // Funciones para gestión de niveles múltiples
    function abrirModalNiveles(id, nombre) {
        document.getElementById('nivelesUsuarioId').value = id;
        document.getElementById('modalNivelesUsuarioNombre').textContent = nombre;
        
        // Cargar niveles asignados
        cargarNivelesAsignados(id);
        
        // Resetear formulario de agregar
        document.getElementById('nuevoNivel').value = '';
        document.getElementById('nuevoIdNivel').innerHTML = '<option value="">Primero seleccione nivel</option>';
        document.getElementById('nuevoIdNivel').disabled = true;
        document.getElementById('btnAgregarNivel').disabled = true;
        
        openModal('modalAsignarNiveles');
    }

    function cargarNivelesAsignados(usuarioId) {
        fetch(`/admin/usuarios/${usuarioId}/niveles`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('nivelesAsignadosContainer');
            const emptyMsg = document.getElementById('nivelesAsignadosEmpty');
            
            if (data.success && data.data && data.data.length > 0) {
                emptyMsg.style.display = 'none';
                let html = '';
                data.data.forEach(nivel => {
                    const nivelLabels = { 1: 'Nivel 1', 2: 'Nivel 2', 3: 'Nivel 3', 4: 'Nivel 4' };
                    html += `
                        <div class="flex items-center justify-between p-2 bg-white border rounded">
                            <span class="font-medium text-gray-900">${nivelLabels[nivel.nivel] || 'Nivel ' + nivel.nivel}: ${nivel.nombre || 'N/A'}</span>
                            <button type="button" onclick="eliminarNivelUsuario(${usuarioId}, ${nivel.nivel})" 
                                    class="text-red-500 hover:text-red-700 text-sm" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    `;
                });
                container.innerHTML = html;
            } else {
                emptyMsg.style.display = 'block';
                container.innerHTML = '';
                container.appendChild(emptyMsg);
            }
        })
        .catch(error => {
            console.error('Error al cargar niveles:', error);
        });
    }

    function cargarOpcionesNivel(nivel) {
        const select = document.getElementById('nuevoIdNivel');
        const btn = document.getElementById('btnAgregarNivel');
        
        select.innerHTML = '<option value="">Cargando...</option>';
        select.disabled = true;
        btn.disabled = true;
        
        if (!nivel) {
            select.innerHTML = '<option value="">Primero seleccione nivel</option>';
            return;
        }
        
        let url = '';
        if (nivel === '1') {
            url = '{{ route("admin.niveles.nivel1") }}';
        } else if (nivel === '2') {
            url = '{{ route("admin.niveles.nivel2.all") }}';
        } else if (nivel === '3') {
            url = '{{ route("admin.niveles.nivel3.all") }}';
        } else if (nivel === '4') {
            url = '{{ route("admin.niveles.nivel4.all") }}';
        }
        
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.success && data.data) {
                    select.innerHTML = '<option value="">Seleccione unidad</option>';
                    data.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.nombre;
                        select.appendChild(opt);
                    });
                    select.disabled = false;
                    btn.disabled = false;
                } else {
                    select.innerHTML = '<option value="">Sin datos disponibles</option>';
                }
            })
            .catch(() => {
                select.innerHTML = '<option value="">Error al cargar</option>';
                select.disabled = false;
                btn.disabled = false;
            });
    }

    function agregarNivelUsuario() {
        const usuarioId = document.getElementById('nivelesUsuarioId').value;
        const nivel = document.getElementById('nuevoNivel').value;
        const idNivel = document.getElementById('nuevoIdNivel').value;
        
        if (!nivel || !idNivel) {
            alert('Seleccione nivel y unidad');
            return;
        }
        
        const btn = document.getElementById('btnAgregarNivel');
        btn.disabled = true;
        btn.textContent = 'Agregando...';
        
        fetch(`/admin/usuarios/${usuarioId}/niveles`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                niveles: [{ nivel: parseInt(nivel), id_nivel: parseInt(idNivel) }]
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar niveles asignados
                cargarNivelesAsignados(usuarioId);
                // Resetear formulario
                document.getElementById('nuevoNivel').value = '';
                document.getElementById('nuevoIdNivel').innerHTML = '<option value="">Primero seleccione nivel</option>';
                document.getElementById('nuevoIdNivel').disabled = true;
                btn.disabled = true;
                btn.textContent = 'Agregar';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al agregar nivel');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Agregar';
        });
    }

    function eliminarNivelUsuario(usuarioId, nivel) {
        if (!confirm('¿Eliminar este nivel del usuario?')) return;
        
        // Obtener niveles actuales y enviar lista sin el que se quiere eliminar
        fetch(`/admin/usuarios/${usuarioId}/niveles`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data) {
                const nuevosNiveles = data.data.filter(n => n.nivel !== nivel);
                
                fetch(`/admin/usuarios/${usuarioId}/niveles`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ niveles: nuevosNiveles })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        cargarNivelesAsignados(usuarioId);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        });
    }

    // Actualizar la lista de modales para ESC
    const originalModals = ['modalAddUser', 'modalEditUser', 'modalDeleteUser'];
    const allModals = ['modalAddUser', 'modalEditUser', 'modalDeleteUser', 'modalAsignarNiveles'];
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            allModals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });
</script>
@endpush
@endsection
