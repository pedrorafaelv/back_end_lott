<?php

namespace Database\Seeders;

use App\Models\WithdrawalLog;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;

class WithdrawalLogSeeder extends Seeder
{
    public function run(): void
    {
        if (WithdrawalRequest::count() === 0) {
            $this->command->warn('No hay withdrawal_requests. Ejecuta primero WithdrawalRequestSeeder.');
            return;
        }

        WithdrawalLog::factory()
            ->count(120)
            ->create();

        $this->command->info('WithdrawalLogs creados exitosamente.');
    }
}