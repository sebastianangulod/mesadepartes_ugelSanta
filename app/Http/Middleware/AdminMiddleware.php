<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware para verificar si el usuario es administrador
 * 
 * Este middleware:
 * - Verifica que el usuario esté autenticado
 * - Verifica que el usuario sea administrador
 * - Redirige a login si no está autenticado
 * - Redirige a dashboard si no es administrador
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder a esta área.');
        }

        // Verificar si el usuario es administrador
        if (!Auth::user()->esAdministrador()) {
            // Redirigir según el tipo de usuario
            if (Auth::user()->esInterno()) {
                return redirect()->route('mesa-partes.tramites-recibidos')->with('error', 'No tiene permisos de administrador para acceder a esta área.');
            }
            return redirect()->route('dashboard')->with('error', 'No tiene permisos de administrador para acceder a esta área.');
        }

        return $next($request);
    }
}