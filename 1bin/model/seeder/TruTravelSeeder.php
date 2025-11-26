<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TruTravel\TruTravelPackage;
use App\Models\PhilippineSkyAirway\PhilippineSkyAirwayFlight;
use App\Models\AureliYa\AureliYaListing;
use App\Models\SkyRoute\SkyRouteTransport;
use Faker\Factory as Faker;

class TruTravelSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $flights = PhilippineSkyAirwayFlight::all();
        $stays = AureliYaListing::all();
        $transports = SkyRouteTransport::all();

        for ($i = 0; $i < 2000; $i++) {

            $flight = $flights->random();
            $loc = $flight->destination;

            $stay = $stays->where('location', $loc)->random();
            $transport = $transports->where('location', $loc)->random();

            $basePrice = $flight->price + $stay->price + $transport->price;
            $discount = $basePrice * $faker->randomFloat(2, 0.10, 0.30);
            $finalPrice = $basePrice - $discount;

            TruTravelPackage::create([
                'name' => 'Travel Package to '.$loc,
                'description' => 'Flight + Stay + Transport Discounted Bundle',
                'location' => $loc,
                'flight_id' => $flight->flight_id,
                'accommodation_id' => $stay->accommodation_id,
                'transport_id' => $transport->transport_id,
                'price' => round($finalPrice, 2),
            ]);
        }
    }
}
