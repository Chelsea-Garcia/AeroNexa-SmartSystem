<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Booking;
use App\Models\skyroute\Trip;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $trips = Trip::take(1)->get();

        foreach ($trips as $trip) {
            Booking::create([
                'user_id'         => rand(1, 5),
                'trip_id'         => $trip->_id,
                'travel_date'     => now()->format('Y-m-d'),
                'payment_method'  => 'AEROPAY',
                'total_amount'    => rand(100, 300),
                'transaction_code' => 'TX-' . rand(10000, 99999),
                'payment_status'  => 'paid',
            ]);
        }
    }
}
