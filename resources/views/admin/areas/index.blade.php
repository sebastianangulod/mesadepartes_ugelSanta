{{--
    Vista de Administración de Áreas (Nivel 1)
    Equivalente a adminAreas.php del PHP puro
--}}
@extends('layouts.admin')

@section('title', 'Administración de Áreas - Mesa de Partes')

@push('styles')
<style>
    /* Estilos para modales - centrados en el área de contenido (sin sidebar) */
    #modalAddArea,
    #modalEditArea,
    #modalDeleteArea {
        backdrop-filter: blur(4px);
        /* Centrar solo en el área de contenido, considerando el sidebar de 250px */
        left: 250px;
        width: calc(100% - 250px);
    }
    
    /* Resaltar bordes del modal para mejor visibilidad */
    #modalAddArea > div,
    #modalEditArea > div,
    #modalDeleteArea > div {
        border: 2px solid #e5e7eb;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Borde más visible en hover o focus */
    #modalAddArea > div:focus-within,
    #modalEditArea > div:focus-within,
    #modalDeleteArea > div:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
    }
    
    /* En pantallas pequeñas, el modal debe ocupar toda la pantalla */
    @media (max-width: 768px) {
        #modalAddArea,
        #modalEditArea,
        #modalDeleteArea {
            left: 0;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Administración de Áreas</h2>
        <button onclick="openModal('modalAddArea')" class="btn-primary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Añadir Área
        </button>
    </div>

    <!-- Tabla de Áreas -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        ID
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Nombre
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Acciones
                    </th>
                </tr>
            </thead>
            <tbody id="areasTable">
                @forelse($areas as $area)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        {{ $area->id }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-medium text-gray-900">
                        {{ $area->nombre }}
                    </td>
                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                        <button onclick="editarArea({{ $area->id }}, '{{ $area->nombre }}')" 
                                class="text-green-500 hover:text-green-700 mr-3" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="eliminarArea({{ $area->id }}, '{{ $area->nombre }}')" 
                                class="text-red-500 hover:text-red-700" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center text-gray-500">
                        No hay áreas registradas
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Añadir Área -->
<div id="modalAddArea" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h2 class="text-xl font-bold text-gray-900">Añadir Área</h2>
        </div>
        <div class="p-6">
        <form id="formCrearArea" onsubmit="crearArea(event)">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nombreArea">
                    Nombre del Área
                </label>
                <input type="text" id="nombreArea" name="nombre" required
                       pattern="[A-Za-záéíóúñÁÉÍÓÚÑ\s]{3,55}"
                       title="El nombre debe contener solo letras y espacios, entre 3 y 55 caracteres"
                       placeholder="Nombre del Área" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <div id="errorNombreArea" class="text-red-500 text-sm mt-1 hidden"></div>
            </div>
                <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                    <button type="button" onclick="closeModal('modalAddArea')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Modal Editar Área -->
<div id="modalEditArea" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h2 class="text-xl font-bold text-gray-900">Editar Área</h2>
        </div>
        <div class="p-6">
        <form id="formEditarArea" onsubmit="actualizarArea(event)">
            @csrf
            @method('PUT')
            <input type="hidden" id="editAreaId" name="id">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="editAreaName">
                    Nombre del Área
                </label>
                <input type="text" id="editAreaName" name="nombre" required
                       pattern="[A-Za-záéíóúñÁÉÍÓÚÑ\s]{3,55}"
                       placeholder="Nombre del Área" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
                <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                <button type="button" onclick="closeModal('modalEditArea')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Actualizar</button>
            </div>
        </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Área -->
<div id="modalDeleteArea" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="px-6 py-4 border-b-2 border-gray-300">
            <h2 class="text-2xl font-bold text-red-600">Eliminar Área</h2>
        </div>
        <div class="px-6 py-4">
        <form id="formEliminarArea" onsubmit="confirmarEliminarArea(event)">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteAreaId" name="id">
            <p class="mb-4 text-gray-700">¿Estás seguro de que deseas eliminar el área <strong id="deleteAreaName"></strong>?</p>
            
            <!-- Información de elementos relacionados -->
            <div id="deleteAreaDependencias" class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg hidden">
                <p class="text-sm font-semibold text-yellow-800 mb-2">
                    <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Se eliminarán también los siguientes elementos relacionados:
                </p>
                <ul id="deleteAreaDependenciasList" class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                </ul>
            </div>
        </form>
        </div>
        <div class="px-6 py-4 border-t-2 border-gray-300 flex justify-end space-x-2">
            <button type="button" onclick="closeModal('modalDeleteArea')" class="btn-secondary">Cancelar</button>
            <button type="button" onclick="confirmarEliminarArea(event)" class="btn-danger">Eliminar</button>
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
            const modals = ['modalAddArea', 'modalEditArea', 'modalDeleteArea'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });

    function crearArea(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        fetch('{{ route("admin.areas.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
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
            alert('Error al crear el área');
        });
    }

    function editarArea(id, nombre) {
        document.getElementById('editAreaId').value = id;
        document.getElementById('editAreaName').value = nombre;
        openModal('modalEditArea');
    }

    function actualizarArea(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const id = document.getElementById('editAreaId').value;
        
        fetch(`/admin/areas/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nombre: document.getElementById('editAreaName').value
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
            alert('Error al actualizar el área');
        });
    }

    function eliminarArea(id, nombre) {
        document.getElementById('deleteAreaId').value = id;
        document.getElementById('deleteAreaName').textContent = nombre;
        
        // Cargar información de dependencias
        cargarDependenciasArea(id);
        
        openModal('modalDeleteArea');
    }
    
    // Función para cargar dependencias del área
    function cargarDependenciasArea(id) {
        fetch(`/admin/areas/${id}/dependencias`)
            .then(response => response.json())
            .then(data => {
                const dependenciasDiv = document.getElementById('deleteAreaDependencias');
                const dependenciasList = document.getElementById('deleteAreaDependenciasList');
                
                if (data.success && data.data.tiene_dependencias) {
                    dependenciasList.innerHTML = '';
                    const dependencias = data.data.dependencias;
                    
                    const labels = {
                        'nivel2': 'Niveles 2 (Subáreas)',
                        'procedimientos': 'Procedimientos'
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
                document.getElementById('deleteAreaDependencias').classList.add('hidden');
            });
    }

    function confirmarEliminarArea(event) {
        if (event) {
        event.preventDefault();
        }
        const id = document.getElementById('deleteAreaId').value;
        
        fetch(`/admin/areas/${id}`, {
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
            alert('Error al eliminar el área');
        });
    }
</script>
@endpush
@endsection

