<?php

namespace App\Models\aureliya;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $connection = 'aureliya';
    protected $table = 'bookings';
    protected $primaryKey = "_id";
    public $incrementing = false;
    protected $keyType = "string";

    protected $fillable = [
        '_id',
        'user_id',
        'property_id',
        'check_in',
        'check_out',
        'total_price',
        'payment_method',
        'payment_status',
        'transaction_code',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
