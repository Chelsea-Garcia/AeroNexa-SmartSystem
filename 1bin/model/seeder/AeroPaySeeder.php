<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AeroPay\AeroPayTransaction;

class AeroPaySeeder extends Seeder
{
    public function run()
    {
        AeroPayTransaction::factory(50000)->create();
    }
}
