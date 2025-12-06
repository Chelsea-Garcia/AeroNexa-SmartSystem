<?php

namespace Database\Factories\aureliya;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $countryCities = [

        // ============================================================
        //  PHILIPPINES (Luzon / Visayas / Mindanao)
        // ============================================================
        'Philippines' => [
            'Luzon' => [
                'Manila',
                'Quezon City',
                'Pasig',
                'Angeles',
                'Baguio',
                'Olongapo',
                'Bataan',
            ],
            'Visayas' => [
                'Cebu',
                'Iloilo',
                'Bacolod',
                'Kalibo',
                'Tacloban',
                'Dumaguete',
            ],
            'Mindanao' => [
                'Davao',
                'Zamboanga',
                'Cagayan de Oro',
                'General Santos',
            ],
        ],

        // ============================================================
        // JAPAN (Kanto / Kansai / Chubu / Hokkaido)
        // ============================================================
        'Japan' => [
            'Kanto' => [
                'Tokyo',
                'Yokohama',
                'Saitama',
            ],
            'Kansai' => [
                'Osaka',
                'Kyoto',
                'Kobe',
            ],
            'Chubu' => [
                'Nagoya',
                'Shizuoka',
                'Kanazawa',
            ],
            'Hokkaido' => [
                'Sapporo',
                'Hakodate',
                'Asahikawa',
            ],
        ],

        // ============================================================
        // SOUTH KOREA
        // ============================================================
        'South Korea' => [
            'Seoul Capital Area' => [
                'Seoul',
                'Incheon',
                'Suwon',
            ],
            'Yeongnam' => [
                'Busan',
                'Daegu',
                'Ulsan',
            ],
        ],

        // ============================================================
        // CHINA
        // ============================================================
        'China' => [
            'East China' => [
                'Shanghai',
                'Hangzhou',
                'Nanjing',
            ],
            'South China' => [
                'Shenzhen',
                'Guangzhou',
                'Hong Kong',
            ],
            'North China' => [
                'Beijing',
                'Chengdu',
                'Tianjin',
            ],
        ],

        // ============================================================
        // THE REST (ONLY REALISTIC DIVISIONS)
        // ============================================================
        'Singapore' => [
            'Singapore Region' => [
                'Singapore'
            ],
        ],

        'Thailand' => [
            'Central' => [
                'Bangkok',
                'Nonthaburi',
                'Ayutthaya',
            ],
            'Northern' => [
                'Chiang Mai',
                'Chiang Rai',
                'Lampang',
            ],
            'Eastern' => [
                'Pattaya',
                'Chonburi',
                'Rayong',
            ],
        ],

        'Malaysia' => [
            'Klang Valley' => [
                'Kuala Lumpur',
                'Petaling Jaya',
                'Shah Alam',
            ],
            'Northern Region' => [
                'Penang',
                'Ipoh',
                'Alor Setar',
            ],
            'Southern Region' => [
                'Johor Bahru',
                'Batu Pahat',
                'Kluang',
            ],
        ],

        'India' => [
            'North India' => [
                'Delhi',
                'Jaipur',
                'Chandigarh',
            ],
            'West India' => [
                'Mumbai',
                'Pune',
                'Ahmedabad',
            ],
            'South India' => [
                'Bangalore',
                'Hyderabad',
                'Chennai',
            ],
        ],

        'Taiwan' => [
            'Northern Taiwan' => [
                'Taipei',
                'Keelung',
                'New Taipei',
            ],
            'Central Taiwan' => [
                'Taichung',
                'Changhua',
                'Miaoli',
            ],
        ],

        'Qatar' => [
            'Central Qatar' => [
                'Doha',
            ],
        ],

        'UAE' => [
            'Dubai Emirate' => [
                'Dubai',
                'Hatta',
                'Jebel Ali',
            ],
            'Abu Dhabi Emirate' => [
                'Abu Dhabi',
                'Al Ain',
                'Madinat Zayed',
            ],
        ],

        'United Kingdom' => [
            'London Region' => [
                'London',
                'Croydon',
                'Westminster',
            ],
            'Northwest England' => [
                'Manchester',
                'Liverpool',
                'Blackpool',
            ],
        ],

        'France' => [
            'Île-de-France' => [
                'Paris',
                'Versailles',
                'Boulogne-Billancourt',
            ],
            'Auvergne-Rhône-Alpes' => [
                'Lyon',
                'Grenoble',
                'Saint-Étienne',
            ],
        ],

        'Germany' => [
            'Hesse' => [
                'Frankfurt',
                'Wiesbaden',
                'Darmstadt',
            ],
            'Berlin Region' => [
                'Berlin',
                'Potsdam',
                'Oranienburg',
            ],
        ],

        'Netherlands' => [
            'North Holland' => [
                'Amsterdam',
                'Haarlem',
                'Zaandam',
            ],
            'South Holland' => [
                'Rotterdam',
                'The Hague',
                'Leiden',
            ],
        ],

        'Spain' => [
            'Community of Madrid' => [
                'Madrid',
                'Alcalá de Henares',
                'Getafe',
            ],
            'Catalonia' => [
                'Barcelona',
                'Tarragona',
                'Girona',
            ],
            'Valencian Community' => [
                'Valencia',
                'Alicante',
                'Castellón',
            ],
        ],

        'Italy' => [
            'Lazio' => [
                'Rome',
                'Frosinone',
                'Latina',
            ],
            'Lombardy' => [
                'Milan',
                'Bergamo',
                'Brescia',
            ],
        ],

        'Switzerland' => [
            'Zurich Canton' => [
                'Zurich',
                'Winterthur',
                'Uster',
            ],
            'Geneva Canton' => [
                'Geneva',
                'Carouge',
                'Meyrin',
            ],
        ],

        'Türkiye' => [
            'Marmara Region' => [
                'Istanbul',
                'Bursa',
                'Izmit',
            ],
            'Central Anatolia' => [
                'Ankara',
                'Konya',
                'Kayseri',
            ],
        ],

        'USA' => [
            'West Coast' => [
                'Los Angeles',
                'San Francisco',
                'Seattle',
            ],
            'Northeast' => [
                'New York',
                'Boston',
                'Philadelphia',
            ],
            'Midwest' => [
                'Chicago',
                'Detroit',
                'Minneapolis',
            ],
            'South' => [
                'Miami',
                'Houston',
                'Atlanta',
            ],
        ],

        'Canada' => [
            'British Columbia' => [
                'Vancouver',
                'Surrey',
                'Burnaby',
            ],
            'Ontario' => [
                'Toronto',
                'Ottawa',
                'Hamilton',
            ],
            'Quebec' => [
                'Montreal',
                'Quebec City',
                'Laval',
            ],
            'Alberta' => [
                'Calgary',
                'Edmonton',
                'Red Deer',
            ],
        ],

        'Mexico' => [
            'Mexico City Region' => [
                'Mexico City',
                'Ecatepec',
                'Coyoacán',
            ],
            'Jalisco' => [
                'Guadalajara',
                'Zapopan',
                'Tlaquepaque',
            ],
            'Nuevo León' => [
                'Monterrey',
                'Guadalupe',
                'San Nicolás',
            ],
        ],

        'South Africa' => [
            'Gauteng' => [
                'Johannesburg',
                'Pretoria',
                'Benoni',
            ],
            'Western Cape' => [
                'Cape Town',
                'Stellenbosch',
                'Paarl',
            ],
            'KwaZulu-Natal' => [
                'Durban',
                'Pietermaritzburg',
                'Umhlanga',
            ],
        ],

        'Egypt' => [
            'Cairo Governorate' => [
                'Cairo',
                'Nasr City',
                'Heliopolis',
            ],
            'Giza Governorate' => [
                'Giza',
                '6th of October',
                'Sheikh Zayed',
            ],
        ],

        'Morocco' => [
            'Casablanca-Settat' => [
                'Casablanca',
                'Mohammedia',
                'Settat',
            ],
            'Marrakesh-Safi' => [
                'Marrakesh',
                'Safi',
                'Essaouira',
            ],
        ],

        'Ethiopia' => [
            'Addis Ababa Region' => [
                'Addis Ababa',
                'Arada',
                'Bole',
            ],
        ],

        'Kenya' => [
            'Nairobi County' => [
                'Nairobi',
                'Karen',
                'Westlands',
            ],
            'Coast Province' => [
                'Mombasa',
                'Malindi',
                'Lamu',
            ],
        ],

        'Nigeria' => [
            'Lagos State' => [
                'Lagos',
                'Ikeja',
                'Ikorodu',
            ],
            'Federal Capital Territory' => [
                'Abuja',
                'Gwagwalada',
                'Lugbe',
            ],
        ],

        'Brazil' => [
            'São Paulo State' => [
                'São Paulo',
                'Campinas',
                'Santos',
            ],
            'Rio de Janeiro State' => [
                'Rio de Janeiro',
                'Niteroi',
                'Petrópolis',
            ],
            'Federal District' => [
                'Brasilia',
                'Gama',
                'Taguatinga',
            ],
        ],

        'Argentina' => [
            'Buenos Aires Province' => [
                'Buenos Aires',
                'La Plata',
                'Mar del Plata',
            ],
            'Córdoba Province' => [
                'Cordoba',
                'Villa Carlos Paz',
                'Río Cuarto',
            ],
        ],

        'Chile' => [
            'Santiago Metropolitan' => [
                'Santiago',
                'Puente Alto',
                'Maipú',
            ],
        ],

        'Peru' => [
            'Lima Province' => [
                'Lima',
                'Callao',
                'Miraflores',
            ],
        ],

        'Colombia' => [
            'Capital District' => [
                'Bogotá',
                'Engativá',
                'Suba',
            ],
            'Antioquia' => [
                'Medellín',
                'Envigado',
                'Bello',
            ],
        ],

        'Australia' => [
            'New South Wales' => [
                'Sydney',
                'Newcastle',
                'Wollongong',
            ],
            'Victoria' => [
                'Melbourne',
                'Geelong',
                'Ballarat',
            ],
            'Queensland' => [
                'Brisbane',
                'Gold Coast',
                'Cairns',
            ],
            'Western Australia' => [
                'Perth',
                'Fremantle',
                'Mandurah',
            ],
        ],

        'New Zealand' => [
            'Auckland Region' => [
                'Auckland',
                'Manukau',
                'Waitakere',
            ],
            'Wellington Region' => [
                'Wellington',
                'Lower Hutt',
                'Porirua',
            ],
        ],
    ];

    public function definition()
    {
        // 1. Pick random country
        $country = $this->faker->randomElement(array_keys($this->countryCities));

        // 2. Pick random division inside that country
        $divisions = array_keys($this->countryCities[$country]);
        $division = $this->faker->randomElement($divisions);

        // 3. Pick real city names (no array_keys here)
        $cities = $this->countryCities[$country][$division];
        $city = $this->faker->randomElement($cities);

        // 4. Build full address
        $streetNumber = $this->faker->numberBetween(1, 999);
        $streetName = $this->faker->streetName();
        $address = "#{$streetNumber} {$streetName} St., {$city}, {$division}, {$country}";

        $type = $this->faker->randomElement([
            'Apartment',
            'House',
            'Hotel',
            'Resort',
            'Villa',
            'Room'
        ]);

        return [
            '_id' => (string) Str::uuid(),

            'title'       => "{$type} in {$city}, {$country}",
            'description' => $this->faker->paragraph(4),

            'country'  => $country,
            'division' => $division,
            'city'     => $city,

            'type' => strtolower($type),

            'price_per_night' => $this->faker->randomFloat(2, 1500, 15000),
            'max_guests'      => $this->faker->numberBetween(1, 10),

            'address' => $address,
        ];
    }
}
