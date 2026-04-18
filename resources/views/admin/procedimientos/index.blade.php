{{--
    Vista de Administración de Procedimientos
    Equivalente a adminProcedimientos.php del PHP puro
--}}
@extends('layouts.admin')

@section('title', 'Administración de Procedimientos - Mesa de Partes')

@push('styles')
<style>
    /* Estilos para modales - centrados en el área de contenido (sin sidebar) */
    #modalAddProcedimiento,
    #modalEditProcedimiento,
    #modalDeleteProcedimiento,
    #modalAsignarEspecialistas {
        backdrop-filter: blur(4px);
        /* Centrar solo en el área de contenido, considerando el sidebar de 250px */
        left: 250px;
        width: calc(100% - 250px);
    }
    
    /* Resaltar bordes del modal para mejor visibilidad */
    #modalAddProcedimiento > div,
    #modalEditProcedimiento > div,
    #modalDeleteProcedimiento > div,
    #modalAsignarEspecialistas > div {
        border: 2px solid #e5e7eb;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Borde más visible en hover o focus */
    #modalAddProcedimiento > div:focus-within,
    #modalEditProcedimiento > div:focus-within,
    #modalDeleteProcedimiento > div:focus-within,
    #modalAsignarEspecialistas > div:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
    }
    
    /* Asegurar que el contenido del modal sea scrollable */
    .modal-content-procedimiento {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #f7fafc;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-content-procedimiento::-webkit-scrollbar {
        width: 8px;
    }
    
    .modal-content-procedimiento::-webkit-scrollbar-track {
        background: #f7fafc;
    }
    
    .modal-content-procedimiento::-webkit-scrollbar-thumb {
        background-color: #cbd5e0;
        border-radius: 4px;
    }
    
    .modal-content-procedimiento::-webkit-scrollbar-thumb:hover {
        background-color: #a0aec0;
    }
    
    /* En pantallas pequeñas, el modal debe ocupar toda la pantalla */
    @media (max-width: 768px) {
        #modalAddProcedimiento,
        #modalEditProcedimiento,
        #modalDeleteProcedimiento,
        #modalAsignarEspecialistas {
            left: 0;
            width: 100%;
        }
    }
    
    /* Estilos para checkboxes de especialistas */
    .especialista-item {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        transition: background-color 0.2s;
    }
    
    .especialista-item:hover {
        background-color: #f9fafb;
    }
    
    .especialista-item:last-child {
        border-bottom: none;
    }
    
    .especialista-checkbox {
        margin-right: 0.75rem;
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Gestión de Procedimientos</h2>
        <button onclick="openModal('modalAddProcedimiento')" class="btn-primary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Crear Procedimiento
        </button>
    </div>

    <!-- Tabla de Procedimientos -->
    <div class="bg-white shadow-md rounded-lg p-6 overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nombre del Procedimiento</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Área (Nivel 1)</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Subárea (Nivel 2)</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nivel Final</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($procedimientos as $index => $procedimiento)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $index + 1 }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-medium">{{ $procedimiento->nombre }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $procedimiento->area->nombre ?? 'N/A' }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ $procedimiento->subarea->nombre ?? 'N/A' }}</td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        {{ $procedimiento->nivel_final_nombre ?? 'N/A' }} 
                        (Nivel {{ $procedimiento->nivel_final }})
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <div class="flex items-center space-x-2">
                            <button onclick="editarProcedimiento({{ $procedimiento->id }})" 
                                    class="text-green-500 hover:text-green-700" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>
                            <a href="{{ route('admin.procedimientos.flujo', $procedimiento->id) }}"
                                    class="text-purple-500 hover:text-purple-700 inline-flex" title="Flujo (nodos y equipos)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                                </svg>
                            </a>
                            <button onclick="asignarEspecialistas({{ $procedimiento->id }}, '{{ $procedimiento->nombre }}')" 
                                    class="text-blue-500 hover:text-blue-700" title="Asignar Especialistas">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </button>
                            <button onclick="eliminarProcedimiento({{ $procedimiento->id }}, '{{ $procedimiento->nombre }}')" 
                                    class="text-red-500 hover:text-red-700" title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                        No hay procedimientos registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Añadir Procedimiento -->
