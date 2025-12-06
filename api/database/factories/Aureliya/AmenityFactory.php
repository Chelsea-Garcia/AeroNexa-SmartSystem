<?php

namespace Database\Factories\aureliya;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AmenityFactory extends Factory
{
    public function definition()
    {
        return [
            '_id' => (string) Str::uuid(),
            'name' => $this->faker->unique()->randomElement([
                'Wifi',
                'Pool',
                'Parking',
                'Kitchen',
                'Air Conditioning',
                'Gym',
                'TV',
                'Hot Shower',
                'Balcony',
                'Security'
            ]),
        ];
    }
}
