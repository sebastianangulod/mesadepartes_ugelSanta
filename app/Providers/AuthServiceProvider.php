<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Usuario;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     */
    public function boot(): void
    {
        // Definir gates de autorización
        Gate::define('administrar-tramites', function (Usuario $usuario) {
            return in_array($usuario->rol, ['ADMINISTRADOR', 'OPERADOR']);
        });
        
        Gate::define('crear-tramites', function (Usuario $usuario) {
            return in_array($usuario->rol, ['ADMINISTRADOR', 'OPERADOR', 'USUARIO']);
        });
        
        Gate::define('gestionar-usuarios', function (Usuario $usuario) {
            return $usuario->rol === 'ADMINISTRADOR';
        });
        
        Gate::define('gestionar-reportes', function (Usuario $usuario) {
            return in_array($usuario->rol, ['ADMINISTRADOR', 'OPERADOR']);
        });
    }
}