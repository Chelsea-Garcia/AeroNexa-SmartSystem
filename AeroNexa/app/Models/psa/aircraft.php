<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Aircraft extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'airports';

    protected $fillable = [
        'aircraftCode',
        'model',
        'manufacturer',
        'capacity',
        'range_km',
        'status',
        'yearOfManufacture',
    ];

    protected $casts = [
        'capacity' => 'array',
        'yearOfManufacture' => 'integer'
    ];
}
