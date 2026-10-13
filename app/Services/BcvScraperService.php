<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BcvScraperService
{
    /**
     * URL del BCV donde publica las tasas.
     * Según el BCV, la tasa se publica entre 3:30 y 4:00 PM [citation:10]
     */
    private const BCV_URL = 'https://www.bcv.org.ve/';

    /**
     * Obtiene la tasa oficial USD/VES desde la página del BCV.
     *
     * @return array{rate: float, value_date: string, source: string}|null
     */
    public function fetchUsdRate(): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withOptions([
                    'verify' => false, // El BCV a veces tiene problemas con certificados
                ])
                ->get(self::BCV_URL);

            if (!$response->successful()) {
                Log::error('BCV Scraper: HTTP error', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            return $this->parseHtml($response->body());

        } catch (\Exception $e) {
            Log::error('BCV Scraper: Exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Parsea el HTML del BCV para extraer la tasa USD.
     *
     * El BCV tiene un bloque con id="dolar" que contiene el valor.
     * Estructura típica:
     * <div id="dolar">
     *   <div class="col-sm-6 col-xs-6 centrado">
     *     <strong> 875,65050000</strong>
     *   </div>
     * </div>
     */
    private function parseHtml(string $html): ?array
    {
        // Patrón para extraer el valor del dólar
        // El BCV usa coma como separador decimal (formato español)
        $pattern = '/<div[^>]*id="dolar"[^>]*>.*?<strong[^>]*>\s*([\d,]+)\s*<\/strong>/s';

        if (!preg_match($pattern, $html, $matches)) {
            // Fallback: buscar cualquier número después de "USD"
            $patternFallback = '/USD.*?(\d{3,4}[,.]\d{2,8})/s';
            if (!preg_match($patternFallback, $html, $matches)) {
                Log::warning('BCV Scraper: No se pudo parsear el HTML');
                return null;
            }
        }

        // Convertir formato español (875,65050000) a float
        $rate = (float) str_replace(',', '.', $matches[1]);

        if ($rate <= 0) {
            Log::warning('BCV Scraper: Tasa inválida', ['rate' => $rate]);
            return null;
        }

        return [
            'rate'       => $rate,
            'value_date' => Carbon::today()->toDateString(),
            'source'     => 'bcv',
        ];
    }

    /**
     * Parsea una fecha en formato español del BCV.
     * Ej: "Viernes, 09 Octubre 2026"
     */
    public function parseSpanishDate(string $dateString): ?Carbon
    {
        try {
            // Remover día de la semana
            $cleaned = preg_replace('/^[^,]+,\s*/', '', $dateString);
            // Formato: "09 Octubre 2026"
            $months = [
                'Enero' => 'January', 'Febrero' => 'February', 'Marzo' => 'March',
                'Abril' => 'April', 'Mayo' => 'May', 'Junio' => 'June',
                'Julio' => 'July', 'Agosto' => 'August', 'Septiembre' => 'September',
                'Octubre' => 'October', 'Noviembre' => 'November', 'Diciembre' => 'December',
            ];
            
            foreach ($months as $es => $en) {
                $cleaned = str_replace($es, $en, $cleaned);
            }
            
            return Carbon::parse($cleaned);
        } catch (\Exception $e) {
            return null;
        }
    }
}