<?php

namespace Database\Factories\aureliya;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $countryCities = [

        // ============================
        // Philippines
        // ============================
        'Philippines' => [
            'Manila',
            'Cebu',
            'Angeles',
            'Davao',
            'Zamboanga',
            'Iloilo',
            'Palawan',
            'Kalibo',
            'Bacolod',
            'Tuguegarao',
            // extra cities
            'Baguio',
            'Tagaytay',
            'Quezon City',
            'Pasig'
        ],

        // Japan
        'Japan' => [
            'Tokyo',
            'Osaka',
            // extra
            'Kyoto',
            'Nagoya',
            'Sapporo'
        ],

        // South Korea
        'South Korea' => [
            'Incheon',
            'Seoul',
            // extra
            'Busan',
            'Daegu'
        ],

        // China
        'China' => [
            'Beijing',
            'Shanghai',
            'Guangzhou',
            'Hong Kong',
            // extra
            'Shenzhen',
            'Chengdu'
        ],

        'Singapore' => ['Singapore'],

        'Thailand' => [
            'Bangkok',
            // extra
            'Chiang Mai',
            'Pattaya'
        ],

        'Malaysia' => [
            'Kuala Lumpur',
            // extra
            'Penang',
            'Johor Bahru'
        ],

        'India' => [
            'Delhi',
            'Mumbai',
            // extra
            'Bangalore',
            'Hyderabad'
        ],

        'Taiwan' => ['Taipei', 'Taichung'],
        'Qatar' => ['Doha'],
        'UAE' => ['Dubai', 'Abu Dhabi'],

        // ============================
        // Europe
        // ============================
        'United Kingdom' => [
            'London',
            // extra
            'Manchester',
            'Liverpool'
        ],

        'France' => ['Paris', 'Lyon'],
        'Germany' => ['Frankfurt', 'Berlin'],
        'Netherlands' => ['Amsterdam', 'Rotterdam'],
        'Spain' => ['Madrid', 'Barcelona', 'Valencia'],
        'Italy' => ['Rome', 'Milan'],
        'Switzerland' => ['Zurich', 'Geneva'],
        'Türkiye' => ['Istanbul', 'Ankara'],

        // ============================
        // North America
        // ============================
        'USA' => [
            'Los Angeles',
            'San Francisco',
            'Seattle',
            'New York',
            'Chicago',
            // extra
            'Las Vegas',
            'Miami'
        ],

        'Canada' => [
            'Vancouver',
            'Toronto',
            // extra
            'Montreal',
            'Calgary'
        ],

        'Mexico' => [
            'Mexico City',
            // extra
            'Guadalajara',
            'Monterrey'
        ],

        // ============================
        // Africa
        // ============================
        'South Africa' => [
            'Johannesburg',
            'Cape Town',
            // extra
            'Durban'
        ],

        'Egypt' => ['Cairo', 'Giza'],
        'Morocco' => ['Casablanca', 'Marrakesh'],
        'Ethiopia' => ['Addis Ababa'],
        'Kenya' => ['Nairobi', 'Mombasa'],
        'Nigeria' => ['Lagos', 'Abuja'],

        // ============================
        // South America
        // ============================
        'Brazil' => ['São Paulo', 'Rio de Janeiro', 'Brasilia'],
        'Argentina' => ['Buenos Aires', 'Cordoba'],
        'Chile' => ['Santiago'],
        'Peru' => ['Lima'],
        'Colombia' => ['Bogotá', 'Medellín'],

        // ============================
        // Australia / Oceania
        // ============================
        'Australia' => ['Sydney', 'Melbourne', 'Brisbane', 'Perth'],
        'New Zealand' => ['Auckland', 'Wellington'],
    ];

    public function definition()
    {
        $country = $this->faker->randomElement(array_keys($this->countryCities));
        $city = $this->faker->randomElement($this->countryCities[$country]);
        $type = $this->faker->randomElement(['Apartment', 'House', 'Hotel', 'Resort', 'Villa', 'Room']);

        return [
            '_id' => (string) Str::uuid(),

            'title' => "{$type} in {$city}, {$country}",
            'description' => $this->faker->paragraph(4),

            'country' => $country,
            'city' => $city,
            'type' => strtolower($type),

            'price_per_night' => $this->faker->randomFloat(2, 1500, 15000),
            'max_guests' => $this->faker->numberBetween(1, 10),
            'address' => $this->faker->streetAddress(),
        ];
    }
}
