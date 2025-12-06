<?php

namespace Database\Seeders\aureliya;

use Illuminate\Database\Seeder;
use App\Models\aureliya\Amenity;

class AmenitySeeder extends Seeder
{
    public function run()
    {
        $amenities = [
            'Wifi',
            'Free parking',
            'Pool',
            'Hot tub',
            'Kitchen',
            'Air conditioning',
            'Washer',
            'Dryer',
            'TV',
            'Gym',
            'Breakfast included',
            'Ocean view',
            'Pet-friendly',
            '24/7 Security',
        ];

        foreach ($amenities as $name) {
            Amenity::create([
                '_id' => uuid_create(UUID_TYPE_RANDOM),
                'name' => $name

            ]);
        }
    }
}
