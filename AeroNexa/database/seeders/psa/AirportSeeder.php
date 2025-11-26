<?php

namespace Database\Seeders\psa;

use Illuminate\Database\Seeder;
use App\Models\psa\Airport;

class AirportSeeder extends Seeder
{
    public function run()
    {
        $airports = [

            // -----------------------
            // 🇵🇭 Philippines
            // -----------------------
            ['code' => 'MNL', 'name' => 'Ninoy Aquino International Airport', 'city' => 'Manila', 'country' => 'Philippines', 'latitude' => 14.5086, 'longitude' => 121.0198],
            ['code' => 'CEB', 'name' => 'Mactan–Cebu International Airport', 'city' => 'Cebu', 'country' => 'Philippines', 'latitude' => 10.3133, 'longitude' => 123.9828],
            ['code' => 'CRK', 'name' => 'Clark International Airport', 'city' => 'Angeles', 'country' => 'Philippines', 'latitude' => 15.1850, 'longitude' => 120.5590],
            ['code' => 'DVO', 'name' => 'Davao International Airport', 'city' => 'Davao', 'country' => 'Philippines', 'latitude' => 7.1301, 'longitude' => 125.6458],
            ['code' => 'ZAM', 'name' => 'Zamboanga International Airport', 'city' => 'Zamboanga', 'country' => 'Philippines', 'latitude' => 6.9224, 'longitude' => 122.0596],
            ['code' => 'ILO', 'name' => 'Iloilo International Airport', 'city' => 'Iloilo', 'country' => 'Philippines', 'latitude' => 10.8330, 'longitude' => 122.4930],
            ['code' => 'PPS', 'name' => 'Puerto Princesa International Airport', 'city' => 'Palawan', 'country' => 'Philippines', 'latitude' => 9.7421, 'longitude' => 118.7596],
            ['code' => 'KLO', 'name' => 'Kalibo International Airport', 'city' => 'Kalibo', 'country' => 'Philippines', 'latitude' => 11.6870, 'longitude' => 122.3760],
            ['code' => 'BCD', 'name' => 'Bacolod–Silay Airport', 'city' => 'Bacolod', 'country' => 'Philippines', 'latitude' => 10.7764, 'longitude' => 123.0143],
            ['code' => 'TUG', 'name' => 'Tuguegarao Airport', 'city' => 'Tuguegarao', 'country' => 'Philippines', 'latitude' => 17.6434, 'longitude' => 121.7332],

            // -----------------------
            // 🌏 Asia
            // -----------------------
            ['code' => 'NRT', 'name' => 'Narita International Airport', 'city' => 'Tokyo', 'country' => 'Japan', 'latitude' => 35.7730, 'longitude' => 140.3929],
            ['code' => 'HND', 'name' => 'Haneda Airport', 'city' => 'Tokyo', 'country' => 'Japan', 'latitude' => 35.5494, 'longitude' => 139.7798],
            ['code' => 'KIX', 'name' => 'Kansai International Airport', 'city' => 'Osaka', 'country' => 'Japan', 'latitude' => 34.4273, 'longitude' => 135.2440],
            ['code' => 'ICN', 'name' => 'Incheon International Airport', 'city' => 'Incheon', 'country' => 'South Korea', 'latitude' => 37.4602, 'longitude' => 126.4407],
            ['code' => 'GMP', 'name' => 'Gimpo Airport', 'city' => 'Seoul', 'country' => 'South Korea', 'latitude' => 37.5583, 'longitude' => 126.7906],
            ['code' => 'PEK', 'name' => 'Beijing Capital Airport', 'city' => 'Beijing', 'country' => 'China', 'latitude' => 40.0799, 'longitude' => 116.6031],
            ['code' => 'PVG', 'name' => 'Shanghai Pudong Airport', 'city' => 'Shanghai', 'country' => 'China', 'latitude' => 31.1441, 'longitude' => 121.8083],
            ['code' => 'CAN', 'name' => 'Guangzhou Baiyun Airport', 'city' => 'Guangzhou', 'country' => 'China', 'latitude' => 23.3924, 'longitude' => 113.2988],
            ['code' => 'HKG', 'name' => 'Hong Kong International Airport', 'city' => 'Hong Kong', 'country' => 'China', 'latitude' => 22.3080, 'longitude' => 113.9185],
            ['code' => 'SIN', 'name' => 'Singapore Changi Airport', 'city' => 'Singapore', 'country' => 'Singapore', 'latitude' => 1.3644, 'longitude' => 103.9915],
            ['code' => 'BKK', 'name' => 'Suvarnabhumi Airport', 'city' => 'Bangkok', 'country' => 'Thailand', 'latitude' => 13.6900, 'longitude' => 100.7501],
            ['code' => 'DMK', 'name' => 'Don Mueang Airport', 'city' => 'Bangkok', 'country' => 'Thailand', 'latitude' => 13.9125, 'longitude' => 100.6067],
            ['code' => 'KUL', 'name' => 'Kuala Lumpur International Airport', 'city' => 'Kuala Lumpur', 'country' => 'Malaysia', 'latitude' => 2.7557, 'longitude' => 101.7099],
            ['code' => 'DEL', 'name' => 'Indira Gandhi Airport', 'city' => 'Delhi', 'country' => 'India', 'latitude' => 28.5562, 'longitude' => 77.1000],
            ['code' => 'BOM', 'name' => 'Mumbai Airport', 'city' => 'Mumbai', 'country' => 'India', 'latitude' => 19.0896, 'longitude' => 72.8656],
            ['code' => 'TPE', 'name' => 'Taoyuan Airport', 'city' => 'Taipei', 'country' => 'Taiwan', 'latitude' => 25.0797, 'longitude' => 121.2349],
            ['code' => 'DOH', 'name' => 'Hamad International Airport', 'city' => 'Doha', 'country' => 'Qatar', 'latitude' => 25.2731, 'longitude' => 51.6081],
            ['code' => 'DXB', 'name' => 'Dubai International Airport', 'city' => 'Dubai', 'country' => 'UAE', 'latitude' => 25.2532, 'longitude' => 55.3657],

            // -----------------------
            // 🌍 Europe
            // -----------------------
            ['code' => 'LHR', 'name' => 'Heathrow Airport', 'city' => 'longitudedon', 'country' => 'United Kingdom', 'latitude' => 51.4700, 'longitude' => -0.4543],
            ['code' => 'LGW', 'name' => 'Gatwick Airport', 'city' => 'longitudedon', 'country' => 'United Kingdom', 'latitude' => 51.1537, 'longitude' => -0.1821],
            ['code' => 'CDG', 'name' => 'Charles de Gaulle Airport', 'city' => 'Paris', 'country' => 'France', 'latitude' => 49.0097, 'longitude' => 2.5479],
            ['code' => 'FRA', 'name' => 'Frankfurt Airport', 'city' => 'Frankfurt', 'country' => 'Germany', 'latitude' => 50.0379, 'longitude' => 8.5622],
            ['code' => 'AMS', 'name' => 'Schiphol Airport', 'city' => 'Amsterdam', 'country' => 'Netherlands', 'latitude' => 52.3105, 'longitude' => 4.7683],
            ['code' => 'MAD', 'name' => 'Madrid Barajas Airport', 'city' => 'Madrid', 'country' => 'Spain', 'latitude' => 40.4983, 'longitude' => -3.5676],
            ['code' => 'BCN', 'name' => 'Barcelongitudea–El Prat Airport', 'city' => 'Barcelongitudea', 'country' => 'Spain', 'latitude' => 41.2969, 'longitude' => 2.0785],
            ['code' => 'FCO', 'name' => 'Fiumicino Airport', 'city' => 'Rome', 'country' => 'Italy', 'latitude' => 41.8003, 'longitude' => 12.2389],
            ['code' => 'ZRH', 'name' => 'Zurich Airport', 'city' => 'Zurich', 'country' => 'Switzerland', 'latitude' => 47.4582, 'longitude' => 8.5555],
            ['code' => 'IST', 'name' => 'Istanbul Airport', 'city' => 'Istanbul', 'country' => 'Türkiye', 'latitude' => 41.2753, 'longitude' => 28.7519],

            // -----------------------
            // 🌎 North America
            // -----------------------
            ['code' => 'LAX', 'name' => 'Los Angeles Airport', 'city' => 'Los Angeles', 'country' => 'USA', 'latitude' => 33.9416, 'longitude' => -118.4085],
            ['code' => 'SFO', 'name' => 'San Francisco Airport', 'city' => 'San Francisco', 'country' => 'USA', 'latitude' => 37.6213, 'longitude' => -122.3790],
            ['code' => 'SEA', 'name' => 'Seattle–Tacoma Airport', 'city' => 'Seattle', 'country' => 'USA', 'latitude' => 47.4502, 'longitude' => -122.3088],
            ['code' => 'JFK', 'name' => 'JFK Airport', 'city' => 'New York', 'country' => 'USA', 'latitude' => 40.6413, 'longitude' => -73.7781],
            ['code' => 'ORD', 'name' => 'O’Hare Airport', 'city' => 'Chicago', 'country' => 'USA', 'latitude' => 41.9742, 'longitude' => -87.9073],
            ['code' => 'YVR', 'name' => 'Vancouver Airport', 'city' => 'Vancouver', 'country' => 'Canada', 'latitude' => 49.1947, 'longitude' => -123.1792],
            ['code' => 'YYZ', 'name' => 'Toronto Pearson Airport', 'city' => 'Toronto', 'country' => 'Canada', 'latitude' => 43.6777, 'longitude' => -79.6248],
            ['code' => 'MEX', 'name' => 'Mexico City Airport', 'city' => 'Mexico City', 'country' => 'Mexico', 'latitude' => 19.4361, 'longitude' => -99.0719],

            // -----------------------
            // 🌍 Africa
            // -----------------------
            ['code' => 'JNB', 'name' => 'O. R. Tambo Airport', 'city' => 'Johannesburg', 'country' => 'South Africa', 'latitude' => -26.1337, 'longitude' => 28.2420],
            ['code' => 'CPT', 'name' => 'Cape Town Airport', 'city' => 'Cape Town', 'country' => 'South Africa', 'latitude' => -33.9680, 'longitude' => 18.5972],
            ['code' => 'CAI', 'name' => 'Cairo Airport', 'city' => 'Cairo', 'country' => 'Egypt', 'latitude' => 30.1129, 'longitude' => 31.3990],
            ['code' => 'CMN', 'name' => 'Mohammed V Airport', 'city' => 'Casablanca', 'country' => 'Morocco', 'latitude' => 33.3675, 'longitude' => -7.5899],
            ['code' => 'ADD', 'name' => 'Addis Ababa Bole Airport', 'city' => 'Addis Ababa', 'country' => 'Ethiopia', 'latitude' => 8.9779, 'longitude' => 38.7993],
            ['code' => 'NBO', 'name' => 'Jomo Kenyatta Airport', 'city' => 'Nairobi', 'country' => 'Kenya', 'latitude' => -1.3192, 'longitude' => 36.9278],
            ['code' => 'LOS', 'name' => 'Murtala Muhammed Airport', 'city' => 'Lagos', 'country' => 'Nigeria', 'latitude' => 6.5774, 'longitude' => 3.3212],

            // -----------------------
            // 🌎 South America
            // -----------------------
            ['code' => 'GRU', 'name' => 'Guarulhos Airport', 'city' => 'São Paulo', 'country' => 'Brazil', 'latitude' => -23.4356, 'longitude' => -46.4731],
            ['code' => 'GIG', 'name' => 'Galeão Airport', 'city' => 'Rio de Janeiro', 'country' => 'Brazil', 'latitude' => -22.8101, 'longitude' => -43.2506],
            ['code' => 'EZE', 'name' => 'Ezeiza Airport', 'city' => 'Buenos Aires', 'country' => 'Argentina', 'latitude' => -34.8120, 'longitude' => -58.5348],
            ['code' => 'SCL', 'name' => 'Santiago Airport', 'city' => 'Santiago', 'country' => 'Chile', 'latitude' => -33.3929, 'longitude' => -70.7858],
            ['code' => 'LIM', 'name' => 'Jorge Chávez Airport', 'city' => 'Lima', 'country' => 'Peru', 'latitude' => -12.0219, 'longitude' => -77.1143],
            ['code' => 'BOG', 'name' => 'El Dorado Airport', 'city' => 'Bogotá', 'country' => 'Colombia', 'latitude' => 4.7016, 'longitude' => -74.1469],

            // -----------------------
            // 🌏 Australia & Oceania
            // -----------------------
            ['code' => 'SYD', 'name' => 'Sydney Airport', 'city' => 'Sydney', 'country' => 'Australia', 'latitude' => -33.9399, 'longitude' => 151.1753],
            ['code' => 'MEL', 'name' => 'Melbourne Airport', 'city' => 'Melbourne', 'country' => 'Australia', 'latitude' => -37.6690, 'longitude' => 144.8410],
            ['code' => 'BNE', 'name' => 'Brisbane Airport', 'city' => 'Brisbane', 'country' => 'Australia', 'latitude' => -27.3842, 'longitude' => 153.1175],
            ['code' => 'AKL', 'name' => 'Auckland Airport', 'city' => 'Auckland', 'country' => 'New Zealand', 'latitude' => -37.0082, 'longitude' => 174.7850],

        ];

        Airport::insert($airports);
    }
}
