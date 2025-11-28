<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // Table name is _bookings by Laravel convention.
    protected $connection = 'trutravel';
    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'package_id',
        'psa_booking_id',
        'skyroute_booking_id',
        'aureliya_booking_id',
        'transaction_code',
        'amount',
        'currency',
        'payment_status',
        'status',
        'metadata', // JSON field for breakdown or misc data
    ];

    protected $casts = [
        'amount'       => 'float',
        'metadata'     => 'array',
        'payment_status' => 'string',
        'status'       => 'string',
    ];

    // Relationships
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    // convenience accessor for breakdown (if you store it in metadata['breakdown'])
    public function getBreakdownAttribute()
    {
        return $this->metadata['breakdown'] ?? null;
    }
}
