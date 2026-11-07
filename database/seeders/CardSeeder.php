<?php

namespace Database\Seeders;

use App\Services\CardGenerationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardSeeder extends Seeder
{
    public function run(CardGenerationService $service)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        try {
            DB::table('card_ficha')->truncate();
            \App\Models\Card::truncate();

            // Ajusta el groupFicha_id y el count que quieras sembrar.
            $groupFichaId = 1;
            $count        = 500;
            // GroupFicha::where('status',1)->each(fn($g) => $service->generateForGroupFicha($g->id, 500));

            $result = $service->generateForGroupFicha($groupFichaId, $count);
            $this->command->info("Seeder: {$result['created']} cartones generados para groupficha {$groupFichaId}.");
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // ← bug corregido
        }
    }
}