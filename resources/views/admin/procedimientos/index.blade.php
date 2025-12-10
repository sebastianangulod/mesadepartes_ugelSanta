{{--
    Vista de Administración de Procedimientos
    Equivalente a adminProcedimientos.php del PHP puro
--}}
@extends('layouts.app')

@section('title', 'Administración de Procedimientos - Mesa de Partes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <x-alerts />
    
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
                        <button onclick="editarProcedimiento({{ $procedimiento->id }})" 
                                class="text-green-500 hover:text-green-700 mr-2" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        <button onclick="eliminarProcedimiento({{ $procedimiento->id }}, '{{ $procedimiento->nombre }}')" 
                                class="text-red-500 hover:text-red-700" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
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
<div id="modalAddProcedimiento" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-lg w-full max-w-md my-8">
        <h2 class="text-2xl font-bold mb-6">Añadir Procedimiento</h2>
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
                <label for="nivel3Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3</label>
                <select id="nivel3Procedimiento" name="id_nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 3</option>
                </select>
            </div>
            <div class="mb-4 nivel4-container" style="display: none;">
                <label for="nivel4Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 4</label>
                <select id="nivel4Procedimiento" name="id_nivel_final" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 4</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-primary">Guardar</button>
                <button type="button" onclick="closeModal('modalAddProcedimiento')" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Editar Procedimiento -->
<div id="modalEditProcedimiento" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-lg w-full max-w-md my-8">
        <h2 class="text-2xl font-bold mb-6">Editar Procedimiento</h2>
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
                <label for="editNivel3Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 3</label>
                <select id="editNivel3Procedimiento" name="id_nivel3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 3</option>
                </select>
            </div>
            <div class="mb-4 edit-nivel4-container" style="display: none;">
                <label for="editNivel4Procedimiento" class="block text-sm font-medium text-gray-700 mb-1">Nivel 4</label>
                <select id="editNivel4Procedimiento" name="id_nivel_final" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Seleccione Nivel 4</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-primary">Actualizar</button>
                <button type="button" onclick="closeModal('modalEditProcedimiento')" class="btn-secondary">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Eliminar Procedimiento -->
<div id="modalDeleteProcedimiento" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-lg w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-red-600">Eliminar Procedimiento</h2>
        <form id="formEliminarProcedimiento" onsubmit="confirmarEliminarProcedimiento(event)">
            @csrf
            @method('DELETE')
            <input type="hidden" id="deleteProcedimientoId" name="id">
            <p class="mb-4 text-gray-700">¿Estás seguro de que deseas eliminar el procedimiento <strong id="deleteProcedimientoNombre"></strong>?</p>
            <div class="flex justify-end space-x-2">
                <button type="submit" class="btn-danger">Eliminar</button>
                <button type="button" onclick="closeModal('modalDeleteProcedimiento')" class="btn-secondary">Cancelar</button>
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
        
        // Cargar niveles 3
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
        
        // Cargar niveles 4 (necesitamos niveles 3 primero)
        nivel3Select.addEventListener('change', function() {
            const nivel3Id = this.value;
            if (nivel3Id) {
                fetch(`/api/niveles/nivel4/${nivel3Id}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.data) {
                        nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
                        data.data.forEach(nivel4 => {
                            const option = document.createElement('option');
                            option.value = nivel4.id;
                            option.textContent = nivel4.nombre;
                            nivel4Select.appendChild(option);
                        });
                    }
                });
            }
        });
    }

    function toggleNivelFinal(nivel) {
        const nivel3Container = document.querySelector('.nivel3-container');
        const nivel4Container = document.querySelector('.nivel4-container');
        const nivel3Select = document.getElementById('nivel3Procedimiento');
        const nivel4Select = document.getElementById('nivel4Procedimiento');
        
        nivel3Container.style.display = nivel === 3 ? 'block' : 'none';
        nivel4Container.style.display = nivel === 4 ? 'block' : 'none';
        
        if (nivel === 3) {
            nivel3Select.required = true;
            nivel4Select.required = false;
        } else if (nivel === 4) {
            nivel3Select.required = false;
            nivel4Select.required = true;
        }
    }

    function crearProcedimiento(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const nivelFinal = formData.get('nivel_final');
        const idNivelFinal = nivelFinal === '3' 
            ? formData.get('id_nivel3') 
            : formData.get('id_nivel_final');
        
        fetch('{{ route("admin.procedimientos.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nombre: formData.get('nombre'),
                id_area: formData.get('id_area'),
                id_subarea: formData.get('id_subarea'),
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
                cargarSubareasEdit(proc.id_area, proc.id_subarea);
                
                // Configurar nivel final
                if (proc.nivel_final == '3') {
                    document.querySelector('input[name="edit_nivel_final"][value="3"]').checked = true;
                    toggleNivelFinalEdit(3);
                } else if (proc.nivel_final == '4') {
                    document.querySelector('input[name="edit_nivel_final"][value="4"]').checked = true;
                    toggleNivelFinalEdit(4);
                }
                
                openModal('modalEditProcedimiento');
            }
        });
    }

    function cargarSubareasEdit(areaId, subareaId = null) {
        const subareaSelect = document.getElementById('editSubareaProcedimiento');
        subareaSelect.innerHTML = '<option value="">Seleccione Nivel 2</option>';
        
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
                    if (subareaId && nivel2.id == subareaId) {
                        option.selected = true;
                    }
                    subareaSelect.appendChild(option);
                });
                if (subareaId) {
                    cargarNivelesFinalesEdit(subareaId);
                }
            }
        });
    }

    function cargarNivelesFinalesEdit(subareaId) {
        // Similar a cargarNivelesFinales pero para edición
        const nivel3Select = document.getElementById('editNivel3Procedimiento');
        const nivel4Select = document.getElementById('editNivel4Procedimiento');
        
        nivel3Select.innerHTML = '<option value="">Seleccione Nivel 3</option>';
        nivel4Select.innerHTML = '<option value="">Seleccione Nivel 4</option>';
        
        if (!subareaId) return;
        
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

    function toggleNivelFinalEdit(nivel) {
        const nivel3Container = document.querySelector('.edit-nivel3-container');
        const nivel4Container = document.querySelector('.edit-nivel4-container');
        
        nivel3Container.style.display = nivel === 3 ? 'block' : 'none';
        nivel4Container.style.display = nivel === 4 ? 'block' : 'none';
    }

    function actualizarProcedimiento(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const id = document.getElementById('editProcedimientoId').value;
        const nivelFinal = formData.get('edit_nivel_final');
        const idNivelFinal = nivelFinal === '3' 
            ? formData.get('id_nivel3') 
            : formData.get('id_nivel_final');
        
        fetch(`/admin/procedimientos/${id}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nombre: formData.get('nombre'),
                id_area: formData.get('id_area'),
                id_subarea: formData.get('id_subarea'),
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
        openModal('modalDeleteProcedimiento');
    }

    function confirmarEliminarProcedimiento(event) {
        event.preventDefault();
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

