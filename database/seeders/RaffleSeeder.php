<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Raffle;
use Illuminate\Support\Facades\DB;


class RaffleSeeder extends Seeder
{
    public function run()
    {

        // Limpiar tabla
         // Deshabilitar verificaciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Raffle::truncate(); 
        Raffle::query()->delete();
        
        // Crear 50 sorteos con datos aleatorios
        Raffle::factory()
            ->count(50)
            ->create();
            
        // Crear 10 sorteos activos
        Raffle::factory()
            ->active()
            ->count(10)
            ->create();
            
        // Crear 5 sorteos finalizados con un usuario específico
        Raffle::factory()
            ->finished()
            ->count(5)
            ->create([
                'user_id' => 1
            ]);
            
        $this->command->info('Sorteos creados exitosamente.');
    }
}   