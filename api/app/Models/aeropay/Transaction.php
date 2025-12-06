<?php

namespace App\Models\aeropay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $connection = "aeropay";
    protected $table = "transactions";
    protected $primaryKey = "_id";
    public $incrementing = false;
    protected $keyType = "string";

    protected $fillable = [
        '_id',
        'transaction_code',
        'user_id',
        'partner',
        'partner_reference_id',
        'amount',
        'currency',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->_id) {
                $model->_id = Str::uuid();
            }

            if (!$model->transaction_code) {
                $model->transaction_code = 'APAY-' . strtoupper(Str::random(8));
            }
        });
    }
}
