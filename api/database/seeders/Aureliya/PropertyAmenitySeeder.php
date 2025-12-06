<?php

namespace Database\Seeders\aureliya;

use Illuminate\Database\Seeder;
use App\Models\aureliya\Property;
use App\Models\aureliya\Amenity;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PropertyAmenitySeeder extends Seeder
{
    public function run()
    {
        $properties = Property::all();
        $amenities = Amenity::all()->pluck('_id')->toArray();

        foreach ($properties as $prop) {

            $randomAmenityIds = collect($amenities)
                ->shuffle()
                ->take(rand(3, 7)); // assign 3–7 amenities

            foreach ($randomAmenityIds as $amenityId) {
                DB::connection('aureliya')->table('property_amenities')->insert([
                    'property_id' => $prop->_id,
                    'amenity_id' => $amenityId,
                ]);
            }
        }
    }
}
