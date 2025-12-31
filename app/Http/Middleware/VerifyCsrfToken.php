<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Endpoints de API de validación que son consumidos por JavaScript en el frontend
        'api/validar-dni',
        'api/validar-ruc',
        'api/validar-email',
    ];
}

