<?php

namespace Database\Seeders;

use App\Models\GroupFicha;
use Illuminate\Database\Seeder;

class GroupFichaSeeder extends Seeder
{
    public function run(): void
    {
        GroupFicha::query()->delete();

        GroupFicha::factory()->comics()->create();
        GroupFicha::factory()->caricatura()->create();
        GroupFicha::factory()->futbol()->create();
        GroupFicha::factory()->animales()->create();
        GroupFicha::factory()->deportes()->create();
        GroupFicha::factory()->videojuegos()->create();
        GroupFicha::factory()->cine()->create();
        GroupFicha::factory()->flags()->create();
        GroupFicha::factory()->history()->create();
        GroupFicha::factory()->terror_movies()->create();
        GroupFicha::factory()->dinosaurus()->create();
        // GroupFicha::factory()->count(10)->create();

        $this->command->info('✅ Temas creados: ' . GroupFicha::count());
    }
}