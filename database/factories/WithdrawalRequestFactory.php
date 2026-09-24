<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Via;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalRequestFactory extends Factory
{
    protected $model = WithdrawalRequest::class;

    public function definition(): array
    {
        $amount     = $this->faker->randomFloat(2, 50, 2000);
        $commission = round($amount * 0.05, 2);   // 5% de comisión
        $netAmount  = round($amount - $commission, 2);
        $status     = $this->faker->randomElement(['pending', 'approved', 'rejected', 'completed', 'cancelled']);

        return [
            'user_id'       => User::inRandomOrder()->value('id') ?? User::factory(),
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'VES']),
            'amount'        => $amount,
            'commission'    => $commission,
            'net_amount'    => $netAmount,
            'via_id'        => Via::inRandomOrder()->value('id'),   // puede ser null
            'payment_data'  => json_encode([
                'bank'    => $this->faker->company(),
                'account' => $this->faker->bankAccountNumber(),
            ]),
            'status'        => $status,
            'admin_notes'   => $this->faker->optional(0.4)->sentence(),
            'user_notes'    => $this->faker->optional(0.6)->sentence(),

            // Campos que dependen del status
            'approved_by'   => in_array($status, ['approved', 'completed'])
                                ? (User::inRandomOrder()->value('id') ?? null)
                                : null,
            'approved_at'   => in_array($status, ['approved', 'completed'])
                                ? $this->faker->dateTimeBetween('-30 days', 'now')
                                : null,
            'completed_at'  => $status === 'completed'
                                ? $this->faker->dateTimeBetween('-15 days', 'now')
                                : null,
            'cancelled_at'  => $status === 'cancelled'
                                ? $this->faker->dateTimeBetween('-15 days', 'now')
                                : null,

            'created_at'    => $this->faker->dateTimeBetween('-60 days', 'now'),
            'updated_at'    => now(),
        ];
    }
}