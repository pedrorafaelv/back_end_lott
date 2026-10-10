<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Raffle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class RaffleFactory extends Factory
{
    protected $model = Raffle::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-2 months', '+2 months');
        $end   = Carbon::parse($start)->addDays($this->faker->numberBetween(1, 30));

        return [
            // ===== Básicos =====
            'name'        => 'Loteria ' . $this->faker->unique()->bothify('???-####'),
            'description' => $this->faker->paragraph(2),

            // ===== Relaciones =====
            'user_id'  => User::factory(),
            'group_id' => null,
            'admin_user' => null, // se asigna al User admin desde el seeder

            // ===== Fechas principales =====
            'start_date' => $start,
            'end_date'   => $end,

            // ===== Horarios =====
            'scheduled_date' => Carbon::parse($start)->format('Y-m-d'),
            'scheduled_hour' => '20:00:00',
            'start_hour'     => '00:00:00',
            'end_hour'       => '23:59:59',
            'time_zone'      => 'America/Caracas',

            // ===== Montos =====
            'total_amount' => $this->faker->randomFloat(2, 1000, 100000),
            'card_amount'  => $this->faker->randomFloat(2, 10, 500),
            'currency'     => $this->faker->randomElement(['USD', 'VES']),

            // ===== Reglas de juego =====
            'minimun_play'       => $this->faker->numberBetween(1, 5),
            'maximun_play'       => $this->faker->numberBetween(10, 100),
            'maximun_user_play'  => $this->faker->numberBetween(5, 50),

            // ===== Retenciones =====
            'retention_percent'       => $this->faker->numberBetween(0, 20),
            'retention_amount'        => $this->faker->numberBetween(0, 500),
            'admin_retention_percent' => $this->faker->numberBetween(0, 10),
            'admin_retention_amount'  => $this->faker->numberBetween(0, 200),

            // ===== Tipo / Config =====
            'raffle_type' => $this->faker->numberBetween(0, 1), // 0=manual, 1=auto
            'privacy'     => $this->faker->numberBetween(0, 1), // 0=public, 1=private

            // ===== Premios =====
            'reward_line'  => $this->faker->numberBetween(0, 1),
            'percent_line' => $this->faker->numberBetween(10, 50),
            'reward_full'  => $this->faker->numberBetween(0, 1),
            'percent_full' => $this->faker->numberBetween(30, 100),

            // ===== Ganadores (null al crear) =====
            'winner'      => null,
            'full_winner' => null,
        ];
    }

    // ==================== States ====================

    public function active(): static
    {
        return $this->state(fn () => [
            'start_date' => now()->subDays(rand(1, 5)),
            'end_date'   => now()->addDays(rand(1, 10)),
            'winner'     => null,
            'full_winner'=> null,
        ]);
    }

    public function finished(): static
    {
        return $this->state(fn () => [
            'start_date'  => now()->subDays(rand(20, 30)),
            'end_date'    => now()->subDays(rand(1, 5)),
            'winner'      => (string) $this->faker->numberBetween(1, 100),
            'full_winner' => (string) $this->faker->numberBetween(1, 100),
        ]);
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'start_date' => now()->addDays(rand(1, 5)),
            'end_date'   => now()->addDays(rand(6, 15)),
            'winner'     => null,
            'full_winner'=> null,
        ]);
    }

    public function withGroup(): static
    {
        return $this->state(fn () => [
            'group_id' => Group::factory(),
        ]);
    }

    public function withoutGroup(): static
    {
        return $this->state(fn () => ['group_id' => null]);
    }
}