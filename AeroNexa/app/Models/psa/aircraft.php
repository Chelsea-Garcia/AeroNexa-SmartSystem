<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Aircraft extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'aircrafts';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'aircraft_code',
        'model',
        'manufacturer',
        'capacity',
        'range_km',
        'status',
        'year_of_manufacture',
    ];

    public $timestamps = false;

    // Flights using this aircraft (Flight has aircraft_code)
    public function flights()
    {
        return $this->hasMany(Flight::class, 'aircraft_code', 'aircraft_code');
    }
}
