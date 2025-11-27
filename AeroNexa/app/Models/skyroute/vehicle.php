<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class Vehicle extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'vehicles';

    protected $fillable = [
        'location_id',
        'type',          // SUV / Car / Bus
        'name',
        'plate_number',
        'fare_per_km',   // NEW
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
