<?php

namespace App\Models\SkyRoute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SkyRouteTransport extends Model
{
    use HasFactory;

    protected $connection = 'mongodb_skyroute';
    protected $collection = 'transports';

    protected $fillable = [
        'transport_id',
        'transport_type',
        'vehicle_number',
        'capacity',
        'route',
        'location',
        'status',
        'price',
    ];
}
