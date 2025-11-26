<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AureliYa\AureliYaListing;

class AureliYaSeeder extends Seeder
{
    public function run()
    {
        AureliYaListing::factory(3000)->create();
    }
}
