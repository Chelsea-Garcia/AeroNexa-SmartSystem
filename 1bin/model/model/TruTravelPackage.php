<?php

namespace App\Models\TruTravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TruTravelPackage extends Model
{
    use HasFactory;

    protected $connection = 'trutravel';
    protected $table = 'packages';


    protected $fillable = [
        'name',
        'description',
        'location',
        'flight_id',
        'accommodation_id',
        'transport_id',
        'price',
    ];
}
