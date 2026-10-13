<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public function __construct(
        private BcvScraperService $scraper
    ) {}

    /**
     * Actualiza la tasa desde el BCV si es necesario.
     * Evita duplicados si ya existe la tasa para la fecha de hoy.
     *
     * @return array{success: bool, rate?: float, message: string}
     */
    public function updateUsdRate(): array
    {
        // 1. Obtener la tasa del BCV
        $scraped = $this->scraper->fetchUsdRate();

        if (!$scraped) {
            return [
                'success' => false,
                'message' => 'No se pudo obtener la tasa del BCV',
            ];
        }

        // 2. Verificar si ya existe para esa fecha
        $existing = ExchangeRate::getRateForDate($scraped['value_date']);

        if ($existing && $existing->rate == $scraped['rate']) {
            return [
                'success' => true,
                'rate'    => (float) $existing->rate,
                'message' => 'La tasa ya está actualizada para ' . $scraped['value_date'],
            ];
        }

        // 3. Guardar o actualizar
        $rate = ExchangeRate::updateOrCreate(
            [
                'currency_from' => 'USD',
                'currency_to'   => 'VES',
                'value_date'    => $scraped['value_date'],
            ],
            [
                'rate'       => $scraped['rate'],
                'scraped_at' => now(),
                'source'     => $scraped['source'],
            ]
        );

        Log::info('BCV: Tasa actualizada', [
            'rate'  => $scraped['rate'],
            'date'  => $scraped['value_date'],
        ]);

        return [
            'success' => true,
            'rate'    => (float) $rate->rate,
            'message' => 'Tasa actualizada correctamente',
        ];
    }

    /**
     * Obtiene la tasa más reciente desde la BD.
     */
    public function getLatestRate(): ?ExchangeRate
    {
        return ExchangeRate::getLatestUsdRate();
    }
}