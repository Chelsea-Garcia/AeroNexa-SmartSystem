<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class Location extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'locations';

    protected $fillable = [
        'country',
        'division',
        'city',
        'latitude',
        'longitude',
    ];
}
