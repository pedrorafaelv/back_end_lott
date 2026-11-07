<?php

namespace App\Support;

use DateTimeZone;
use Illuminate\Http\Request;

class TimezoneResolver
{
    /**
     * Cachea la lista de zonas horarias IANA para búsqueda O(1).
     * Se inicializa la primera vez que se llama.
     *
     * @var array<string, int>|null
     */
    private static ?array $validZones = null;

    /**
     * Resuelve la zona horaria del request.
     *
     * Prioridad:
     *   1. Header "X-Timezone" (enviado por el interceptor de Angular)
     *   2. Config "app.fallback_timezone" (por defecto America/Caracas)
     *
     * Si el header llega pero no es una zona IANA válida, se usa el fallback.
     */
    public static function fromRequest(Request $request): string
    {
        $header   = $request->header('X-Timezone');
        $fallback = config('app.fallback_timezone', 'America/Caracas');

        if (!$header) {
            return $fallback;
        }

        // array_flip → búsqueda O(1) en lugar de in_array O(n)
        self::$validZones ??= array_flip(DateTimeZone::listIdentifiers());

        return isset(self::$validZones[$header])
            ? $header
            : $fallback;
    }
}