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
        // Sesión expirada / token CSRF vencido (419).
        // - Formularios públicos (login, registro, recuperación): el invitado dejó
        //   la pestaña abierta y el token del HTML caducó. Se devuelve al formulario
        //   con mensaje claro y se conservan los datos (menos contraseñas).
        // - Resto: llevar al login con mensaje de sesión expirada.
        // Las peticiones AJAX/JSON mantienen 419.
        $exceptions->respond(function (Response $response) {
            if ($response->getStatusCode() === 419
                && !request()->expectsJson()
                && !request()->ajax()) {
                if (request()->is('login', 'login-interno', 'registro', 'recuperar', 'restablecer*')) {
                    return back()
                        ->withInput(request()->except([
                            'password',
                            'password_confirmation',
                            'password_actual',
                            'g-recaptcha-response',
                        ]))
                        ->withErrors(['error' => 'La página estuvo inactiva por mucho tiempo y la seguridad se renovó. Por favor, intente nuevamente.']);
                }

                return redirect()->route('login')
                    ->withErrors(['error' => 'Su sesión expiró por inactividad. Por favor, inicie sesión nuevamente.']);
            }

            return $response;
        });
    })->create();
