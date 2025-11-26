<?php

namespace App\Models\PhilippineSkyAirway;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhilippineSkyAirwayFlight extends Model
{
    use HasFactory;

    protected $connection = 'mongodb_psa';
    protected $collection = 'flights';

    protected $fillable = [
        'flight_id',
        'origin',
        'destination',
        'seats',
        'price',
        'departure_time',
    ];
}
