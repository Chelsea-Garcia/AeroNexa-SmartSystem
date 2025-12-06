<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Airport extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'airports';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'city',
        'country',
        'latitude',
        'longitude'
    ];

    public $timestamps = false;

    // Routes where this airport is origin
    public function originRoutes()
    {
        return $this->hasMany(Route::class, 'origin', 'code');
    }

    // Routes where this airport is destination
    public function destinationRoutes()
    {
        return $this->hasMany(Route::class, 'destination', 'code');
    }

    // Flights departing from this airport (Flight has origin)
    public function departingFlights()
    {
        return $this->hasMany(Flight::class, 'origin', 'code');
    }

    // Flights arriving at this airport (Flight has destination)
    public function arrivingFlights()
    {
        return $this->hasMany(Flight::class, 'destination', 'code');
    }
}
