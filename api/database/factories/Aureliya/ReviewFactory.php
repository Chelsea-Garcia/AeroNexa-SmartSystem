<?php

namespace Database\Factories\aureliya;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            '_id' => (string) Str::uuid(),

            // Foreign keys are filled later in seeder
            'property_id' => null,
            'user_id' => 'AEX-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),

            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(12),

            // Add timestamps
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
