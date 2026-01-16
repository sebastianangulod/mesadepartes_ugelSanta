{{--
    Componente Sidebar para Panel de Administración
    Diseño consistente con el sidebar de Mesa de Partes
--}}
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
            <button id="settingsBtnAdmin" type="button" class="focus:outline-none transition-transform hover:scale-110" style="position: absolute; bottom: 2px; right: -28px; background: transparent; border: none; cursor: pointer; padding: 4px; z-index: 10000;">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
                <!-- Menú dropdown -->
                <div id="settingsDropdownAdmin" class="settings-dropdown" style="display: none; position: fixed; background: white; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); min-width: 200px; z-index: 10000; overflow: hidden;">
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
            <!-- Menu (Inicio) -->
            <li>
                <a href="{{ route('admin.index') }}" 
                   class="menu-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Menu
                </a>
            </li>
            
            <!-- Usuarios -->
            <li>
                <a href="{{ route('admin.usuarios') }}" 
                   class="menu-item {{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Usuarios
                </a>
            </li>
            
            <!-- Niveles -->
            <li>
                <a href="{{ route('admin.niveles') }}" 
                   class="menu-item {{ request()->routeIs('admin.niveles') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Niveles
                </a>
            </li>
            
            <!-- Procedimientos -->
            <li>
                <a href="{{ route('admin.procedimientos') }}" 
                   class="menu-item {{ request()->routeIs('admin.procedimientos') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Procedimientos
                </a>
            </li>
            
            <!-- Mesa de Partes -->
            <li>
                <a href="{{ route('mesa-partes.tramites-recibidos') }}" 
                   class="menu-item {{ request()->routeIs('mesa-partes.tramites-recibidos') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Mesa de Partes
                </a>
            </li>
        </ul>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const settingsBtn = document.getElementById('settingsBtnAdmin');
    const settingsDropdown = document.getElementById('settingsDropdownAdmin');
    
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

<!-- Indicador de carga -->
<div id="loadingIndicator" class="loading">
    <div class="loading-content">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-2">Cargando...</p>
    </div>
</div>

