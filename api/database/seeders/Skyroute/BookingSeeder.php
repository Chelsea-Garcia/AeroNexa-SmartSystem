<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Booking;
use App\Models\skyroute\Vehicle;

class BookingSeeder extends Seeder
{
    public function run()
    {
        // 1. Get bookings that need updating
        $bookings = Booking::whereNull('passenger_amount')->get();
        
        if ($bookings->isEmpty()) {
            $this->command->info('No bookings found to update.');
            return;
        }

        $this->command->info("Updating {$bookings->count()} bookings based on vehicle capacity...");

        foreach ($bookings as $booking) {
            
            // 2. Find the associated Vehicle
            // We try to load the relation, or find it manually if needed
            $vehicle = $booking->vehicle ?? Vehicle::find($booking->vehicle_id);

            // 3. Define Max Capacity based on Type (Hardcoded)
            $maxCapacity = 5; // Default (Car)

            if ($vehicle) {
                switch ($vehicle->type) {
                    case 'Bus':
                        $maxCapacity = 56;
                        break;
                    case 'SUV':
                        $maxCapacity = 7;
                        break;
                    case 'Car':
                    default:
                        $maxCapacity = 5;
                        break;
                }
            }

            // 4. Generate Random Pax (1 to Max)
            $pax = rand(1, $maxCapacity);

            $booking->passenger_amount = $pax;
            $booking->save();
        }

        $this->command->info("Successfully updated bookings with realistic passenger counts!");
    }
}