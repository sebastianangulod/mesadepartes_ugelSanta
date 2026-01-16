<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Mesa de Partes - UGEL')</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Mesa de Partes Layout CSS -->
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
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: box-shadow 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Button styles */
        .btn-primary {
            background-color: #3182ce;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #2c5aa0;
        }

        .btn-secondary {
            background-color: #718096;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background-color: #4a5568;
        }

        .btn-success {
            background-color: #38a169;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-success:hover {
            background-color: #2f855a;
        }

        .btn-danger {
            background-color: #e53e3e;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #c53030;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        table th {
            background-color: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        table tr:hover {
            background-color: #f7fafc;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                max-width: calc(100% - 200px);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="flex">
        <!-- Sidebar -->
        <x-mesa-partes-sidebar :tramitesPendientes="$tramitesPendientes ?? 0" />

        <!-- Main Content -->
        <div class="main-content p-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">@yield('header', 'Mesa de Partes')</h1>
                @hasSection('actions')
                <div class="mt-4">
                    @yield('actions')
                </div>
                @endif
            </div>

            <!-- Alerts -->
            @unless(isset($hideAlerts) && $hideAlerts)
                <x-alerts />
            @endunless

            <!-- Content -->
            <div class="content-body">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading">
        <div class="loading-content">
            <div class="spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #3182ce; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto;"></div>
            <p class="mt-2">Cargando...</p>
        </div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <!-- Scripts -->
    <script>
        // Asegurar que el loading esté oculto al cargar la página
        (function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        })();

        // Configuración global para AJAX
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        }

        // Funciones globales
        window.app = {
            // Mostrar loading
            showLoading: function() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'block';
                }
            },
            
            // Ocultar loading
            hideLoading: function() {
                const loadingOverlay = document.getElementById('loadingOverlay');
                if (loadingOverlay) {
                    loadingOverlay.style.display = 'none';
                }
            },
            
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
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit'
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
            }
        };

        // Ocultar loading cuando la página se carga completamente
        document.addEventListener('DOMContentLoaded', function() {
            window.app.hideLoading();
        });

        // Ocultar loading cuando la ventana se carga completamente
        window.addEventListener('load', function() {
            window.app.hideLoading();
        });

        // Ocultar loading cuando se navega con el historial del navegador (botones atrás/adelante)
        window.addEventListener('pageshow', function(event) {
            // Si la página se carga desde el cache del navegador (navegación con historial)
            if (event.persisted) {
                window.app.hideLoading();
            } else {
                // Si es una carga normal, también ocultar el loading
                window.app.hideLoading();
            }
        });

        // Ocultar loading cuando se navega hacia atrás o adelante
        window.addEventListener('popstate', function(event) {
            window.app.hideLoading();
        });

        // Prevenir que el loading se muestre automáticamente en enlaces normales
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"]):not([onclick])').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Solo mostrar loading si no es un enlace que abre en nueva pestaña
                    if (!this.target || this.target === '_self') {
                        // Mostrar loading solo si es una navegación real
                        const href = this.getAttribute('href');
                        if (href && !href.startsWith('#') && !href.startsWith('javascript:')) {
                            window.app.showLoading();
                        }
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>

