<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Route extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'routes';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'origin',
        'destination',
        'type',
        'distance_km',
        'duration',
        'price',
        'frequency'
    ];

    public $timestamps = false;

    // Airports
    public function originAirport()
    {
        return $this->belongsTo(Airport::class, 'origin', 'code');
    }

    public function destinationAirport()
    {
        return $this->belongsTo(Airport::class, 'destination', 'code');
    }

    // Flights under this route
    public function flights()
    {
        return $this->hasMany(Flight::class, 'route_id', '_id');
    }
}
