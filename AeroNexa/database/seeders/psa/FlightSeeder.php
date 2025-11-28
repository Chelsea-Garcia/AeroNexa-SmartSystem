<?php

namespace Database\Seeders\psa;

use Illuminate\Database\Seeder;
use App\Models\psa\Route;
use App\Models\psa\Aircraft;
use App\Models\psa\Flight;
use Carbon\Carbon;

class FlightSeeder extends Seeder
{
    private $usedTimes = []; // track used dep times per ORIGIN

    public function run()
    {
        $routes = Route::all();
        $aircrafts = Aircraft::all();

        $flights = [];

        foreach ($routes as $route) {

            $origin = $route->origin;
            if (!isset($this->usedTimes[$origin])) {
                $this->usedTimes[$origin] = [];
            }

            $frequency = max(1, (int) $route->frequency);

            // aircraft filter logic unchanged
            $candidates = $aircrafts->filter(function ($ac) use ($route) {
                $distance = $route->distance_km ?? 0;

                // Short domestic
                if ($distance <= 800) {
                    return str_contains($ac->model, 'ATR') || str_contains($ac->model, 'A320');
                }

                // Medium domestic/regional
                if ($distance > 800 && $distance <= 3000) {
                    return str_contains($ac->model, 'A320') || str_contains($ac->model, 'A321');
                }

                // Long-haul
                if ($distance > 3000) {
                    return str_contains($ac->model, 'A330') || str_contains($ac->model, 'Boeing 777');
                }

                return false;
            })->values();

            // fallback if none match
            if ($candidates->isEmpty()) {
                $candidates = $aircrafts;
            }


            // Generate time slots according to new logic
            $timeSlots = $this->generateDistributedSlots($frequency);

            for ($i = 0; $i < $frequency; $i++) {

                $flightNum = $this->generateFlightNumber($origin, $route->destination, $i);

                $air = $candidates->random();

                // **NEW LOGIC**
                $depTime = $this->getUniqueDepartureTime($origin, $timeSlots[$i]);

                $duration = $this->normalizeDuration(
                    $route->duration ?? null,
                    $route->distance_km ?? null
                );

                $seats = null;
                if (is_array($air->capacity)) {
                    $seats = $air->capacity['economy'] ?? null;
                } elseif (is_object($air->capacity)) {
                    $seats = $air->capacity->economy ?? null;
                } else {
                    $seats = $air->capacity ?? null;
                }

                $arrTime = (clone $depTime)->addMinutes($duration);

                $depTimeFormatted = $depTime->format('H:i');
                $arrTimeFormatted = $arrTime->format('H:i');

                $flights[] = [
                    'flight_number'   => $flightNum,
                    'route_id'        => $route->_id ?? $route->id ?? null,
                    'origin'          => $origin,
                    'destination'     => $route->destination,
                    'departure_time'  => $depTimeFormatted,
                    'arrival_time'    => $arrTimeFormatted,
                    'duration_min'    => $duration,
                    'aircraft_code'   => $air->aircraft_code ?? ($air->registration ?? null),
                    'aircraft_model'  => $air->model ?? null,
                    'seats' => $seats,
                    'price'       => $route->price ?? null,
                ];
            }
        }

        foreach (array_chunk($flights, 500) as $chunk) {
            Flight::insert($chunk);
        }

        $this->command->info('Flights seeded: ' . count($flights));
    }

    // ------------------------------------------
    // FLIGHT NUMBER GENERATION
    // ------------------------------------------
    private function generateFlightNumber($origin, $dest, $index)
    {
        $seed = crc32(($origin ?? '') . '-' . ($dest ?? '') . '-' . $index);
        $num = str_pad((string) ($seed % 1000), 3, '0', STR_PAD_LEFT);
        return 'PSA' . $num;
    }

    // ------------------------------------------
    // DISTRIBUTION LOGIC (Q2)
    // ------------------------------------------
    private function generateDistributedSlots($frequency)
    {
        $morningStart = 5 * 60;     // 05:00
        $morningEnd = 11 * 60;      // 11:00

        $afternoonStart = 12 * 60;  // 12:00
        $afternoonEnd = 17 * 60;    // 17:00

        $eveningStart = 18 * 60;    // 18:00
        $eveningEnd = 23 * 60;      // 23:00

        // equal distribution
        $base = intdiv($frequency, 3);
        $extra = $frequency % 3;

        $slots = [
            'morning' => $base,
            'afternoon' => $base,
            'evening' => $base,
        ];

        // distribute leftovers (morning → afternoon → evening)
        if ($extra > 0) $slots['morning']++;
        if ($extra > 1) $slots['afternoon']++;

        $result = [];

        // generate times inside each block
        foreach (['morning', 'afternoon', 'evening'] as $block) {
            $count = $slots[$block];

            for ($i = 0; $i < $count; $i++) {
                switch ($block) {
                    case 'morning':
                        $result[] = rand($morningStart, $morningEnd);
                        break;

                    case 'afternoon':
                        $result[] = rand($afternoonStart, $afternoonEnd);
                        break;

                    case 'evening':
                        $result[] = rand($eveningStart, $eveningEnd);
                        break;
                }
            }
        }

        shuffle($result);  // randomize order within the day

        return $result;
    }

    // ------------------------------------------
    // UNIQUE TIME LOGIC (Q3)
    // ------------------------------------------
    private function getUniqueDepartureTime($origin, $minuteOfDay)
    {
        while (true) {
            $hour = intdiv($minuteOfDay, 60);
            $min = $minuteOfDay % 60;

            $timeStr = sprintf("%02d:%02d", $hour, $min);

            if (!in_array($timeStr, $this->usedTimes[$origin])) {
                $this->usedTimes[$origin][] = $timeStr;
                return Carbon::createFromTime($hour, $min, 0);
            }

            // regenerate inside same block (±10 min jitter)
            $minuteOfDay += rand(-10, 10);
            if ($minuteOfDay < 300) $minuteOfDay = 300;   // clamp above 05:00
            if ($minuteOfDay > 1380) $minuteOfDay = 1380; // clamp below 23:00
        }
    }

    private function normalizeDuration($durationField, $distanceKm)
    {
        if (is_numeric($durationField)) {
            return (int) $durationField;
        }

        if ($distanceKm) {
            return (int) round(($distanceKm / 800) * 60);
        }

        return 120;
    }
}
