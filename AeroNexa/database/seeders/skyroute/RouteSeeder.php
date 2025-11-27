<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Route;
use App\Models\skyroute\TransportLine;
use App\Models\skyroute\Location;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        // Route::truncate(); // optional if you want to clear

        // High route count: 10-20 per line where possible
        foreach (TransportLine::all() as $line) {

            $origin = $line->location;
            if (!$origin) continue;

            // candidates: same country & same division
            $candidates = Location::where('country', $origin->country)
                ->where('division', $origin->division)
                ->where('_id', '!=', $origin->_id)
                ->get();

            // For taxi we allow same-city route as well
            $allowSelf = $line->type === 'taxi';

            // If no other city, for taxi still create self-route and skip others
            if ($candidates->count() === 0 && !$allowSelf) {
                continue;
            }

            // target pool: if no candidates but taxi => use origin itself only
            $targetsPool = $candidates->count() ? $candidates : collect([$origin]);

            // decide how many unique destination picks to attempt
            $min = 10;
            $max = 20; // high density
            $attempts = rand($min, $max);

            $created = 0;
            for ($i = 0; $i < $attempts; $i++) {

                // pick a random destination (may be same as origin if taxi allowed)
                $dest = $targetsPool->random();

                // skip if same and not allowed
                if ($dest->_id == $origin->_id && !$allowSelf) continue;

                // avoid duplicate route for same line + pair
                $exists = Route::where('line_id', $line->_id)
                    ->where('origin_city', $origin->city)
                    ->where('destination_city', $dest->city)
                    ->exists();

                if ($exists) continue;

                // estimate minutes roughly: smaller for close divisions, random otherwise
                $estimated = rand(10, 180); // random realistic range

                Route::create([
                    'line_id' => $line->_id,
                    'type' => $line->type,
                    'origin_city' => $origin->city,
                    'destination_city' => $dest->city,
                    'estimated_minutes' => $estimated,
                ]);

                $created++;
            }

            // ensure at least a few routes exist for each line
            if ($created === 0) {
                // fallback: create 3 simple routes using pool or self
                $pool = $targetsPool;
                for ($j = 0; $j < 3; $j++) {
                    $dest = $pool->random();
                    if ($dest->_id == $origin->_id && !$allowSelf) {
                        // skip and pick another if possible
                        if ($pool->count() > 1) {
                            $dest = $pool->where('_id', '!=', $origin->_id)->random();
                        }
                    }
                    Route::create([
                        'line_id' => $line->_id,
                        'type' => $line->type,
                        'origin_city' => $origin->city,
                        'destination_city' => $dest->city,
                        'estimated_minutes' => rand(15, 120),
                    ]);
                }
            }
        }
    }
}
