<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Booking extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'bookings';

    protected $fillable = [
        'user_id',
        'passenger_id',
        'flight_id',
        'flight_date',
        'departure_time',
        'arrival_time',
        'total_amount',
        'payment_method',
        'transaction_code',
        'payment_status',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    // Passenger of the booking
    public function passenger()
    {
        return $this->belongsTo(Passenger::class, 'passenger_id', '_id');
    }

    // Flight of the booking
    public function flight()
    {
        return $this->belongsTo(Flight::class, 'flight_id', '_id');
    }
}
