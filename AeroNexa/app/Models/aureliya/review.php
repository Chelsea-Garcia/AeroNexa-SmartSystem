<?php

namespace App\Models\aureliya;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $connection = 'aureliya';
    protected $table = 'reviews';
    protected $primaryKey = "_id";
    public $incrementing = false;
    protected $keyType = "string";

    protected $fillable = [
        '_id',
        'property_id',
        'user_id',
        'rating',
        'comment',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }
}
