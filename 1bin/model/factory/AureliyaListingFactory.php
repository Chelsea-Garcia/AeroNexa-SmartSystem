<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AureliYaListingFactory extends Factory
{
    public function definition()
    {
        $faker = $this->faker;

        $ph = ['Manila','Cebu','Davao','Boracay','Palawan'];
        $intl = ['Hong Kong','Singapore','Tokyo','Seoul'];

        $types = ['hotel','motel','private_stay','resort','hostel','apartment'];

        return [
            'accommodation_id' => $faker->unique()->bothify('AY-#####'),
            'provider_id' => $faker->numberBetween(1, 100),
            'title' => $faker->sentence(3),
            'accommodation_type' => $faker->randomElement($types),
            'location' => $faker->randomElement(array_merge($ph,$intl)),
            'price' => $faker->randomFloat(2, 900, 12000),
            'availability' => $faker->boolean(80),
        ];
    }
}
