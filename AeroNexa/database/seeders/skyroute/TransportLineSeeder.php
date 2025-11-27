<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Location;
use App\Models\skyroute\TransportLine;

class TransportLineSeeder extends Seeder
{
    /**
     * Countries/divisions where trains are realistic.
     * We enable train lines for these countries (for simplicity: all divisions in country).
     * You can refine to per-division if needed later.
     */
    protected $trainCountries = [
        'Japan',
        'South Korea',
        'China',
        'Singapore',
        'Malaysia',
        'Thailand',
        'India',
        'Taiwan',
        'United Kingdom',
        'France',
        'Germany',
        'Netherlands',
        'Spain',
        'Italy',
        'Switzerland',
        'Türkiye',
        'USA',
        'Canada',
        'Mexico',
        'Brazil',
        'Argentina',
        'Australia',
        'New Zealand',
        'Egypt',
        'Colombia'
    ];

    public function run(): void
    {
        // Remove existing if you want idempotent behaviour (optional)
        // TransportLine::truncate();

        $typesAlways = ['taxi', 'bus'];

        foreach (Location::all() as $loc) {

            // create taxi + bus always
            foreach ($typesAlways as $type) {
                TransportLine::create([
                    'location_id' => $loc->_id,
                    'type'        => $type,
                    'name'        => "{$loc->city} {$type} line",
                ]);
            }

            // create train line only for realistic countries
            if (in_array($loc->country, $this->trainCountries)) {
                TransportLine::create([
                    'location_id' => $loc->_id,
                    'type'        => 'train',
                    'name'        => "{$loc->city} train line",
                ]);
            }
        }
    }
}
