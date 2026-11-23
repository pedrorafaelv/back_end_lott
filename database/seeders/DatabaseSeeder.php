<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Fichaseeder;
use Database\Seeders\Cardseeder;
use Database\Seeders\ViaSeeder;
use Database\Seeders\LevelSeeder;
use Database\Seeders\LevelUserSeeder;
use Database\Seeders\GroupFichaSeeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            ViaSeeder::class,
            levelSeeder::class,
            GroupFichaSeeder::class,
            UserSeeder::class,
            GroupSeeder::class,
            FichaSeeder::class,
            FichaGroupFichaSeeder::class,
            CardSeeder::class,
            AccountsSeeder::class,
            GroupUserSeeder::class,
            // RaffleSeeder::class,
            WithdrawalRequestSeeder::class,
            WithdrawalLogSeeder::class,
            levelUserSeeder::class,]);
    }
}
