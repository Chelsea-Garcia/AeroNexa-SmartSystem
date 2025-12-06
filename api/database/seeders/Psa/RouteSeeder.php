<?php

namespace Database\Seeders\psa;

use Illuminate\Database\Seeder;
use App\Models\psa\Route;
use App\Models\psa\Airport;

class RouteSeeder extends Seeder
{
    public function run()
    {
        $airports = Airport::all();

        $philippines = $airports->where('country', 'Philippines');
        $international = $airports->where('country', '!=', 'Philippines');

        $routes = [];

        foreach ($philippines as $origin) {

            // ---------------------------
            // 1. PH → PH (Domestic)
            // ---------------------------
            foreach ($philippines as $destination) {
                if ($origin->code === $destination->code) continue;

                $distance = $this->calculateDistance(
                    $origin->latitude,
                    $origin->longitude,
                    $destination->latitude,
                    $destination->longitude
                );

                $routes[] = [
                    'origin'        => $origin->code,
                    'destination'   => $destination->code,
                    'type'          => 'domestic',
                    'distance_km'   => round($distance),
                    'duration'      => $this->estimateDomesticDuration($distance),
                    'price'     => $this->estimateDomesticPrice($distance),
                    'frequency'     => $this->getFrequency($distance),
                ];
            }

            // ---------------------------
            // 2. PH → INT
            // ---------------------------
            foreach ($international as $destination) {

                $distance = $this->calculateDistance(
                    $origin->latitude,
                    $origin->longitude,
                    $destination->latitude,
                    $destination->longitude
                );

                $countryDuration = $this->estimateDurationByCountry($destination->country);
                $countryPrice = $this->estimatePriceByCountry($destination->country);

                // OUTBOUND
                $routes[] = [
                    'origin'        => $origin->code,
                    'destination'   => $destination->code,
                    'type'          => 'international',
                    'distance_km'   => round($distance),
                    'duration'      => $countryDuration,
                    'price'     => $countryPrice,
                    'frequency'     => $this->getFrequency($distance),
                ];

                // RETURN
                $routes[] = [
                    'origin'        => $destination->code,
                    'destination'   => $origin->code,
                    'type'          => 'international',
                    'distance_km'   => round($distance),
                    'duration'      => $countryDuration,
                    'price'     => $countryPrice,
                    'frequency'     => $this->getFrequency($distance),
                ];
            }
        }

        Route::insert($routes);
    }

    // ---------------------------
    // Realistic Frequency Logic
    // ---------------------------
    private function getFrequency($distance)
    {
        if ($distance <= 800) {
            // Short-haul / Domestic
            return 3;
        }

        if ($distance <= 4000) {
            // Regional Asia
            return 2;
        }

        // Long-haul (Europe, NA, ME, Oceania)
        return 1;
    }


    // ---------------------------
    // Distance Calculation (Haversine)
    // ---------------------------
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) ** 2;

        return $earthRadius * (2 * asin(sqrt($a)));
    }

    // ---------------------------
    // Domestic Using Distance
    // ---------------------------
    private function estimateDomesticDuration($distance)
    {
        return round(($distance / 700) * 60);
    }

    private function estimateDomesticPrice($distance)
    {
        return max(1500, $distance * 3.5);
    }

    // ---------------------------
    // International (Country Based)
    // ---------------------------
    private function estimateDurationByCountry($country)
    {
        return match ($country) {
            'Japan', 'South Korea', 'China', 'Hong Kong', 'Taiwan', 'Singapore',
            'Malaysia', 'Thailand', 'Vietnam' => rand(180, 360),

            'Qatar', 'UAE' => rand(480, 600),

            'United Kingdom', 'France', 'Germany', 'Netherlands', 'Spain',
            'Italy', 'Switzerland', 'Türkiye' => rand(840, 1020),

            'USA', 'Canada', 'Mexico' => rand(720, 1080),

            'Brazil', 'Argentina', 'Chile', 'Peru', 'Colombia' => rand(1200, 1800),

            'South Africa', 'Egypt', 'Morocco', 'Ethiopia', 'Kenya', 'Nigeria' => rand(720, 1200),

            'Australia', 'New Zealand' => rand(420, 600),

            default => rand(300, 600),
        };
    }

    private function estimatePriceByCountry($country)
    {
        return match ($country) {
            'Japan', 'South Korea', 'China', 'Hong Hong', 'Taiwan', 'Singapore',
            'Malaysia', 'Thailand', 'Vietnam' => rand(8000, 20000),

            'Qatar', 'UAE' => rand(18000, 30000),

            'United Kingdom', 'France', 'Germany', 'Netherlands', 'Spain',
            'Italy', 'Switzerland', 'Türkiye' => rand(25000, 60000),

            'USA', 'Canada', 'Mexico' => rand(30000, 70000),

            'Brazil', 'Argentina', 'Chile', 'Peru', 'Colombia' => rand(35000, 80000),

            'South Africa', 'Egypt', 'Morocco', 'Ethiopia', 'Kenya', 'Nigeria' => rand(20000, 50000),

            'Australia', 'New Zealand' => rand(12000, 25000),

            default => rand(10000, 30000),
        };
    }
}
