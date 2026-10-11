{{--
    Sidebar Mesa de Partes — UGEL Santa
    Diseño institucional moderno: navy profundo + acentos oficiales.
    Props: tramitesPendientes (int)
--}}
@props(['tramitesPendientes' => 0])

@php
    $user = Auth::user();
    $isInternal = $user && $user->tipo_user == 2;
    $isAdmin = $user && $user->esAdmin();

    // Nombre de usuario: campo único `nombres` tal cual, sin recortes
    // (funciona igual con uno o varios nombres).
    $displayName = $user ? trim($user->nombres ?? '') : '';
    if ($displayName === '') {
        $displayName = 'Usuario';
    }

    // Iniciales desde las dos primeras palabras de `nombres`.
    $words = preg_split('/\s+/', $displayName);
    $firstWord = $words[0] ?? 'U';
    $secondInitial = isset($words[1]) ? mb_substr($words[1], 0, 1) : mb_substr($firstWord, 1, 1);
    $initials = strtoupper(mb_substr($firstWord, 0, 1) . $secondInitial);

    // Etiqueta de rol: solo para administrador y personal interno.
    // Al usuario externo no se le muestra etiqueta.
    $roleLabel = '';
    if ($user) {
        if ($user->esAdministrador()) {
            $roleLabel = 'Administrador';
        } elseif ($user->esInterno()) {
            $roleLabel = 'Personal interno';
        }
    }
    $tipoPersona = !$user ? '' : ($user->tipo_persona == 1 ? 'Persona natural' : ($user->tipo_persona == 2 ? 'Persona jurídica' : 'Entidad pública'));

    $linkBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-semibold transition-all duration-200';
    $linkIdle = 'text-slate-300/90 hover:text-white hover:bg-white/10';
    $linkActive = 'bg-white text-primary-900 shadow-lg shadow-black/20 hover:bg-white';
@endphp

