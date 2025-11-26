<?php

namespace App\Models\psa;

use Mongodb\Laravel\Eloquent\Model;

class Flight extends Model
{
    protected $connection = 'philippineskyairway';   // if you're using MongoDB
    protected $collection = 'flights';   // collection name

    protected $fillable = [
        'flight_number',
        'route_id',
        'origin',
        'destination',
        'departure_time',
        'arrival_time',
        'duration_min',
        'aircraft_code',
        'aircraft_model',
        'price',
        'status'
    ];
}
