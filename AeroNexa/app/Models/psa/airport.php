<?php

namespace App\Models\psa;

use Mongodb\Laravel\Eloquent\Model;

class Airport extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'airports';

    protected $fillable = [
        'code',
        'name',
        'city',
        'country',
        'latitude',
        'longitude',
    ];
}
