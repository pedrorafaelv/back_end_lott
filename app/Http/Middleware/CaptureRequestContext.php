<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureRequestContext
{
    /**
     * Captura la IP real del cliente y el User-Agent de forma fiable.
     * Los inyecta en el request para que el controlador los use.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // $request->attributes->set('client_ip', $this->resolveClientIp($request));
        // $request->attributes->set('client_user_agent', $request->userAgent());

        // return $next($request);

        // La IP confiable la resuelve Laravel según TrustProxies
        $request->attributes->set('client_ip', $request->ip());
        $request->attributes->set('client_user_agent', $request->userAgent());

        return $next($request);
    }

    /**
     * Resuelve la IP del cliente considerando proxies inversos.
     * Ajusta `TRUSTED_PROXY_COUNT` en .env según tu infraestructura.
     */
    private function resolveClientIp(Request $request): ?string
    {
        // Laravel ya maneja TrustProxies si está configurado correctamente.
        // Si no, hacemos una resolución manual de X-Forwarded-For.
        $trustedCount = (int) config('app.trusted_proxy_count', 0);

        if ($trustedCount > 0) {
            $xff = $request->header('X-Forwarded-For');
            if ($xff) {
                $chain = array_map('trim', explode(',', $xff));
                $index = count($chain) - 1 - $trustedCount;
                $candidate = $chain[$index] ?? $chain[0] ?? null;

                if ($candidate && filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return $candidate;
                }
            }
        }

        return $request->ip();
    }
}