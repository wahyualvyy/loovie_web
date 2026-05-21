<?php

namespace Database\Factories;

use App\Models\FinancialAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialAccount>
 */
class FinancialAccountFactory extends Factory
{
    protected $model = FinancialAccount::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company(),
            'type' => fake()->randomElement(['cash', 'bank', 'digital_wallet']),
            'initial_balance' => 0,
            'current_balance' => 0,
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
