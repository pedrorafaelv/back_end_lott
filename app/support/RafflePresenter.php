<?php

namespace App\Support;

use Carbon\Carbon;

class RafflePresenter
{
    /**
     * Convierte un raffle (stdClass o array) para el JSON de respuesta:
     *  - Añade `scheduled_date_utc` (UTC puro, para countdown/lógica).
     *  - Sobrescribe `scheduled_date` con la tz del usuario (para mostrar).
     *
     * No muta el original: devuelve un clon si es stdClass, o un nuevo array.
     * Si `scheduled_date` está vacío, deja el objeto tal cual.
     */
    public static function convertDate(object|array $raffle, string $userTz): object|array
    {
        $isArray = is_array($raffle);

        $rawDate = $isArray ? ($raffle['scheduled_date'] ?? null) : ($raffle->scheduled_date ?? null);

        if (!$rawDate) {
            return $raffle;
        }

        $utc = Carbon::parse($rawDate, 'UTC');

        $utcString   = $utc->format('Y-m-d H:i:s');
        $localString = $utc->copy()->setTimezone($userTz)->format('Y-m-d H:i:s');

        if ($isArray) {
            $raffle['scheduled_date_utc'] = $utcString;
            $raffle['scheduled_date']     = $localString;
        } else {
            $raffle->scheduled_date_utc = $utcString;
            $raffle->scheduled_date     = $localString;
        }

        return $raffle;
    }

    /**
     * Aplica `convertDate` a una colección (Illuminate\Support\Collection).
     */
    public static function convertCollection($collection, string $userTz)
    {
        return $collection->map(fn ($r) => self::convertDate($r, $userTz));
    }
}