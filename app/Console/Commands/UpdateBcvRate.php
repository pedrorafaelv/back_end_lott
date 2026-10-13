<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class UpdateBcvRate extends Command
{
    protected $signature = 'bcv:update-rate';
    protected $description = 'Actualiza la tasa oficial USD/VES desde el BCV';

    public function handle(ExchangeRateService $service): int
    {
        $this->info('🔄 Consultando BCV...');

        $result = $service->updateUsdRate();

        if ($result['success']) {
            $this->info("✅ {$result['message']}");
            if (isset($result['rate'])) {
                $this->info("💵 Tasa actual: {$result['rate']} Bs/USD");
            }
            return self::SUCCESS;
        }

        $this->error("❌ {$result['message']}");
        return self::FAILURE;
    }

    // * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
}