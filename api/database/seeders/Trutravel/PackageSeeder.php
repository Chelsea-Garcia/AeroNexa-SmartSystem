<?php

namespace Database\Seeders\trutravel;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\trutravel\Package;

class PackageSeeder extends Seeder
{
    /**
     * Helper: Haversine distance (km)
     */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earth * (2 * asin(min(1, sqrt($a))));
    }

    private function getLongitude(array $loc)
    {
        return $loc['longitude'] ?? $loc['longtitude'] ?? $loc['long'] ?? 0;
    }

    // HELPER: Safely get ID
    private function getId($item)
    {
        return $item['_id'] ?? $item['id'] ?? null;
    }

    public function run()
    {
        // ==========================================
        // 1. CONFIGURATION (Tweaked to 1000)
        // ==========================================
        $targetCount = 925; 
        
        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        $this->command->info("--- Starting Package Seeder (Target: $targetCount) ---");

        // 2. Fetch Data
        try {
            $airportsResp  = Http::get("{$psaBase}/airports");
            $flightsResp   = Http::get("{$psaBase}/flights");
            $locationsResp = Http::get("{$srBase}/locations");
            $countriesResp = Http::get("{$aureBase}/countries");
        } catch (\Exception $e) {
            $this->command->error("CRITICAL: Connection Failed. " . $e->getMessage());
            return;
        }

        // 3. Process Data
        $airports  = collect($airportsResp->json() ?? []);
        $flights   = collect($flightsResp->json() ?? []);
        $locations = collect($locationsResp->json()['data'] ?? $locationsResp->json() ?? []); 
        $countries = collect($countriesResp->json() ?? []);

        // 4. Fetch Properties
        $props = collect([]);
        if ($countries->isNotEmpty()) {
            $this->command->info("Fetching properties...");
            foreach ($countries as $countryName) {
                try {
                    $pResp = Http::get("{$aureBase}/properties", ['country' => $countryName]);
                    $countryProps = $pResp->json();
                    if (!empty($countryProps) && is_array($countryProps)) {
                        $props = $props->merge($countryProps);
                    }
                } catch (\Exception $e) { }
            }
        }

        if ($airports->isEmpty() || $flights->isEmpty() || $locations->isEmpty() || $props->isEmpty()) { 
            $this->command->error("❌ STOP: One or more datasets are empty."); 
            return; 
        }

        // 5. Organize Flights
        $airportMap = $airports->keyBy('code');
        $domestic   = collect();
        $outbound   = collect();
        $inbound    = collect();

        foreach ($flights as $flight) {
            $originCode = $flight['origin'] ?? null;
            $destCode   = $flight['destination'] ?? null;

            if (!isset($airportMap[$originCode]) || !isset($airportMap[$destCode])) continue;

            $originObj = $airportMap[$originCode];
            $destObj   = $airportMap[$destCode];

            $isOriginPH = in_array(strtoupper($originObj['country']), ['PHILIPPINES', 'PH']);
            $isDestPH   = in_array(strtoupper($destObj['country']), ['PHILIPPINES', 'PH']);

            if ($isOriginPH && $isDestPH) {
                $domestic->push($flight);
            } elseif ($isOriginPH && !$isDestPH) {
                $outbound->push($flight);
            } elseif (!$isOriginPH && $isDestPH) {
                $inbound->push($flight);
            }
        }

        // 6. Generate Packages
        $created = 0;
        $attempts = 0;
        $maxAttempts = $targetCount * 10;

        $this->command->info("Generating packages (this may take a while)...");

        while ($created < $targetCount && $attempts < $maxAttempts) {
            $attempts++;

            // A. Pick Strategy
            $rand = rand(1, 100);
            $type = 'DOMESTIC';
            $flight = null;

            if ($rand <= 40 && $domestic->isNotEmpty()) {
                $type = 'DOMESTIC';
                $flight = $domestic->random();
            } elseif ($rand <= 70 && $outbound->isNotEmpty()) {
                $type = 'INTERNATIONAL_OUTBOUND';
                $flight = $outbound->random();
            } elseif ($inbound->isNotEmpty()) {
                $type = 'INTERNATIONAL_INBOUND';
                $flight = $inbound->random();
            } else {
                if ($domestic->isNotEmpty()) $flight = $domestic->random();
                elseif ($outbound->isNotEmpty()) $flight = $outbound->random();
                elseif ($inbound->isNotEmpty()) $flight = $inbound->random();
            }

            if (!$flight) continue;

            // B. Resolve Details
            $destInfo = $airportMap[$flight['destination']];
            $destCity = $destInfo['city']; 

            // C. Return Flight
            $returnFlight = $flights->first(function ($f) use ($flight) {
                return $f['origin'] === $flight['destination'] && $f['destination'] === $flight['origin'];
            });
            if (!$returnFlight) continue;

            // D. Aureliya Property
            $property = $props->first(function ($p) use ($destCity) {
                return strcasecmp($p['city'] ?? '', $destCity) === 0;
            });
            if (!$property && $type !== 'DOMESTIC') {
                $property = $props->first(function ($p) use ($destInfo) {
                    return strcasecmp($p['country'] ?? '', $destInfo['country']) === 0;
                });
            }
            if (!$property) continue;

            // E. SkyRoute Locations
            $srDestLocation = $locations->first(function ($l) use ($destCity) {
                return strcasecmp($l['city'] ?? '', $destCity) === 0;
            });
            if (!$srDestLocation) continue;

            $srPropLocation = $locations->first(function ($l) use ($property) {
                return strcasecmp($l['city'] ?? '', $property['city']) === 0;
            });
            if (!$srPropLocation) $srPropLocation = $srDestLocation; 

            // F. Find Vehicle
            $vehiclesInCity = collect([]);
            try {
                // Warning: This HTTP call inside a 1000x loop will take time
                $vResp = Http::get("{$srBase}/vehicles/city/" . urlencode($srDestLocation['city']));
                if ($vResp->successful()) {
                    $responseData = $vResp->json();
                    $candidates = $responseData['data'] ?? $responseData;
                    if (is_array($candidates) && isset($candidates[0]) && is_array($candidates[0])) {
                        $vehiclesInCity = collect($candidates);
                    }
                }
            } catch (\Exception $e) { }

            if ($vehiclesInCity->isEmpty()) continue; 
            
            $vehicle = $vehiclesInCity->random();

            // 7. Calculate Pricing
            $nights = rand(3, 7);
            $days = $nights + 1;
            
            $flightCost = ($flight['price'] ?? 5000) + ($returnFlight['price'] ?? 5000);
            $hotelCost = ($property['price_per_night'] ?? 2500) * $nights;

            $distKm = 20; 
            $destId = $this->getId($srDestLocation);
            $propId = $this->getId($srPropLocation);

            if (isset($srDestLocation['latitude']) && isset($srPropLocation['latitude'])) {
                if ($destId && $propId && $destId !== $propId) {
                    $distKm = $this->haversine(
                        (float)$srDestLocation['latitude'], (float)$this->getLongitude($srDestLocation),
                        (float)$srPropLocation['latitude'], (float)$this->getLongitude($srPropLocation)
                    );
                }
            }
            
            $vBase = (float)($vehicle['base_price'] ?? 100);
            $vRate = (float)($vehicle['fare_per_km'] ?? 15);
            $transferCost = ($vBase + ($distKm * $vRate)) * 2; 

            $totalBase = $flightCost + $hotelCost + $transferCost;
            $discount  = rand(5, 25) / 100;
            $finalPrice = round($totalBase * (1 - $discount), 2);

            // 8. Insert Data
            $description = sprintf(
                "%s (%s) Package. Includes %s flights (%s-%s) on %s. %d Nights at %s. Includes %s transfer via %s.",
                ucwords(strtolower(str_replace('_', ' ', $type))),
                $destCity,
                $type === 'DOMESTIC' ? 'domestic' : 'international',
                $flight['origin'], $flight['destination'],
                $flight['airline'] ?? 'Airline',
                $nights,
                $property['title'],
                $type === 'DOMESTIC' ? 'private' : 'airport',
                $vehicle['name']
            );

            $packageData = [
                '_id' => (string) Str::uuid(),
                'name' => "{$nights}D/{$days}N {$destCity} Getaway",
                'description' => $description,
                'package_type' => $type,

                'skyroute_origin_id'      => (string) $this->getId($srDestLocation),
                'skyroute_destination_id' => (string) $this->getId($srPropLocation),
                'skyroute_vehicle_id'     => (string) $this->getId($vehicle),
                'airline_flight_id'       => (string) $this->getId($flight),
                'airline_return_flight_id'=> (string) $this->getId($returnFlight),
                'aureliya_property_id'    => (string) $this->getId($property),

                'nights' => $nights,
                'base_price' => $totalBase,
                'discount_rate' => $discount,
                'final_price' => $finalPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $connection = (new Package())->getConnectionName();
            DB::connection($connection)->table('packages')->insert($packageData);

            $created++;

            // ==========================================
            // LOGIC TWEAK: Hide console spam
            // ==========================================
            // Commented out to hide detail logs:
            // $this->command->info("[$created/$targetCount] Created {$type} package to {$destCity}");
            
            // Show a progress dot every 50 items instead
            if ($created % 50 == 0) {
                $this->command->getOutput()->write('.');
            }
        }

        $this->command->info("\nSuccessfully seeded {$created} packages.");
    }
}