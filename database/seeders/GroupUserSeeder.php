<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\DB;

class GroupUserSeeder extends Seeder
{
    public function run()
    {
        // Limpiar tabla de relación
        DB::table('group_user')->truncate();

        // Obtener todos los usuarios y grupos
        $users = User::all();
        $groups = Group::all();

        if ($users->isEmpty() || $groups->isEmpty()) {
            $this->command->warn('No hay usuarios o grupos para asignar.');
            return;
        }

        // Asignar usuarios a grupos aleatoriamente
        foreach ($users as $user) {
            // Cada usuario pertenece a 1-3 grupos aleatorios
            $randomGroups = $groups->random(rand(1, 3));
            
            foreach ($randomGroups as $group) {
                DB::table('group_user')->insert([
                    'user_id' => $user->id,
                    'group_id' => $group->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Asignaciones específicas
        // Asignar usuario con ID 1 al grupo ID 1 (Administrador al grupo Premium)
        if (User::find(1) && Group::find(1)) {
            DB::table('group_user')->insertOrIgnore([
                'user_id' => 1,
                'group_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Asignar usuario con ID 2 al grupo ID 1 y 2 (Juan al Premium y VIP)
        if (User::find(2) && Group::find(1) && Group::find(2)) {
            DB::table('group_user')->insertOrIgnore([
                ['user_id' => 2, 'group_id' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['user_id' => 2, 'group_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $this->command->info('Relaciones usuario-grupo creadas exitosamente.');
    }
}