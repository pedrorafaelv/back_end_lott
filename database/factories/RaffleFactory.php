<?php

namespace Database\Factories;

use App\Models\Raffle;
use App\Models\User;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class RaffleFactory extends Factory
{
    protected $model = Raffle::class;

    public function definition()
    {
        // Fecha de inicio aleatoria entre hace 2 meses y dentro de 2 meses
        $startDate = $this->faker->dateTimeBetween('-2 months', '+2 months');
        $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(1, 30));
        
        return [
            'name' => $this->faker->unique()->sentence(3), // Nombre único
            'description' => $this->faker->paragraph(2), // 2 párrafos
            'user_id' => User::factory(), // Crea un usuario automáticamente
            'group_id' => null, // O usa Group::factory()
            'start_date' => $startDate,
            'end_date' => $endDate,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    public function finished()
    {
        return $this->state(function (array $attributes) {
            return [
                'start_date' => now()->subDays(rand(10, 20)),
                'end_date' => now()->subDays(rand(1, 5)),
            ];
        });
    }

    /**
     * Estado: Sorteo futuro (aún no comienza)
     */
    public function upcoming()
    {
        return $this->state(function (array $attributes) {
            return [
                'start_date' => now()->addDays(rand(1, 5)),
                'end_date' => now()->addDays(rand(6, 15)),
            ];
        });
    }
      
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'start_date' => now()->subDays(rand(1, 5)),
                'end_date' => now()->addDays(rand(1, 10)),
            ];
        });
    }

    /**
     * Estado: Sorteo con grupo
     */
    public function withGroup()
    {
        return $this->state(function (array $attributes) {
            return [
                'group_id' => Group::factory(),
            ];
        });
    }

    /**
     * Estado: Sorteo sin grupo
     */
    public function withoutGroup()
    {
        return $this->state(function (array $attributes) {
            return [
                'group_id' => null,
            ];
        });
    }

}