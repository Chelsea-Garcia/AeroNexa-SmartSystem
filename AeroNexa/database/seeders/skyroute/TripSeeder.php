<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Route;
use App\Models\skyroute\Trip;
use Illuminate\Support\Str;

class TripSeeder extends Seeder
{
    public function run(): void
    {
        // Trip::truncate(); // optional

        foreach (Route::all() as $route) {

            switch ($route->type) {

                case 'taxi':
                    $this->createOperationalTrips($route, 'morning', 3, 5, 11);
                    $this->createOperationalTrips($route, 'evening', 3, 17, 23);
                    break;

                case 'bus':
                    $this->createOperationalTrips($route, 'morning', 3, 5, 11);
                    $this->createOperationalTrips($route, 'evening', 3, 16, 22);
                    break;

                case 'train':
                    $this->createOperationalTrips($route, 'morning', 3, 5, 11);
                    break;
            }
        }
    }

    private function createOperationalTrips($route, $operation, $count, $startHour, $endHour)
    {
        // TRANSPORT TYPE PREFIXES (2-letter)
        $prefix = match ($route->type) {
            'taxi'  => 'TA',
            'bus'   => 'BU',
            'train' => 'TR',
            default => 'XX'
        };

        for ($i = 0; $i < $count; $i++) {

            $depHour = rand($startHour, $endHour);
            $depMin = [0, 15, 30, 45][array_rand([0, 1, 2, 3])];

            $departure = sprintf("%02d:%02d", $depHour, $depMin);
            $arrival   = date("H:i", strtotime($departure) + ($route->estimated_minutes * 60));

            // fare logic
            if ($route->estimated_minutes < 20) {
                $fare = rand(40, 80);
            } elseif ($route->estimated_minutes < 60) {
                $fare = rand(80, 160);
            } else {
                $fare = rand(150, 400);
            }

            Trip::create([
                'route_id'       => $route->_id,
                'line_id'        => $route->line_id,

                // NEW: 2-letter codes
                'trip_code'      => $prefix
                    . '-' . strtoupper(substr($route->origin_city, 0, 3))
                    . '-' . strtoupper(substr($route->destination_city, 0, 3))
                    . '-' . rand(1000, 9999),

                'operation'      => $operation,
                'departure_time' => $departure,
                'arrival_time'   => $arrival,
                'fare'           => $fare,
            ]);
        }
    }
}
