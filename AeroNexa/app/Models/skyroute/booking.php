<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class Booking extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'bookings';

    protected $fillable = [
        'user_id',
        'origin_location_id',
        'destination_location_id',
        'vehicle_id',
        'date',
        'time',
        'passenger_name',
        'payment_method',
        'payment_status',
        'estimated_amount',
        'transaction_code',
    ];

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
