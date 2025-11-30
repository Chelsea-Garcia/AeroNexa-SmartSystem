<?php


namespace Database\Factories\aeronexa;


use App\Models\AeroNexa\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class UserFactory extends Factory
{
    protected $model = User::class;


    public function definition()
    {
        $password = Hash::make('password'); // simple default password for seed users


        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'password' => $password,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
