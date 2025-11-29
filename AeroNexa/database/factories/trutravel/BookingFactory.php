<?php

namespace Database\Factories\trutravel;

use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => $this->faker->uuid(),
            'package_id' => null,

            'psa_booking_id' => null,
            'skyroute_booking_id' => null,
            'aureliya_booking_id' => null,

            'transaction_code' => null,

            'amount' => 0,
            'currency' => 'PHP',

            'payment_status' => 'pending',
            'status' => 'pending',

            'metadata' => null,
        ];
    }
}
