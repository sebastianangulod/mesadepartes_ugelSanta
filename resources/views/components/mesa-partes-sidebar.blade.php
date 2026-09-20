{{--
    Componente Sidebar para Mesa de Partes
    Reutilizable en todas las vistas de Mesa de Partes
    Equivalente al sidebar de documentosEnviados.php del PHP puro
    
    Props:
    - tramitesPendientes: número de trámites pendientes para mostrar en badge (opcional, default: 0)
--}}
@props(['tramitesPendientes' => 0])

<aside class="sidebar text-white p-4" style="padding-top: 0;">
    <!-- Header con título y botón de cerrar sesión -->
    <div class="pt-4 pb-4 border-b border-gray-600">
        <h1 class="text-xl font-bold mb-4 px-4">Mesa de Partes UGEL</h1>
        
        <div class="px-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 text-sm rounded block w-full text-center">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
    
    <!-- Perfil de usuario -->
    <div class="user-profile" style="padding-top: 1.5rem; padding-bottom: 1.5rem; position: relative;">
        <div class="user-avatar" style="width: 80px; height: 80px; background-color: white; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; position: relative;">
            <svg class="w-10 h-10 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            <!-- Botón de engranaje con dropdown -->
            <button id="settingsBtn" type="button" class="focus:outline-none transition-transform hover:scale-110" style="position: absolute; bottom: 2px; right: -28px; background: transparent; border: none; cursor: pointer; padding: 4px; z-index: 10000;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
                <!-- Menú dropdown -->
                <div id="settingsDropdown" class="settings-dropdown" style="display: none; position: fixed; background: white; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); min-width: 200px; z-index: 10000; overflow: hidden;">
                    <a href="{{ route('perfil.actualizar-datos') }}" class="settings-menu-item" style="padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 10px; transition: background-color 0.2s; text-decoration: none;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='white'">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span style="color: #374151; font-size: 14px; font-weight: 500;">Actualizar Datos</span>
                    </a>
                    <a href="{{ route('perfil.actualizar-contraseña') }}" class="settings-menu-item" style="padding: 12px 16px; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: background-color 0.2s; text-decoration: none;" onmouseover="this.style.backgroundColor='#f3f4f6'" onmouseout="this.style.backgroundColor='white'">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        <span style="color: #374151; font-size: 14px; font-weight: 500;">Actualizar Contraseña</span>
                    </a>
                </div>
        </div>
        <p class="text-white font-bold text-base mt-2" style="text-transform: uppercase;">
            {{ strtoupper(strtok(Auth::user()->nombres, ' ') . ' ' . Auth::user()->apellido_paterno) }}
        </p>
        <p class="text-white text-sm mt-1" style="color: #a0aec0;">
            @if(Auth::user()->tipo_persona == 1)
                Persona Natural
            @elseif(Auth::user()->tipo_persona == 2)
                Persona Jurídica
            @elseif(Auth::user()->tipo_persona == 3)
                Entidad Pública
            @endif
        </p>
    </div>
    
    <!-- Menú de navegación -->
    <nav class="mt-4">
        <ul style="list-style: none; padding: 0; margin: 0;">
            <!-- Inicio - Primero en el menú -->
            <li>
                <a href="{{ route('mesa-partes.index') }}" 
                   class="menu-item {{ request()->routeIs('mesa-partes.index') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Inicio
                </a>
            </li>
            
            <!-- Trámite Nuevo - Disponible para todos los usuarios -->
            <!-- Usuarios externos crean trámites VIRTUALES -->
            <!-- Usuarios internos crean trámites PRESENCIALES -->
            <li>
                <a href="{{ route('mesa-partes.nuevo') }}" 
                   class="menu-item {{ request()->routeIs('mesa-partes.nuevo') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Trámite Nuevo
                </a>
            </li>
            
            <!-- Rastrear Trámites -->
            <li>
                <a href="{{ route('mesa-partes.rastrear') }}" 
                   class="menu-item {{ request()->routeIs('mesa-partes.rastrear') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Rastrear Trámites
                </a>
            </li>
            
            <!-- Documentos Enviados -->
            <li>
                <a href="{{ route('mesa-partes.documentos-enviados') }}" 
                   class="menu-item {{ request()->routeIs('mesa-partes.documentos-enviados') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13H5v-2h14v2z"></path>
                    </svg>
                    Documentos Enviados
                </a>
            </li>
            
            <!-- Administrador - Solo para admins -->
            @if(Auth::user()->esAdmin())
            <li>
                <a href="{{ route('admin.index') }}" 
                   class="menu-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Administrador
                </a>
            </li>
            @endif
            
            <!-- Mis Trámites / Recepción de Trámites - Para todos los usuarios autenticados -->
            <li>
                @if(Auth::user()->tipo_user == 2)
                    <a href="{{ route('mesa-partes.tramites-recibidos') }}" 
                       class="menu-item {{ request()->routeIs('mesa-partes.tramites-recibidos') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Recepción de Trámites
                        @if(isset($tramitesPendientes) && $tramitesPendientes > 0)
                            <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $tramitesPendientes }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('mesa-partes.tramites-recibidos') }}" 
                       class="menu-item {{ request()->routeIs('mesa-partes.tramites-recibidos') ? 'active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        Mis Trámites
                    </a>
                @endif
            </li>
        </ul>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const settingsBtn = document.getElementById('settingsBtn');
    const settingsDropdown = document.getElementById('settingsDropdown');
    
    if (settingsBtn && settingsDropdown) {
        // Función para calcular y posicionar el dropdown
        function positionDropdown() {
            const btnRect = settingsBtn.getBoundingClientRect();
            const dropdown = settingsDropdown;
            
            // Posicionar a la derecha del botón, centrado verticalmente
            dropdown.style.top = (btnRect.top + btnRect.height / 2) + 'px';
            dropdown.style.transform = 'translateY(-50%)';
            dropdown.style.left = (btnRect.right + 12) + 'px';
        }
        
        // Toggle dropdown al hacer clic en el botón
        settingsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isVisible = settingsDropdown.style.display === 'block';
            
            if (!isVisible) {
                positionDropdown();
                settingsDropdown.style.display = 'block';
            } else {
                settingsDropdown.style.display = 'none';
            }
        });
        
        // Reposicionar al hacer scroll o redimensionar
        window.addEventListener('scroll', function() {
            if (settingsDropdown.style.display === 'block') {
                positionDropdown();
            }
        });
        
        window.addEventListener('resize', function() {
            if (settingsDropdown.style.display === 'block') {
                positionDropdown();
            }
        });
        
        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (!settingsBtn.contains(e.target) && !settingsDropdown.contains(e.target)) {
                settingsDropdown.style.display = 'none';
            }
        });
        
        // Prevenir que el clic en el menú lo cierre
        settingsDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});
</script>

