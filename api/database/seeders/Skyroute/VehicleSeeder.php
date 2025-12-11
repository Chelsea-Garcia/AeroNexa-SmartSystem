<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Vehicle;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch all existing vehicles
        $vehicles = Vehicle::all();

        foreach ($vehicles as $vehicle) {
            // Define pricing based on vehicle type
            $pricing = match ($vehicle->type) {
                'Bus' => ['base' => 50, 'per_km' => 15],
                'SUV' => ['base' => 200, 'per_km' => 25],
                default => ['base' => 100, 'per_km' => 12], // Car/Default
            };

            // Update the vehicle with new pricing fields
            // This preserves existing data like name, plate_number, location_id
            $vehicle->update([
                'base_price' => $pricing['base'],
                'fare_per_km' => $pricing['per_km']
            ]);
        }

        $this->command->info('Updated ' . $vehicles->count() . ' vehicles with base price and fare per km.');
    }
}