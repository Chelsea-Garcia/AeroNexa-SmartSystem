<?php

namespace App\Models\psa;

use MongoDB\Laravel\Eloquent\Model;

class Passenger extends Model
{
    protected $connection = 'philippineskyairway';
    protected $collection = 'passengers';

    protected $primaryKey = '_id';      // <-- important for find(), update(), delete()
    protected $keyType = 'string';      // MongoDB ObjectId is string
    public $incrementing = false;       // MongoDB does NOT auto-increment

    protected $fillable = [
        'user_id',             // Aeronexa user who owns this passenger profile
        'first_name',
        'last_name',
        'gender',
        'birthdate',
        'nationality',
        'passport_number',
        'passport_expiry',
        'special_assistance',  // wheelchair, medical assistance, etc.
        'contact_number',
        'emergency_contact_name',
        'emergency_contact_number'
    ];

    protected $casts = [
        'birthdate' => 'string',
        'passport_expiry' => 'string'
    ];
}
