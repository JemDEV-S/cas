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
        // Rutas de FIRMA PERÚ (llamadas desde componente local, no desde navegador)
        'api/*',
        // Temporal: Excluir guardado de evaluaciones para debug
        'evaluations/*/details',
    ];
}
