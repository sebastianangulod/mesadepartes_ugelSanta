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
            
            <!-- Trámite Nuevo - Solo para usuarios tipo 1 (externos) -->
            @if(Auth::user()->tipo_user == 1)
            <li>
                <a href="{{ route('mesa-partes.nuevo') }}" 
                   class="menu-item {{ request()->routeIs('mesa-partes.nuevo') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Trámite Nuevo
                </a>
            </li>
            @endif
            
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
            
            <!-- Recepción de Trámites - Solo para usuarios tipo 2 (internos) -->
            @if(Auth::user()->tipo_user == 2)
            <li>
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
            </li>
            @endif
        </ul>
    </nav>
</aside>

