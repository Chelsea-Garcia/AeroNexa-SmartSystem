<?php

namespace Database\Factories\trutravel;

use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true) . ' Package',
            'description' => $this->faker->sentence(),

            // IDs will later be overwritten by the Seeder
            'skyroute_origin_id' => null,
            'skyroute_destination_id' => null,
            'skyroute_vehicle_id' => null,
            'airline_flight_id' => null,
            'aureliya_property_id' => null,

            'base_price' => 0,
            'discount_rate' => 0,
            'final_price' => 0,
        ];
    }
}
