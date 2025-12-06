<?php

namespace Database\Seeders\trutravel;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Faker\Factory as Faker;
use Illuminate\Http\Client\ConnectionException;

class BookingSeeder extends Seeder
{
    private $passengerCache = [];

    public function run()
    {
        // 1. Config
        $psa     = 'http://localhost:8000/api/psa';
        $tru     = 'http://localhost:8004/api/trutravel';
        $aero    = 'http://localhost:8005/api/aeronexa';

        $TARGET_BOOKINGS = 770;
        $BATCH_SIZE = 25; // Concurrent requests per batch

        $this->command->info("--- Starting TruTravel Seeder ($TARGET_BOOKINGS bookings) ---");

        // 2. Fetch Resources with Exception Handling
        try {
            $usersResponse = Http::timeout(60)->get("$aero/users?per_page=1000");
            $users = $usersResponse->successful() ? ($usersResponse->json()['data'] ?? []) : [];

            $packResponse = Http::timeout(60)->get("$tru/packages");
            $packages = $packResponse->successful() ? $packResponse->json() : [];
        } catch (\Exception $e) {
            $this->command->error("Connection failed while fetching resources: " . $e->getMessage());
            return;
        }

        if (empty($users) || empty($packages)) {
            $this->command->error("Missing Users or Packages. Seed AeroNexa and TruTravel first.");
            return;
        }

        // 3. Pre-seed Passengers (Phase 1)
        $this->command->info("Phase 1: Ensuring passengers exist...");
        $userBar = $this->command->getOutput()->createProgressBar(count($users));
        $userBar->start();

        foreach ($users as $user) {
            $uid = (string)($user['id'] ?? $user['_id']);
            $this->getOrFetchPassengers($psa, $uid);
            $userBar->advance();
        }
        $userBar->finish();
        $this->command->newLine();

        // 4. Booking Loop (Phase 2)
        $this->command->info("Phase 2: Generating bookings...");
        $bar = $this->command->getOutput()->createProgressBar($TARGET_BOOKINGS);
        $bar->start();

        $batches = (int) ceil($TARGET_BOOKINGS / $BATCH_SIZE);

        for ($b = 0; $b < $batches; $b++) {

            // Prepare Batch
            $preparedRequests = [];
            $currentBatchSize = min($BATCH_SIZE, $TARGET_BOOKINGS - ($b * $BATCH_SIZE));

            for ($i = 0; $i < $currentBatchSize; $i++) {
                $user = $users[array_rand($users)];
                $uid = (string)($user['id'] ?? $user['_id']);
                $uname = ($user['first_name'] ?? 'Guest') . ' ' . ($user['last_name'] ?? '');

                $passengers = $this->passengerCache[$uid] ?? [];

                if (empty($passengers)) continue;

                $reqData = $this->prepareBooking($uid, $uname, $passengers, $packages);

                if ($reqData) {
                    $preparedRequests[] = $reqData;
                }
            }

            // Execute Batch
            if (!empty($preparedRequests)) {
                // Use a try-catch for the batch execution to catch connection issues
                try {
                    $responses = Http::pool(function ($pool) use ($tru, $preparedRequests) {
                        foreach ($preparedRequests as $req) {
                            $pool->post("$tru/bookings", $req);
                        }
                    });

                    // Status Updates
                    $statusUpdates = [];
                    foreach ($responses as $response) {
                        // Check if the response is valid before checking successful()
                        if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                            $json = $response->json();
                            // Handle data structure
                            $booking = $json['data']['booking'] ?? $json['data'] ?? $json;
                            $bid = $this->getMongoId($booking);

                            if ($bid) {
                                $statuses = ['paid', 'paid', 'paid', 'pending', 'failed', 'cancelled'];
                                $st = $statuses[array_rand($statuses)];

                                $statusUpdates[] = [
                                    'url' => "$tru/booking/$bid/status",
                                    'data' => ['payment_status' => $st]
                                ];
                            }
                        }
                    }

                    // Execute Status Updates
                    if (!empty($statusUpdates)) {
                        Http::pool(function ($pool) use ($statusUpdates) {
                            foreach ($statusUpdates as $upd) {
                                $pool->put($upd['url'], $upd['data']);
                            }
                        });
                    }
                } catch (\Exception $e) {
                    // Log error but continue seeding (don't crash the loop)
                    // $this->command->warn("Batch failed: " . $e->getMessage());
                }
            }

            $bar->advance($currentBatchSize);
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("TruTravel Seeding Completed.");
    }

    private function prepareBooking($uid, $uname, $passengers, $packages)
    {
        $pack = $packages[array_rand($packages)];
        $pass = $passengers[array_rand($passengers)];

        $packId = $this->getMongoId($pack);
        $passId = $this->getMongoId($pass);

        if ($packId && $passId) {
            return [
                'user_id'        => $uid,
                'package_id'     => $packId,
                'travel_date'    => now()->addDays(rand(1, 180))->format('Y-m-d'),
                'passenger_name' => $uname,
                'passenger_id'   => $passId,
            ];
        }
        return null;
    }

    private function getOrFetchPassengers($baseUrl, $uid)
    {
        if (isset($this->passengerCache[$uid])) {
            return $this->passengerCache[$uid];
        }

        try {
            $resp = Http::get("$baseUrl/passengers/user/$uid");
            $passengers = $resp->successful() ? $resp->json() : [];
        } catch (\Exception $e) {
            $passengers = [];
        }

        if (empty($passengers)) {
            $passengers = $this->createDummyPassengers($baseUrl, $uid);
        }

        $this->passengerCache[$uid] = $passengers;
        return $passengers;
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

            try {
                $resp = Http::post("$baseUrl/passengers", $payload);
                if ($resp->successful()) {
                    $json = $resp->json();
                    if (isset($json['data'])) {
                        $created[] = $json['data'];
                    }
                }
            } catch (\Exception $e) {
                // Ignore failed passenger creation
            }
        }
        return $created;
    }

    private function getMongoId($model)
    {
        if (empty($model)) return null;
        if (is_string($model)) return $model; // Handle direct string ID
        if (isset($model['_id']) && is_string($model['_id'])) return $model['_id'];
        if (isset($model['id']) && is_string($model['id'])) return $model['id'];
        if (isset($model['_id']['$oid'])) return $model['_id']['$oid'];
        return null;
    }
}
