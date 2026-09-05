<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CardRaffleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Desactivamos las restricciones de clave foránea
        Schema::disableForeignKeyConstraints();
        
        // Limpiamos la tabla antes de insertar (opcional)
        DB::table('card_raffle')->truncate();
        
        // Habilitamos nuevamente las restricciones
        Schema::enableForeignKeyConstraints();

        // Obtener todos los IDs de raffles y cards
        $raffleIds = DB::table('raffles')->pluck('id')->toArray();
        $cardIds = DB::table('cards')->pluck('id')->toArray();

        // Verificar que existan datos
        if (empty($raffleIds) || empty($cardIds)) {
            $this->command->warn('⚠️ No hay raffles o cards en la base de datos. No se insertaron registros.');
            return;
        }

        // Preparar los datos a insertar
        $data = [];
        $now = now();

        foreach ($raffleIds as $raffleId) {
            foreach ($cardIds as $cardId) {
                $data[] = [
                    'card_id' => $cardId,
                    'raffle_id' => $raffleId,
                    'user_id' => null, // Puedes asignar un usuario aleatorio si quieres
                    'indice' => rand(1, 100), // Índice aleatorio (ajusta según tu lógica)
                    'active' => rand(0, 1) ? 'SI' : 'NO', // Activo aleatorio
                    'start_date' => $now->copy()->subDays(rand(1, 30)),
                    'end_date' => $now->copy()->addDays(rand(1, 60)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Insertar en lotes para mejor rendimiento
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $chunk) {
            DB::table('card_raffle')->insert($chunk);
        }

        $this->command->info('✅ Se insertaron ' . count($data) . ' registros en card_raffle.');
    }
}