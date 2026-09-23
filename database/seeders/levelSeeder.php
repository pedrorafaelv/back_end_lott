<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LevelsSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'id' => 1,
                'slug' => 'novato',
                'name' => 'Novato',
                'description' => 'Nivel inicial. Aún no puedes crear sorteos.',
                'icon' => 'fa-seedling',
                'color' => '#909090',
                'active' => '1',
                'order' => 1,
                'max_raffles_active' => 0,
                'max_amount' => 0,
                'max_retention_percent' => 0,
                'can_create_private' => false,
                'can_use_auto_type' => false,
                'can_use_custom_fichas' => false,
                'min_games_played' => 0,
                'min_days_registered' => 0,
                'min_wins' => 0,
            ],
            [
                'id' => 2,
                'slug' => 'jugador',
                'name' => 'Jugador',
                'description' => 'Puedes crear tu primer sorteo.',
                'icon' => 'fa-star',
                'color' => '#3a7ebf',
                'active' => '1',
                'order' => 2,
                'max_raffles_active' => 1,
                'max_amount' => 100,
                'max_retention_percent' => 5,
                'can_create_private' => false,
                'can_use_auto_type' => false,
                'can_use_custom_fichas' => false,
                'min_games_played' => 50,
                'min_days_registered' => 30,
                'min_wins' => 0,
            ],
            [
                'id' => 3,
                'slug' => 'avanzado',
                'name' => 'Avanzado',
                'description' => 'Desbloquea sorteos privados.',
                'icon' => 'fa-fire',
                'color' => '#f39c12',
                'active' => '1',
                'order' => 3,
                'max_raffles_active' => 3,
                'max_amount' => 500,
                'max_retention_percent' => 10,
                'can_create_private' => true,
                'can_use_auto_type' => false,
                'can_use_custom_fichas' => false,
                'min_games_played' => 200,
                'min_days_registered' => 90,
                'min_wins' => 10,
            ],
            [
                'id' => 4,
                'slug' => 'elite',
                'name' => 'Élite',
                'description' => 'Sorteos automáticos y fichas personalizadas.',
                'icon' => 'fa-gem',
                'color' => '#00e676',
                'active' => '1',
                'order' => 4,
                'max_raffles_active' => 5,
                'max_amount' => 2000,
                'max_retention_percent' => 15,
                'can_create_private' => true,
                'can_use_auto_type' => true,
                'can_use_custom_fichas' => true,
                'min_games_played' => 500,
                'min_days_registered' => 180,
                'min_wins' => 30,
            ],
            [
                'id' => 5,
                'slug' => 'vip',
                'name' => 'VIP',
                'description' => 'Máximos privilegios para creadores.',
                'icon' => 'fa-crown',
                'color' => '#ffd700',
                'active' => '1',
                'order' => 5,
                'max_raffles_active' => 10,
                'max_amount' => 10000,
                'max_retention_percent' => 20,
                'can_create_private' => true,
                'can_use_auto_type' => true,
                'can_use_custom_fichas' => true,
                'min_games_played' => 1000,
                'min_days_registered' => 365,
                'min_wins' => 100,
            ],
            [
                'id' => 6,
                'slug' => 'admin',
                'name' => 'Admin',
                'description' => 'Acceso total sin restricciones.',
                'icon' => 'fa-user-shield',
                'color' => '#ff4757',
                'active' => '1',
                'order' => 6,
                'max_raffles_active' => 999,
                'max_amount' => 999999,
                'max_retention_percent' => 100,
                'can_create_private' => true,
                'can_use_auto_type' => true,
                'can_use_custom_fichas' => true,
                'min_games_played' => 0,
                'min_days_registered' => 0,
                'min_wins' => 0,
            ],
        ];

        foreach ($levels as $level) {
            DB::table('levels')->updateOrInsert(
                ['id' => $level['id']],
                array_merge($level, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}