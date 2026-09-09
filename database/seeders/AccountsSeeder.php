<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\User;

class AccountsSeeder extends Seeder
{
    public function run()
    {
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