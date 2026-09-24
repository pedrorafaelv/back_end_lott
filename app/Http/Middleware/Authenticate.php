<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // Si es una petición API o espera JSON → no redirigir, devolver 401
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        // Para peticiones web sí redirigir a login
        // (solo si la ruta existe)
        return route('login');
    }
}