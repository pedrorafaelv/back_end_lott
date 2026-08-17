<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::query()->delete();

        // Crear 1 usuario administrador específico
        User::factory()
            ->admin()
            ->create();

        // Crear 1 usuario con email específico
        User::factory()->create([
            'name' => 'Usuario Prueba',
            'email' => 'test@loteria.com',
        ]);

        // Crear 20 usuarios aleatorios
        User::factory()
            ->count(20)
            ->create();

        // Crear 5 usuarios sin email verificado
        User::factory()
            ->unverified()
            ->count(5)
            ->create();

        $this->command->info('Usuarios creados exitosamente.');
    }
}