<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WithdrawalLog;
use App\Models\WithdrawalRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class WithdrawalLogFactory extends Factory
{
    protected $model = WithdrawalLog::class;

    public function definition(): array
    {
        $action = $this->faker->randomElement([
            'created',
            'approved',
            'rejected',
            'completed',
            'cancelled',
            'status_changed',
            'note_added',
        ]);

        return [
            'withdrawal_request_id' => WithdrawalRequest::inRandomOrder()->value('id')
                                        ?? WithdrawalRequest::factory(),

            'admin_id'  => $this->faker->optional(0.7)->randomElement(
                                User::inRandomOrder()->pluck('id')->toArray()
                            ),

            'action'    => $action,

            'notes'     => $this->faker->optional(0.8)->sentence(),

            'data'      => json_encode([
                'previous_status' => $this->faker->randomElement(['pending', 'approved']),
                'new_status'      => $action,
                'ip'              => $this->faker->ipv4(),
            ]),

            'created_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'updated_at' => now(),
        ];
    }
}