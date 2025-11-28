<?php

namespace Database\Seeders\psa;

use Illuminate\Database\Seeder;
use App\Models\psa\Aircraft;

class AircraftSeeder extends Seeder
{
    public function run(): void
    {
        $aircrafts = [
            ['code' => 'A320-PSA1',  'model' => 'Airbus A320',        'eco' => 168, 'biz' => 0,  'range' => 6100],
            ['code' => 'A320-PSA2',  'model' => 'Airbus A320',        'eco' => 168, 'biz' => 0,  'range' => 6100],
            ['code' => 'A320-PSA3',  'model' => 'Airbus A320',        'eco' => 168, 'biz' => 0,  'range' => 6100],
            ['code' => 'ATR72-PSA1', 'model' => 'ATR 72-600',         'eco' => 78,  'biz' => 0,  'range' => 1500],
            ['code' => 'ATR72-PSA2', 'model' => 'ATR 72-600',         'eco' => 78,  'biz' => 0,  'range' => 1500],
            ['code' => 'A321N-PSA1', 'model' => 'Airbus A321neo',     'eco' => 220, 'biz' => 0,  'range' => 7400],
            ['code' => 'A321N-PSA2', 'model' => 'Airbus A321neo',     'eco' => 220, 'biz' => 0,  'range' => 7400],
            ['code' => 'A330-PSA1',  'model' => 'Airbus A330-300',    'eco' => 350, 'biz' => 18, 'range' => 11300],
            ['code' => 'A330-PSA2',  'model' => 'Airbus A330-300',    'eco' => 350, 'biz' => 18, 'range' => 11300],
            ['code' => 'B777-PSA1',  'model' => 'Boeing 777-300ER',   'eco' => 316, 'biz' => 42, 'range' => 13650],
            ['code' => 'B777-PSA2',  'model' => 'Boeing 777-300ER',   'eco' => 316, 'biz' => 42, 'range' => 13650],
            ['code' => 'B777-PSA3',  'model' => 'Boeing 777-300ER',   'eco' => 316, 'biz' => 42, 'range' => 13650],
        ];

        foreach ($aircrafts as $a) {

            Aircraft::create([
                'aircraft_code' => $a['code'],
                'model'         => $a['model'],
                'manufacturer'  => str_contains($a['model'], 'Boeing') ? 'Boeing' : 'Airbus',
                'capacity'      => [
                    'economy'  => $a['eco'],
                    'business' => $a['biz'],
                    'total'    => $a['eco'] + $a['biz']
                ],
                'range_km'           => $a['range'],
                'status'             => 'active',
                'year_of_manufacture' => rand(2015, 2024),
            ]);
        }
    }
}
