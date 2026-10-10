<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\Fichaseeder;
use Database\Seeders\Cardseeder;
use Database\Seeders\ViaSeeder;
use Database\Seeders\LevelSeeder;
use Database\Seeders\LevelUserSeeder;


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
            // UserSeeder::class,
                    AccountsSeeder::class,
                    GroupSeeder::class,
                    GroupUserSeeder::class,
                    RaffleSeeder::class,
                    FichaSeeder::class,
                    FichaGroupFichaSeeder::class,
                    CardSeeder::class,
                    ViasSeeder::class,
                    WithdrawalRequestSeeder::class,
                    WithdrawalLogSeeder::class,
                    levelSeeder::class,
                    levelUserSeeder::class,]);
    }
}
