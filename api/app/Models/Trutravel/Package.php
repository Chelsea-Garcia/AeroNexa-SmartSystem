<?php

namespace App\Models\trutravel;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    // Table name is _packages by Laravel convention.
    protected $connection = 'trutravel';
    protected $table = 'packages';

    // Configure _id as the primary key (UUID)
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        '_id', // Add this to fillable
        'name',
        'package_type',
        'description',
        'skyroute_origin_id',
        'skyroute_destination_id',
        'skyroute_vehicle_id',
        'airline_flight_id',
        'airline_return_flight_id',
        'aureliya_property_id',
        'nights',
        'base_price',
        'discount_rate',
        'final_price',
    ];

    protected $casts = [
        'base_price'    => 'float',
        'discount_rate' => 'float',
        'final_price'   => 'float',
    ];

    // If you want final_price computed automatically when creating/updating packages:
    protected static function booted()
    {
        static::saving(function ($model) {
            // if discount_rate exists compute final price, otherwise leave final_price if provided
            if ($model->discount_rate !== null && $model->base_price !== null) {
                $dr = (float) $model->discount_rate;
                // support both fraction (0.15) or percentage (15)
                if ($dr > 1) $dr = $dr / 100;
                $model->final_price = round($model->base_price * (1 - $dr), 2);
            }
        });
    }

    // Relationship: a package may have many bookings
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'package_id');
    }
}
