<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Panel de Administración - Mesa de Partes')</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Admin Sidebar CSS -->
    <style>
        /* Sidebar container */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #2D3748 !important;
            color: white;
            overflow-y: auto;
        }

        /* Main content area */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background-color: #f3f4f6;
            overflow-x: hidden;
            max-width: calc(100% - 250px);
            width: 100%;
        }
        
        body {
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }
        
        html {
            margin: 0;
            padding: 0;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                position: static;
                padding-top: 0;
            }
            
            .main-content {
                margin-left: 0;
                max-width: 100%;
            }
        }

        /* Loading indicator */
        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
        }

        .loading-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }

        /* User profile section */
        .user-profile {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-avatar {
            background-color: white;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Nav list styling */
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        /* Menu items */
        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: background-color 0.3s;
            color: white;
            text-decoration: none;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .menu-item svg {
            margin-right: 10px;
        }

        /* Active menu item */
        .menu-item.active,
        .menu-item.bg-blue-900 {
            background-color: #1a365d;
        }

        /* Card styles */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        /* Button styles */
        .btn-primary {
            background-color: #2563eb;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-secondary {
            background-color: #4b5563;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background-color: #374151;
        }
        
        .btn-success {
            background-color: #16a34a;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-success:hover {
            background-color: #15803d;
        }
        
        .btn-danger {
            background-color: #dc2626;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-danger:hover {
            background-color: #b91c1c;
        }
        
        .btn-warning {
            background-color: #ca8a04;
            color: white;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease-in-out;
            border: none;
            cursor: pointer;
        }
        .btn-warning:hover {
            background-color: #a16207;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="flex">
        <x-admin-sidebar />
        
        <!-- Contenido principal -->
        <main class="main-content flex-1 p-8">
            @unless(isset($hideAlerts) && $hideAlerts)
                @if(session('success'))
                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                        <span class="font-semibold">Correcto:</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
            @endunless
            
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
        </main>
    </div>

    <!-- Scripts -->
    <script>
        // Configuración global para AJAX
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }
        
        // Función para mostrar el indicador de carga
        function showLoading() {
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) {
                indicator.style.display = 'block';
            }
        }
        
        // Función para ocultar el indicador de carga
        function hideLoading() {
            const indicator = document.getElementById('loadingIndicator');
            if (indicator) {
                indicator.style.display = 'none';
            }
        }
        
        // Asegurar que el loading esté oculto al cargar la página
        (function() {
            const loadingIndicator = document.getElementById('loadingIndicator');
            if (loadingIndicator) {
                loadingIndicator.style.display = 'none';
            }
        })();

        // Ocultar loading cuando la página se carga completamente
        document.addEventListener('DOMContentLoaded', function() {
            hideLoading();
        });

        // Ocultar loading cuando la ventana se carga completamente
        window.addEventListener('load', function() {
            hideLoading();
        });

        // Ocultar loading cuando se navega con el historial del navegador (botones atrás/adelante)
        window.addEventListener('pageshow', function(event) {
            // Si la página se carga desde el cache del navegador (navegación con historial)
            if (event.persisted) {
                hideLoading();
            } else {
                // Si es una carga normal, también ocultar el loading
                hideLoading();
            }
        });

        // Ocultar loading cuando se navega hacia atrás o adelante
        window.addEventListener('popstate', function(event) {
            hideLoading();
        });

        // Agregar evento de clic a los enlaces para mostrar el indicador de carga
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"]):not([onclick])').forEach(link => {
                link.addEventListener('click', function() {
                    // Solo mostrar loading si no es un enlace que abre en nueva pestaña
                    if (!this.target || this.target === '_self') {
                        const href = this.getAttribute('href');
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                            showLoading();
                        }
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>

