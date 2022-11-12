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
        $this->call(FichaSeeder::class);
        //$this->call(cardSeeder::class);
    }
}
