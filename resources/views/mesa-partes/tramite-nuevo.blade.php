@extends('layouts.mesa-partes')

@php $esInterno = ($esInterno ?? ($tipoTramite ?? 'VIRTUAL') === 'PRESENCIAL'); @endphp

@section('title', $esInterno ? 'Nuevo Trámite Presencial · UGEL Santa' : 'Nuevo Trámite · UGEL Santa')

@section('header', $esInterno ? 'Mesa de Partes Presencial' : 'Mesa de Partes Virtual')

@section('subtitle', $esInterno
    ? 'Registre un trámite presencial validando al ciudadano titular por DNI o RUC antes del envío.'
    : 'Elija el procedimiento, adjunte su documento en PDF y envíe su expediente.')

@section('content')
<div class="max-w-3xl mx-auto">
    {{-- Pasos del trámite (orientativo, sin navegación) --}}
    <ol class="grid grid-cols-3 gap-2 sm:gap-3 mb-5" aria-label="Pasos para crear el trámite">
        @if($esInterno)
            <li class="flex items-center gap-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm px-3.5 py-3">
                <span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-600 text-[13px] font-extrabold flex items-center justify-center shrink-0">1</span>
                <span class="text-[12px] font-bold text-slate-700 leading-tight">Validar<br>ciudadano</span>
            </li>
            <li class="flex items-center gap-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm px-3.5 py-3">
                <span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-600 text-[13px] font-extrabold flex items-center justify-center shrink-0">2</span>
                <span class="text-[12px] font-bold text-slate-700 leading-tight">Procedimiento<br>y documento</span>
            </li>
            <li class="flex items-center gap-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm px-3.5 py-3">
                <span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-600 text-[13px] font-extrabold flex items-center justify-center shrink-0">3</span>
                <span class="text-[12px] font-bold text-slate-700 leading-tight">Enviar<br>trámite</span>
            </li>
        @else
            <li class="flex items-center gap-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm px-3.5 py-3">
                <span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-600 text-[13px] font-extrabold flex items-center justify-center shrink-0">1</span>
                <span class="text-[12px] font-bold text-slate-700 leading-tight">Elegir<br>procedimiento</span>
            </li>
            <li class="flex items-center gap-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm px-3.5 py-3">
                <span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-600 text-[13px] font-extrabold flex items-center justify-center shrink-0">2</span>
                <span class="text-[12px] font-bold text-slate-700 leading-tight">Adjuntar<br>PDF</span>
            </li>
            <li class="flex items-center gap-2.5 rounded-2xl bg-white border border-slate-200 shadow-sm px-3.5 py-3">
                <span class="w-7 h-7 rounded-lg bg-slate-200 text-slate-600 text-[13px] font-extrabold flex items-center justify-center shrink-0">3</span>
                <span class="text-[12px] font-bold text-slate-700 leading-tight">Enviar y<br>guardar voucher</span>
            </li>
        @endif
    </ol>

    <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 {{ $esInterno ? 'bg-emerald-700' : 'bg-primary-800' }} text-white">
            <h2 class="text-[17px] font-extrabold tracking-tight">{{ $esInterno ? 'Registro presencial a nombre del ciudadano' : 'Datos del expediente' }}</h2>
            @if($esInterno)
            <p class="mt-1 text-[13px] text-emerald-100">El trámite será de tipo <strong>PRESENCIAL</strong> y saldrá a nombre del ciudadano (DNI validado). No se crea usuario si no está registrado.</p>
            @else
            <p class="mt-1 text-[13px] text-white/75">Complete los datos con cuidado: el documento adjunto sustenta su solicitud.</p>
            @endif
        </div>

        <div class="p-6">
        <form id="tramiteForm" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="action" value="crear_tramite">

            @if($esInterno)
            {{-- ============ DATOS DEL CIUDADANO (titular) ============ --}}
            <div class="border {{ $esInterno ? 'border-emerald-200 bg-emerald-50/60' : 'border-slate-200' }} rounded-2xl p-5">
                <h3 class="text-[14px] font-extrabold text-slate-900">Datos del ciudadano (titular del trámite)</h3>
                <p class="text-[12px] text-slate-500 mt-0.5 mb-4">Seleccione el tipo de persona y valide el documento antes de continuar.</p>

                <div class="mb-4">
                    <span class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-2">Tipo de persona</span>
                    <div class="flex flex-wrap gap-x-6 gap-y-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="titular_tipo_persona" value="1" checked class="h-4 w-4 accent-emerald-700">
                            <span class="ml-2 text-sm font-medium text-slate-700">Persona Natural</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="titular_tipo_persona" value="2" class="h-4 w-4 accent-emerald-700">
                            <span class="ml-2 text-sm font-medium text-slate-700">Persona Jurídica</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" name="titular_tipo_persona" value="3" class="h-4 w-4 accent-emerald-700">
                            <span class="ml-2 text-sm font-medium text-slate-700">Entidad Pública</span>
                        </label>
                    </div>
                </div>

                <div id="bloqueDni">
                <div class="mb-4">
                    <label for="titular_dni" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">DNI del ciudadano <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" id="titular_dni" name="titular_dni" maxlength="8" pattern="[0-9]{8}" placeholder="8 dígitos" inputmode="numeric"
                            class="flex-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                        <button type="button" id="btnValidarDni"
                            class="shrink-0 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-bold px-4 py-2.5 transition">Validar DNI</button>
                    </div>
                    <p id="estadoCiudadano" class="text-sm mt-2 hidden"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-1">
                    <div>
                        <label for="titular_nombres" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Nombres</label>
                        <input type="text" id="titular_nombres" name="titular_nombres" readonly
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-100 text-slate-700">
                    </div>
                    <div>
                        <label for="titular_apellido_paterno" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Apellido Paterno</label>
                        <input type="text" id="titular_apellido_paterno" name="titular_apellido_paterno" readonly
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-100 text-slate-700">
                    </div>
                    <div>
                        <label for="titular_apellido_materno" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Apellido Materno</label>
                        <input type="text" id="titular_apellido_materno" name="titular_apellido_materno" readonly
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-100 text-slate-700">
                    </div>
                </div>
                </div>

                <div id="bloqueJuridica" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mb-1">
                    <div>
                        <label for="titular_ruc" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">RUC <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" id="titular_ruc" name="titular_ruc" maxlength="11" pattern="[0-9]{11}" placeholder="11 dígitos" inputmode="numeric"
                                class="flex-1 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition">
                            <button type="button" id="btnValidarRuc"
                                class="shrink-0 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-sm font-bold px-4 py-2.5 transition">Validar RUC</button>
                        </div>
                    </div>
                    <div>
                        <label for="titular_razon_social" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Razón Social</label>
                        <input type="text" id="titular_razon_social" name="titular_razon_social" readonly maxlength="200"
                            class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-100 text-slate-700">
                    </div>
                </div>

                <div id="bloqueEntidad" class="hidden mb-1">
                    <label for="titular_nombre_entidad" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">Nombre de la Entidad <span class="text-red-500">*</span></label>
                    <input type="text" id="titular_nombre_entidad" maxlength="200" placeholder="Nombre de la entidad pública"
                        class="block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        onInput="this.value = this.value.toUpperCase()">
                </div>
            </div>
            @endif

            {{-- ============ DATOS DEL EXPEDIENTE ============ --}}
            <div>
                <label for="id_procedimiento" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">
                    Procedimiento <span class="text-red-500">*</span>
                </label>
                <select id="id_procedimiento" name="id_procedimiento" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm font-medium text-slate-700 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition" required>
                    <option value="" disabled selected>Seleccionar un procedimiento</option>
                    @foreach($procedimientos as $procedimiento)
                        <option value="{{ $procedimiento->id }}">{{ $procedimiento->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="archivo" class="block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5">
                    Adjuntar Archivo (PDF) <span class="text-red-500">*</span>
                </label>
                <input type="file" id="archivo" name="archivo" required accept=".pdf"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[12px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition">
                <p id="archivoError" class="mt-1.5 text-[12.5px] font-semibold text-red-600 hidden">Por favor, seleccione un archivo PDF.</p>
                <p class="mt-1.5 text-[11.5px] text-slate-400">Tamaño máximo: 25 MB · Solo archivos PDF</p>
            </div>

            <div>
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-bold {{ $esInterno ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-primary-50 text-primary-700 border border-primary-100' }}">
                    Tipo: {{ $tipoTramite }} · se asigna automáticamente según su usuario
                </span>
            </div>

            <button type="submit" id="submitBtn" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 rounded-xl bg-primary-700 hover:bg-primary-800 text-white text-sm font-bold py-3 px-6 shadow-md shadow-primary-700/20 transition disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Crear Trámite {{ $esInterno ? 'Presencial' : '' }}
            </button>
        </form>
        </div>
    </section>

    <p class="mt-4 text-center text-[12px] text-slate-400">Verifique los datos antes de enviar. Guarde el número de expediente y el voucher para el seguimiento.</p>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
<style>
    .choices { margin-bottom: 0; }
    .choices__inner { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem; min-height: 46px; padding: 6px 12px; }
    .choices.is-focused .choices__inner { background-color: #fff; border-color: #2b6dc0; box-shadow: 0 0 0 2px rgba(43,109,192,.25); }
    input[readonly] { background-color: #f1f5f9; }
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
        // Entidad pública: solo nombre de la entidad (sin DNI ni RUC).
        document.getElementById('bloqueDni').classList.toggle('hidden', tipo === '3');
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

    // Devuelve la sección del titular a su estado inicial:
    // todo vacío y desbloqueado (solo se bloquea tras validación exitosa).
    function resetTitular() {
        titularDniValidado = false;
        titularRucValidado = false;
        ['titular_dni', 'titular_nombres', 'titular_apellido_paterno', 'titular_apellido_materno',
         'titular_ruc', 'titular_razon_social', 'titular_nombre_entidad'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.value = '';
            el.readOnly = (id === 'titular_nombres' || id === 'titular_apellido_paterno' ||
                           id === 'titular_apellido_materno' || id === 'titular_razon_social');
        });
        const btnDni = document.getElementById('btnValidarDni');
        if (btnDni) { btnDni.disabled = false; btnDni.textContent = 'Validar DNI'; }
        const btnRuc = document.getElementById('btnValidarRuc');
        if (btnRuc) { btnRuc.disabled = false; btnRuc.textContent = 'Validar RUC'; }
        const est = document.getElementById('estadoCiudadano');
        if (est) est.classList.add('hidden');
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
                    // Cambiar de tipo de persona reinicia el titular por completo.
                    resetTitular();
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
                if (titularRucValidado) {
                    titularRucValidado = false;
                    this.readOnly = false;
                    const btn = document.getElementById('btnValidarRuc');
                    if (btn) { btn.disabled = false; btn.textContent = 'Validar RUC'; }
                }
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
                // Entidad pública: solo nombre (sin DNI ni RUC), como en el login.
                if (tipo === '3') {
                    const nomEnt = document.getElementById('titular_nombre_entidad').value.trim();
                    if (!nomEnt) { alert('Debe ingresar el nombre de la entidad pública.'); return; }
                    formData.append('titular_tipo_persona', tipo);
                    formData.append('titular_dni', '');
                    formData.append('titular_nombres', '');
                    formData.append('titular_apellido_paterno', '');
                    formData.append('titular_apellido_materno', '');
                    formData.append('titular_ruc', '');
                    formData.append('titular_razon_social', nomEnt.toUpperCase());
                } else {
                    if (!titularDniValidado) { alert('Debe validar el DNI del ciudadano antes de crear el trámite.'); return; }
                    if (tipo === '2' && !titularRucValidado) { alert('Debe validar el RUC de la empresa.'); return; }
                    formData.append('titular_tipo_persona', tipo);
                    formData.append('titular_dni', document.getElementById('titular_dni').value.trim());
                    formData.append('titular_nombres', document.getElementById('titular_nombres').value);
                    formData.append('titular_apellido_paterno', document.getElementById('titular_apellido_paterno').value);
                    formData.append('titular_apellido_materno', document.getElementById('titular_apellido_materno').value);
                    formData.append('titular_ruc', document.getElementById('titular_ruc') ? document.getElementById('titular_ruc').value.trim() : '');
                    formData.append('titular_razon_social', document.getElementById('titular_razon_social').value);
                }
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
            const tipo = tipoPersonaTitular();
            if (tipo === '3') {
                // Entidad pública: basta el nombre (sin validación externa).
                const ne = document.getElementById('titular_nombre_entidad');
                titularOk = !!(ne && ne.value.trim());
            } else {
                titularOk = titularDniValidado;
                if (tipo === '2') titularOk = titularOk && titularRucValidado;
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
                    if (btnRuc && titularRucValidado) { btnRuc.disabled = true; btnRuc.textContent = '✓ Validado'; }
                    if (titularRucValidado) document.getElementById('titular_ruc').readOnly = true;
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
                document.getElementById('titular_ruc').readOnly = true;
                btn.disabled = true; btn.textContent = '✓ Validado';
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
