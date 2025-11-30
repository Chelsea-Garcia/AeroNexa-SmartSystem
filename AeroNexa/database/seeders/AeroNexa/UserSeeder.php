<?php


namespace Database\Seeders\aeronexa;


use Illuminate\Database\Seeder;
use App\Models\aeronexa\User;


class UserSeeder extends Seeder
{
    public function run()
    {
        // create 100 users
        User::factory()->count(800)->create();
    }
}
