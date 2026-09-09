<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Fichaseeder;
use Database\Seeders\Cardseeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(AccountsSeeder::class);
        $this->call(GroupSeeder::class);
        $this->call(GroupUserSeeder::class);
        $this->call(RaffleSeeder::class);
        $this->call(FichaSeeder::class);
        $this->call(FichaGroupFichaSeeder::class);
        $this->call(CardSeeder::class);
    }
}
