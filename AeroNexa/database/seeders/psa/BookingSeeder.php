<?php

namespace Database\Seeders\psa;

use Illuminate\Database\Seeder;
use App\Models\psa\Booking;
use App\Models\psa\Passenger;
use App\Models\psa\Flight;

class BookingSeeder extends Seeder
{
    public function run()
    {
        // MongoDB truncate alternative
        Booking::raw()->deleteMany([]);

        $passenger = Passenger::first();
        $flight = Flight::first();

        if (!$passenger || !$flight) {
            $this->command->error('Passenger or Flight not found. Seed PSA passengers and flights first.');
            return;
        }

        // Extract HH:mm only
        $dep = substr($flight->departure_time, 0, 5);
        $arr = substr($flight->arrival_time, 0, 5);

        $bookings = [
            [
                'user_id'        => $passenger->user_id,
                'passenger_id'   => $passenger->_id,
                'flight_id'      => $flight->_id,

                'flight_date'    => '2025-02-18',
                'departure_time' => $dep,
                'arrival_time'   => $arr,

                'total_amount'   => $flight->price,   // corrected field name
                'payment_method' => 'AEROPAY',

                'transaction_code' => null,
                'payment_status'   => 'pending',
            ],
        ];

        Booking::insert($bookings);

        $this->command->info('Booking seeding completed (MongoDB).');
    }
}
