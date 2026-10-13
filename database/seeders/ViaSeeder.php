<?php

namespace Database\Seeders;

use App\Models\Via;
use Illuminate\Database\Seeder;
use Database\Factories\ViaFactory;

class ViaSeeder extends Seeder
{
    public function run(): void
{
    foreach (ViaFactory::catalogStates() as $state) {
        $attributes = Via::factory()->{$state}()->make()->toArray();
        
        Via::updateOrCreate(
            ['code' => $attributes['code']],
            $attributes
        );
    }
}
}