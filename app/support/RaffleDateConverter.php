<?php

namespace App\Support;
use Carbon\Carbon;

class RaffleDateConverter
{
    /**
     * Convierte fecha + hora local (tz del usuario) a Carbon en UTC.
     *
     * @throws \InvalidArgumentException
     */
    public static function localToUtc(string $date, string $hour, ?string $tz = null): Carbon
    {
        $tz    = $tz ?: config('app.fallback_timezone', 'America/Caracas');
        $local = $date . ' ' . $hour . ':00';

        $parsed = Carbon::createFromFormat('Y-m-d H:i:s', $local, $tz);

        if (!$parsed || $parsed->format('Y-m-d H:i:s') !== $local) {
            throw new \InvalidArgumentException("Fecha/hora inválida: {$local} (tz {$tz})");
        }

        return $parsed->utc();
    }

    /**
     * Inverso: Carbon en UTC → array [date, hour] en la tz destino.
     * Útil para rellenar el formulario de editar.
     *
     * @return array{date: string, hour: string}
     */
    public static function utcToLocal(Carbon|string $utc, string $tz): array
    {
        $carbon = $utc instanceof Carbon
            ? $utc->copy()
            : Carbon::parse($utc, 'UTC');

        $local = $carbon->setTimezone($tz);

        return [
            'date' => $local->format('Y-m-d'),
            'hour' => $local->format('H:i'),
        ];
    }
}