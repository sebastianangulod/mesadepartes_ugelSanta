{{--
    Vista de Administración de Niveles (2, 3, 4)
    Equivalente a adminNiveles.php del PHP puro
--}}
@extends('layouts.admin')

@section('title', 'Administración de Niveles - Mesa de Partes')

@push('styles')
<style>
    /* Estilos para modales - centrados en el área de contenido (sin sidebar) */
    #modalNivel,
    #modalDeleteNivel {
        backdrop-filter: blur(4px);
        /* Centrar solo en el área de contenido, considerando el sidebar de 250px */
        left: 250px;
        width: calc(100% - 250px);
    }
    
    /* Resaltar bordes del modal para mejor visibilidad */
    #modalNivel > div,
    #modalDeleteNivel > div {
        border: 2px solid #e5e7eb;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Borde más visible en hover o focus */
    #modalNivel > div:focus-within,
    #modalDeleteNivel > div:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
    }
    
    /* En pantallas pequeñas, el modal debe ocupar toda la pantalla */
    @media (max-width: 768px) {
        #modalNivel,
        #modalDeleteNivel {
            left: 0;
            width: 100%;
        }
    }
    
    /* Estilos para niveles colapsables */
    .nivel-header {
        position: relative;
        padding-left: 24px;
    }
    
    .nivel-header.expandible {
        cursor: pointer;
    }
    
    .nivel-header.expandible:hover {
        background-color: rgba(0, 0, 0, 0.02);
        border-radius: 4px;
    }
    
    .nivel-header:not(.expandible) {
        cursor: default;
    }
    
    .nivel-header::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
    }
    
    .nivel-header.expandible::before {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'/%3E%3C/svg%3E");
        transition: transform 0.2s;
    }
    
    .nivel-header.expandible.expanded::before {
        transform: translateY(-50%) rotate(90deg);
    }
    
    .nivel-header:not(.expandible)::before {
        display: none;
    }
    
    .nivel-children {
        display: none;
        margin-left: 24px;
    }
    
    .nivel-children.expanded {
        display: block;
    }
</style>
@endpush

@section('content')
    
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-900">Administración de Niveles</h2>
        <button onclick="agregarNivel(1, null)" 
                class="btn-primary flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Crear Nivel 1
        </button>
    </div>
    
    <div class="content-body">
        <div class="niveles-container">
            <div id="nivelesContainer">
                @foreach($niveles1 as $nivel1)
                <div class="mb-6 bg-white rounded-lg shadow-md p-6" data-nivel="1" data-id="{{ $nivel1->id }}">
                    <!-- Nivel 1 -->
                    <div class="nivel-header flex items-center justify-between mb-4 pb-4 border-b-2 border-blue-200 {{ $nivel1->nivel2->count() > 0 ? 'expandible' : '' }}" 
                         @if($nivel1->nivel2->count() > 0) onclick="toggleNivel(1, {{ $nivel1->id }})" @endif>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-blue-600">Nivel 1: {{ $nivel1->nombre }}</h3>
                            <p class="text-sm text-gray-500">ID: {{ $nivel1->id }}</p>
                        </div>
                        <button onclick="event.stopPropagation(); agregarNivel(2, {{ $nivel1->id }})" 
                                class="btn-primary text-sm">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Agregar Nivel 2
                        </button>
                    </div>

                    @if($nivel1->nivel2->count() > 0)
                        <div class="nivel-children" data-parent="1-{{ $nivel1->id }}">
                            @foreach($nivel1->nivel2 as $nivel2)
                            <div class="mb-4 bg-gray-50 rounded-lg p-4" data-nivel="2" data-id="{{ $nivel2->id }}">
                                <!-- Nivel 2 -->
                                <div class="nivel-header flex items-center justify-between mb-3 pb-3 border-b border-green-200 {{ $nivel2->nivel3->count() > 0 ? 'expandible' : '' }}" 
                                     @if($nivel2->nivel3->count() > 0) onclick="toggleNivel(2, {{ $nivel2->id }})" @endif>
                                    <div class="flex-1">
                                        <h4 class="text-lg font-semibold text-green-600">Nivel 2: {{ $nivel2->nombre }}</h4>
                                        <p class="text-xs text-gray-500">ID: {{ $nivel2->id }}</p>
                                    </div>
                                    <div class="space-x-2" onclick="event.stopPropagation();">
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
                                    <div class="nivel-children" data-parent="2-{{ $nivel2->id }}">
                                        @foreach($nivel2->nivel3 as $nivel3)
                                        <div class="mb-3 bg-white rounded p-3" data-nivel="3" data-id="{{ $nivel3->id }}">
                                            <!-- Nivel 3 -->
                                            <div class="nivel-header flex items-center justify-between mb-2 pb-2 border-b border-yellow-200 {{ $nivel3->nivel4->count() > 0 ? 'expandible' : '' }}" 
                                                 @if($nivel3->nivel4->count() > 0) onclick="toggleNivel(3, {{ $nivel3->id }})" @endif>
                                                <div class="flex-1">
                                                    <h5 class="text-md font-medium text-yellow-600">Nivel 3: {{ $nivel3->nombre }}</h5>
                                                    <p class="text-xs text-gray-500">ID: {{ $nivel3->id }}</p>
                                                </div>
                                                <div class="space-x-2" onclick="event.stopPropagation();">
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
                                                <div class="nivel-children" data-parent="3-{{ $nivel3->id }}">
                                                    @foreach($nivel3->nivel4 as $nivel4)
                                                    <div class="bg-gray-50 rounded p-2" data-nivel="4" data-id="{{ $nivel4->id }}">
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
                                                </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Agregar/Editar Nivel -->
