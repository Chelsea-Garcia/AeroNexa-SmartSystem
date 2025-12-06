<?php

namespace Database\Seeders\psa;

use Illuminate\Database\Seeder;
use App\Models\psa\Passenger;

class PassengerSeeder extends Seeder
{
    public function run()
    {
        Passenger::truncate();

        $passengers = [
            [
                'user_id'                  => 'AEX-00000001', // Aeronexa account
                'first_name'               => 'John',
                'last_name'                => 'Doe',
                'gender'                   => 'Male',
                'birthdate'                => '2000-05-12',
                'nationality'              => 'Filipino',
                'passport_number'          => 'P1234567A',
                'passport_expiry'          => '2032-10-05',
                'special_assistance'       => null,
                'contact_number'           => '+639171234567',
                'emergency_contact_name'   => 'Jane Doe',
                'emergency_contact_number' => '+639181234567',
            ],

            [
                'user_id'                  => 'AEX-00000001', // Same user, second passenger
                'first_name'               => 'Marie',
                'last_name'                => 'Doe',
                'gender'                   => 'Female',
                'birthdate'                => '2002-03-23',
                'nationality'              => 'Filipino',
                'passport_number'          => 'P7654321B',
                'passport_expiry'          => '2031-01-20',
                'special_assistance'       => 'Wheelchair Assistance',
                'contact_number'           => '+639191234888',
                'emergency_contact_name'   => 'John Doe',
                'emergency_contact_number' => '+639171234567',
            ]
        ];

        Passenger::insert($passengers);

        $this->command->info('Passenger seeding completed (Option 2 – Professional Airline Model).');
    }
}
