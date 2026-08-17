<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;

class GroupSeeder extends Seeder
{
    public function run()
    {
        Group::query()->delete();

        // Crear grupos específicos
        Group::factory()->premium()->create();
        Group::factory()->vip()->create();
        
        // Crear 10 grupos aleatorios
        Group::factory()
            ->count(10)
            ->create();

        $this->command->info('Grupos creados exitosamente.');
    }
}