<div id="modalNivel" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="overflow-y: auto;">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="sticky top-0 bg-white border-b-2 border-gray-300 px-6 py-4 z-10 rounded-t-lg">
            <h2 class="text-xl font-bold text-gray-900" id="modalNivelTitulo">Crear Nivel</h2>
        </div>
        <div class="p-6">
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
                
                <div class="flex justify-end space-x-2 mt-6 pt-4 border-t-2 border-gray-300">
                    <button type="button" onclick="closeModal('modalNivel')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary" id="btnGuardarNivel">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Eliminar Nivel -->
<div id="modalDeleteNivel" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4" style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md border-2 border-gray-200">
        <div class="px-6 py-4 border-b-2 border-gray-300">
            <h2 class="text-2xl font-bold text-red-600">Eliminar Nivel</h2>
        </div>
        <div class="px-6 py-4">
            <input type="hidden" id="deleteNivelTipo" name="nivel">
            <input type="hidden" id="deleteNivelId" name="id">
            
            <p class="mb-4 text-gray-700">
                ¿Estás seguro de que deseas eliminar el nivel <strong id="deleteNivelNombre"></strong>?
            </p>
        </div>
        <div class="px-6 py-4 border-t-2 border-gray-300 flex justify-end space-x-2">
            <button type="button" onclick="closeModal('modalDeleteNivel')" class="btn-secondary">Cancelar</button>
            <button type="button" onclick="confirmarEliminarNivel(event)" class="btn-danger">Eliminar</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Función para expandir/colapsar niveles
    function toggleNivel(nivel, id) {
        const childrenContainer = document.querySelector(`.nivel-children[data-parent="${nivel}-${id}"]`);
        const header = document.querySelector(`[data-nivel="${nivel}"][data-id="${id}"] .nivel-header`);
        
        if (childrenContainer && header) {
            const isExpanded = childrenContainer.classList.contains('expanded');
            
            if (isExpanded) {
                // Colapsar: ocultar todos los hijos
                childrenContainer.classList.remove('expanded');
                header.classList.remove('expanded');
            } else {
                // Expandir: mostrar solo el siguiente nivel
                childrenContainer.classList.add('expanded');
                header.classList.add('expanded');
            }
        }
    }
    
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
            const modals = ['modalNivel', 'modalDeleteNivel'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });

    function agregarNivel(nivel, parentId) {
        const nivelTable = `nivel_${nivel}`;
        document.getElementById('modalNivelTitulo').textContent = `Crear Nivel ${nivel}`;
        document.getElementById('nivelTipo').value = nivelTable;
        document.getElementById('nivelId').value = '';
        document.getElementById('nivelParent').value = parentId || '';
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
        
        const bodyData = {
            nivel: nivel,
            nombre: document.getElementById('nombreNivel').value
        };
        
        // Solo agregar id_parent si no es nivel 1
        const idParent = document.getElementById('nivelParent').value;
        if (idParent && nivel !== 'nivel_1') {
            bodyData.id_parent = idParent;
        }
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bodyData)
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
        if (event) {
            event.preventDefault();
        }
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

