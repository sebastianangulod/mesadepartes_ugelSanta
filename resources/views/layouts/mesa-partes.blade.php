<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistema de Mesa de Partes Digital — UGEL Santa">

    <title>@yield('title', 'Mesa de Partes · UGEL Santa')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html { -webkit-font-smoothing: antialiased; }
        body { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }

        /* Scrollbar institucional */
        .mp-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .mp-scroll::-webkit-scrollbar-track { background: transparent; }
        .mp-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,.45); border-radius: 999px; }
        .mp-scroll::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,.7); }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.18); border-radius: 999px; }

        /* Patrón sutil del sidebar */
        .sidebar-pattern {
            background-image:
                radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 18px 18px;
        }

        /* Animaciones */
        @keyframes mp-fade-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        /* NOTA: fill 'backwards' (no 'both'): retener el keyframe final dejaría un
           transform perpetuo que rompería el posicionamiento fixed de los modales
           (el overlay debe cubrir toda la pantalla, no solo el contenedor). */
        .mp-animate { animation: mp-fade-up .45s ease backwards; }
        .mp-animate-1 { animation-delay: .05s; } .mp-animate-2 { animation-delay: .12s; }
        .mp-animate-3 { animation-delay: .18s; } .mp-animate-4 { animation-delay: .24s; }

        @keyframes mp-spin { to { transform: rotate(360deg); } }
        .mp-spinner { animation: mp-spin 1s linear infinite; }

        @keyframes mp-modal-in { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .mp-modal-panel { animation: mp-modal-in .22s ease both; }

        /* Foco accesible */
        :focus-visible { outline: 2px solid #2b6dc0; outline-offset: 2px; }

        @media print {
            aside, header.mp-topbar, footer.mp-footer, #sidebarOverlay { display: none !important; }
            .mp-main-wrap { margin-left: 0 !important; }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">

    <!-- Overlay móvil -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-950/50 backdrop-blur-[2px] z-30 hidden lg:hidden"></div>

    <div class="min-h-screen lg:flex">
        <!-- Sidebar -->
        <x-mesa-partes-sidebar :tramitesPendientes="$tramitesPendientes ?? 0" />

        <!-- Columna principal -->
        <div class="mp-main-wrap flex-1 flex flex-col min-w-0 lg:ml-[280px] min-h-screen">

            @php
                $mpUser = Auth::user();
                $mpName = $mpUser ? trim($mpUser->nombres ?? '') : '';
                if ($mpName === '') {
                    $mpName = 'Usuario';
                }
                $mpWords = preg_split('/\s+/', $mpName);
                $mpFirst = $mpWords[0] ?? 'U';
                $mpSecond = isset($mpWords[1]) ? mb_substr($mpWords[1], 0, 1) : mb_substr($mpFirst, 1, 1);
                $mpInitials = strtoupper(mb_substr($mpFirst, 0, 1) . $mpSecond);
                // Encabezado: nombre y apellido paterno (el sidebar muestra solo el nombre).
                $mpShort = trim($mpFirst . ' ' . ($mpUser->apellido_paterno ?? ''));
                $mpFull = ($mpUser->nombre_completo ?? $mpName) ?: $mpName;
            @endphp
            <!-- ══════════ TOPBAR ══════════ -->
            <header class="mp-topbar sticky top-0 z-20 bg-white/85 backdrop-blur-md border-b border-slate-200">
                <div class="flex items-center gap-3 px-4 sm:px-6 lg:px-8 h-[68px] max-w-[1440px] mx-auto w-full">
                    <!-- Hamburger móvil -->
                    <button id="sidebarToggle" type="button"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition"
                        aria-label="Abrir menú">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Título -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 truncate">@yield('header', 'Mesa de Partes')</h1>
                            @if(Auth::check() && Auth::user()->esInterno())
                                <span class="hidden sm:inline-flex items-center gap-1 rounded-full bg-primary-50 border border-primary-100 text-primary-700 text-[11px] font-bold px-2.5 py-0.5 uppercase tracking-wide">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                                    Uso interno
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Fecha + usuario -->
                    <div class="hidden md:flex flex-col items-end mr-1 leading-tight">
                        <span class="text-[12px] font-semibold text-slate-700 capitalize">{{ ucfirst(\Carbon\Carbon::now('America/Lima')->locale('es')->translatedFormat('l, d \d\e F')) }}</span>
                        <span class="text-[11px] text-slate-400">{{ \Carbon\Carbon::now('America/Lima')->format('Y · H:i') }} · Lima, Perú</span>
                    </div>

                    @auth
                    <div class="flex items-center gap-2 pl-2 sm:border-l sm:border-slate-200 sm:ml-1 sm:pl-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-600 to-primary-900 text-white flex items-center justify-center text-sm font-extrabold shadow-md ring-2 ring-white">
                            {{ $mpInitials }}
                        </div>
                        <div class="hidden sm:block leading-tight">
                            <p class="text-[13px] font-bold text-slate-800 max-w-[160px] truncate" title="{{ $mpFull }}">{{ $mpShort }}</p>
                            <p class="text-[11px] text-slate-500 font-medium">
                                @if(Auth::user()->esAdministrador()) Administrador
                                @elseif(Auth::user()->esInterno()) Personal interno
                                @else Usuario externo @endif
                            </p>
                        </div>
                    </div>
                    @endauth
                </div>
                <!-- Línea institucional -->
                <div class="h-[3px] bg-gradient-to-r from-primary-800 via-primary-500 to-accent-600"></div>
            </header>

            <!-- ══════════ CONTENIDO ══════════ -->
            <main class="flex-1 w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
                {{-- El título de la vista vive únicamente en el encabezado superior (topbar).
                     Aquí solo se muestra el subtítulo y las acciones para evitar la duplicación. --}}
                @php
                    $hasSubtitle = trim($__env->yieldContent('subtitle')) !== '';
                    $hasActions = trim($__env->yieldContent('actions')) !== '';
                @endphp
                @if($hasSubtitle || $hasActions)
                <div class="mp-animate flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
                    <div class="min-w-0">
                        @hasSection('subtitle')
                            <p class="text-sm text-slate-500 max-w-2xl">@yield('subtitle')</p>
                        @endif
                    </div>
                    @hasSection('actions')
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            @yield('actions')
                        </div>
                    @endif
                </div>
                @endif

                <!-- Alertas -->
                @unless(isset($hideAlerts) && $hideAlerts)
                    <div class="mp-animate mp-animate-1"><x-alerts /></div>
                @endunless

                <!-- Cuerpo -->
                <div class="content-body mp-animate mp-animate-2">
                    @yield('content')
                </div>
            </main>

            <!-- ══════════ FOOTER ══════════ -->
            <footer class="mp-footer mt-auto border-t border-slate-200 bg-white">
                <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col lg:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3.5">
                        <span class="inline-flex items-center rounded-2xl border border-slate-200 bg-white shadow-sm px-4 py-2.5 shrink-0">
                            <img src="{{ asset('img/logo-ugel.png') }}" alt="UGEL Santa" class="h-12 w-auto object-contain" onerror="this.style.display='none'">
                        </span>
                        <span class="text-[12.5px] leading-snug text-slate-500">
                            <strong class="block text-[13.5px] text-slate-800">UGEL Santa · Mesa de Partes Digital</strong>
                            Sistema de trámite documentario — Unidad de Gestión Educativa Local Santa
                        </span>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-x-6 gap-y-1.5 text-[12px] text-slate-500 text-center sm:text-right">
                        <p class="flex items-center gap-1.5 font-semibold text-slate-600">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Atención al ciudadano · Lun–Vie 8:00–16:30 · Chimbote, Santa — Áncash
                        </p>
                        <p class="font-medium">© {{ date('Y') }} UGEL Santa · Todos los derechos reservados</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] bg-slate-950/45 backdrop-blur-[2px] items-center justify-center">
        <div class="bg-white rounded-2xl px-8 py-6 shadow-2xl flex flex-col items-center gap-3 mp-modal-panel">
            <div class="mp-spinner w-10 h-10 rounded-full border-4 border-slate-200 border-t-primary-600"></div>
            <p class="text-sm font-semibold text-slate-700">Procesando solicitud…</p>
            <p class="text-xs text-slate-400">Por favor, espere un momento</p>
        </div>
    </div>

    <script>
        (function() {
            const overlay = document.getElementById('sidebarOverlay');
            const toggle = document.getElementById('sidebarToggle');
            // El aside lo renderiza el componente con id="mpSidebar"
            const sidebar = document.getElementById('mpSidebar');
            const loading = document.getElementById('loadingOverlay');

            function openSidebar() {
                if (!sidebar) return;
                sidebar.classList.remove('-translate-x-full');
                if (overlay) overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
            }
            function closeSidebar() {
                if (!sidebar) return;
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    if (overlay) overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            }
            if (toggle) toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (sidebar && sidebar.classList.contains('-translate-x-full')) openSidebar();
                else closeSidebar();
            });
            if (overlay) overlay.addEventListener('click', closeSidebar);
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    if (overlay) overlay.classList.add('hidden');
                    if (sidebar) sidebar.classList.remove('-translate-x-full');
                    document.body.classList.remove('overflow-hidden');
                } else {
                    if (sidebar) sidebar.classList.add('-translate-x-full');
                }
            });
            // Cerrar sidebar móvil al navegar
            document.querySelectorAll('#mpSidebar a').forEach(function(a) {
                a.addEventListener('click', function() { closeSidebar(); });
            });

            function hideLoading() {
                if (!loading) return;
                loading.classList.add('hidden');
                loading.classList.remove('flex');
                document.querySelectorAll('.mp-main-wrap, #mpSidebar').forEach(function(el) {
                    el.removeAttribute('inert');
                });
            }
            function showLoading() {
                if (!loading) return;
                loading.classList.remove('hidden');
                loading.classList.add('flex');
                // Congelar la interfaz: ni clics, ni teclado, ni navegación
                // mientras dura la operación.
                document.querySelectorAll('.mp-main-wrap, #mpSidebar').forEach(function(el) {
                    el.setAttribute('inert', '');
                });
            }
            hideLoading();

            // Contador de mutaciones en curso (lo usa el interceptor fetch y
            // los modales para ignorar cierres con Escape/backdrop a mitad de proceso).
            // Se inicializa en el objeto window.app más abajo.

            // Rutas POST de solo lectura: no congelan (usan loader inline).

            // Interceptor fetch: toda mutación (POST/PUT/PATCH/DELETE) congela
            // la interfaz hasta resolverse. Las lecturas no.
            if (window.fetch && !window.fetch.__mpWrapped) {
                const originalFetch = window.fetch.bind(window);
                const wrappedFetch = function(url, options) {
                    const method = (((options && options.method) || 'GET') + '').toUpperCase();
                    const target = (typeof url === 'string' ? url : (url && url.url) || '') + '';
                    const esLectura = method === 'GET' || window.app.READ_ONLY_POSTS.some(function(p) {
                        return target.indexOf(p) !== -1;
                    });
                    if (esLectura) {
                        return originalFetch(url, options);
                    }
                    window.app.mutationsInFlight++;
                    window.app.showLoading();
                    return originalFetch(url, options).finally(function() {
                        window.app.mutationsInFlight = Math.max(0, window.app.mutationsInFlight - 1);
                        if (window.app.mutationsInFlight === 0) {
                            window.app.hideLoading();
                        }
                    });
                };
                wrappedFetch.__mpWrapped = true;
                window.fetch = wrappedFetch;
            }

            // Envíos nativos (login, registro, perfil...): congelar la página
            // durante la petición. Los formularios AJAX cancelan el envío
            // (preventDefault) y por tanto no congelan desde aquí.
            document.addEventListener('submit', function(e) {
                const form = e.target;
                setTimeout(function() {
                    if (!e.defaultPrevented) {
                        const btn = e.submitter || (form && form.querySelector && form.querySelector('[type="submit"]'));
                        if (btn) {
                            btn.disabled = true;
                        }
                        window.app.showLoading();
                    }
                }, 0);
            });

            if (typeof window.$ !== 'undefined' && window.$.ajaxSetup) {
                window.$.ajaxSetup({
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                });
            }

            window.app = {
                showLoading: showLoading,
                hideLoading: hideLoading,
                // Congelamiento uniforme: el interceptor fetch y los envíos
                // nativos leen y actualizan estos campos en tiempo real.
                mutationsInFlight: 0,
                READ_ONLY_POSTS: ['/mesa-partes/rastrear/buscar', '/mesa-partes/tramites-recibidos/ajax'],
                openSidebar: openSidebar,
                closeSidebar: closeSidebar,
                showNotification: function(message, type) {
                    console.log('[' + (type || 'success').toUpperCase() + '] ' + message);
                },
                confirmAction: function(message, callback) { if (confirm(message)) callback(); },
                formatDate: function(dateString) {
                    try {
                        return new Date(dateString).toLocaleDateString('es-PE', {
                            year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
                        });
                    } catch (e) { return dateString; }
                },
                formatEstado: function(estado) {
                    const estados = { 'PENDIENTE': 'Pendiente', 'EN_PROCESO': 'En Proceso', 'OBSERVADO': 'Observado', 'VERIFICADO': 'Verificado', 'FINALIZADO': 'Finalizado', 'RECHAZADO': 'Rechazado' };
                    return estados[estado] || estado;
                }
            };

            document.addEventListener('DOMContentLoaded', hideLoading);
            window.addEventListener('load', hideLoading);
            window.addEventListener('pageshow', hideLoading);
            window.addEventListener('popstate', hideLoading);

            // Loading solo en navegación real (evita que se quede pegado en descargas / nueva pestaña)
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"])').forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (this.hasAttribute('download')) return;
                        if (this.target && this.target !== '_self') return;
                        const href = this.getAttribute('href');
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) showLoading();
                    });
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
