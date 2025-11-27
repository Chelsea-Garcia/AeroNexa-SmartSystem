<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Booking;
use App\Models\skyroute\Location;
use App\Models\skyroute\Vehicle;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $origin = Location::first();
        $destination = Location::skip(1)->first();
        $vehicle = Vehicle::first();

        if (!$origin || !$destination || !$vehicle) {
            // make sure vehicles & locations are seeded first
            info('BookingSeeder: missing origin/destination/vehicle - skip creating sample booking.');
            return;
        }

        // Haversine distance (km)
        $distanceKm = $this->haversine(
            $origin->latitude,
            $origin->longitude,
            $destination->latitude,
            $destination->longitude
        );

        $farePerKm = $vehicle->fare_per_km ?? 25; // fallback
        $estimated = round($distanceKm * $farePerKm, 2);

        Booking::create([
            'user_id' => 1,
            'origin_location_id' => $origin->_id,
            'destination_location_id' => $destination->_id,
            'vehicle_id' => $vehicle->_id,
            'date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'time' => '09:00',
            'passenger_name' => 'Jane Sample',
            'payment_method' => 'AEROPAY',
            'payment_status' => 'pending',
            'estimated_amount' => $estimated,
            'transaction_code' => null,
        ]);
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        // if coordinates missing return 0
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return 0.0;
        }

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
