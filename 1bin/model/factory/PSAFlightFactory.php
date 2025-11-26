<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PSAFlightFactory extends Factory
{
    public function definition()
    {
        $faker = $this->faker;

        // Philippines locations
        $phCities = ['Manila', 'Cebu', 'Davao', 'Clark'];

        // International destination list
        $intlCities = ['Hong Kong','Singapore','Tokyo','Seoul','Bangkok','Kuala Lumpur','Dubai'];

        // 60% chance flights originate from PH
        $originIsPH = $faker->boolean(60);

        if ($originIsPH) {
            // PH to PH or PH to International
            $origin = $faker->randomElement($phCities);

            $possibleDestinations = array_merge($phCities, $intlCities);
            $destination = $faker->randomElement($possibleDestinations);

        } else {
            // International to PH only
            $origin = $faker->randomElement($intlCities);
            $destination = $faker->randomElement($phCities);
        }

        // Force that origin != destination
        while ($destination === $origin) {
            if ($originIsPH) {
                $destination = $faker->randomElement(array_merge($phCities, $intlCities));
            } else {
                $destination = $faker->randomElement($phCities);
            }
        }

        return [
            'flight_id' => $faker->unique()->bothify('FL-#####'),
            'origin' => $origin,
            'destination' => $destination,
            'seats' => $faker->numberBetween(120, 300),
            'price' => $faker->numberBetween(2500, 25000),
            'departure_time' => $faker->dateTimeBetween('+1 day', '+90 days'),
        ];
    }
}