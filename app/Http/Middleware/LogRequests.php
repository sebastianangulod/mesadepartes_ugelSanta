<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Solo loguear rutas de login
        if ($request->is('login-interno') && $request->isMethod('post')) {
            file_put_contents(
                storage_path('logs/debug.log'),
                date('Y-m-d H:i:s') . " - MIDDLEWARE: Petición POST a login-interno\n" .
                "IP: " . $request->ip() . "\n" .
                "All inputs: " . json_encode($request->all(), JSON_PRETTY_PRINT) . "\n" .
                "Headers: " . json_encode($request->headers->all(), JSON_PRETTY_PRINT) . "\n\n",
                FILE_APPEND
            );
        }
        
        return $next($request);
    }
}

