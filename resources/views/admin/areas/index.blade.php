{{--
    Vista de Administración de Áreas (Nivel 1)
    Equivalente a adminAreas.php del PHP puro
--}}
@extends('layouts.app')

@section('title', 'Administración de Áreas - Mesa de Partes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <x-alerts />
    
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
<div id="modalAddArea" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">Añadir Área</h2>
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
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button" onclick="closeModal('modalAddArea')" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Área -->
<div id="modalEditArea" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4">Editar Área</h2>
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
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-success">Actualizar</button>
                <button type="button" onclick="closeModal('modalEditArea')" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar Área -->
<div id="modalDeleteArea" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Eliminar Área</h2>
        <form id="formEliminarArea" onsubmit="confirmarEliminarArea(event)">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteAreaId" name="id">
            <p class="mb-4 text-gray-700">¿Estás seguro de que deseas eliminar el área <strong id="deleteAreaName"></strong>?</p>
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-danger">Eliminar</button>
                <button type="button" onclick="closeModal('modalDeleteArea')" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

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
        openModal('modalDeleteArea');
    }

    function confirmarEliminarArea(event) {
        event.preventDefault();
        const form = event.target;
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

