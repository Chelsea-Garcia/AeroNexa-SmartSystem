<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SkyRouteTransportFactory extends Factory
{
    public function definition()
    {
        $faker = $this->faker;

        $ph = ['Manila','Cebu','Davao','Clark','Iloilo'];
        $intl = ['Hong Kong','Singapore','Tokyo'];

        $isPH = $faker->boolean(75);
        $cities = $isPH ? $ph : $intl;

        $type = $faker->randomElement(['taxi','bus','train']);

        $capacity = match($type) {
            'taxi' => $faker->numberBetween(1,4),
            'bus' => $faker->numberBetween(20,60),
            'train' => $faker->numberBetween(100,300),
        };

        $price = match($type) {
            'taxi' => $faker->randomFloat(2, 50, 300),
            'bus' => $faker->randomFloat(2, 20, 150),
            'train' => $faker->randomFloat(2, 30, 200),
        };

        return [
            'transport_id' => $faker->unique()->bothify('TR-#####'),
            'transport_type' => $type,
            'vehicle_number' => $faker->bothify($isPH ? 'PH-###' : 'INT-###'),
            'capacity' => $capacity,
            'route' => $faker->randomElement($cities).' - '.$faker->randomElement($cities),
            'location' => $faker->randomElement($cities),
            'status' => $faker->randomElement(['available','occupied','maintenance']),
            'price' => $price,
        ];
    }
}
