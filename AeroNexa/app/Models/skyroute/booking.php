<?php

namespace App\Models\skyroute;

use MongoDB\Laravel\Eloquent\Model;

class Booking extends Model
{
    protected $connection = 'skyroute';
    protected $collection = 'bookings';

    protected $fillable = [
        'user_id',
        'trip_id',
        'travel_date',
        'payment_method',
        'total_amount',
        'transaction_code',
        'payment_status',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }
}
