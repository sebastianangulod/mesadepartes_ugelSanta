@extends('layouts.mesa-partes')

@php $esInterno = ($esInterno ?? ($tipoTramite ?? 'VIRTUAL') === 'PRESENCIAL'); @endphp

@section('title', $esInterno ? 'Nuevo Trámite Presencial - Mesa de Partes' : 'Nuevo Trámite - Mesa de Partes')

@section('header', $esInterno ? 'Mesa de Partes Presencial' : 'Mesa de Partes Virtual')

@section('content')
<div class="flex space-x-6">
    <div class="w-full bg-white shadow-md rounded-lg overflow-hidden">
        <div class="bg-gradient-to-r {{ $esInterno ? 'from-emerald-600 to-emerald-700 border-emerald-800' : 'from-purple-600 to-purple-700 border-purple-800' }} text-gray px-6 py-4 border-b-2">
            <h2 class="text-xl font-bold">{{ $esInterno ? 'Registro presencial a nombre del ciudadano' : 'Datos del Expediente' }}</h2>
            @if($esInterno)
            <p class="text-sm text-emerald-100 mt-1">El trámite será de tipo <strong>PRESENCIAL</strong> y saldrá a nombre del ciudadano (DNI validado). No se crea usuario si no está registrado.</p>
            @endif
        </div>

        <div class="p-6">
        <form id="tramiteForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" value="crear_tramite">

            @if($esInterno)
            {{-- ============ DATOS DEL CIUDADANO (titular) ============ --}}
            <div class="mb-6 border border-emerald-200 rounded-md p-4 bg-emerald-50/40">
                <h3 class="font-bold text-gray-900 mb-3">Datos del ciudadano (titular del trámite)</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de persona</label>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="titular_tipo_persona" value="1" checked class="h-4 w-4 text-emerald-700 border-gray-300">
                            <span class="ml-2 text-sm text-gray-900">Persona Natural</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="titular_tipo_persona" value="2" class="h-4 w-4 text-emerald-700 border-gray-300">
                            <span class="ml-2 text-sm text-gray-900">Persona Jurídica</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="titular_tipo_persona" value="3" class="h-4 w-4 text-emerald-700 border-gray-300">
                            <span class="ml-2 text-sm text-gray-900">Entidad Pública</span>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="titular_dni" class="block text-gray-900 font-medium mb-2">DNI del ciudadano <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="titular_dni" name="titular_dni" maxlength="8" pattern="[0-9]{8}" placeholder="8 dígitos"
                            class="flex-1 p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500 text-gray-900">
                        <button type="button" id="btnValidarDni"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-200">Validar DNI</button>
                    </div>
                    <p id="estadoCiudadano" class="text-sm mt-2 hidden"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nombres</label>
                        <input type="text" id="titular_nombres" name="titular_nombres" readonly
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-3 py-2 bg-gray-100 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Apellido Paterno</label>
                        <input type="text" id="titular_apellido_paterno" name="titular_apellido_paterno" readonly
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-3 py-2 bg-gray-100 text-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Apellido Materno</label>
                        <input type="text" id="titular_apellido_materno" name="titular_apellido_materno" readonly
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-3 py-2 bg-gray-100 text-gray-900">
                    </div>
                </div>

                <div id="bloqueJuridica" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                    <div>
                        <label for="titular_ruc" class="block text-sm font-medium text-gray-700 mb-1">RUC <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="titular_ruc" name="titular_ruc" maxlength="11" pattern="[0-9]{11}" placeholder="11 dígitos"
                                class="flex-1 border-gray-300 rounded-md shadow-sm px-3 py-2 text-gray-900">
                            <button type="button" id="btnValidarRuc"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition duration-200">Validar RUC</button>
                        </div>
                    </div>
                    <div>
                        <label for="titular_razon_social" class="block text-sm font-medium text-gray-700">Razón Social</label>
                        <input type="text" id="titular_razon_social" name="titular_razon_social" readonly maxlength="200"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-3 py-2 bg-gray-100 text-gray-900">
                    </div>
                </div>

                <div id="bloqueEntidad" class="hidden mb-2">
                    <label for="titular_nombre_entidad" class="block text-sm font-medium text-gray-700">Nombre de la Entidad <span class="text-red-500">*</span></label>
                    <input type="text" id="titular_nombre_entidad" maxlength="200" placeholder="Nombre de la entidad pública"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm px-3 py-2 text-gray-900"
                        onInput="this.value = this.value.toUpperCase()">
                </div>
            </div>
            @endif

            {{-- ============ DATOS DEL EXPEDIENTE ============ --}}
            <div class="mb-4">
                <label for="id_procedimiento" class="block text-gray-900 font-medium mb-2">
                    Procedimiento <span class="text-red-500">*</span>
                </label>
                <select id="id_procedimiento" name="id_procedimiento" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" required>
                    <option value="" disabled selected>Seleccionar un procedimiento</option>
                    @foreach($procedimientos as $procedimiento)
                        <option value="{{ $procedimiento->id }}">{{ $procedimiento->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="archivo" class="block text-gray-900 font-medium mb-2">
                    Adjuntar Archivo (PDF) <span class="text-red-500">*</span>
                </label>
                <input type="file" id="archivo" name="archivo" class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900" required accept=".pdf">
                <p id="archivoError" class="text-red-500 mt-2 hidden">Por favor, seleccione un archivo PDF.</p>
                <p class="text-gray-600 text-sm mt-2">Tamaño máximo: 25MB. Solo archivos PDF.</p>
            </div>

            <div class="mb-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $esInterno ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                    Tipo: {{ $tipoTramite }} (automático según tu usuario)
                </span>
            </div>

            <button type="submit" id="submitBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                Crear Trámite {{ $esInterno ? 'Presencial' : '' }}
            </button>
        </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<style>
    .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 48px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); background-color: #2D3748 !important; }
    .sidebar .nav-link { color: rgba(255, 255, 255, 0.8); transition: color 0.3s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: rgba(255, 255, 255, 0.1); }
    main { margin-left: 240px; }
    @media (max-width: 767.98px) { .sidebar { position: static; padding-top: 0; } main { margin-left: 0; } }
    .choices { margin-bottom: 0; }
    .choices__inner { background-color: white; border: 1px solid #e2e8f0; border-radius: 0.375rem; min-height: 42px; }
    input[readonly] { background-color: #f3f4f6; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    const ES_INTERNO = @json($esInterno);
    let procedimientoSelect;
    let titularDniValidado = false;
    let titularRucValidado = false;

    function csrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function tipoPersonaTitular() {
        if (!ES_INTERNO) return 1;
        const r = document.querySelector('input[name="titular_tipo_persona"]:checked');
        return r ? r.value : '1';
    }

    function toggleBloquesTitular() {
        if (!ES_INTERNO) return;
        const tipo = tipoPersonaTitular();
        document.getElementById('bloqueJuridica').classList.toggle('hidden', tipo !== '2');
        document.getElementById('bloqueEntidad').classList.toggle('hidden', tipo !== '3');
        validarFormulario();
    }

    function setEstadoCiudadano(texto, tipo) {
        const el = document.getElementById('estadoCiudadano');
        if (!el) return;
        el.classList.remove('hidden');
        el.className = 'text-sm mt-2 ' + (tipo === 'ok' ? 'text-green-700' : tipo === 'warn' ? 'text-amber-700' : 'text-red-600');
        el.textContent = texto;
    }

    function bloquearCamposDni(bloquear) {
        ['titular_nombres', 'titular_apellido_paterno', 'titular_apellido_materno'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.readOnly = true;
        });
        const dni = document.getElementById('titular_dni');
        if (dni) dni.readOnly = bloquear;
    }

    $(document).ready(function() {
        const procedimientoElement = document.getElementById('id_procedimiento');
        if (procedimientoElement) {
            procedimientoSelect = new Choices(procedimientoElement, {
                searchEnabled: true, placeholder: true,
                placeholderValue: 'Seleccionar un procedimiento',
                searchPlaceholderValue: 'Buscar procedimiento...',
                itemSelectText: '', shouldSort: false,
            });
            procedimientoElement.addEventListener('addItem', validarFormulario);
            procedimientoElement.addEventListener('change', validarFormulario);
        }

        if (ES_INTERNO) {
            document.querySelectorAll('input[name="titular_tipo_persona"]').forEach(r => {
                r.addEventListener('change', function() {
                    titularRucValidado = false;
                    const btn = document.getElementById('btnValidarRuc');
                    if (btn) { btn.disabled = false; btn.textContent = 'Validar RUC'; }
                    toggleBloquesTitular();
                });
            });

            document.getElementById('titular_dni').addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
                if (titularDniValidado) {
                    titularDniValidado = false;
                    const btn = document.getElementById('btnValidarDni');
                    btn.disabled = false; btn.textContent = 'Validar DNI';
                    const dni = document.getElementById('titular_dni');
                    if (dni) dni.readOnly = false;
                    setEstadoCiudadano('DNI modificado. Vuelva a validar.', 'error');
                }
                validarFormulario();
            });

            const rucInput = document.getElementById('titular_ruc');
            if (rucInput) rucInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11);
                titularRucValidado = false;
                validarFormulario();
            });

            const entInput = document.getElementById('titular_nombre_entidad');
            if (entInput) entInput.addEventListener('input', validarFormulario);

            document.getElementById('btnValidarDni').addEventListener('click', validarDniCiudadano);
            const btnRuc = document.getElementById('btnValidarRuc');
            if (btnRuc) btnRuc.addEventListener('click', validarRucCiudadano);

            toggleBloquesTitular();
        }

        $('#archivo').change(function() {
            var file = this.files[0];
            var tamañoMaximo = 26214400;
            if (!file || file.type !== 'application/pdf') {
                $('#archivoError').removeClass('hidden').text('Por favor, seleccione un archivo PDF válido.');
                validarFormulario(); return;
            }
            if (file.size > tamañoMaximo) {
                $('#archivoError').removeClass('hidden').text('El archivo excede 25MB. Actual: ' + (file.size / 1048576).toFixed(2) + 'MB');
                validarFormulario(); return;
            }
            $('#archivoError').addClass('hidden');
            validarFormulario();
        });

        $('#tramiteForm').submit(function(e) {
            e.preventDefault();

            var selectedValue = null;
            if (procedimientoSelect && typeof procedimientoSelect.getValue === 'function') {
                var cv = procedimientoSelect.getValue(true);
                selectedValue = Array.isArray(cv) ? cv[0] : cv;
            } else {
                selectedValue = $('#id_procedimiento').val();
            }
            if (!selectedValue) { alert('Debe seleccionar un procedimiento.'); return; }

            var archivoInput = document.getElementById('archivo');
            var archivo = archivoInput && archivoInput.files.length > 0 ? archivoInput.files[0] : null;
            if (!archivo) { alert('Debe adjuntar un archivo PDF.'); return; }
            if (archivo.type !== 'application/pdf' || archivo.size > 26214400) { alert('Archivo inválido o mayor a 25MB.'); return; }

            var formData = new FormData();
            formData.append('_token', csrfToken());
            formData.append('action', 'crear_tramite');
            formData.append('id_procedimiento', selectedValue);
            formData.append('archivo', archivo);

            if (ES_INTERNO) {
                const tipo = tipoPersonaTitular();
                if (!titularDniValidado) { alert('Debe validar el DNI del ciudadano antes de crear el trámite.'); return; }
                if (tipo === '2' && !titularRucValidado) { alert('Debe validar el RUC de la empresa.'); return; }
                if (tipo === '3') {
                    const nomEnt = document.getElementById('titular_nombre_entidad').value.trim();
                    if (!nomEnt) { alert('Debe ingresar el nombre de la entidad pública.'); return; }
                    document.getElementById('titular_razon_social').value = nomEnt.toUpperCase();
                }
                formData.append('titular_tipo_persona', tipo);
                formData.append('titular_dni', document.getElementById('titular_dni').value.trim());
                formData.append('titular_nombres', document.getElementById('titular_nombres').value);
                formData.append('titular_apellido_paterno', document.getElementById('titular_apellido_paterno').value);
                formData.append('titular_apellido_materno', document.getElementById('titular_apellido_materno').value);
                formData.append('titular_ruc', document.getElementById('titular_ruc') ? document.getElementById('titular_ruc').value.trim() : '');
                formData.append('titular_razon_social', document.getElementById('titular_razon_social').value);
            }

            $.ajax({
                url: '{{ route("mesa-partes.guardar") }}',
                type: 'POST', data: formData, contentType: false, processData: false, dataType: 'json',
                beforeSend: function() {
                    $('#submitBtn').prop('disabled', true);
                    $('#submitBtn').html('<span class="inline-block animate-spin mr-2">⏳</span> Procesando...');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        if (response.redirect) window.location.href = response.redirect;
                    } else { alert('Error: ' + response.message); resetBoton(); }
                },
                error: function(xhr) {
                    var msg = 'Ocurrió un error al procesar la solicitud.';
                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        if (xhr.responseJSON.errors) {
                            const det = [];
                            $.each(xhr.responseJSON.errors, function(f, ms) {
                                (Array.isArray(ms) ? ms : [ms]).forEach(m => det.push(m));
                            });
                            if (det.length) msg = 'Errores de validación:\n\n' + det.join('\n');
                        }
                    }
                    alert(msg); resetBoton();
                }
            });
        });
    });

    function resetBoton() {
        $('#submitBtn').prop('disabled', false);
        $('#submitBtn').text(ES_INTERNO ? 'Crear Trámite Presencial' : 'Crear Trámite');
        validarFormulario();
    }

    function validarFormulario() {
        var procOk = false, archOk = false;
        if (procedimientoSelect && typeof procedimientoSelect.getValue === 'function') {
            var v = procedimientoSelect.getValue(true);
            procOk = !!(Array.isArray(v) ? v[0] : v);
        } else { procOk = $('#id_procedimiento').val() !== ''; }
        var file = $('#archivo')[0] ? $('#archivo')[0].files[0] : null;
        archOk = !!(file && file.type === 'application/pdf' && file.size <= 26214400);

        let titularOk = true;
        if (ES_INTERNO) {
            titularOk = titularDniValidado;
            const tipo = tipoPersonaTitular();
            if (tipo === '2') titularOk = titularOk && titularRucValidado;
            if (tipo === '3') {
                const ne = document.getElementById('titular_nombre_entidad');
                titularOk = titularOk && !!(ne && ne.value.trim());
            }
        }
        $('#submitBtn').prop('disabled', !(procOk && archOk && titularOk));
    }

    async function validarDniCiudadano() {
        const btn = document.getElementById('btnValidarDni');
        const dni = document.getElementById('titular_dni').value.trim();
        if (dni.length !== 8) { alert('El DNI debe tener 8 dígitos'); return; }
        btn.disabled = true; btn.textContent = 'Validando...';
        try {
            // 1. ¿Está registrado en el sistema?
            const respLocal = await fetch('/api/ciudadano/' + dni, { headers: { 'Accept': 'application/json' } });
            const local = await respLocal.json();
            if (respLocal.ok && local.registrado) {
                document.getElementById('titular_nombres').value = local.nombres || '';
                document.getElementById('titular_apellido_paterno').value = local.apellido_paterno || '';
                document.getElementById('titular_apellido_materno').value = local.apellido_materno || '';
                if (local.tipo_persona) {
                    const radio = document.querySelector('input[name="titular_tipo_persona"][value="' + local.tipo_persona + '"]');
                    if (radio) radio.checked = true;
                    toggleBloquesTitular();
                }
                if (local.tipo_persona == 2) {
                    document.getElementById('titular_ruc').value = local.ruc || '';
                    document.getElementById('titular_razon_social').value = local.razon_social || '';
                    titularRucValidado = !!(local.ruc && local.razon_social);
                    const btnRuc = document.getElementById('btnValidarRuc');
                    if (btnRuc && titularRucValidado) { btnRuc.textContent = '✓ Validado'; }
                }
                if (local.tipo_persona == 3) {
                    document.getElementById('titular_nombre_entidad').value = local.razon_social || '';
                    document.getElementById('titular_razon_social').value = local.razon_social || '';
                }
                titularDniValidado = true;
                bloquearCamposDni(true);
                btn.textContent = '✓ Validado';
                setEstadoCiudadano('Ciudadano registrado en el sistema. El trámite saldrá a su nombre.', 'ok');
                validarFormulario();
                return;
            }
            // 2. No registrado: validar por RENIEC (igual que en registro externo)
            const resp = await fetch('{{ route("api.validar-dni") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ dni: dni })
            });
            const data = await resp.json();
            if (resp.ok && data.success) {
                document.getElementById('titular_nombres').value = data.nombres || '';
                document.getElementById('titular_apellido_paterno').value = data.apellidoPaterno || '';
                document.getElementById('titular_apellido_materno').value = data.apellidoMaterno || '';
                titularDniValidado = true;
                bloquearCamposDni(true);
                btn.textContent = '✓ Validado';
                setEstadoCiudadano('DNI validado por RENIEC. No está registrado: el trámite saldrá a su nombre (snapshot).', 'warn');
                validarFormulario();
            } else {
                alert(data.message || 'No se pudo validar el DNI');
                btn.disabled = false; btn.textContent = 'Validar DNI';
            }
        } catch (e) {
            console.error(e);
            alert('Error al validar el DNI.');
            btn.disabled = false; btn.textContent = 'Validar DNI';
        }
    }

    async function validarRucCiudadano() {
        const btn = document.getElementById('btnValidarRuc');
        const ruc = document.getElementById('titular_ruc').value.trim();
        if (ruc.length !== 11) { alert('El RUC debe tener 11 dígitos'); return; }
        btn.disabled = true; btn.textContent = 'Validando...';
        try {
            const resp = await fetch('{{ route("api.validar-ruc") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ ruc: ruc })
            });
            const data = await resp.json();
            if (resp.ok && data.success) {
                document.getElementById('titular_razon_social').value = data.razonSocial || '';
                titularRucValidado = true;
                btn.textContent = '✓ Validado';
                validarFormulario();
            } else {
                alert(data.message || 'No se pudo validar el RUC');
                btn.disabled = false; btn.textContent = 'Validar RUC';
            }
        } catch (e) {
            console.error(e);
            alert('Error al validar el RUC.');
            btn.disabled = false; btn.textContent = 'Validar RUC';
        }
    }
</script>
@endpush