<div id="modalAddProcedimiento" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md relative my-auto modal-content-procedimiento border-2 border-gray-200">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h2 class="text-xl font-bold text-gray-900">Añadir Procedimiento</h2>
        </div>
        <div class="p-6">
        <form id="formCrearProcedimiento" onsubmit="crearProcedimiento(event)">
            @csrf
            <div class="mb-4">
                <label for="nombreProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Procedimiento</label>
                <input type="text" id="nombreProcedimiento" name="nombre" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required minlength="3" maxlength="255">
            </div>
            <div class="mb-4">
                <label for="areaProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 1 (Área)</label>
                <select id="areaProcedimiento" name="id_area" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required onchange="cargarSubareas(this.value)">
                    <option value="">Seleccione Nivel 1</option>
                    @foreach($nivel1 as $area)
                        <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="subareaProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 2 (Subárea)</label>
                <select id="subareaProcedimiento" name="id_subarea" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required disabled onchange="cargarNivelesFinales(this.value)">
                    <option value="">Seleccione Nivel 2</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel Final</label>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="nivel_final" value="3" class="form-radio text-blue-600" required onchange="toggleNivelFinal(3)">
                        <span class="ml-2">Nivel 3</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="nivel_final" value="4" class="form-radio text-blue-600" onchange="toggleNivelFinal(4)">
                        <span class="ml-2">Nivel 4</span>
                    </label>
                </div>
            </div>
            <div class="mb-4 nivel3-container" style="display: none;">
                <label for="nivel3Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3 <span class="text-red-500">*</span></label>
                <select id="nivel3Procedimiento" name="id_nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarNivel4DesdeNivel3(this.value)">
                    <option value="">Seleccione Nivel 3</option>
                </select>
            </div>
            <div class="mb-4 nivel4-container" style="display: none;">
                <label for="nivel4Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 4 <span class="text-red-500">*</span></label>
                <select id="nivel4Procedimiento" name="id_nivel_final" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 4</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalAddProcedimiento')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Modal Editar Procedimiento -->
<div id="modalEditProcedimiento" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md relative my-auto modal-content-procedimiento border-2 border-gray-200">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h2 class="text-xl font-bold text-gray-900">Editar Procedimiento</h2>
        </div>
        <div class="p-6">
        <form id="formEditarProcedimiento" onsubmit="actualizarProcedimiento(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="editProcedimientoId" name="id">
            <div class="mb-4">
                <label for="editNombreProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nombre del Procedimiento</label>
                <input type="text" id="editNombreProcedimiento" name="nombre" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                       required minlength="3" maxlength="255">
            </div>
            <div class="mb-4">
                <label for="editAreaProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 1 (Área)</label>
                <select id="editAreaProcedimiento" name="id_area" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required onchange="cargarSubareasEdit(this.value)">
                    <option value="">Seleccione Nivel 1</option>
                    @foreach($nivel1 as $area)
                        <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="editSubareaProcedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 2 (Subárea)</label>
                <select id="editSubareaProcedimiento" name="id_subarea" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        required onchange="cargarNivelesFinalesEdit(this.value)">
                    <option value="">Seleccione Nivel 2</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nivel Final</label>
                <div class="flex items-center space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" name="edit_nivel_final" value="3" class="form-radio text-blue-600" required onchange="toggleNivelFinalEdit(3)">
                        <span class="ml-2">Nivel 3</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="edit_nivel_final" value="4" class="form-radio text-blue-600" onchange="toggleNivelFinalEdit(4)">
                        <span class="ml-2">Nivel 4</span>
                    </label>
                </div>
            </div>
            <div class="mb-4 edit-nivel3-container" style="display: none;">
                <label for="editNivel3Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3 <span class="text-red-500">*</span></label>
                <select id="editNivel3Procedimiento" name="id_nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        onchange="cargarNivel4DesdeNivel3Edit(this.value)">
                    <option value="">Seleccione Nivel 3</option>
                </select>
            </div>
            <div class="mb-4 edit-nivel4-container" style="display: none;">
                <label for="editNivel4Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 4 <span class="text-red-500">*</span></label>
                <select id="editNivel4Procedimiento" name="id_nivel_final" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 4</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalEditProcedimiento')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Actualizar</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Modal Asignar Especialistas -->
