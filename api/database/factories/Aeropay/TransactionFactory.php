<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TransactionFactory extends Factory
{
    public function definition()
    {
        return [
            'transaction_code' => 'APAY-' . strtoupper(Str::random(8)),
            'user_id' => 'AEX-' . $this->faker->numerify('########'),
            'partner' => $this->faker->randomElement(['PSA', 'TRUTRAVEL', 'SKYROUTE', 'AURELIYA']),
            'partner_reference_id' => (string) $this->faker->uuid,
            'amount' => $this->faker->randomFloat(2, 100, 20000),
            'currency' => 'PHP',
            'payment_method' => $this->faker->randomElement(['AEROPAY', 'CARD', 'GCASH']),
            'status' => 'paid',
            'metadata' => ['ip' => $this->faker->ipv4(), 'note' => 'seeded test'],
        ];
    }
}
