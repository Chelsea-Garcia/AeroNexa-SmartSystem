<?php

namespace App\Models\AeroPay;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AeroPayTransaction extends Model
{
    use HasFactory;

    protected $connection = 'aeropay';

    protected $table = 'transactions';


    protected $fillable = [
        'transaction_id',
        'user_id',
        'amount',
        'status',
    ];
}
