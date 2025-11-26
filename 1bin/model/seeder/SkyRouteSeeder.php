<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SkyRoute\SkyRouteTransport;

class SkyRouteSeeder extends Seeder
{
    public function run()
    {
        SkyRouteTransport::factory(2000)->create();
    }
}
