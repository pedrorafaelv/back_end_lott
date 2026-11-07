<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupFicha;
use App\Models\Raffle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RaffleFactory extends Factory
{
    protected $model = Raffle::class;

    public function definition(): array
    {
        // Fechas coherentes: scheduled define el "cuándo", start/end la ventana
        $scheduled = Carbon::now()->addDays(rand(-10, 10))->setTime(20, 0);
        $start     = (clone $scheduled)->subDays(rand(0, 3));
        $end       = (clone $scheduled)->addDays(rand(1, 10));

        return [
            // ===== Básicos =====
            'name'        => 'Loteria ' . $this->faker->unique()->bothify('???-####'),
            'description' => Str::limit($this->faker->sentence(15), 250),

            // ===== Relaciones (datos REALES con fallback a factory) =====
            'user_id'        => User::query()->inRandomOrder()->value('id') ?? User::factory(),
            'group_id'       => null,
            'admin_user'     => null,
            'groupficha_id'  => Groupficha::query()->inRandomOrder()->value('id')
                                ?? Groupficha::factory(),

            // ===== Fechas =====
            'start_date'     => now(),
            'end_date'       => null,
            'scheduled_date' => $scheduled,
            'scheduled_hour' => $scheduled->format('H:i:s'),                

            // ===== Horarios =====
            'start_hour' => '00:00:00',
            'end_hour'   => '23:59:59',
            'time_zone'  => 'America/Caracas',

            // ===== Estado de jugadores =====
            'min_players_reached_at' => null,   // ✅ typo corregido

            // ===== Montos =====
            'total_amount' => $this->faker->randomFloat(2, 1000, 100000),
            'card_amount'  => $this->faker->randomFloat(2, 10, 500),
            'currency'     => $this->faker->randomElement(['USD', 'VES']),

            // ===== Reglas de juego =====
            'minimun_play'      => $this->faker->numberBetween(1, 5),
            'maximun_play'      => $this->faker->numberBetween(10, 100),
            'maximun_user_play' => $this->faker->numberBetween(5, 50),
            'minimun_user_play' => $this->faker->numberBetween(3, 5),

            // ===== Retenciones =====
            'retention_percent'       => $this->faker->numberBetween(0, 20),
            'retention_amount'        => $this->faker->numberBetween(0, 500),
            'admin_retention_percent' => $this->faker->numberBetween(0, 10),
            'admin_retention_amount'  => $this->faker->numberBetween(0, 200),

            // ===== Tipo / Config =====
            'raffle_type'            => $this->faker->numberBetween(0, 1),
            'privacy'                => $this->faker->numberBetween(0, 1), // 0=pub, 1=priv
            'allow_bots'             => $this->faker->numberBetween(0, 1),
            'allow_promotional_bet'  => 0,                                  // ✅ nueva

            // ===== Premios =====
            'reward_line'  => $this->faker->numberBetween(0, 1),
            'percent_line' => $this->faker->numberBetween(10, 50),
            'reward_full'  => $this->faker->numberBetween(0, 1),
            'percent_full' => $this->faker->numberBetween(30, 100),

            // ===== Ganadores =====
            'winner'      => null,
            'full_winner' => null,
        ];
    }

    // ==================== States de fechas ====================
    // IMPORTANTE: ahora todos sincronizan scheduled_date, que es lo que
    // filtran las queries nuevas.

    public function active(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::now()->subHours(rand(1, 12))->setTime(20, 0);
            return [
                'start_date'     => (clone $scheduled)->subHour(),
                'end_date'       => null,                    // ✅ abierto
                'scheduled_date' => $scheduled,
                'winner'         => null,
                'full_winner'    => null,
            ];
        });
    }

    public function finished(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::now()->subDays(rand(2, 20))->setTime(20, 0);
            return [
                'start_date'     => (clone $scheduled)->subHour(),
                'end_date'       => (clone $scheduled)->addHour(),
                'scheduled_date' => $scheduled,
                'winner'         => (string) $this->faker->numberBetween(1, 100),
                'full_winner'    => (string) $this->faker->numberBetween(1, 100),
            ];
        });
    }

    public function upcoming(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::now()->addDays(rand(1, 5))->setTime(20, 0);
            return [
                'start_date'     => (clone $scheduled)->subHour(),
                'end_date'       => null,                    // ✅ sigue abierto
                'scheduled_date' => $scheduled,
                'winner'         => null,
                'full_winner'    => null,
            ];
        });
    }

    // ==================== States alineados a las 4 queries ====================

    /** end_date = null → aparece en las 4 listas (salvo filtros de fecha) */
    public function open(): static
    {
        return $this->state(fn () => ['end_date' => null]);
    }

    /** Programado HOY → para $public_raffles */
    public function scheduledToday(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::today()->setTime(20, 0);
            return [
                'scheduled_date' => $scheduled,
                'scheduled_hour' => $scheduled->format('H:i:s'),
                'privacy'        => 0,
                'end_date'       => null,
            ];
        });
    }

    /** Programado mañana → borde inferior de $futures_raffles */
    public function scheduledTomorrow(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::tomorrow()->setTime(20, 0);
            return [
                'scheduled_date' => $scheduled,
                'scheduled_hour' => $scheduled->format('H:i:s'),
                'end_date'       => null,
            ];
        });
    }

    /** Programado en 7 días → borde superior de $futures_raffles */
    public function scheduledInOneWeek(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::now()->addWeek()->setTime(20, 0);
            return [
                'scheduled_date' => $scheduled,
                'scheduled_hour' => $scheduled->format('H:i:s'),
                'end_date'       => null,
            ];
        });
    }

    /** Programado en 8+ días → NO debe aparecer en $futures_raffles */
    public function scheduledFarFuture(): static
    {
        return $this->state(function () {
            $scheduled = Carbon::now()->addDays(rand(8, 30))->setTime(20, 0);
            return [
                'scheduled_date' => $scheduled,
                'scheduled_hour' => $scheduled->format('H:i:s'),
                'end_date'       => null,
            ];
        });
    }

    // ==================== Grupos ====================

    public function withGroup(): static
    {
        return $this->state(fn () => [
            'group_id' => Group::query()->inRandomOrder()->value('id')
                          ?? Group::factory(),
        ]);
    }

    public function withoutGroup(): static
    {
        return $this->state(fn () => ['group_id' => null]);
    }

    public function public(): static
    {
        return $this->state(fn () => ['privacy' => 0]);
    }

    public function private(): static
    {
        return $this->state(fn () => ['privacy' => 1]);
    }

    /** Asignar un usuario concreto (útil en tests del endpoint) */
    public function forUser(User|int $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }
}