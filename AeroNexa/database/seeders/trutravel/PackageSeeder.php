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
     * Haversine distance (km)
     */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) ** 2;

        return $earth * (2 * asin(min(1, sqrt($a))));
    }

    /**
     * Safely read longitude key
     */
    private function getLongitude(array $loc)
    {
        return $loc['longitude'] ?? $loc['longtitude'] ?? $loc['long'] ?? 0;
    }

    public function run()
    {
        $count = 1000;

        $psaBase  = 'http://localhost:8000/api/psa';
        $aureBase = 'http://localhost:8002/api/aureliya';
        $srBase   = 'http://localhost:8003/api/skyroute';

        // Fetch API data
        $airportsResp  = Http::get("{$psaBase}/airports");
        $locationsResp = Http::get("{$srBase}/locations");
        $flightsResp   = Http::get("{$psaBase}/flights");
        $propsResp     = Http::get("{$aureBase}/properties");

        if (
            !$airportsResp->successful() || !$locationsResp->successful() ||
            !$flightsResp->successful() || !$propsResp->successful()
        ) {
            $this->command->error('Unable to fetch required API data. Make sure services are running.');
            return;
        }

        $airports  = collect($airportsResp->json());
        $locations = collect($locationsResp->json());
        $flights   = collect($flightsResp->json());
        $props     = collect($propsResp->json());

        if ($airports->isEmpty() || $locations->isEmpty() || $flights->isEmpty() || $props->isEmpty()) {
            $this->command->error('APIs returned empty datasets.');
            return;
        }

        // Create airport code to city mapping
        $airportMap = $airports->keyBy('code')->map(function ($airport) {
            return [
                'city' => $airport['city'],
                'country' => $airport['country'],
                '_id' => $airport['_id'] ?? $airport['id']
            ];
        });

        $this->command->info("Loaded " . $airports->count() . " airports, " .
            $locations->count() . " locations, " .
            $flights->count() . " flights, " .
            $props->count() . " properties.");

        $created = 0;

        for ($i = 0; $i < $count; $i++) {
            // Step 1: Pick a random outbound flight
            $outboundFlight = $flights->random();

            $originCode = $outboundFlight['origin'] ?? null;
            $destCode = $outboundFlight['destination'] ?? null;

            if (!isset($airportMap[$originCode]) || !isset($airportMap[$destCode])) {
                continue;
            }

            $originAirport = $airportMap[$originCode];
            $destAirport = $airportMap[$destCode];

            $flightOriginCity = $originAirport['city'];
            $flightDestCity = $destAirport['city'];
            $flightOriginCountry = $originAirport['country'];
            $flightDestCountry = $destAirport['country'];

            // Find return flight (reverse route)
            $returnFlight = $flights->first(function ($f) use ($destCode, $originCode) {
                return ($f['origin'] === $destCode && $f['destination'] === $originCode);
            });

            if (!$returnFlight) {
                continue; // Skip if no return flight available
            }

            // Step 2: Find locations
            $originLocation = $locations->first(function ($l) use ($flightOriginCity) {
                return !empty($l['city']) && strcasecmp($l['city'], $flightOriginCity) === 0;
            });

            $destLocation = $locations->first(function ($l) use ($flightDestCity) {
                return !empty($l['city']) && strcasecmp($l['city'], $flightDestCity) === 0;
            });

            if (!$originLocation || !$destLocation) {
                continue;
            }

            // Step 3: Find property (try destination city first, then nearby)
            $destLat = (float) ($destLocation['latitude'] ?? 0);
            $destLon = (float) $this->getLongitude($destLocation);

            $propertiesInDestCity = $props->filter(function ($p) use ($flightDestCity) {
                return !empty($p['city']) && strcasecmp($p['city'], $flightDestCity) === 0;
            });

            $skyroute2_needed = false;
            $propertyLocation = null;

            if ($propertiesInDestCity->isNotEmpty()) {
                $property = $propertiesInDestCity->random();
                $propertyCity = $flightDestCity;
                $propertyLocation = $destLocation;
            } else {
                // Find nearby city
                $nearbyCities = $locations->filter(function ($l) use ($flightDestCity, $destLat, $destLon) {
                    if (strcasecmp($l['city'], $flightDestCity) === 0) return false;
                    $lat = (float) ($l['latitude'] ?? 0);
                    $lon = (float) $this->getLongitude($l);
                    return $this->haversine($destLat, $destLon, $lat, $lon) <= 300;
                })->values();

                if ($nearbyCities->isEmpty()) {
                    $nearbyCities = $locations->where('city', '!=', $flightDestCity)->values();
                }

                $property = null;
                foreach ($nearbyCities->shuffle()->take(10) as $nearbyLoc) {
                    $propInCity = $props->first(function ($p) use ($nearbyLoc) {
                        return !empty($p['city']) && strcasecmp($p['city'], $nearbyLoc['city']) === 0;
                    });
                    if ($propInCity) {
                        $property = $propInCity;
                        $propertyCity = $nearbyLoc['city'];
                        $propertyLocation = $nearbyLoc;
                        $skyroute2_needed = true;
                        break;
                    }
                }

                if (!$property) continue;
            }

            // Step 4: Calculate costs
            $nights = rand(2, 5);

            // Outbound SkyRoute 1: Origin city local transfer
            $vehiclesOriginResp = Http::get("{$srBase}/vehicles/city/" . urlencode($flightOriginCity));
            $vehicle1 = null;
            $skyroute1_cost = 150.0;

            if ($vehiclesOriginResp->successful()) {
                $vehCol = collect($vehiclesOriginResp->json());
                if ($vehCol->isNotEmpty()) {
                    $vehicle1 = $vehCol->random();
                    $fare = (float)($vehicle1['fare_per_km'] ?? 12.0);
                    $skyroute1_cost = round($fare * rand(5, 15), 2);
                }
            }

            // Outbound Flight
            $outboundFlightPrice = (float)($outboundFlight['price'] ?? 5000.0);

            // Outbound SkyRoute 2: Dest city to property city (if needed)
            $skyroute2_cost = 0.0;
            $vehicle2 = null;

            if ($skyroute2_needed && $propertyLocation) {
                $propertyLat = (float) ($propertyLocation['latitude'] ?? 0);
                $propertyLon = (float) $this->getLongitude($propertyLocation);
                $distKm = $this->haversine($destLat, $destLon, $propertyLat, $propertyLon);

                $vehiclesDestResp = Http::get("{$srBase}/vehicles/city/" . urlencode($flightDestCity));
                if ($vehiclesDestResp->successful()) {
                    $vehCol2 = collect($vehiclesDestResp->json());
                    if ($vehCol2->isNotEmpty()) {
                        $vehicle2 = $vehCol2->random();
                        $fare2 = (float)($vehicle2['fare_per_km'] ?? 12.0);
                        $skyroute2_cost = round(max(200, $distKm * $fare2), 2);
                    }
                }
            }

            // Aureliya accommodation
            $price_per_night = (float)($property['price_per_night'] ?? 2000.0);
            $aureliya_cost = round($price_per_night * $nights, 2);

            // Return SkyRoute 3: Property city back to dest city (if needed)
            $skyroute3_cost = $skyroute2_cost; // Same distance, same vehicle type
            $vehicle3 = $vehicle2;

            // Return Flight
            $returnFlightPrice = (float)($returnFlight['price'] ?? 5000.0);

            // Return SkyRoute 4: Dest city to origin (local transfer)
            $skyroute4_cost = $skyroute1_cost; // Same as outbound local transfer
            $vehicle4 = $vehicle1;

            // Total pricing
            $basePrice = round(
                $skyroute1_cost + $outboundFlightPrice + $skyroute2_cost +
                    $aureliya_cost +
                    $skyroute3_cost + $returnFlightPrice + $skyroute4_cost,
                2
            );

            $discountRate = rand(10, 50) / 100;
            $finalPrice = round($basePrice * (1 - $discountRate), 2);

            // Package type
            $packageType = strcasecmp($flightOriginCountry, $flightDestCountry) !== 0
                ? 'INTERNATIONAL'
                : 'DOMESTIC';

            // Build description
            $description = sprintf(
                "OUTBOUND: Flight %s from %s (%s) to %s (%s)%s. " .
                    "ACCOMMODATION: %d nights at %s in %s. " .
                    "RETURN: %sSkyRoute transfer to %s (%s), then flight %s back to %s (%s).",
                $outboundFlight['flight_number'] ?? 'N/A',
                $flightOriginCity,
                $originCode,
                $flightDestCity,
                $destCode,
                $skyroute2_needed ? ", SkyRoute transfer to {$propertyCity}" : "",
                $nights,
                $property['title'] ?? 'property',
                $propertyCity,
                $skyroute2_needed ? "SkyRoute from {$propertyCity} to {$flightDestCity}, " : "",
                $flightDestCity,
                $destCode,
                $returnFlight['flight_number'] ?? 'N/A',
                $flightOriginCity,
                $originCode
            );

            // Insert using DB to bypass auto-increment
            // Get the connection name from Package model
            $connection = (new Package())->getConnectionName();

            $packageData = [
                '_id' => (string) Str::uuid(),
                'name' => sprintf("%s to %s Package", strtoupper($flightOriginCity), strtoupper($propertyCity)),
                'description' => $description,
                'package_type' => $packageType,
                'skyroute_origin_id' => (string)($originLocation['id'] ?? $originLocation['_id'] ?? Str::uuid()),
                'skyroute_destination_id' => (string)($propertyLocation['id'] ?? $propertyLocation['_id'] ?? Str::uuid()),
                'skyroute_vehicle_id' => $vehicle2 ? (string)($vehicle2['_id'] ?? $vehicle2['id'] ?? null) : ($vehicle1 ? (string)($vehicle1['_id'] ?? $vehicle1['id'] ?? null) : null),
                'airline_flight_id' => (string)($outboundFlight['id'] ?? Str::uuid()),
                'airline_return_flight_id' => (string)($returnFlight['id'] ?? Str::uuid()),
                'aureliya_property_id' => (string)($property['_id'] ?? Str::uuid()),
                'nights' => $nights,
                'base_price' => $basePrice,
                'discount_rate' => $discountRate,
                'final_price' => $finalPrice,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Don't specify 'id' - let it auto-increment
            DB::connection($connection)->table('packages')->insert($packageData);

            $created++;
        }

        $this->command->info("TruTravelPackageSeeder: created {$created} valid packages with round trips.");
    }
}
