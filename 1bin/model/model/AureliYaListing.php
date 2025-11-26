<?php

namespace App\Models\AureliYa;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AureliYaListing extends Model
{
    use HasFactory;

    protected $connection = 'aureliya';
    protected $table = 'accommodations';


    protected $fillable = [
        'accommodation_id',
        'provider_id',
        'title',
        'accommodation_type',
        'location',
        'price',
        'availability',
    ];
}
