{{--
    Vista de Administración de Niveles (2, 3, 4)
    Equivalente a adminNiveles.php del PHP puro
--}}
@extends('layouts.app')

@section('title', 'Administración de Niveles - Mesa de Partes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <x-alerts />
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Administración de Niveles</h2>
    </div>
    
    <div class="content-body">
        <div class="niveles-container">
            <div id="nivelesContainer">
                @foreach($niveles1 as $nivel1)
                <div class="mb-6 bg-white rounded-lg shadow-md p-6">
                    <!-- Nivel 1 -->
                    <div class="flex items-center justify-between mb-4 pb-4 border-b-2 border-blue-200">
                        <div>
                            <h3 class="text-xl font-bold text-blue-600">Nivel 1: {{ $nivel1->nombre }}</h3>
                            <p class="text-sm text-gray-500">ID: {{ $nivel1->id }}</p>
                        </div>
                        <button onclick="agregarNivel(2, {{ $nivel1->id }})" 
                                class="btn-primary text-sm">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Agregar Nivel 2
                        </button>
                    </div>

                    @if($nivel1->nivel2->count() > 0)
                        @foreach($nivel1->nivel2 as $nivel2)
                        <div class="ml-6 mb-4 bg-gray-50 rounded-lg p-4">
                            <!-- Nivel 2 -->
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-green-200">
                                <div>
                                    <h4 class="text-lg font-semibold text-green-600">Nivel 2: {{ $nivel2->nombre }}</h4>
                                    <p class="text-xs text-gray-500">ID: {{ $nivel2->id }}</p>
                                </div>
                                <div class="space-x-2">
                                    <button onclick="editarNivel('nivel_2', {{ $nivel2->id }}, '{{ $nivel2->nombre }}')" 
                                            class="text-green-500 hover:text-green-700" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="eliminarNivel('nivel_2', {{ $nivel2->id }}, '{{ $nivel2->nombre }}')" 
                                            class="text-red-500 hover:text-red-700" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                    <button onclick="agregarNivel(3, {{ $nivel2->id }})" 
                                            class="btn-primary text-xs">
                                        + Nivel 3
                                    </button>
                                </div>
                            </div>

                            @if($nivel2->nivel3->count() > 0)
                                @foreach($nivel2->nivel3 as $nivel3)
                                <div class="ml-6 mb-3 bg-white rounded p-3">
                                    <!-- Nivel 3 -->
                                    <div class="flex items-center justify-between mb-2 pb-2 border-b border-yellow-200">
                                        <div>
                                            <h5 class="text-md font-medium text-yellow-600">Nivel 3: {{ $nivel3->nombre }}</h5>
                                            <p class="text-xs text-gray-500">ID: {{ $nivel3->id }}</p>
                                        </div>
                                        <div class="space-x-2">
                                            <button onclick="editarNivel('nivel_3', {{ $nivel3->id }}, '{{ $nivel3->nombre }}')" 
                                                    class="text-yellow-500 hover:text-yellow-700" title="Editar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="eliminarNivel('nivel_3', {{ $nivel3->id }}, '{{ $nivel3->nombre }}')" 
                                                    class="text-red-500 hover:text-red-700" title="Eliminar">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                            <button onclick="agregarNivel(4, {{ $nivel3->id }})" 
                                                    class="btn-primary text-xs">
                                                + Nivel 4
                                            </button>
                                        </div>
                                    </div>

                                    @if($nivel3->nivel4->count() > 0)
                                        @foreach($nivel3->nivel4 as $nivel4)
                                        <div class="ml-6 bg-gray-50 rounded p-2">
                                            <!-- Nivel 4 -->
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h6 class="text-sm font-medium text-purple-600">Nivel 4: {{ $nivel4->nombre }}</h6>
                                                    <p class="text-xs text-gray-500">ID: {{ $nivel4->id }}</p>
                                                </div>
                                                <div class="space-x-2">
                                                    <button onclick="editarNivel('nivel_4', {{ $nivel4->id }}, '{{ $nivel4->nombre }}')" 
                                                            class="text-purple-500 hover:text-purple-700" title="Editar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                    </button>
                                                    <button onclick="eliminarNivel('nivel_4', {{ $nivel4->id }}, '{{ $nivel4->nombre }}')" 
                                                            class="text-red-500 hover:text-red-700" title="Eliminar">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    @endif
                                </div>
                                @endforeach
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar/Editar Nivel -->
<div id="modalNivel" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4" id="modalNivelTitulo">Crear Nivel</h2>
        <form id="formNivel" onsubmit="guardarNivel(event)">
            @csrf
            <input type="hidden" id="nivelTipo" name="nivel">
            <input type="hidden" id="nivelId" name="id">
            <input type="hidden" id="nivelParent" name="id_parent">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nombreNivel">
                    Nombre del Nivel
                </label>
                <input type="text" id="nombreNivel" name="nombre" required
                       placeholder="Nombre del Nivel" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-primary" id="btnGuardarNivel">Guardar</button>
                <button type="button" onclick="closeModal('modalNivel')" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar Nivel -->
<div id="modalDeleteNivel" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-4 text-red-600">Eliminar Nivel</h2>
        <form id="formEliminarNivel" onsubmit="confirmarEliminarNivel(event)">
            @csrf
            <input type="hidden" id="deleteNivelTipo" name="nivel">
            <input type="hidden" id="deleteNivelId" name="id">
            
            <p class="mb-4 text-gray-700">
                ¿Estás seguro de que deseas eliminar el nivel <strong id="deleteNivelNombre"></strong>?
            </p>
            
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-danger">Eliminar</button>
                <button type="button" onclick="closeModal('modalDeleteNivel')" class="btn-secondary">Cancelar</button>
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

    function agregarNivel(nivel, parentId) {
        const nivelTable = `nivel_${nivel}`;
        document.getElementById('modalNivelTitulo').textContent = `Crear Nivel ${nivel}`;
        document.getElementById('nivelTipo').value = nivelTable;
        document.getElementById('nivelId').value = '';
        document.getElementById('nivelParent').value = parentId;
        document.getElementById('nombreNivel').value = '';
        document.getElementById('btnGuardarNivel').textContent = 'Guardar';
        openModal('modalNivel');
    }

    function editarNivel(nivel, id, nombre) {
        document.getElementById('modalNivelTitulo').textContent = 'Editar Nivel';
        document.getElementById('nivelTipo').value = nivel;
        document.getElementById('nivelId').value = id;
        document.getElementById('nivelParent').value = '';
        document.getElementById('nombreNivel').value = nombre;
        document.getElementById('btnGuardarNivel').textContent = 'Actualizar';
        openModal('modalNivel');
    }

    function guardarNivel(event) {
        event.preventDefault();
        const form = event.target;
        const nivel = document.getElementById('nivelTipo').value;
        const id = document.getElementById('nivelId').value;
        const formData = new FormData(form);
        
        const url = id ? `/admin/niveles/${id}` : '/admin/niveles';
        const method = id ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nivel: nivel,
                nombre: document.getElementById('nombreNivel').value,
                id_parent: document.getElementById('nivelParent').value || null
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
            alert('Error al guardar el nivel');
        });
    }

    function eliminarNivel(nivel, id, nombre) {
        document.getElementById('deleteNivelTipo').value = nivel;
        document.getElementById('deleteNivelId').value = id;
        document.getElementById('deleteNivelNombre').textContent = nombre;
        openModal('modalDeleteNivel');
    }

    function confirmarEliminarNivel(event) {
        event.preventDefault();
        const nivel = document.getElementById('deleteNivelTipo').value;
        const id = document.getElementById('deleteNivelId').value;
        
        fetch(`/admin/niveles/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nivel: nivel
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
            alert('Error al eliminar el nivel');
        });
    }
</script>
@endpush
@endsection

