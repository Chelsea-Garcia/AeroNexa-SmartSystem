<?php

namespace Database\Seeders\aureliya;

use Illuminate\Database\Seeder;
use App\Models\aureliya\Review;
use App\Models\aureliya\Property;

class ReviewSeeder extends Seeder
{
    public function run()
    {
        $properties = Property::all();

        foreach ($properties as $property) {
            // Each property gets 3–8 reviews
            Review::factory()->count(rand(3, 8))->create([
                'property_id' => $property->_id,
            ]);
        }
    }
}
