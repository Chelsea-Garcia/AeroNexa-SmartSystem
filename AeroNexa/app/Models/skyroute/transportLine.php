<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class TransportLine extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'transport_lines';

    protected $fillable = [
        'location_id',    // link to city
        'type',           // taxi | bus | train
        'name',           // "Tokyo Taxi Line"
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function routes()
    {
        return $this->hasMany(Route::class, 'line_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class, 'line_id');
    }
}
