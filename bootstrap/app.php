<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/validar-dni',
            'api/validar-ruc',
            'api/validar-email',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sesión expirada (p. ej. logout tras inactividad): en vez del 419,
        // llevar al login con mensaje. Las peticiones AJAX/JSON mantienen 419.
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419
                && !request()->expectsJson()
                && !request()->ajax()) {
                return redirect()->route('login')
                    ->withErrors(['error' => 'Su sesión expiró por inactividad. Por favor, inicie sesión nuevamente.']);
            }

            return $response;
        });
    })->create();
