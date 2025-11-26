<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PhilippineSkyAirway\PhilippineSkyAirwayFlight;

class PSASeeder extends Seeder
{
    public function run()
    {
        PhilippineSkyAirwayFlight::factory(5000)->create();
    }
}
