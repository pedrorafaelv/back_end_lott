<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Seeder;

class WithdrawalRequestSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            $this->command->warn('No hay users. Ejecuta primero UserSeeder.');
            return;
        }

        WithdrawalRequest::factory()
            ->count(50)
            ->create();

        $this->command->info('WithdrawalRequests creados exitosamente.');
    }
}