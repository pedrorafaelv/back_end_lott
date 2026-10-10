<?php

namespace Database\Seeders;

use App\Models\Raffle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RaffleSeeder extends Seeder
{
    public function run(): void
    {
        // 🔒 Desactivar FKs de forma segura (se restauran siempre)
        Schema::disableForeignKeyConstraints();
        Raffle::truncate();
        Schema::enableForeignKeyConstraints();

        // 👤 Asegurar un admin
        $admin = User::where('is_admin', true)->first()
              ?? User::factory()->create(['is_admin' => true]);

        // 🎲 50 sorteos aleatorios
        Raffle::factory()
            ->count(50)
            ->create([
                'user_id'    => $admin->id,
                'admin_user' => $admin->id,
            ]);

        // ✅ 10 activos
        Raffle::factory()
            ->active()
            ->count(10)
            ->create([
                'user_id'    => $admin->id,
                'admin_user' => $admin->id,
            ]);

        // 🏁 5 finalizados
        Raffle::factory()
            ->finished()
            ->count(5)
            ->create([
                'user_id'    => $admin->id,
                'admin_user' => $admin->id,
            ]);

        $this->command->info('✅ Sorteos creados: ' . Raffle::count());
        $this->command->info('   · Activos:    ' . Raffle::active()->count());
        $this->command->info('   · Finalizados:' . Raffle::finished()->count());
        $this->command->info('   · Próximos:   ' . Raffle::upcoming()->count());
    }
}