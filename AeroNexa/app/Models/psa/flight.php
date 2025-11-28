<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Flight extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'flights';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

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

    public $timestamps = false;

    // Flight belongs to a route
    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id', '_id');
    }

    // Airports
    public function originAirport()
    {
        return $this->belongsTo(Airport::class, 'origin', 'code');
    }

    public function destinationAirport()
    {
        return $this->belongsTo(Airport::class, 'destination', 'code');
    }

    // Aircraft
    public function aircraft()
    {
        return $this->belongsTo(Aircraft::class, 'aircraft_code', 'aircraft_code');
    }

    // Bookings of this flight
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'flight_id', '_id');
    }
}
