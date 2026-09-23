<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\User;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'currency_code' => $this->faker->randomElement(['USD', 'EUR', 'VES', 'CLP']),
            'amount' => $this->faker->randomFloat(2, 0, 10000),
            'credit' => $this->faker->randomFloat(2, 0, 5000),
            'credit_promotion' => $this->faker->randomFloat(2, 0, 1000),
            'deposit' => $this->faker->numberBetween(0, 10000),
            'withdrawal' => $this->faker->numberBetween(0, 5000),
            'via_id' => $this->faker->numberBetween(1, 8),
        ];
    }
}