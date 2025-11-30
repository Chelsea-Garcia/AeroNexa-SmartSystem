<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FakeBookingSeeder extends Seeder
{
    public function run()
    {
        // Base URLs
        $psa = 'http://localhost:8000/api/psa';
        $aeropay = 'http://localhost:8001/api/aeropay';
        $aure = 'http://localhost:8002/api/aureliya';
        $sky = 'http://localhost:8003/api/skyroute';
        $tru = 'http://localhost:8004/api/trutravel';
        $aero = 'http://localhost:8005/api/aeronexa';

        // Fetch users
        $users = Http::get("$aero/users")->json()['data'] ?? [];

        // Foreign resources
        $psaFlights = Http::get("$psa/flights")->json() ?? [];
        $properties = Http::get("$aure/properties")->json()['data'] ?? [];
        $locations = Http::get("$sky/locations")->json()['data'] ?? [];
        $packages = Http::get("$tru/packages")->json()['data'] ?? [];

        // Extract city list
        $cities = [];
        foreach ($locations as $l) {
            if (!empty($l['city'])) $cities[] = $l['city'];
            if (!empty($l['location_city'])) $cities[] = $l['location_city'];
        }
        $cities = array_values(array_unique($cities));

        // Loop all users
        foreach ($users as $user) {

            $uid = (string)$user['id'];
            $uname = $user['first_name'] . ' ' . ($user['last_name'] ?? '');

            /* ================================
   PSA PASSENGERS (CREATE IF NONE)
================================= */
            $psaPassengers = Http::get("$psa/passengers/user/$uid")->json() ?? [];

            // If no passengers exist for this user → create 1–3 passengers
            if (empty($psaPassengers)) {

                $firstNames = ['John', 'Maria', 'Lucas', 'Emily', 'Kenji', 'Aisha', 'Noah', 'Sakura', 'Miguel', 'Anna'];
                $lastNames  = ['Santos', 'Garcia', 'Tanaka', 'Smith', 'Reyes', 'Johnson', 'Lee', 'Martinez', 'Kim', 'Sharma'];
                $genders    = ['male', 'female'];
                $countries  = ['Philippines', 'Japan', 'USA', 'Canada', 'South Korea', 'Australia', 'Singapore'];

                $toGenerate = rand(1, 3);

                for ($i = 0; $i < $toGenerate; $i++) {
                    $fname = $firstNames[array_rand($firstNames)];
                    $lname = $lastNames[array_rand($lastNames)];

                    $passengerPayload = [
                        'user_id'                  => $uid,
                        'first_name'               => $fname,
                        'last_name'                => $lname,
                        'gender'                   => $genders[array_rand($genders)],
                        'birthdate'                => now()->subYears(rand(18, 60))->subDays(rand(0, 365))->format('Y-m-d'),
                        'nationality'              => $countries[array_rand($countries)],
                        'passport_number'          => strtoupper(Str::random(2)) . rand(1000000, 9999999),
                        'passport_expiry'          => now()->addYears(rand(1, 10))->format('Y-m-d'),
                        'special_assistance'       => null,
                        'contact_number'           => '09' . rand(100000000, 999999999),
                        'emergency_contact_name'   => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
                        'emergency_contact_number' => '09' . rand(100000000, 999999999),
                    ];

                    Http::post("$psa/passengers", $passengerPayload);
                }

                // Fetch again after creation
                $psaPassengers = Http::get("$psa/passengers/user/$uid")->json() ?? [];
            }

            /* Continue with PSA booking only if we have flights + passengers */
            if (!empty($psaFlights) && !empty($psaPassengers)) {

                $flight = $psaFlights[array_rand($psaFlights)];
                $pass   = $psaPassengers[array_rand($psaPassengers)];

                $flightDate = now()->addDays(rand(1, 60))->format('Y-m-d');

                $psaPayload = [
                    'user_id'     => $uid,
                    'passenger_id' => $pass['_id'] ?? $pass['id'],
                    'flight_id'   => $flight['_id'] ?? $flight['id'],
                    'flight_date' => $flightDate,
                ];

                $b = Http::post("$psa/bookings", $psaPayload)->json();

                if (!empty($b['id'])) {
                    $this->randomizeStatus("$psa/booking/{$b['id']}/status");
                }
            }

            /* ================================
               AURELIYA HOTEL BOOKINGS
            ================================= */
            if (!empty($properties)) {
                $prop = $properties[array_rand($properties)];

                $checkIn = now()->addDays(rand(1, 30));
                $checkOut = $checkIn->copy()->addDays(rand(1, 5));

                $payload = [
                    'user_id' => $uid,
                    'property_id' => $prop['id'] ?? $prop['_id'] ?? null,
                    'check_in' => $checkIn->format('Y-m-d'),
                    'check_out' => $checkOut->format('Y-m-d'),
                ];

                $b = Http::post("$aure/bookings", $payload)->json();
                if (!empty($b['id'])) {
                    $this->randomizeStatus("$aure/booking/{$b['id']}/status");
                }
            }

            /* ================================
               SKYROUTE VEHICLE BOOKINGS
            ================================= */
            if (!empty($locations) && !empty($cities)) {
                $city = $cities[array_rand($cities)];

                // Fetch vehicles for this city
                $vehicles = Http::get("$sky/vehicles/city/" . urlencode($city))->json() ?? [];

                if (!empty($vehicles)) {
                    $veh = $vehicles[array_rand($vehicles)];

                    $loc1 = $locations[array_rand($locations)];
                    $loc2 = $locations[array_rand($locations)];

                    if (($loc1['_id'] ?? $loc1['id']) === ($loc2['_id'] ?? $loc2['id'])) {
                        $loc2 = $locations[array_rand($locations)];
                    }

                    $rideDate = now()->addDays(rand(1, 120))->format('Y-m-d');
                    $rideTime = sprintf('%02d:%02d', rand(0, 23), rand(0, 59));

                    $payload = [
                        'user_id' => $uid,
                        'vehicle_id' => $veh['_id'] ?? $veh['id'] ?? null,
                        'origin_location_id' => $loc1['_id'] ?? $loc1['id'] ?? null,
                        'destination_location_id' => $loc2['_id'] ?? $loc2['id'] ?? null,
                        'date' => $rideDate,
                        'time' => $rideTime,
                        'passenger_name' => $uname
                    ];

                    $b = Http::post("$sky/bookings", $payload)->json();

                    if (!empty($b['id'])) {
                        $this->randomizeStatus("$sky/booking/{$b['id']}/status");
                    }
                }
            }

            /* ================================
               TRUTRAVEL PACKAGE BOOKINGS
            ================================= */
            $psaPassengersForUser = Http::get("$psa/passengers/user/$uid")->json() ?? [];

            if (!empty($packages)) {
                $pack = $packages[array_rand($packages)];

                $travelDate = now()->addDays(rand(1, 90))->format('Y-m-d');

                $payload = [
                    'user_id' => $uid,
                    'package_id' => $pack['id'] ?? $pack['_id'] ?? null,
                    'travel_date' => $travelDate,
                    'passenger_name' => $uname,
                    'passenger_id' => (!empty($psaPassengersForUser)
                        ? ($psaPassengersForUser[array_rand($psaPassengersForUser)]['_id'] ?? null)
                        : null),
                ];

                $b = Http::post("$tru/bookings", $payload)->json();

                if (!empty($b['id'])) {
                    $this->randomizeStatus("$tru/booking/{$b['id']}/status");
                }
            }
        }
    }

    private function randomizeStatus($endpoint)
    {
        $statuses = ['paid', 'pending', 'failed', 'cancelled'];
        $st = $statuses[array_rand($statuses)];
        Http::put($endpoint, ['payment_status' => $st]);
    }
}
