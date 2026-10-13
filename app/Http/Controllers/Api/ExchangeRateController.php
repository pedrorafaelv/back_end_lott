<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;

class ExchangeRateController extends Controller
{
    public function __construct(
        private ExchangeRateService $service
    ) {}

    /**
     * GET /api/exchange-rate/latest
     * Obtiene la tasa más reciente desde la BD (para el frontend).
     */
    public function latest(): JsonResponse
    {
        $rate = $this->service->getLatestRate();

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'No hay tasas registradas',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'currency_from' => $rate->currency_from,
                'currency_to'   => $rate->currency_to,
                'rate'          => (float) $rate->rate,
                'value_date'    => $rate->value_date->toDateString(),
                'scraped_at'    => $rate->scraped_at->toIso8601String(),
                'source'        => $rate->source,
            ],
        ]);
    }

    /**
     * POST /api/exchange-rate/refresh
     * Fuerza la actualización desde el BCV (scraping).
     * Útil para el frontend cuando el usuario quiere refrescar.
     */
    public function refresh(): JsonResponse
    {
        $result = $this->service->updateUsdRate();

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'rate'    => $result['rate'],
                'message' => $result['message'],
            ],
        ]);
    }
}