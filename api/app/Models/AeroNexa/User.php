<?php


namespace App\Models\AeroNexa;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;


    protected $connection = 'aeronexa'; // use default DB connection; override if you have a separate DB


    protected $table = 'users';


    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
    ];


    protected $hidden = [
        'password',
    ];


    // If you want casts
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
