{{-- Sistema de notificaciones: toasts, diálogos, campana y avisos flash. --}}
<!-- Toasts del sistema (reemplazan alerts y avisos bloqueantes) -->
    <div id="mpToasts" class="fixed top-4 right-4 z-[10000] flex flex-col gap-2 w-[min(92vw,360px)]" aria-live="polite"></div>

    <!-- Diálogo genérico del sistema (confirmar / pedir dato / elegir / informar) -->
    <div id="mpDialog" class="fixed inset-0 z-[10001] hidden items-center justify-center p-4 bg-slate-950/55 backdrop-blur-[2px]">
        <div class="mp-modal-panel bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <h3 id="mpDialogTitle" class="text-[16px] font-extrabold text-slate-900 pr-2"></h3>
            <div id="mpDialogBody" class="mt-2 text-sm text-slate-600"></div>
            <div id="mpDialogBtns" class="flex justify-end gap-2 mt-5"></div>
        </div>
    </div>

    <script>
(function() {
// ══════ Toasts del sistema ══════
            window.app.toast = function(message, type) {
                type = type || 'info';
                const box = document.getElementById('mpToasts');
                if (!box) {
                    console.log('[' + type.toUpperCase() + '] ' + message);
                    return;
                }
                const estilos = {
                    success: { wrap: 'border-emerald-200 bg-emerald-50 text-emerald-900', dot: 'bg-emerald-500', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>' },
                    error: { wrap: 'border-red-200 bg-red-50 text-red-900', dot: 'bg-red-500', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>' },
                    warning: { wrap: 'border-amber-200 bg-amber-50 text-amber-900', dot: 'bg-amber-500', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>' },
                    info: { wrap: 'border-primary-100 bg-primary-50 text-primary-900', dot: 'bg-primary-600', icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>' }
                };
                const est = estilos[type] || estilos.info;
                const el = document.createElement('div');
                el.setAttribute('role', type === 'error' ? 'alert' : 'status');
                el.className = 'mp-modal-panel flex items-start gap-2.5 rounded-2xl border shadow-lg px-4 py-3 text-[13px] font-medium ' + est.wrap;
                el.innerHTML =
                    '<span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white ' + est.dot + '">' +
                    '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">' + est.icon + '</svg></span>' +
                    '<span class="flex-1 min-w-0 break-words"></span>' +
                    '<button type="button" aria-label="Cerrar aviso" class="shrink-0 opacity-60 hover:opacity-100 font-bold text-base leading-none px-1">×</button>';
                el.querySelector('span.flex-1').textContent = message;
                el.querySelector('button').addEventListener('click', function() { el.remove(); });
                box.appendChild(el);
                setTimeout(function() { if (el.parentNode) el.remove(); }, 5000);
                while (box.children.length > 4) { box.removeChild(box.firstChild); }
            };

            // Avisos flash de sesión como toasts.
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('[data-flash-toasts]').forEach(function(slot) {
                    if (slot.dataset.flashSuccess) window.app.toast(slot.dataset.flashSuccess, 'success');
                    if (slot.dataset.flashError) window.app.toast(slot.dataset.flashError, 'error');
                    if (slot.dataset.flashInfo) window.app.toast(slot.dataset.flashInfo, 'info');
                    Object.keys(slot.dataset).forEach(function(k) {
                        if (/^flashErr\d+$/.test(k)) window.app.toast(slot.dataset[k], 'error');
                    });
                });
            });

            // ══════ Diálogo genérico del sistema ══════
            function mpDialogClose() {
                const m = document.getElementById('mpDialog');
                if (!m) return;
                m.classList.add('hidden');
                m.classList.remove('flex');
            }
            function mpDialogMakeButton(label, cls, onClick) {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = cls;
                b.textContent = label;
                b.addEventListener('click', onClick);
                return b;
            }
            window.app.infoDialog = function(title, htmlMessage) {
                return new Promise(function(resolve) {
                    const m = document.getElementById('mpDialog');
                    document.getElementById('mpDialogTitle').textContent = title || 'Información';
                    const body = document.getElementById('mpDialogBody');
                    body.innerHTML = '';
                    const p = document.createElement('div');
                    p.className = 'whitespace-pre-wrap break-words select-all rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-slate-800';
                    p.textContent = htmlMessage || '';
                    body.appendChild(p);
                    const btns = document.getElementById('mpDialogBtns');
                    btns.innerHTML = '';
                    btns.appendChild(mpDialogMakeButton('Aceptar', 'px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition', function() {
                        mpDialogClose(); resolve();
                    }));
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                    const first = btns.querySelector('button');
                    if (first) first.focus();
                });
            };
            window.app.confirmDialog = function(message, opts) {
                opts = opts || {};
                return new Promise(function(resolve) {
                    const m = document.getElementById('mpDialog');
                    document.getElementById('mpDialogTitle').textContent = opts.title || 'Confirmar acción';
                    const body = document.getElementById('mpDialogBody');
                    body.innerHTML = '';
                    const p = document.createElement('p');
                    p.textContent = message || '';
                    body.appendChild(p);
                    const btns = document.getElementById('mpDialogBtns');
                    btns.innerHTML = '';
                    btns.appendChild(mpDialogMakeButton(opts.cancelText || 'Cancelar', 'px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition', function() {
                        mpDialogClose(); resolve(false);
                    }));
                    btns.appendChild(mpDialogMakeButton(opts.confirmText || 'Confirmar', opts.danger === false
                        ? 'px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition'
                        : 'px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-bold hover:bg-red-700 transition', function() {
                        mpDialogClose(); resolve(true);
                    }));
                    const done = function(ok) { mpDialogClose(); resolve(ok); };
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                    m.onkeydown = function(e) { if (e.key === 'Escape') done(false); };
                    m.onclick = function(e) { if (e.target === m) done(false); };
                    const first = btns.querySelector('button');
                    if (first) first.focus();
                });
            };
            window.app.promptDialog = function(opts) {
                opts = opts || {};
                return new Promise(function(resolve) {
                    const m = document.getElementById('mpDialog');
                    document.getElementById('mpDialogTitle').textContent = opts.title || 'Ingrese un valor';
                    const body = document.getElementById('mpDialogBody');
                    body.innerHTML = '';
                    if (opts.label) {
                        const lab = document.createElement('label');
                        lab.className = 'block text-[12px] font-bold uppercase tracking-wide text-slate-500 mb-1.5';
                        lab.textContent = opts.label;
                        body.appendChild(lab);
                    }
                    const input = document.createElement(opts.multiline ? 'textarea' : 'input');
                    if (!opts.multiline) input.type = 'text';
                    if (opts.placeholder) input.placeholder = opts.placeholder;
                    if (opts.multiline) input.rows = 4;
                    input.className = 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500';
                    body.appendChild(input);
                    const err = document.createElement('p');
                    err.className = 'hidden mt-1.5 text-[12.5px] font-semibold text-red-600';
                    err.textContent = 'Este campo es obligatorio.';
                    body.appendChild(err);
                    const btns = document.getElementById('mpDialogBtns');
                    btns.innerHTML = '';
                    const done = function(val) { mpDialogClose(); resolve(val); };
                    btns.appendChild(mpDialogMakeButton(opts.cancelText || 'Cancelar', 'px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition', function() { done(null); }));
                    btns.appendChild(mpDialogMakeButton(opts.confirmText || 'Aceptar', 'px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition', function() {
                        const val = (input.value || '').trim();
                        if (opts.required !== false && !val) {
                            err.classList.remove('hidden');
                            input.focus();
                            return;
                        }
                        done(val);
                    }));
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                    m.onkeydown = function(e) { if (e.key === 'Escape') done(null); };
                    m.onclick = function(e) { if (e.target === m) done(null); };
                    input.focus();
                });
            };
            window.app.listDialog = function(opts) {
                opts = opts || {};
                return new Promise(function(resolve) {
                    const m = document.getElementById('mpDialog');
                    document.getElementById('mpDialogTitle').textContent = opts.title || 'Seleccione una opción';
                    const body = document.getElementById('mpDialogBody');
                    body.innerHTML = '';
                    const list = document.createElement('div');
                    list.className = 'flex flex-col gap-2 max-h-[40vh] overflow-y-auto mp-scroll';
                    (opts.options || []).forEach(function(op, i) {
                        const lab = document.createElement('label');
                        lab.className = 'flex items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm font-medium cursor-pointer hover:border-primary-300 hover:bg-primary-50 transition has-checked:border-primary-500 has-checked:bg-primary-50';
                        const radio = document.createElement('input');
                        radio.type = 'radio';
                        radio.name = 'mpListChoice';
                        radio.value = op.value;
                        if (i === 0) radio.checked = true;
                        const span = document.createElement('span');
                        span.textContent = op.label;
                        lab.appendChild(radio);
                        lab.appendChild(span);
                        list.appendChild(lab);
                    });
                    body.appendChild(list);
                    const btns = document.getElementById('mpDialogBtns');
                    btns.innerHTML = '';
                    const done = function(val) { mpDialogClose(); resolve(val); };
                    btns.appendChild(mpDialogMakeButton(opts.cancelText || 'Cancelar', 'px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-bold hover:bg-slate-200 transition', function() { done(null); }));
                    btns.appendChild(mpDialogMakeButton(opts.confirmText || 'Elegir', 'px-4 py-2.5 rounded-xl bg-primary-700 text-white text-sm font-bold hover:bg-primary-800 transition', function() {
                        const sel = list.querySelector('input[name="mpListChoice"]:checked');
                        done(sel ? sel.value : null);
                    }));
                    m.classList.remove('hidden');
                    m.classList.add('flex');
                    m.onkeydown = function(e) { if (e.key === 'Escape') done(null); };
                    m.onclick = function(e) { if (e.target === m) done(null); };
                });
            };

            // ══════ Campana de notificaciones ══════
            function mpNotifCsrf() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }
            function mpNotifTipoEstilo(tipo) {
                return {
                    success: ['bg-emerald-100 text-emerald-700', 'M5 13l4 4L19 7'],
                    error: ['bg-red-100 text-red-700', 'M6 18L18 6M6 6l12 12'],
                    warning: ['bg-amber-100 text-amber-700', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                    info: ['bg-primary-100 text-primary-700', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z']
                }[tipo] || ['bg-primary-100 text-primary-700', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
            }
            window.app.refreshNotifBadge = function() {
                fetch('/notificaciones/no-leidas', { headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        const n = (d && d.no_leidas) || 0;
                        const badge = document.getElementById('mpNotifBadge');
                        if (badge) {
                            badge.textContent = n > 99 ? '99+' : n;
                            badge.classList.toggle('hidden', n <= 0);
                            badge.classList.toggle('inline-flex', n > 0);
                        }
                        document.querySelectorAll('[data-notif-badge]').forEach(function(b) {
                            b.textContent = n > 99 ? '99+' : n;
                            b.classList.toggle('hidden', n <= 0);
                            b.classList.toggle('inline-flex', n > 0);
                        });
                    })
                    .catch(function() {});
            };
            function mpNotifRow(n) {
                const est = mpNotifTipoEstilo(n.tipo);
                const a = document.createElement(n.url ? 'a' : 'div');
                if (n.url) {
                    a.href = n.url;
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        fetch('/notificaciones/' + n.id + '/leer', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': mpNotifCsrf(), 'Accept': 'application/json' }
                        }).finally(function() { window.location.href = n.url; });
                    });
                }
                a.className = 'flex items-start gap-2.5 px-4 py-3 hover:bg-slate-50 transition ' + (!n.leida ? 'bg-primary-50/50' : '');
                a.innerHTML =
                    '<span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ' + est[0] + '">' +
                    '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="' + est[1] + '"/></svg></span>' +
                    '<span class="min-w-0 flex-1"><span class="block text-[13px] font-bold text-slate-800 leading-snug"></span>' +
                    '<span class="block text-[12px] text-slate-500 leading-snug mt-0.5"></span>' +
                    '<span class="block text-[11px] text-slate-400 mt-1"></span></span>' +
                    (!n.leida ? '<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary-600"></span>' : '');
                const spans = a.querySelectorAll('span span');
                spans[0].textContent = n.titulo || '';
                spans[1].textContent = n.mensaje || '';
                spans[2].textContent = n.fecha || '';
                return a;
            }
            function mpNotifCargar() {
                const list = document.getElementById('mpNotifList');
                if (!list) return;
                list.innerHTML = '<p class="px-4 py-8 text-center text-[13px] font-semibold text-slate-400">Cargando…</p>';
                fetch('/notificaciones/ajax?filtro=todas&limite=6', { headers: { 'Accept': 'application/json' } })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        list.innerHTML = '';
                        const items = (d && d.notificaciones) || [];
                        if (!items.length) {
                            list.innerHTML = '<p class="px-4 py-8 text-center text-[13px] font-semibold text-slate-400">Sin notificaciones por ahora.</p>';
                            return;
                        }
                        items.forEach(function(n) { list.appendChild(mpNotifRow(n)); });
                    })
                    .catch(function() {
                        list.innerHTML = '<p class="px-4 py-8 text-center text-[13px] font-semibold text-red-500">No se pudieron cargar.</p>';
                    });
            }
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('mpNotifBtn');
                const menu = document.getElementById('mpNotifMenu');
                if (!btn || !menu) {
                    return;
                }
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        const abierto = !menu.classList.contains('hidden');
                        if (abierto) {
                            menu.classList.add('hidden');
                        } else {
                            mpNotifCargar();
                            menu.classList.remove('hidden');
                        }
                    });
                    document.addEventListener('click', function(e) {
                        if (!menu.classList.contains('hidden') && !menu.contains(e.target) && !btn.contains(e.target)) {
                            menu.classList.add('hidden');
                        }
                    });
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') menu.classList.add('hidden');
                    });
                    const readAll = document.getElementById('mpNotifReadAll');
                    if (readAll) readAll.addEventListener('click', function() {
                        fetch('/notificaciones/leer-todas', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': mpNotifCsrf(), 'Accept': 'application/json' }
                        }).finally(function() {
                            window.app.refreshNotifBadge();
                            mpNotifCargar();
                        });
                    });
                window.app.refreshNotifBadge();
                setInterval(function() {
                    if (!document.hidden) window.app.refreshNotifBadge();
                }, 60000);
            });
})();
</script>