<aside id="mpSidebar"
    class="sidebar-scroll fixed inset-y-0 left-0 w-[280px] z-40 -translate-x-full lg:translate-x-0 transition-transform duration-300
           bg-gradient-to-b from-[#0b1c34] via-[#112a4d] to-[#0b1c34] text-white flex flex-col overflow-hidden">

    <!-- Textura + línea superior -->
    <div class="sidebar-pattern absolute inset-0 pointer-events-none opacity-60"></div>
    <div class="h-1 bg-gradient-to-r from-primary-400 via-white/70 to-accent-600 shrink-0 relative"></div>

    <div class="relative flex flex-col h-full">
        <!-- ═══ Tarjeta de usuario ═══ -->
        <div class="px-4 pt-5 shrink-0">
            <div class="rounded-2xl bg-white/[.07] border border-white/10 p-4 backdrop-blur-sm">
                <div class="flex items-center gap-3">
                    <div class="relative shrink-0">
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-white to-slate-200 text-primary-900 flex items-center justify-center text-[15px] font-extrabold shadow">
                            {{ $initials }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-400 border-2 border-[#16294a]" title="En línea"></span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13.5px] font-bold truncate leading-tight" title="{{ $displayName }}">{{ $displayName }}</p>
                        @if($roleLabel)
                        <p class="text-[11.5px] text-slate-300/80 font-medium truncate">{{ $roleLabel }}</p>
                        @endif
                    </div>
                    <!-- Engranaje / ajustes -->
                    <div class="relative shrink-0">
                        <button id="mpSettingsBtn" type="button" aria-label="Ajustes de cuenta" aria-haspopup="true"
                            class="w-8 h-8 rounded-lg bg-white/10 hover:bg-white/20 border border-white/10 flex items-center justify-center transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                        <div id="mpSettingsMenu"
                            class="hidden absolute right-0 top-10 w-56 rounded-xl bg-white text-slate-700 shadow-2xl border border-slate-200 overflow-hidden z-50">
                            <p class="px-4 pt-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">Mi cuenta</p>
                            <a href="{{ route('perfil.actualizar-datos') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] font-semibold hover:bg-slate-50 transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Actualizar datos
                            </a>
                            <a href="{{ route('perfil.actualizar-contraseña') }}"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-[13px] font-semibold hover:bg-slate-50 transition border-t border-slate-100">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                Cambiar contraseña
                            </a>
                        </div>
                    </div>
                </div>
                @if($tipoPersona)
                <p class="mt-2.5 inline-flex items-center rounded-md bg-white/10 text-[11px] font-semibold text-slate-200 px-2 py-0.5">{{ $tipoPersona }}</p>
                @endif
            </div>
        </div>

        <!-- ═══ Tipo de portal ═══ -->
        <div class="px-4 mt-3 shrink-0">
            <span class="flex items-center justify-center gap-1.5 rounded-full bg-white/10 border border-white/15 text-[11px] font-bold px-2.5 py-1.5 text-slate-100">
                <span class="w-1.5 h-1.5 rounded-full {{ $isInternal ? 'bg-amber-300' : 'bg-emerald-300' }}"></span>
                {{ $isInternal ? 'Portal interno' : 'Portal ciudadano' }}
            </span>
        </div>

        <!-- ═══ Navegación (única zona con scroll) ═══ -->
        <nav class="flex-1 min-h-0 overflow-y-auto mp-scroll sidebar-scroll px-3 py-5 space-y-6" aria-label="Navegación principal">

            <!-- Principal -->
            <div>
                <p class="px-3 mb-2 text-[10.5px] font-extrabold uppercase tracking-[.14em] text-slate-400">Principal</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('mesa-partes.index') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('mesa-partes.index') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('mesa-partes.index') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            </span>
                            <span class="flex-1">Inicio</span>
                            @if(request()->routeIs('mesa-partes.index'))
                                <span class="w-1.5 h-6 rounded-full bg-primary-600"></span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('mesa-partes.nuevo') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('mesa-partes.nuevo','mesa-partes.guardar','mesa-partes.confirmacion') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('mesa-partes.nuevo') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </span>
                            <span class="flex-1">Trámite nuevo</span>
                            <span class="text-[10px] font-extrabold uppercase tracking-wide rounded-md px-1.5 py-0.5 {{ request()->routeIs('mesa-partes.nuevo') ? 'bg-primary-600 text-white' : 'bg-emerald-400/20 text-emerald-200 border border-emerald-300/20' }}">{{ $isInternal ? 'Presencial' : 'Virtual' }}</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('mesa-partes.rastrear') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('mesa-partes.rastrear*') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('mesa-partes.rastrear*') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </span>
                            <span class="flex-1">Rastrear trámite</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('mesa-partes.documentos-enviados') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('mesa-partes.documentos-enviados') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('mesa-partes.documentos-enviados') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span class="flex-1">Documentos enviados</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('notificaciones.index') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('notificaciones.*') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('notificaciones.*') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.857 17.082a24 24 0 00-5.714 0M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9M10.3 21a1.94 1.94 0 003.4 0"/></svg>
                            </span>
                            <span class="flex-1">Notificaciones</span>
                            <span data-notif-badge class="hidden min-w-[24px] h-6 px-1.5 rounded-full bg-red-500 text-white text-[11px] font-extrabold items-center justify-center shadow">0</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Gestión interna -->
            <div>
                <p class="px-3 mb-2 text-[10.5px] font-extrabold uppercase tracking-[.14em] text-slate-400">
                    {{ $isInternal ? 'Bandeja interna' : 'Mis trámites' }}
                </p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('mesa-partes.tramites-recibidos') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('mesa-partes.tramites-recibidos*') ? $linkActive : ($isInternal ? 'bg-amber-300/15 text-amber-100 border border-amber-200/20 hover:bg-amber-300/25 hover:text-white' : $linkIdle) }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('mesa-partes.tramites-recibidos*') ? 'bg-primary-600 text-white' : ($isInternal ? 'bg-amber-300 text-amber-950' : 'bg-white/10 text-slate-200 group-hover:bg-white/15') }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                            </span>
                            <span class="flex-1">{{ $isInternal ? 'Recepción de trámites' : 'Mis trámites' }}</span>
                            @if(($tramitesPendientes ?? 0) > 0 && $isInternal)
                                <span class="min-w-[24px] h-6 px-1.5 rounded-full bg-red-500 text-white text-[11px] font-extrabold inline-flex items-center justify-center shadow" title="{{ $tramitesPendientes }} pendientes">{{ $tramitesPendientes > 99 ? '99+' : $tramitesPendientes }}</span>
                            @endif
                        </a>
                        @if($isInternal)
                        <p class="px-3 mt-1.5 text-[11px] leading-snug text-slate-400">Bandeja de trabajo diaria: acepta, observa, deriva y finaliza.</p>
                        @endif
                    </li>
                    @if($isInternal)
                    <li>
                        <a href="{{ route('mesa-partes.reportes') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('mesa-partes.reportes*') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('mesa-partes.reportes*') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6V7m4 10v-3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </span>
                            <span class="flex-1">Reportes</span>
                            @if(request()->routeIs('mesa-partes.reportes*'))
                                <span class="w-1.5 h-6 rounded-full bg-primary-600"></span>
                            @endif
                        </a>
                        <p class="px-3 mt-1.5 text-[11px] leading-snug text-slate-400">Estadísticas semanales y mensuales de trámites ingresados.</p>
                    </li>
                    @endif
                </ul>
            </div>

            @if($isAdmin)
            <div>
                <p class="px-3 mb-2 text-[10.5px] font-extrabold uppercase tracking-[.14em] text-slate-400">Administración</p>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('admin.index') }}"
                           class="{{ $linkBase }} {{ request()->routeIs('admin.*') ? $linkActive : $linkIdle }}">
                            <span class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('admin.*') ? 'bg-primary-50 text-primary-700' : 'bg-white/10 text-slate-200 group-hover:bg-white/15' }}">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            </span>
                            <span class="flex-1">Panel administrador</span>
                            <svg class="w-3.5 h-3.5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </li>
                </ul>
            </div>
            @endif
        </nav>

        <!-- ═══ Salida (siempre visible, fijada al fondo) ═══ -->
        <div class="shrink-0 px-4 pt-4 pb-7 border-t border-white/10 bg-black/25 space-y-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 rounded-xl bg-red-500 hover:bg-red-600 active:bg-red-700 text-white text-[13.5px] font-extrabold py-3 shadow-lg shadow-red-950/40 transition-all">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Cerrar sesión
                </button>
            </form>
            <p class="text-center text-[10.5px] text-slate-400 font-medium">© {{ date('Y') }} UGEL Santa · Todos los derechos reservados</p>
        </div>
    </div>
</aside>

<script>
(function() {
    function initMpSidebar() {
        const btn = document.getElementById('mpSettingsBtn');
        const menu = document.getElementById('mpSettingsMenu');
        if (!btn || !menu) return;
        if (btn.dataset.bound) return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.classList.toggle('hidden');
        });
        document.addEventListener('click', function(e) {
            if (!menu.classList.contains('hidden') && !menu.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') menu.classList.add('hidden');
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMpSidebar);
    } else {
        initMpSidebar();
    }
})();
</script>
