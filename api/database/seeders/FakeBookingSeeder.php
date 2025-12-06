<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Faker\Factory as Faker;

class FakeBookingSeeder extends Seeder
{
    private $passengerCache = [];
    private $vehicleCache = []; // NEW: Cache vehicles to prevent N+1 HTTP calls

    public function run()
    {
        // 1. Define Microservice URLs
        $psa     = 'http://localhost:8000/api/psa';
        $aeropay = 'http://localhost:8001/api/aeropay';
        $aure    = 'http://localhost:8002/api/aureliya';
        $sky     = 'http://localhost:8003/api/skyroute';
        $tru     = 'http://localhost:8004/api/trutravel';
        $aero    = 'http://localhost:8005/api/aeronexa';

        // Target iterations
        $TARGET_ITERATIONS = 6100;
        $BATCH_SIZE = 50; // Execute 50 requests concurrently

        $this->command->info("Fetching external resources...");

        // 2. Fetch Base Resources (Increase timeouts)
        $timeout = 60;
        $usersResponse = Http::timeout($timeout)->get("$aero/users?per_page=1000");
        $users = $usersResponse->successful() ? ($usersResponse->json()['data'] ?? []) : [];

        $flightsResponse = Http::timeout($timeout)->get("$psa/flights");
        $psaFlights = $flightsResponse->successful() ? $flightsResponse->json() : [];

        $propsResponse = Http::timeout($timeout)->get("$aure/properties");
        $properties = $propsResponse->successful() ? $propsResponse->json() : [];

        $locResponse = Http::timeout($timeout)->get("$sky/locations");
        $locations = $locResponse->successful() ? $locResponse->json() : [];

        $packResponse = Http::timeout($timeout)->get("$tru/packages");
        $packages = $packResponse->successful() ? $packResponse->json() : [];

        // Extract valid cities
        $cities = [];
        foreach ($locations as $l) {
            $city = $l['city'] ?? $l['location_city'] ?? null;
            if ($city) $cities[] = $city;
        }
        $cities = array_values(array_unique($cities));

        if (empty($users)) {
            $this->command->warn("No users found. Seed AeroNexa first.");
            return;
        }

        // --- PRE-FETCH VEHICLES (Optimization) ---
        $this->command->info("Pre-fetching vehicles for " . count($cities) . " cities...");
        foreach ($cities as $city) {
            $resp = Http::get("$sky/vehicles/city/" . urlencode($city));
            if ($resp->successful()) {
                $this->vehicleCache[$city] = $resp->json();
            } else {
                $this->vehicleCache[$city] = [];
            }
        }

        // --- PHASE 1: Pre-seed Passengers ---
        // $this->command->info("Phase 1: Ensuring passengers exist for all " . count($users) . " users...");
        // $userBar = $this->command->getOutput()->createProgressBar(count($users));
        // $userBar->start();

        // foreach ($users as $user) {
        //     $uid = (string)($user['id'] ?? $user['_id']);
        //     $this->getOrFetchPassengers($psa, $uid);
        //     $userBar->advance();
        // }
        // $userBar->finish();
        // $this->command->newLine();

        // --- PHASE 2: Batched Booking Loop ---
        $this->command->info("Phase 2: Generating {$TARGET_ITERATIONS} bookings in batches of {$BATCH_SIZE}...");
        $bar = $this->command->getOutput()->createProgressBar($TARGET_ITERATIONS);
        $bar->start();

        $batches = (int) ceil($TARGET_ITERATIONS / $BATCH_SIZE);

        for ($b = 0; $b < $batches; $b++) {

            // 1. Prepare Payload Data for this Batch
            $preparedRequests = [];

            // Determine actual batch size (for the last partial batch)
            $currentBatchSize = min($BATCH_SIZE, $TARGET_ITERATIONS - ($b * $BATCH_SIZE));

            for ($i = 0; $i < $currentBatchSize; $i++) {
                $user = $users[array_rand($users)];
                $uid = (string)($user['id'] ?? $user['_id']);
                $uname = ($user['first_name'] ?? 'Guest') . ' ' . ($user['last_name'] ?? '');

                // Get cached passengers
                $passengers = $this->passengerCache[$uid] ?? [];
                if (empty($passengers)) continue;

                $reqData = $this->prepareBookingData(
                    $uid,
                    $uname,
                    $passengers,
                    $psa,
                    $psaFlights,
                    $aure,
                    $properties,
                    $sky,
                    $locations,
                    $cities,
                    $tru,
                    $packages
                );

                if ($reqData) {
                    $preparedRequests[] = $reqData;
                }
            }

            // 2. Execute Booking Pool (Concurrent POSTs)
            if (!empty($preparedRequests)) {
                $responses = Http::pool(function (Pool $pool) use ($preparedRequests) {
                    foreach ($preparedRequests as $idx => $req) {
                        $pool->as($idx)->post($req['url'], $req['data']);
                    }
                });

                // 3. Process Responses & Prepare Status Updates
                $statusUpdates = [];

                foreach ($responses as $idx => $response) {
                    if ($response->successful()) {
                        $json = $response->json();
                        $service = $preparedRequests[$idx]['service'];

                        // Extract ID based on service structure
                        $bookingId = null;
                        if ($service === 'TRUTRAVEL') {
                            $bookingId = $this->getMongoId($json['data']['booking'] ?? $json['data'] ?? $json);
                        } else {
                            // Standard wrapper
                            $bookingId = $this->getMongoId($json['data'] ?? $json);
                        }

                        if ($bookingId) {
                            $statuses = ['paid', 'paid', 'paid', 'pending', 'failed', 'cancelled'];
                            $st = $statuses[array_rand($statuses)];

                            // Determine status URL
                            $baseUrl = match ($service) {
                                'PSA' => $psa,
                                'AURELIYA' => $aure,
                                'SKYROUTE' => $sky,
                                'TRUTRAVEL' => $tru,
                            };

                            $statusUpdates[] = [
                                'url' => "$baseUrl/booking/$bookingId/status",
                                'data' => ['payment_status' => $st]
                            ];
                        }
                    }
                }

                // 4. Execute Status Update Pool (Concurrent PUTs)
                if (!empty($statusUpdates)) {
                    Http::pool(function (Pool $pool) use ($statusUpdates) {
                        foreach ($statusUpdates as $upd) {
                            $pool->put($upd['url'], $upd['data']);
                        }
                    });
                }
            }

            $bar->advance($currentBatchSize);
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Seeding completed.");
    }

    /**
     * Logic to decide service and prepare data (In-Memory)
     */
    private function prepareBookingData($uid, $uname, $passengers, $psa, $flights, $aure, $props, $sky, $locs, $cities, $tru, $packages)
    {
        $rand = rand(1, 100);

        // --- PSA FLIGHT (30%) ---
        if ($rand <= 30 && !empty($flights) && !empty($passengers)) {
            $flight = $flights[array_rand($flights)];
            $pass   = $passengers[array_rand($passengers)];

            $pid = $this->getMongoId($pass);
            $fid = $this->getMongoId($flight);

            if ($pid && $fid) {
                return [
                    'service' => 'PSA',
                    'url' => "$psa/bookings",
                    'data' => [
                        'user_id'      => $uid,
                        'passenger_id' => $pid,
                        'flight_id'    => $fid,
                        'flight_date'  => now()->addDays(rand(1, 180))->format('Y-m-d'),
                    ]
                ];
            }
        }

        // --- AURELIYA HOTEL (30%) ---
        elseif ($rand <= 60 && !empty($props)) {
            $prop = $props[array_rand($props)];
            $propid = $this->getMongoId($prop);

            if ($propid) {
                $checkIn = now()->addDays(rand(1, 180));
                $checkOut = $checkIn->copy()->addDays(rand(1, 7));

                return [
                    'service' => 'AURELIYA',
                    'url' => "$aure/bookings",
                    'data' => [
                        'user_id'     => $uid,
                        'property_id' => $propid,
                        'check_in'    => $checkIn->format('Y-m-d'),
                        'check_out'   => $checkOut->format('Y-m-d'),
                    ]
                ];
            }
        }

        // --- SKYROUTE VEHICLE (30%) ---
        elseif ($rand <= 90 && !empty($locs) && !empty($cities)) {
            // Use Cached Vehicles!
            $city = $cities[array_rand($cities)];
            $vehicles = $this->vehicleCache[$city] ?? [];

            if (!empty($vehicles)) {
                $veh = $vehicles[array_rand($vehicles)];
                $vid = $this->getMongoId($veh);
                $vehLocId = $veh['location_id'] ?? null;

                // Find origin (in memory)
                $originLoc = null;
                foreach ($locs as $l) {
                    if ($this->getMongoId($l) === $vehLocId) {
                        $originLoc = $l;
                        break;
                    }
                }

                if ($originLoc) {
                    // Find destination (in memory)
                    $originDiv = $originLoc['division'] ?? 'Unknown';
                    $possibleDest = array_filter($locs, function ($l) use ($originDiv, $vehLocId) {
                        return ($l['division'] ?? '') === $originDiv && $this->getMongoId($l) !== $vehLocId;
                    });

                    if (!empty($possibleDest)) {
                        $destLoc = $possibleDest[array_rand($possibleDest)];

                        return [
                            'service' => 'SKYROUTE',
                            'url' => "$sky/bookings",
                            'data' => [
                                'user_id'                 => $uid,
                                'vehicle_id'              => $vid,
                                'origin_location_id'      => $this->getMongoId($originLoc),
                                'destination_location_id' => $this->getMongoId($destLoc),
                                'date'                    => now()->addDays(rand(1, 120))->format('Y-m-d'),
                                'time'                    => sprintf('%02d:%02d', rand(6, 22), rand(0, 59)),
                                'passenger_name'          => $uname
                            ]
                        ];
                    }
                }
            }
        }

        // --- TRUTRAVEL PACKAGE (10%) ---
        else {
            if (!empty($packages) && !empty($passengers)) {
                $pack = $packages[array_rand($packages)];
                $pass = $passengers[array_rand($passengers)];

                $packId = $this->getMongoId($pack);
                $passId = $this->getMongoId($pass);

                if ($packId && $passId) {
                    return [
                        'service' => 'TRUTRAVEL',
                        'url' => "$tru/bookings",
                        'data' => [
                            'user_id'        => $uid,
                            'package_id'     => $packId,
                            'travel_date'    => now()->addDays(rand(1, 180))->format('Y-m-d'),
                            'passenger_name' => $uname,
                            'passenger_id'   => $passId,
                        ]
                    ];
                }
            }
        }

        return null;
    }

    private function getOrFetchPassengers($baseUrl, $uid)
    {
        if (isset($this->passengerCache[$uid])) {
            return $this->passengerCache[$uid];
        }

        $resp = Http::get("$baseUrl/passengers/user/$uid");
        $passengers = $resp->successful() ? $resp->json() : [];

        if (empty($passengers)) {
            $passengers = $this->createDummyPassengers($baseUrl, $uid);
        }

        $this->passengerCache[$uid] = $passengers;
        return $passengers;
    }

    private function getMongoId($model)
    {
        if (empty($model)) return null;
        if (isset($model['_id']) && is_string($model['_id'])) return $model['_id'];
        if (isset($model['id']) && is_string($model['id'])) return $model['id'];
        if (isset($model['_id']['$oid'])) return $model['_id']['$oid'];
        return null;
    }

    private function createDummyPassengers($baseUrl, $userId)
    {
        $created = [];
        $faker = Faker::create();
        $count = rand(1, 5);

        for ($i = 0; $i < $count; $i++) {
            $payload = [
                'user_id'                  => $userId,
                'first_name'               => $faker->firstName,
                'last_name'                => $faker->lastName,
                'gender'                   => $faker->randomElement(['Male', 'Female']),
                'birthdate'                => $faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                'nationality'              => $faker->country,
                'passport_number'          => 'P' . $faker->unique()->numerify('#######'),
                'passport_expiry'          => $faker->dateTimeBetween('+2 years', '+10 years')->format('Y-m-d'),
                'special_assistance'       => null,
                'contact_number'           => $faker->numerify('09#########'),
                'emergency_contact_name'   => $faker->name,
                'emergency_contact_number' => $faker->numerify('09#########'),
            ];

            $resp = Http::post("$baseUrl/passengers", $payload);
            if ($resp->successful()) {
                $json = $resp->json();
                if (isset($json['data'])) {
                    $created[] = $json['data'];
                }
            }
        }
        return $created;
    }
}
