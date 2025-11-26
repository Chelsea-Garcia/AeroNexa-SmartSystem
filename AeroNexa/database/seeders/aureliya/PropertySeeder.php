<?php

namespace Database\Seeders\aureliya;

use Illuminate\Database\Seeder;
use App\Models\aureliya\Property;

class PropertySeeder extends Seeder
{
    public function run()
    {
        // Generate 30 properties
        Property::factory()->count(5000)->create();
    }
}
