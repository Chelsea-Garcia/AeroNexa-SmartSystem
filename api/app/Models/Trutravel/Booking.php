<?php

namespace App\Models\trutravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $connection = 'trutravel';
    protected $table = 'bookings';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        '_id',
        'user_id',
        'package_id',
        'travel_date',
        'return_date',
        'transaction_code',
        'amount',
        'currency',
        'payment_status',
        'status',
        'payment_breakdown',
        'metadata',
    ];

    protected $casts = [
        'travel_date' => 'date',
        'return_date' => 'date',
        'amount' => 'decimal:2',
        'payment_breakdown' => 'array',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', '_id');
    }
}
