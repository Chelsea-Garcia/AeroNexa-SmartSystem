<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class Route extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'routes';

    protected $fillable = [
        'line_id',
        'type',                // taxi | bus | train
        'origin_city',
        'destination_city',
        'estimated_minutes',
    ];

    public function line()
    {
        return $this->belongsTo(TransportLine::class, 'line_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'route_id');
    }
}
