<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class AccountsSeeder extends Seeder
{
    public function run()
    {
         // Desactivamos las restricciones de clave foránea
        Schema::disableForeignKeyConstraints();
        
        // Limpiamos la tabla antes de insertar (opcional)
        DB::table('accounts')->truncate();
        
        // Habilitamos nuevamente las restricciones
        Schema::enableForeignKeyConstraints();

        // Crear 10 cuentas aleatorias
        Account::factory()
            ->count(10)
            ->create();

        // O crear cuentas específicas para usuarios existentes
        $users = User::all();
        foreach ($users as $user) {
            Account::factory()
                ->count(3) // 3 cuentas por usuario (diferentes monedas)
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}