<?php

namespace Database\Seeders;

use App\Models\Raffle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RaffleSeeder extends Seeder
{
    public function run(): void
    {
        // 🔒 Desactivar FKs de forma segura (se restauran siempre)
        Schema::disableForeignKeyConstraints();
        Raffle::truncate();
        Schema::enableForeignKeyConstraints();
        DB::table('raffles')->insert([
    [
        'name' => 'Test público 1h',
        'user_id' => 4, 'group_id' => 1, 'groupficha_id' => 1,
        'privacy' => 0, 'scheduled_date' => now()->addHour(),
        'time_zone' => 'America/Caracas', 'total_amount' => 1000, 'card_amount' => 10,
        'start_date' => now(), 'end_date' => null,
    ],
    [
        'name' => 'Test público +12h',
        'user_id' => 4, 'group_id' => 1, 'groupficha_id' => 1,
        'privacy' => 0, 'scheduled_date' => now()->addHours(12),
        'time_zone' => 'America/Caracas', 'total_amount' => 1000, 'card_amount' => 10,
        'start_date' => now(), 'end_date' => null,
    ],
    [
        'name' => 'Test privado grupo +3d',
        'user_id' => 4, 'group_id' => 1, 'groupficha_id' => 1,
        'privacy' => 1, 'scheduled_date' => now()->addDays(3),
        'time_zone' => 'America/Caracas', 'total_amount' => 1000, 'card_amount' => 10,
        'start_date' => now(), 'end_date' => null,
    ],
    [
        'name' => 'Test futuro +5d',
        'user_id' => 4, 'group_id' => 1, 'groupficha_id' => 1,
        'privacy' => 0, 'scheduled_date' => now()->addDays(5),
        'time_zone' => 'America/Caracas', 'total_amount' => 1000, 'card_amount' => 10,
        'start_date' => now(), 'end_date' => null,
    ],
]);
        // // 👤 Asegurar un admin
        // $admin = User::where('is_admin', true)->first()
        //       ?? User::factory()->create(['is_admin' => true]);

        // // 🎲 50 sorteos aleatorios
        // Raffle::factory()
        //     ->count(50)
        //     ->create([
        //         'user_id'    => $admin->id,
        //         'admin_user' => $admin->id,
        //     ]);

        // // ✅ 10 activos
        // Raffle::factory()
        //     ->active()
        //     ->count(10)
        //     ->create([
        //         'user_id'    => $admin->id,
        //         'admin_user' => $admin->id,
        //     ]);

        // // 🏁 5 finalizados
        // Raffle::factory()
        //     ->finished()
        //     ->count(5)
        //     ->create([
        //         'user_id'    => $admin->id,
        //         'admin_user' => $admin->id,
        //     ]);

        // $this->command->info('✅ Sorteos creados: ' . Raffle::count());
        // $this->command->info('   · Activos:    ' . Raffle::active()->count());
        // $this->command->info('   · Finalizados:' . Raffle::finished()->count());
        // $this->command->info('   · Próximos:   ' . Raffle::upcoming()->count());
    }
}