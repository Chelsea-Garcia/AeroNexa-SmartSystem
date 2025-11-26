<?php

namespace App\Models\psa;

use Mongodb\Laravel\Eloquent\Model;

class Route extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'routes';

    protected $fillable = [
        'origin',
        'destination',
        'type',
        'distance_km',
        'duration',
        'basePrice',
        'frequency'
    ];

    public $timestamps = false;

    // Relationship to airports
    public function originAirport()
    {
        return $this->belongsTo(Airport::class, 'origin', 'code');
    }

    public function destinationAirport()
    {
        return $this->belongsTo(Airport::class, 'destination', 'code');
    }
}
