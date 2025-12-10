{{--
    Componente Sidebar para Mesa de Partes
    Reutilizable en todas las vistas de Mesa de Partes
    Equivalente al sidebar de documentosEnviados.php del PHP puro
    
    Props:
    - tramitesPendientes: número de trámites pendientes para mostrar en badge (opcional, default: 0)
--}}
@props(['tramitesPendientes' => 0])

<aside class="w-64 bg-gray-800 text-white p-4 fixed h-full overflow-y-auto">
    <h1 class="text-xl font-bold mb-8">Mesa de Partes UGEL</h1>
    
    <div class="mb-8">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 text-sm rounded block w-full text-center">
                Cerrar Sesión
            </button>
        </form>
    </div>
    
    <div class="mb-8">
        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
        <p class="text-center text-white mt-2">
            {{ strtok(Auth::user()->nombres, ' ') }} {{ Auth::user()->apellido_paterno }}
        </p>
        <p class="text-center text-white text-sm">
            @if(Auth::user()->tipo_persona == 1)
                Persona Natural
            @elseif(Auth::user()->tipo_persona == 2)
                Persona Jurídica
            @elseif(Auth::user()->tipo_persona == 3)
                Entidad Pública
            @endif
        </p>
    </div>
    
    <nav>
        <ul>
            @if(Auth::user()->tipo_user == 1)
            <li class="mb-4">
                <a href="{{ route('mesa-partes.nuevo') }}" 
                   class="flex items-center hover:text-gray-300 {{ request()->routeIs('mesa-partes.nuevo') ? 'text-blue-300' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Trámite Nuevo
                </a>
            </li>
            @endif
            
            @if(Auth::user()->esAdmin())
            <li class="mb-4">
                <a href="{{ route('admin.index') }}" 
                   class="flex items-center hover:text-gray-300 {{ request()->routeIs('admin.*') ? 'text-blue-300' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    Administrador
                </a>
            </li>
            @endif
            
            @if(Auth::user()->tipo_user == 2)
            <li class="mb-4">
                <a href="{{ route('mesa-partes.tramites-recibidos') }}" 
                   class="flex items-center hover:text-gray-300 {{ request()->routeIs('mesa-partes.tramites-recibidos') ? 'text-blue-300' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    Recepción de Trámites
                    @if(isset($tramitesPendientes) && $tramitesPendientes > 0)
                        <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $tramitesPendientes }}</span>
                    @endif
                </a>
            </li>
            @endif
            
            <li class="mb-4">
                <a href="{{ route('mesa-partes.rastrear') }}" 
                   class="flex items-center hover:text-gray-300 {{ request()->routeIs('mesa-partes.rastrear') ? 'text-blue-300' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Rastrear Trámites
                </a>
            </li>
            
            <li class="mb-4">
                <a href="{{ route('mesa-partes.documentos-enviados') }}" 
                   class="flex items-center hover:text-gray-300 {{ request()->routeIs('mesa-partes.documentos-enviados') ? 'text-blue-300' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13H5v-2h14v2z"></path>
                    </svg>
                    Documentos Enviados
                </a>
            </li>
            
            <li class="mb-4">
                <a href="{{ route('mesa-partes.index') }}" 
                   class="flex items-center hover:text-gray-300 {{ request()->routeIs('mesa-partes.index') ? 'text-blue-300' : '' }}">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Inicio
                </a>
            </li>
        </ul>
    </nav>
</aside>

