<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LevelSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $startDate = $now->copy();
        $endDate = null;

        $levels = [
            [
                'slug' => 'novato',
                'name' => 'Novato',
                'description' => 'Nivel inicial para nuevos usuarios que comienzan su experiencia en la plataforma.',
                'icon' => 'fa-seedling',
                'color' => '#909090',
                'max_raffles_active' => 0,
                'max_amount' => 0,
                'max_retention_percent' => 0,
                'can_create_private' => false,
                'can_use_auto_type' => false,
                'can_use_custom_fichas' => false,
                'min_games_played' => 0,
                'min_days_registered' => 0,
                'min_wins' => 0,
                'order' => 1,
                'active' => '1',
            ],
            [
                'slug' => 'jugador',
                'name' => 'Jugador',
                'description' => 'Jugador con experiencia básica que ya participa activamente en la plataforma.',
                'icon' => 'fa-star',
                'color' => '#3a7ebf',
                'max_raffles_active' => 1,
                'max_amount' => 100,
                'max_retention_percent' => 5,
                'can_create_private' => false,
                'can_use_auto_type' => false,
                'can_use_custom_fichas' => false,
                'min_games_played' => 50,
                'min_days_registered' => 30,
                'min_wins' => 0,
                'order' => 2,
                'active' => '1',
            ],
            [
                'slug' => 'avanzado',
                'name' => 'Avanzado',
                'description' => 'Nivel intermedio con beneficios ampliados y acceso a sorteos privados.',
                'icon' => 'fa-fire',
                'color' => '#f39c12',
                'max_raffles_active' => 3,
                'max_amount' => 500,
                'max_retention_percent' => 10,
                'can_create_private' => true,
                'can_use_auto_type' => false,
                'can_use_custom_fichas' => false,
                'min_games_played' => 200,
                'min_days_registered' => 90,
                'min_wins' => 10,
                'order' => 3,
                'active' => '1',
            ],
            [
                'slug' => 'elite',
                'name' => 'Élite',
                'description' => 'Nivel avanzado con acceso a funcionalidades premium y mayores límites operativos.',
                'icon' => 'fa-gem',
                'color' => '#00e676',
                'max_raffles_active' => 5,
                'max_amount' => 2000,
                'max_retention_percent' => 15,
                'can_create_private' => true,
                'can_use_auto_type' => true,
                'can_use_custom_fichas' => true,
                'min_games_played' => 500,
                'min_days_registered' => 180,
                'min_wins' => 30,
                'order' => 4,
                'active' => '1',
            ],
            [
                'slug' => 'vip',
                'name' => 'VIP',
                'description' => 'Nivel máximo para usuarios destacados con todos los beneficios desbloqueados.',
                'icon' => 'fa-crown',
                'color' => '#ffd700',
                'max_raffles_active' => 10,
                'max_amount' => 10000,
                'max_retention_percent' => 20,
                'can_create_private' => true,
                'can_use_auto_type' => true,
                'can_use_custom_fichas' => true,
                'min_games_played' => 1000,
                'min_days_registered' => 365,
                'min_wins' => 100,
                'order' => 5,
                'active' => '1',
            ],
            [
                'slug' => 'admin',
                'name' => 'Admin',
                'description' => 'Nivel administrativo con acceso total sin restricciones.',
                'icon' => 'fa-user-shield',
                'color' => '#ff4757',
                'max_raffles_active' => 999,
                'max_amount' => 999999,
                'max_retention_percent' => 100,
                'can_create_private' => true,
                'can_use_auto_type' => true,
                'can_use_custom_fichas' => true,
                'min_games_played' => 0,
                'min_days_registered' => 0,
                'min_wins' => 0,
                'order' => 6,
                'active' => '1',
            ],
        ];

        foreach ($levels as $data) {
            Level::updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ])
            );
        }
    }
}