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
    <div class="user-profile" style="padding-top: 1.5rem; padding-bottom: 1.5rem;">
        <div class="user-avatar" style="width: 80px; height: 80px; background-color: white; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;">
            <svg class="w-10 h-10 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
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
            
            <!-- Áreas -->
            <li>
                <a href="{{ route('admin.areas') }}" 
                   class="menu-item {{ request()->routeIs('admin.areas') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Áreas
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

<!-- Indicador de carga -->
<div id="loadingIndicator" class="loading">
    <div class="loading-content">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
        <p class="mt-2">Cargando...</p>
    </div>
</div>

