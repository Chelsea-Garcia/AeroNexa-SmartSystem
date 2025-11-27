<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class Trip extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'trips';

    protected $fillable = [
        'route_id',
        'line_id',
        'trip_code',
        'operation',        // morning | evening
        'departure_time',
        'arrival_time',
        'fare',
    ];

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function line()
    {
        return $this->belongsTo(TransportLine::class, 'line_id');
    }
}
