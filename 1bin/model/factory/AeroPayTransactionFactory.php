<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AeroPayTransactionFactory extends Factory
{
    public function definition()
    {
        return [
            'transaction_id' => $this->faker->uuid(),
            'user_id' => rand(1, 500),
            'amount' => $this->faker->randomFloat(2, 100, 20000),
            'status' => $this->faker->randomElement([
                'pending','authorized','captured','refunded'
            ]),
        ];
    }
}
