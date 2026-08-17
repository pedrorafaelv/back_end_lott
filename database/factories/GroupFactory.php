<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word() . ' Group',
            'description' => $this->faker->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function premium()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'Premium Group',
                'description' => 'Grupo premium con beneficios especiales',
            ];
        });
    }

    public function vip()
    {
        return $this->state(function (array $attributes) {
            return [
                'name' => 'VIP Group',
                'description' => 'Grupo VIP con acceso exclusivo',
            ];
        });
    }
}