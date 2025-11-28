<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Passenger extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'passengers';

    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'birthdate',
        'nationality',
        'passport_number',
        'passport_expiry',
        'special_assistance',
        'contact_number',
        'emergency_contact_name',
        'emergency_contact_number'
    ];

    protected $casts = [
        'birthdate' => 'string',
        'passport_expiry' => 'string'
    ];

    // Passenger can have many bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'passenger_id', '_id');
    }
}
