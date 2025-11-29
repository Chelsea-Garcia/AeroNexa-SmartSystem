<?php

namespace App\Models\aureliya;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $connection = 'aureliya';
    protected $table = 'properties';
    protected $primaryKey = "_id";
    public $incrementing = false;
    protected $keyType = "string";

    protected $fillable = [
        '_id',
        'title',
        'description',
        'country',
        'division',
        'city',
        'address',
        'type',
        'price_per_night',
        'max_guests',
        'address',
        'photos',
    ];

    public function amenities()
    {
        return $this->belongsToMany(
            Amenity::class,
            'property_amenities',   // pivot table
            'property_id',
            'amenity_id'
        );
    }
}