<div id="modalAsignarEspecialistas" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative my-auto modal-content-procedimiento border-2 border-gray-200">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h2 class="text-xl font-bold text-gray-900">Asignar Especialistas</h2>
            <p class="text-sm text-gray-600 mt-1" id="modalProcedimientoNombre"></p>
        </div>
        <div class="p-6">
            <input type="hidden" id="asignarProcedimientoId" name="id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Seleccione los especialistas del nivel final del procedimiento:
                </label>
                <div id="especialistasContainer" class="max-h-96 overflow-y-auto border border-gray-300 rounded-md p-4">
                    <div class="text-center text-gray-500 py-8">
                        <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p>Cargando especialistas...</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalAsignarEspecialistas')" class="btn-secondary">Cancelar</button>
                <button type="button" onclick="guardarAsignacionEspecialistas(event)" class="btn-primary">Guardar Asignación</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar Procedimiento -->
<div id="modalDeleteProcedimiento" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="px-6 py-4 border-b-2 border-gray-300">
            <h2 class="text-2xl font-bold text-red-600">Eliminar Procedimiento</h2>
        </div>
        <div class="px-6 py-4">
            <input type="hidden" id="deleteProcedimientoId" name="id">
            <p class="mb-4 text-gray-700">¿Estás seguro de que deseas eliminar el procedimiento <strong id="deleteProcedimientoNombre"></strong>?</p>
            
            <!-- Información de elementos relacionados -->
            <div id="deleteProcedimientoDependencias" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                <p class="text-sm font-semibold text-yellow-800 mb-2">
                    <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Se eliminarán también los siguientes elementos relacionados:
                </p>
                <ul id="deleteProcedimientoDependenciasList" class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                </ul>
            </div>
        </div>
        <div class="px-6 py-4 border-t-2 border-gray-300 flex justify-end space-x-2">
            <button type="button" onclick="closeModal('modalDeleteProcedimiento')" class="btn-secondary">Cancelar</button>
            <button type="button" onclick="confirmarEliminarProcedimiento(event)" class="btn-danger">Eliminar</button>
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
            const modals = ['modalAddProcedimiento', 'modalEditProcedimiento', 'modalDeleteProcedimiento', 'modalAsignarEspecialistas'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });
    
    // Función para asignar especialistas
    function asignarEspecialistas(id, nombre) {
        document.getElementById('asignarProcedimientoId').value = id;
        document.getElementById('modalProcedimientoNombre').textContent = nombre;
        
        const container = document.getElementById('especialistasContainer');
        container.innerHTML = `
            <div class="text-center text-gray-500 py-8">
                <svg class="animate-spin h-8 w-8 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p>Cargando especialistas...</p>
            </div>
        `;
        
        openModal('modalAsignarEspecialistas');
        
        // Cargar especialistas disponibles
        fetch(`/admin/procedimientos/${id}/especialistas`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const usuarios = data.usuarios || [];
                const usuariosAsignados = data.usuariosAsignados || [];
                
                if (usuarios.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <p class="text-lg font-medium mb-2">No hay especialistas disponibles</p>
                            <p class="text-sm">No se encontraron usuarios internos asignados al nivel final de este procedimiento.</p>
                        </div>
                    `;
                    return;
                }
                
                let html = '<div class="space-y-2">';
                usuarios.forEach(usuario => {
                    const isChecked = usuariosAsignados.includes(usuario.id);
                    html += `
                        <label class="especialista-item flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   class="especialista-checkbox" 
                                   value="${usuario.id}" 
                                   ${isChecked ? 'checked' : ''}>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">
                                    ${usuario.nombres} ${usuario.apellido_paterno} ${usuario.apellido_materno || ''}
                                </div>
                                <div class="text-sm text-gray-500">${usuario.email}</div>
                            </div>
                        </label>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center text-red-500 py-8">
                        <p class="text-lg font-medium mb-2">Error al cargar especialistas</p>
                        <p class="text-sm">${data.message || 'Ocurrió un error inesperado'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="text-center text-red-500 py-8">
                    <p class="text-lg font-medium mb-2">Error al cargar especialistas</p>
                    <p class="text-sm">No se pudo conectar con el servidor. Intente nuevamente.</p>
                </div>
            `;
        });
    }
    
    // Función para guardar la asignación de especialistas
    function guardarAsignacionEspecialistas(event) {
        if (event) {
            event.preventDefault();
        }
        
        const procedimientoId = document.getElementById('asignarProcedimientoId').value;
        const checkboxes = document.querySelectorAll('#especialistasContainer input[type="checkbox"]:checked');
        const usuarios = Array.from(checkboxes).map(cb => parseInt(cb.value));
        
        fetch(`/admin/procedimientos/${procedimientoId}/asignar-especialistas`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                usuarios: usuarios
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Especialistas asignados correctamente');
                closeModal('modalAsignarEspecialistas');
            } else {
                alert('Error: ' + (data.message || 'No se pudieron asignar los especialistas'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al asignar especialistas. Intente nuevamente.');
        });
    }

    function cargarSubareas(areaId) {
        const subareaSelect = document.getElementById('subareaProcedimiento');
        subareaSelect.innerHTML = '<option value="">Seleccione Nivel 2</option>';
        subareaSelect.disabled = true;
        
        if (!areaId) return;
        
        fetch(`/api/niveles/nivel2/${areaId}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel2 => {
                    const option = document.createElement('option');
                    option.value = nivel2.id;
                    option.textContent = nivel2.nombre;
                    subareaSelect.appendChild(option);
                });
                subareaSelect.disabled = false;
            }
        });
    }

    function cargarNivelesFinales(subareaId) {
        const nivel3Select = document.getElementById('nivel3Procedimiento');
        const nivel4Select = document.getElementById('nivel4Procedimiento');
        
        nivel3Select.innerHTML = '<option value="">Seleccione Nivel 3</option>';
        nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
        
        if (!subareaId) return;
        
        // Cargar niveles 3 basados en el nivel 2 (subarea) seleccionado
        fetch(`/api/niveles/nivel3/${subareaId}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel3 => {
                    const option = document.createElement('option');
                    option.value = nivel3.id;
                    option.textContent = nivel3.nombre;
                    nivel3Select.appendChild(option);
                });
            }
        });
    }
    
    // Cargar nivel 4 cuando se selecciona un nivel 3 (solo si nivel_final = 4)
    function cargarNivel4DesdeNivel3(nivel3Id) {
        const nivel4Select = document.getElementById('nivel4Procedimiento');
        const nivelFinalRadio = document.querySelector('input[name="nivel_final"]:checked');
        
        // Solo cargar nivel 4 si el nivel final seleccionado es 4
        if (!nivelFinalRadio || nivelFinalRadio.value !== '4') {
            nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
            return;
        }
        
        nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
        nivel4Select.disabled = true;
        
        if (!nivel3Id) return;
        
        fetch(`/api/niveles/nivel4/${nivel3Id}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                data.data.forEach(nivel4 => {
                    const option = document.createElement('option');
                    option.value = nivel4.id;
                    option.textContent = nivel4.nombre;
                    nivel4Select.appendChild(option);
                });
                nivel4Select.disabled = false;
            }
        });
    }

    function toggleNivelFinal(nivel) {
        const nivel3Container = document.querySelector('.nivel3-container');
        const nivel4Container = document.querySelector('.nivel4-container');
        const nivel3Select = document.getElementById('nivel3Procedimiento');
        const nivel4Select = document.getElementById('nivel4Procedimiento');
        const subareaSelect = document.getElementById('subareaProcedimiento');
        
        // Siempre mostrar nivel 3 (es necesario para ambos casos)
        nivel3Container.style.display = 'block';
        
        if (nivel === 3) {
            // Si termina en nivel 3, solo mostrar selector de nivel 3
            nivel4Container.style.display = 'none';
            nivel3Select.required = true;
            nivel4Select.required = false;
            nivel4Select.value = ''; // Limpiar valor
        } else if (nivel === 4) {
            // Si termina en nivel 4, mostrar ambos pero nivel 4 depende de nivel 3
            nivel4Container.style.display = 'block';
            nivel3Select.required = true; // Nivel 3 es obligatorio para llegar a nivel 4
            nivel4Select.required = true;
            nivel4Select.disabled = true; // Deshabilitar hasta que se seleccione nivel 3
            
            // Si ya hay un nivel 3 seleccionado, cargar sus niveles 4
            if (nivel3Select.value) {
                cargarNivel4DesdeNivel3(nivel3Select.value);
            }
        }
        
        // Si hay subarea seleccionada, recargar niveles 3
        if (subareaSelect.value) {
            cargarNivelesFinales(subareaSelect.value);
        }
    }

    function crearProcedimiento(event) {
        event.preventDefault();
        const form = event.target;
        
        // Obtener valores directamente de los elementos del formulario
        const nombre = document.getElementById('nombreProcedimiento').value.trim();
        const idArea = document.getElementById('areaProcedimiento').value;
        const idSubarea = document.getElementById('subareaProcedimiento').value;
        const nivelFinal = form.querySelector('input[name="nivel_final"]:checked')?.value;
        
        // Validar nombre
        if (!nombre || nombre.length < 3) {
            alert('El nombre del procedimiento es obligatorio y debe tener al menos 3 caracteres');
            document.getElementById('nombreProcedimiento').focus();
            return;
        }
        
        // Validar área
        if (!idArea) {
            alert('Debe seleccionar un Nivel 1 (Área)');
            return;
        }
        
        // Validar subárea
        if (!idSubarea) {
            alert('Debe seleccionar un Nivel 2 (Subárea)');
            return;
        }
        
        // Validar que se haya seleccionado un nivel final
        if (!nivelFinal || (nivelFinal !== '3' && nivelFinal !== '4')) {
            alert('Debe seleccionar si el procedimiento termina en Nivel 3 o Nivel 4');
            return;
        }
        
        // Obtener el ID del nivel final según la selección
        let idNivelFinal = null;
        if (nivelFinal === '3') {
            idNivelFinal = document.getElementById('nivel3Procedimiento').value;
            if (!idNivelFinal) {
                alert('Debe seleccionar un Nivel 3');
                return;
            }
        } else if (nivelFinal === '4') {
            const idNivel3 = document.getElementById('nivel3Procedimiento').value;
            idNivelFinal = document.getElementById('nivel4Procedimiento').value;
            if (!idNivel3) {
                alert('Debe seleccionar primero un Nivel 3');
                return;
            }
            if (!idNivelFinal) {
                alert('Debe seleccionar un Nivel 4');
                return;
            }
        }
        
        fetch('{{ route("admin.procedimientos.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nombre: nombre,
                id_area: idArea,
                id_subarea: idSubarea,
                nivel_final: nivelFinal,
                id_nivel_final: idNivelFinal
            })
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
            alert('Error al crear el procedimiento');
        });
    }

    function editarProcedimiento(id) {
        fetch(`/admin/procedimientos/${id}/obtener`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const proc = data.data;
                document.getElementById('editProcedimientoId').value = id;
                document.getElementById('editNombreProcedimiento').value = proc.nombre;
                document.getElementById('editAreaProcedimiento').value = proc.id_area;
                
                // Cargar subáreas y niveles
                cargarSubareasEdit(proc.id_area, proc.id_subarea).then(() => {
                    // Configurar nivel final después de cargar los niveles
                    if (proc.nivel_final == '3') {
                        document.querySelector('input[name="edit_nivel_final"][value="3"]').checked = true;
                        toggleNivelFinalEdit(3);
                        // Establecer el nivel 3 seleccionado
                        setTimeout(() => {
                            const nivel3Select = document.getElementById('editNivel3Procedimiento');
                            if (nivel3Select && proc.id_nivel_final) {
                                nivel3Select.value = proc.id_nivel_final;
                            }
                        }, 500);
                    } else if (proc.nivel_final == '4') {
                        document.querySelector('input[name="edit_nivel_final"][value="4"]').checked = true;
                        toggleNivelFinalEdit(4);
                        // Establecer el nivel 3 y luego el nivel 4
                        setTimeout(() => {
                            const nivel3Select = document.getElementById('editNivel3Procedimiento');
                            const nivel4Select = document.getElementById('editNivel4Procedimiento');
                            
                            // El backend ya devuelve id_nivel3 cuando nivel_final es 4
                            if (proc.id_nivel3) {
                                nivel3Select.value = proc.id_nivel3;
                                cargarNivel4DesdeNivel3Edit(proc.id_nivel3).then(() => {
                                    setTimeout(() => {
                                        if (proc.id_nivel_final) {
                                            nivel4Select.value = proc.id_nivel_final;
                                        }
                                    }, 300);
                                });
                            }
                        }, 500);
                    }
                });
                
                openModal('modalEditProcedimiento');
            }
        });
    }

    function cargarSubareasEdit(areaId, subareaId = null) {
        return new Promise((resolve) => {
            const subareaSelect = document.getElementById('editSubareaProcedimiento');
            subareaSelect.innerHTML = '<option value="">Seleccione Nivel 2</option>';
            
            if (!areaId) {
                resolve();
                return;
            }
            
            fetch(`/api/niveles/nivel2/${areaId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    data.data.forEach(nivel2 => {
                        const option = document.createElement('option');
                        option.value = nivel2.id;
                        option.textContent = nivel2.nombre;
                        if (subareaId && nivel2.id == subareaId) {
                            option.selected = true;
                        }
                        subareaSelect.appendChild(option);
                    });
                    if (subareaId) {
                        cargarNivelesFinalesEdit(subareaId).then(() => resolve());
                    } else {
                        resolve();
                    }
                } else {
                    resolve();
                }
            })
            .catch(() => resolve());
        });
    }

    function cargarNivelesFinalesEdit(subareaId) {
        return new Promise((resolve) => {
            const nivel3Select = document.getElementById('editNivel3Procedimiento');
            const nivel4Select = document.getElementById('editNivel4Procedimiento');
            
            nivel3Select.innerHTML = '<option value="">Seleccione Nivel 3</option>';
            nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
            
            if (!subareaId) {
                resolve();
                return;
            }
            
            fetch(`/api/niveles/nivel3/${subareaId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    data.data.forEach(nivel3 => {
                        const option = document.createElement('option');
                        option.value = nivel3.id;
                        option.textContent = nivel3.nombre;
                        nivel3Select.appendChild(option);
                    });
                }
                resolve();
            })
            .catch(() => resolve());
        });
    }
    
    // Cargar nivel 4 cuando se selecciona un nivel 3 en edición (solo si nivel_final = 4)
    function cargarNivel4DesdeNivel3Edit(nivel3Id) {
        return new Promise((resolve) => {
            const nivel4Select = document.getElementById('editNivel4Procedimiento');
            const nivelFinalRadio = document.querySelector('input[name="edit_nivel_final"]:checked');
            
            // Solo cargar nivel 4 si el nivel final seleccionado es 4
            if (!nivelFinalRadio || nivelFinalRadio.value !== '4') {
                nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
                resolve();
                return;
            }
            
            nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
            nivel4Select.disabled = true;
            
            if (!nivel3Id) {
                resolve();
                return;
            }
            
            fetch(`/api/niveles/nivel4/${nivel3Id}`, {
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    data.data.forEach(nivel4 => {
                        const option = document.createElement('option');
                        option.value = nivel4.id;
                        option.textContent = nivel4.nombre;
                        nivel4Select.appendChild(option);
                    });
                    nivel4Select.disabled = false;
                }
                resolve();
            })
            .catch(() => resolve());
        });
    }

    function toggleNivelFinalEdit(nivel) {
        const nivel3Container = document.querySelector('.edit-nivel3-container');
        const nivel4Container = document.querySelector('.edit-nivel4-container');
        const nivel3Select = document.getElementById('editNivel3Procedimiento');
        const nivel4Select = document.getElementById('editNivel4Procedimiento');
        const subareaSelect = document.getElementById('editSubareaProcedimiento');
        
        // Siempre mostrar nivel 3 (es necesario para ambos casos)
        nivel3Container.style.display = 'block';
        
        if (nivel === 3) {
            // Si termina en nivel 3, solo mostrar selector de nivel 3
            nivel4Container.style.display = 'none';
            nivel3Select.required = true;
            nivel4Select.required = false;
            nivel4Select.value = ''; // Limpiar valor
        } else if (nivel === 4) {
            // Si termina en nivel 4, mostrar ambos pero nivel 4 depende de nivel 3
            nivel4Container.style.display = 'block';
            nivel3Select.required = true; // Nivel 3 es obligatorio para llegar a nivel 4
            nivel4Select.required = true;
            nivel4Select.disabled = true; // Deshabilitar hasta que se seleccione nivel 3
            
            // Si ya hay un nivel 3 seleccionado, cargar sus niveles 4
            if (nivel3Select.value) {
                cargarNivel4DesdeNivel3Edit(nivel3Select.value);
            }
        }
        
        // Si hay subarea seleccionada, recargar niveles 3
        if (subareaSelect.value) {
            cargarNivelesFinalesEdit(subareaSelect.value);
        }
    }

    function actualizarProcedimiento(event) {
        event.preventDefault();
        const form = event.target;
        
        // Obtener valores directamente de los elementos del formulario
        const id = document.getElementById('editProcedimientoId').value;
        const nombre = document.getElementById('editNombreProcedimiento').value.trim();
        const idArea = document.getElementById('editAreaProcedimiento').value;
        const idSubarea = document.getElementById('editSubareaProcedimiento').value;
        const nivelFinal = form.querySelector('input[name="edit_nivel_final"]:checked')?.value;
        
        // Validar nombre
        if (!nombre || nombre.length < 3) {
            alert('El nombre del procedimiento es obligatorio y debe tener al menos 3 caracteres');
            document.getElementById('editNombreProcedimiento').focus();
            return;
        }
        
        // Validar área
        if (!idArea) {
            alert('Debe seleccionar un Nivel 1 (Área)');
            return;
        }
        
        // Validar subárea
        if (!idSubarea) {
            alert('Debe seleccionar un Nivel 2 (Subárea)');
            return;
        }
        
        // Validar que se haya seleccionado un nivel final
        if (!nivelFinal || (nivelFinal !== '3' && nivelFinal !== '4')) {
            alert('Debe seleccionar si el procedimiento termina en Nivel 3 o Nivel 4');
            return;
        }
        
        // Obtener el ID del nivel final según la selección
        let idNivelFinal = null;
        if (nivelFinal === '3') {
            idNivelFinal = document.getElementById('editNivel3Procedimiento').value;
            if (!idNivelFinal) {
                alert('Debe seleccionar un Nivel 3');
                return;
            }
        } else if (nivelFinal === '4') {
            const idNivel3 = document.getElementById('editNivel3Procedimiento').value;
            idNivelFinal = document.getElementById('editNivel4Procedimiento').value;
            if (!idNivel3) {
                alert('Debe seleccionar primero un Nivel 3');
                return;
            }
            if (!idNivelFinal) {
                alert('Debe seleccionar un Nivel 4');
                return;
            }
        }
        
        fetch(`/admin/procedimientos/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nombre: nombre,
                id_area: idArea,
                id_subarea: idSubarea,
                nivel_final: nivelFinal,
                id_nivel_final: idNivelFinal
            })
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
            alert('Error al actualizar el procedimiento');
        });
    }

    function eliminarProcedimiento(id, nombre) {
        document.getElementById('deleteProcedimientoId').value = id;
        document.getElementById('deleteProcedimientoNombre').textContent = nombre;
        
        // Cargar información de dependencias
        cargarDependenciasProcedimiento(id);
        
        openModal('modalDeleteProcedimiento');
    }
    
    // Función para cargar dependencias del procedimiento
    function cargarDependenciasProcedimiento(id) {
        fetch(`/admin/procedimientos/${id}/dependencias`)
            .then(response => response.json())
            .then(data => {
                const dependenciasDiv = document.getElementById('deleteProcedimientoDependencias');
                const dependenciasList = document.getElementById('deleteProcedimientoDependenciasList');
                
                if (data.success && data.data.tiene_dependencias) {
                    dependenciasList.innerHTML = '';
                    const dependencias = data.data.dependencias;
                    
                    const labels = {
                        'tramites': 'Trámites asociados',
                        'usuarios_asignados': 'Usuarios especialistas asignados'
                    };
                    
                    Object.keys(dependencias).forEach(key => {
                        if (dependencias[key] > 0) {
                            const li = document.createElement('li');
                            li.textContent = `${labels[key] || key}: ${dependencias[key]}`;
                            dependenciasList.appendChild(li);
                        }
                    });
                    
                    dependenciasDiv.classList.remove('hidden');
                } else {
                    dependenciasDiv.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error al cargar dependencias:', error);
                document.getElementById('deleteProcedimientoDependencias').classList.add('hidden');
            });
    }

    function confirmarEliminarProcedimiento(event) {
        if (event) {
            event.preventDefault();
        }
        const id = document.getElementById('deleteProcedimientoId').value;
        
        fetch(`/admin/procedimientos/${id}`, {
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
            alert('Error al eliminar el procedimiento');
        });
    }
</script>
@endpush
@endsection

