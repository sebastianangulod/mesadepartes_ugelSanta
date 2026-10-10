<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Sistema de Mesa de Partes - UGEL')</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Estilos personalizados (solo CSS válido: @apply no funciona en <style> inline) -->
    <style>
        .sidebar-transition {
            transition: all 0.3s ease-in-out;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-sans">
    <div class="min-h-screen flex flex-col">
        @unless(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('login.interno') || request()->routeIs('password.request') || request()->routeIs('perfil.actualizar-contraseña'))
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo y título -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="flex items-baseline space-x-2 text-lg sm:text-xl font-semibold tracking-tight">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-primary-50 text-xs font-bold uppercase text-primary-800 border border-primary-200">
                                    UGEL
                                </span>
                                <span class="hidden sm:inline text-primary-700">
                                    Mesa de Partes
                                </span>
                            </h1>
                        </div>
                    </div>
                    
                    <!-- Navegación principal -->
                    @auth
                    <nav class="hidden md:flex space-x-8">
                        @if(auth()->user()->esAdministrador())
                            <a href="{{ route('admin.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                Dashboard Admin
                            </a>
                        @elseif(auth()->user()->esInterno())
                            <a href="{{ route('mesa-partes.tramites-recibidos') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                Trámites Recibidos
                            </a>
                        @else
                            <a href="{{ route('mesa-partes.index') }}" class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium transition duration-200">
                                Dashboard
                            </a>
                        @endif
                    </nav>
                    
                    <!-- Menú de usuario -->
                    <div class="flex items-center space-x-4">
                        <!-- Notificaciones -->
                        <div class="relative">
                            <button class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-full p-1">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Perfil de usuario -->
                        <div class="relative">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">
                                        {{ substr(auth()->user()->nombres ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                                <div class="hidden md:block">
                                    <div class="text-sm font-medium">{{ auth()->user()->nombre_completo ?? 'Usuario' }}</div>
                                    <div class="text-xs text-gray-500">{{ auth()->user()->rol_formateado ?? 'Invitado' }}</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botón de cerrar sesión -->
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                <span class="hidden sm:inline">Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                    @else
                    <!-- Sin accesos en cabecera: cada vista trae sus propios enlaces -->
                    @endauth
                </div>
            </div>
        </header>
        @endunless
        
        <!-- Contenido principal -->
        <main class="flex-1">
            <!-- Breadcrumbs y título de página (solo si la vista los define) -->
            @hasSection('header')
            <div class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            @yield('header')
                        </div>
                        <div>
                            @yield('actions')
                        </div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Contenido de la página -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        <span class="font-semibold">Correcto:</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        © {{ date('Y') }} UGEL - Mesa de Partes. Todos los derechos reservados.
                    </div>
                    <div class="text-sm text-gray-500">
                        Versión 1.0.0
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- Scripts -->
    <script>
        // Configuración global para AJAX (usando fetch API en lugar de jQuery)
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Funciones globales
        window.app = {
            // Mostrar notificación
            showNotification: function(message, type = 'success') {
                // Aquí puedes integrar una librería de notificaciones como Toastr
                console.log(`[${type.toUpperCase()}] ${message}`);
            },
            
            // Confirmar acción
            confirmAction: function(message, callback) {
                if (confirm(message)) {
                    callback();
                }
            },
            
            // Formatear fecha
            formatDate: function(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('es-PE', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },
            
            // Formatear estado
            formatEstado: function(estado) {
                const estados = {
                    'PENDIENTE': 'Pendiente',
                    'EN_PROCESO': 'En Proceso',
                    'OBSERVADO': 'Observado',
                    'VERIFICADO': 'Verificado',
                    'FINALIZADO': 'Finalizado',
                    'RECHAZADO': 'Rechazado'
                };
                return estados[estado] || estado;
            },
            
            // Fetch con CSRF token
            fetchWithCSRF: function(url, options = {}) {
                const defaultOptions = {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };
                
                return fetch(url, {
                    ...defaultOptions,
                    ...options,
                    headers: {
                        ...defaultOptions.headers,
                        ...(options.headers || {})
                    }
                });
            }
        };
    </script>
    
    @stack('scripts')
</body>
</html>