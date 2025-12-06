<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Location;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [

            // ============================================================
            //  PHILIPPINES (Luzon / Visayas / Mindanao)
            // ============================================================
            'Philippines' => [
                'Luzon' => [
                    'Manila'       => ['latitude' => 14.599512, 'longitude' => 120.984222],
                    'Quezon City'  => ['latitude' => 14.676041, 'longitude' => 121.043700],
                    'Pasig'        => ['latitude' => 14.5764,   'longitude' => 121.0851],
                    'Angeles'      => ['latitude' => 15.1459,   'longitude' => 120.5830],
                    'Baguio'       => ['latitude' => 16.4023,   'longitude' => 120.5960],
                    'Olongapo'     => ['latitude' => 14.8395,   'longitude' => 120.2828],
                    'Bataan'       => ['latitude' => 14.6600,   'longitude' => 120.5150],
                ],
                'Visayas' => [
                    'Cebu'         => ['latitude' => 10.3157,   'longitude' => 123.8854],
                    'Iloilo'       => ['latitude' => 10.7202,   'longitude' => 122.5621],
                    'Bacolod'      => ['latitude' => 10.6670,   'longitude' => 122.9460],
                    'Kalibo'       => ['latitude' => 11.6833,   'longitude' => 122.3667],
                    'Tacloban'     => ['latitude' => 11.2449,   'longitude' => 125.0016],
                    'Dumaguete'    => ['latitude' => 9.3060,    'longitude' => 123.3054],
                ],
                'Mindanao' => [
                    'Davao'        => ['latitude' => 7.1907,    'longitude' => 125.4553],
                    'Zamboanga'    => ['latitude' => 6.9214,    'longitude' => 122.0790],
                    'Cagayan de Oro' => ['latitude' => 8.4542,   'longitude' => 124.6319],
                    'General Santos' => ['latitude' => 6.1135,   'longitude' => 125.1719],
                ],
            ],

            // ============================================================
            // JAPAN (Kanto / Kansai / Chubu / Hokkaido)
            // ============================================================
            'Japan' => [
                'Kanto' => [
                    'Tokyo'     => ['latitude' => 35.689487, 'longitude' => 139.691711],
                    'Yokohama'  => ['latitude' => 35.443707, 'longitude' => 139.638026],
                    'Saitama'   => ['latitude' => 35.8617,   'longitude' => 139.6455],
                ],
                'Kansai' => [
                    'Osaka'     => ['latitude' => 34.693737, 'longitude' => 135.502167],
                    'Kyoto'     => ['latitude' => 35.011636, 'longitude' => 135.768029],
                    'Kobe'      => ['latitude' => 34.6901,   'longitude' => 135.1955],
                ],
                'Chubu' => [
                    'Nagoya'    => ['latitude' => 35.1815,   'longitude' => 136.9066],
                    'Shizuoka'  => ['latitude' => 34.9756,   'longitude' => 138.3828],
                    'Kanazawa'  => ['latitude' => 36.5613,   'longitude' => 136.6562],
                ],
                'Hokkaido' => [
                    'Sapporo'   => ['latitude' => 43.0618,   'longitude' => 141.3545],
                    'Hakodate'  => ['latitude' => 41.7687,   'longitude' => 140.7288],
                    'Asahikawa' => ['latitude' => 43.7706,   'longitude' => 142.3650],
                ],
            ],

            // ============================================================
            // SOUTH KOREA
            // ============================================================
            'South Korea' => [
                'Seoul Capital Area' => [
                    'Seoul'  => ['latitude' => 37.5665,  'longitude' => 126.9780],
                    'Incheon' => ['latitude' => 37.4563,  'longitude' => 126.7052],
                    'Suwon'  => ['latitude' => 37.2636,  'longitude' => 127.0286],
                ],
                'Yeongnam' => [
                    'Busan'  => ['latitude' => 35.1796,  'longitude' => 129.0756],
                    'Daegu'  => ['latitude' => 35.8714,  'longitude' => 128.6014],
                    'Ulsan'  => ['latitude' => 35.5384,  'longitude' => 129.3114],
                ],
            ],

            // ============================================================
            // CHINA
            // ============================================================
            'China' => [
                'East China' => [
                    'Shanghai' => ['latitude' => 31.2304,  'longitude' => 121.4737],
                    'Hangzhou' => ['latitude' => 30.2741,  'longitude' => 120.1551],
                    'Nanjing'  => ['latitude' => 32.0603,  'longitude' => 118.7969],
                ],
                'South China' => [
                    'Shenzhen' => ['latitude' => 22.5431,  'longitude' => 114.0579],
                    'Guangzhou' => ['latitude' => 23.1291,  'longitude' => 113.2644],
                    'Hong Kong' => ['latitude' => 22.3193,  'longitude' => 114.1694],
                ],
                'North China' => [
                    'Beijing'  => ['latitude' => 39.9042,  'longitude' => 116.4074],
                    'Chengdu'  => ['latitude' => 30.5728,  'longitude' => 104.0668],
                    'Tianjin'  => ['latitude' => 39.3434,  'longitude' => 117.3616],
                ],
            ],

            // ============================================================
            // THE REST (ONLY REALISTIC DIVISIONS)
            // ============================================================
            'Singapore' => [
                'Singapore Region' => [
                    'Singapore' => ['latitude' => 1.3521, 'longitude' => 103.8198]
                ],
            ],

            'Thailand' => [
                'Central' => [
                    'Bangkok'     => ['latitude' => 13.7563, 'longitude' => 100.5018],
                    'Nonthaburi'  => ['latitude' => 13.8600, 'longitude' => 100.5140],
                    'Ayutthaya'   => ['latitude' => 14.3556, 'longitude' => 100.5683],
                ],
                'Northern' => [
                    'Chiang Mai'  => ['latitude' => 18.7877, 'longitude' => 98.9931],
                    'Chiang Rai'  => ['latitude' => 19.9075, 'longitude' => 99.8320],
                    'Lampang'     => ['latitude' => 18.2887, 'longitude' => 99.5046],
                ],
                'Eastern' => [
                    'Pattaya'     => ['latitude' => 12.9236, 'longitude' => 100.8825],
                    'Chonburi'    => ['latitude' => 13.3611, 'longitude' => 100.9847],
                    'Rayong'      => ['latitude' => 12.6828, 'longitude' => 101.2809],
                ],
            ],

            'Malaysia' => [
                'Klang Valley' => [
                    'Kuala Lumpur' => ['latitude' => 3.1390, 'longitude' => 101.6869],
                    'Petaling Jaya' => ['latitude' => 3.1075, 'longitude' => 101.6069],
                    'Shah Alam' => ['latitude' => 3.0738, 'longitude' => 101.5183],
                ],
                'Northern Region' => [
                    'Penang' => ['latitude' => 5.4164, 'longitude' => 100.3327],
                    'Ipoh'   => ['latitude' => 4.5975, 'longitude' => 101.0901],
                    'Alor Setar' => ['latitude' => 6.1190, 'longitude' => 100.3664],
                ],
                'Southern Region' => [
                    'Johor Bahru' => ['latitude' => 1.4927, 'longitude' => 103.7414],
                    'Batu Pahat'  => ['latitude' => 1.8545, 'longitude' => 102.9310],
                    'Kluang'      => ['latitude' => 2.0280, 'longitude' => 103.3165],
                ],
            ],

            'India' => [
                'North India' => [
                    'Delhi' => ['latitude' => 28.7041, 'longitude' => 77.1025],
                    'Jaipur' => ['latitude' => 26.9124, 'longitude' => 75.7873],
                    'Chandigarh' => ['latitude' => 30.7333, 'longitude' => 76.7794],
                ],
                'West India' => [
                    'Mumbai' => ['latitude' => 19.0760, 'longitude' => 72.8777],
                    'Pune'   => ['latitude' => 18.5204, 'longitude' => 73.8567],
                    'Ahmedabad' => ['latitude' => 23.0225, 'longitude' => 72.5714],
                ],
                'South India' => [
                    'Bangalore' => ['latitude' => 12.9716, 'longitude' => 77.5946],
                    'Hyderabad' => ['latitude' => 17.3850, 'longitude' => 78.4867],
                    'Chennai'   => ['latitude' => 13.0827, 'longitude' => 80.2707],
                ],
            ],

            'Taiwan' => [
                'Northern Taiwan' => [
                    'Taipei' => ['latitude' => 25.0330, 'longitude' => 121.5654],
                    'Keelung' => ['latitude' => 25.1276, 'longitude' => 121.7392],
                    'New Taipei' => ['latitude' => 25.0375, 'longitude' => 121.5624],
                ],
                'Central Taiwan' => [
                    'Taichung' => ['latitude' => 24.1477, 'longitude' => 120.6736],
                    'Changhua' => ['latitude' => 24.0751, 'longitude' => 120.5169],
                    'Miaoli'  => ['latitude' => 24.5596, 'longitude' => 120.8200],
                ],
            ],

            'Qatar' => [
                'Central Qatar' => [
                    'Doha' => ['latitude' => 25.2854, 'longitude' => 51.5310],
                ],
            ],

            'UAE' => [
                'Dubai Emirate' => [
                    'Dubai' => ['latitude' => 25.2048, 'longitude' => 55.2708],
                    'Hatta' => ['latitude' => 24.8056, 'longitude' => 56.1026],
                    'Jebel Ali' => ['latitude' => 24.9986, 'longitude' => 55.0330],
                ],
                'Abu Dhabi Emirate' => [
                    'Abu Dhabi' => ['latitude' => 24.4539, 'longitude' => 54.3773],
                    'Al Ain' => ['latitude' => 24.2075, 'longitude' => 55.7447],
                    'Madinat Zayed' => ['latitude' => 23.8636, 'longitude' => 53.6376],
                ],
            ],

            'United Kingdom' => [
                'London Region' => [
                    'London' => ['latitude' => 51.5074, 'longitude' => -0.1278],
                    'Croydon' => ['latitude' => 51.3721, 'longitude' => -0.0982],
                    'Westminster' => ['latitude' => 51.4975, 'longitude' => -0.1357],
                ],
                'Northwest England' => [
                    'Manchester' => ['latitude' => 53.4808, 'longitude' => -2.2426],
                    'Liverpool' => ['latitude' => 53.4084, 'longitude' => -2.9916],
                    'Blackpool' => ['latitude' => 53.8142, 'longitude' => -3.0500],
                ],
            ],

            'France' => [
                'Île-de-France' => [
                    'Paris' => ['latitude' => 48.8566, 'longitude' => 2.3522],
                    'Versailles' => ['latitude' => 48.8049, 'longitude' => 2.1204],
                    'Boulogne-Billancourt' => ['latitude' => 48.8355, 'longitude' => 2.2399],
                ],
                'Auvergne-Rhône-Alpes' => [
                    'Lyon' => ['latitude' => 45.7640, 'longitude' => 4.8357],
                    'Grenoble' => ['latitude' => 45.1885, 'longitude' => 5.7245],
                    'Saint-Étienne' => ['latitude' => 45.4397, 'longitude' => 4.3872],
                ],
            ],

            'Germany' => [
                'Hesse' => [
                    'Frankfurt' => ['latitude' => 50.1109, 'longitude' => 8.6821],
                    'Wiesbaden' => ['latitude' => 50.0826, 'longitude' => 8.2435],
                    'Darmstadt' => ['latitude' => 49.8728, 'longitude' => 8.6512],
                ],
                'Berlin Region' => [
                    'Berlin' => ['latitude' => 52.5200, 'longitude' => 13.4050],
                    'Potsdam' => ['latitude' => 52.3906, 'longitude' => 13.0645],
                    'Oranienburg' => ['latitude' => 52.7536, 'longitude' => 13.2373],
                ],
            ],

            'Netherlands' => [
                'North Holland' => [
                    'Amsterdam' => ['latitude' => 52.3676, 'longitude' => 4.9041],
                    'Haarlem' => ['latitude' => 52.3874, 'longitude' => 4.6462],
                    'Zaandam' => ['latitude' => 52.4452, 'longitude' => 4.8267],
                ],
                'South Holland' => [
                    'Rotterdam' => ['latitude' => 51.9244, 'longitude' => 4.4777],
                    'The Hague' => ['latitude' => 52.0705, 'longitude' => 4.3007],
                    'Leiden' => ['latitude' => 52.1601, 'longitude' => 4.4970],
                ],
            ],

            'Spain' => [
                'Community of Madrid' => [
                    'Madrid' => ['latitude' => 40.4168, 'longitude' => -3.7038],
                    'Alcalá de Henares' => ['latitude' => 40.4818, 'longitude' => -3.3635],
                    'Getafe' => ['latitude' => 40.3086, 'longitude' => -3.7326],
                ],
                'Catalonia' => [
                    'Barcelona' => ['latitude' => 41.3851, 'longitude' => 2.1734],
                    'Tarragona' => ['latitude' => 41.1189, 'longitude' => 1.2445],
                    'Girona' => ['latitude' => 41.9794, 'longitude' => 2.8214],
                ],
                'Valencian Community' => [
                    'Valencia' => ['latitude' => 39.4699, 'longitude' => -0.3763],
                    'Alicante' => ['latitude' => 38.3460, 'longitude' => -0.4907],
                    'Castellón' => ['latitude' => 39.9864, 'longitude' => -0.0511],
                ],
            ],

            'Italy' => [
                'Lazio' => [
                    'Rome' => ['latitude' => 41.9028, 'longitude' => 12.4964],
                    'Frosinone' => ['latitude' => 41.6408, 'longitude' => 13.3514],
                    'Latina' => ['latitude' => 41.4679, 'longitude' => 12.9037],
                ],
                'Lombardy' => [
                    'Milan' => ['latitude' => 45.4642, 'longitude' => 9.1900],
                    'Bergamo' => ['latitude' => 45.6983, 'longitude' => 9.6773],
                    'Brescia' => ['latitude' => 45.5416, 'longitude' => 10.2118],
                ],
            ],

            'Switzerland' => [
                'Zurich Canton' => [
                    'Zurich' => ['latitude' => 47.3769, 'longitude' => 8.5417],
                    'Winterthur' => ['latitude' => 47.4988, 'longitude' => 8.7241],
                    'Uster' => ['latitude' => 47.3486, 'longitude' => 8.7160],
                ],
                'Geneva Canton' => [
                    'Geneva' => ['latitude' => 46.2044, 'longitude' => 6.1432],
                    'Carouge' => ['latitude' => 46.1913, 'longitude' => 6.1400],
                    'Meyrin' => ['latitude' => 46.2368, 'longitude' => 6.0820],
                ],
            ],

            'Türkiye' => [
                'Marmara Region' => [
                    'Istanbul' => ['latitude' => 41.0082, 'longitude' => 28.9784],
                    'Bursa' => ['latitude' => 40.1950, 'longitude' => 29.0600],
                    'Izmit' => ['latitude' => 40.7769, 'longitude' => 29.9406],
                ],
                'Central Anatolia' => [
                    'Ankara' => ['latitude' => 39.9334, 'longitude' => 32.8597],
                    'Konya' => ['latitude' => 37.8746, 'longitude' => 32.4932],
                    'Kayseri' => ['latitude' => 38.7225, 'longitude' => 35.4875],
                ],
            ],

            'USA' => [
                'West Coast' => [
                    'Los Angeles' => ['latitude' => 34.0522, 'longitude' => -118.2437],
                    'San Francisco' => ['latitude' => 37.7749, 'longitude' => -122.4194],
                    'Seattle' => ['latitude' => 47.6062, 'longitude' => -122.3321],
                ],
                'Northeast' => [
                    'New York' => ['latitude' => 40.7128, 'longitude' => -74.0060],
                    'Boston' => ['latitude' => 42.3601, 'longitude' => -71.0589],
                    'Philadelphia' => ['latitude' => 39.9526, 'longitude' => -75.1652],
                ],
                'Midwest' => [
                    'Chicago' => ['latitude' => 41.8781, 'longitude' => -87.6298],
                    'Detroit' => ['latitude' => 42.3314, 'longitude' => -83.0458],
                    'Minneapolis' => ['latitude' => 44.9778, 'longitude' => -93.2650],
                ],
                'South' => [
                    'Miami' => ['latitude' => 25.7617, 'longitude' => -80.1918],
                    'Houston' => ['latitude' => 29.7604, 'longitude' => -95.3698],
                    'Atlanta' => ['latitude' => 33.7490, 'longitude' => -84.3880],
                ],
            ],

            'Canada' => [
                'British Columbia' => [
                    'Vancouver' => ['latitude' => 49.2827, 'longitude' => -123.1207],
                    'Surrey' => ['latitude' => 49.1913, 'longitude' => -122.8490],
                    'Burnaby' => ['latitude' => 49.2488, 'longitude' => -122.9805],
                ],
                'Ontario' => [
                    'Toronto' => ['latitude' => 43.6532, 'longitude' => -79.3832],
                    'Ottawa' => ['latitude' => 45.4215, 'longitude' => -75.6972],
                    'Hamilton' => ['latitude' => 43.2557, 'longitude' => -79.8711],
                ],
                'Quebec' => [
                    'Montreal' => ['latitude' => 45.5017, 'longitude' => -73.5673],
                    'Quebec City' => ['latitude' => 46.8139, 'longitude' => -71.2080],
                    'Laval' => ['latitude' => 45.6066, 'longitude' => -73.7124],
                ],
                'Alberta' => [
                    'Calgary' => ['latitude' => 51.0447, 'longitude' => -114.0719],
                    'Edmonton' => ['latitude' => 53.5461, 'longitude' => -113.4938],
                    'Red Deer' => ['latitude' => 52.2681, 'longitude' => -113.8112],
                ],
            ],

            'Mexico' => [
                'Mexico City Region' => [
                    'Mexico City' => ['latitude' => 19.4326, 'longitude' => -99.1332],
                    'Ecatepec' => ['latitude' => 19.6010, 'longitude' => -99.0503],
                    'Coyoacán' => ['latitude' => 19.3580, 'longitude' => -99.1637],
                ],
                'Jalisco' => [
                    'Guadalajara' => ['latitude' => 20.6597, 'longitude' => -103.3496],
                    'Zapopan' => ['latitude' => 20.7099, 'longitude' => -103.4014],
                    'Tlaquepaque' => ['latitude' => 20.6333, 'longitude' => -103.3140],
                ],
                'Nuevo León' => [
                    'Monterrey' => ['latitude' => 25.6866, 'longitude' => -100.3161],
                    'Guadalupe' => ['latitude' => 25.6886, 'longitude' => -100.2056],
                    'San Nicolás' => ['latitude' => 25.7570, 'longitude' => -100.3107],
                ],
            ],

            'South Africa' => [
                'Gauteng' => [
                    'Johannesburg' => ['latitude' => -26.2041, 'longitude' => 28.0473],
                    'Pretoria' => ['latitude' => -25.7479, 'longitude' => 28.2293],
                    'Benoni' => ['latitude' => -26.1450, 'longitude' => 28.3190],
                ],
                'Western Cape' => [
                    'Cape Town' => ['latitude' => -33.9249, 'longitude' => 18.4241],
                    'Stellenbosch' => ['latitude' => -33.9343, 'longitude' => 18.8636],
                    'Paarl' => ['latitude' => -33.7346, 'longitude' => 18.9626],
                ],
                'KwaZulu-Natal' => [
                    'Durban' => ['latitude' => -29.8587, 'longitude' => 31.0218],
                    'Pietermaritzburg' => ['latitude' => -29.6167, 'longitude' => 30.3918],
                    'Umhlanga' => ['latitude' => -29.7099, 'longitude' => 31.0920],
                ],
            ],

            'Egypt' => [
                'Cairo Governorate' => [
                    'Cairo' => ['latitude' => 30.0444, 'longitude' => 31.2357],
                    'Nasr City' => ['latitude' => 30.0561, 'longitude' => 31.3560],
                    'Heliopolis' => ['latitude' => 30.0818, 'longitude' => 31.3463],
                ],
                'Giza Governorate' => [
                    'Giza' => ['latitude' => 30.0131, 'longitude' => 31.2089],
                    '6th of October' => ['latitude' => 29.9533, 'longitude' => 30.9156],
                    'Sheikh Zayed' => ['latitude' => 30.0405, 'longitude' => 30.9526],
                ],
            ],

            'Morocco' => [
                'Casablanca-Settat' => [
                    'Casablanca' => ['latitude' => 33.5731, 'longitude' => -7.5898],
                    'Mohammedia' => ['latitude' => 33.6772, 'longitude' => -7.3575],
                    'Settat' => ['latitude' => 33.0099, 'longitude' => -7.6170],
                ],
                'Marrakesh-Safi' => [
                    'Marrakesh' => ['latitude' => 31.6295, 'longitude' => -7.9811],
                    'Safi' => ['latitude' => 32.2998, 'longitude' => -9.2335],
                    'Essaouira' => ['latitude' => 31.5085, 'longitude' => -9.7599],
                ],
            ],

            'Ethiopia' => [
                'Addis Ababa Region' => [
                    'Addis Ababa' => ['latitude' => 8.9806, 'longitude' => 38.7578],
                    'Arada' => ['latitude' => 9.0229, 'longitude' => 38.7456],
                    'Bole' => ['latitude' => 8.9870, 'longitude' => 38.8056],
                ],
            ],

            'Kenya' => [
                'Nairobi County' => [
                    'Nairobi' => ['latitude' => -1.2921, 'longitude' => 36.8219],
                    'Karen' => ['latitude' => -1.3293, 'longitude' => 36.7151],
                    'Westlands' => ['latitude' => -1.2694, 'longitude' => 36.8120],
                ],
                'Coast Province' => [
                    'Mombasa' => ['latitude' => -4.0435, 'longitude' => 39.6682],
                    'Malindi' => ['latitude' => -3.2172, 'longitude' => 40.1168],
                    'Lamu' => ['latitude' => -2.2710, 'longitude' => 40.9020],
                ],
            ],

            'Nigeria' => [
                'Lagos State' => [
                    'Lagos' => ['latitude' => 6.5244, 'longitude' => 3.3792],
                    'Ikeja' => ['latitude' => 6.6018, 'longitude' => 3.3515],
                    'Ikorodu' => ['latitude' => 6.6190, 'longitude' => 3.5020],
                ],
                'Federal Capital Territory' => [
                    'Abuja' => ['latitude' => 9.0765, 'longitude' => 7.3986],
                    'Gwagwalada' => ['latitude' => 8.9491, 'longitude' => 7.0898],
                    'Lugbe' => ['latitude' => 9.1667, 'longitude' => 7.3833],
                ],
            ],

            'Brazil' => [
                'São Paulo State' => [
                    'São Paulo' => ['latitude' => -23.5505, 'longitude' => -46.6333],
                    'Campinas' => ['latitude' => -22.9056, 'longitude' => -47.0608],
                    'Santos' => ['latitude' => -23.9675, 'longitude' => -46.3280],
                ],
                'Rio de Janeiro State' => [
                    'Rio de Janeiro' => ['latitude' => -22.9068, 'longitude' => -43.1729],
                    'Niteroi' => ['latitude' => -22.8832, 'longitude' => -43.1034],
                    'Petrópolis' => ['latitude' => -22.5087, 'longitude' => -43.1789],
                ],
                'Federal District' => [
                    'Brasilia' => ['latitude' => -15.7939, 'longitude' => -47.8828],
                    'Gama' => ['latitude' => -15.8696, 'longitude' => -48.0673],
                    'Taguatinga' => ['latitude' => -15.8321, 'longitude' => -48.0792],
                ],
            ],

            'Argentina' => [
                'Buenos Aires Province' => [
                    'Buenos Aires' => ['latitude' => -34.6037, 'longitude' => -58.3816],
                    'La Plata' => ['latitude' => -34.9214, 'longitude' => -57.9545],
                    'Mar del Plata' => ['latitude' => -38.0055, 'longitude' => -57.5426],
                ],
                'Córdoba Province' => [
                    'Cordoba' => ['latitude' => -31.4201, 'longitude' => -64.1888],
                    'Villa Carlos Paz' => ['latitude' => -31.4209, 'longitude' => -64.4989],
                    'Río Cuarto' => ['latitude' => -33.1306, 'longitude' => -64.3493],
                ],
            ],

            'Chile' => [
                'Santiago Metropolitan' => [
                    'Santiago' => ['latitude' => -33.4489, 'longitude' => -70.6693],
                    'Puente Alto' => ['latitude' => -33.6118, 'longitude' => -70.5755],
                    'Maipú' => ['latitude' => -33.4839, 'longitude' => -70.7455],
                ],
            ],

            'Peru' => [
                'Lima Province' => [
                    'Lima' => ['latitude' => -12.0464, 'longitude' => -77.0428],
                    'Callao' => ['latitude' => -12.0561, 'longitude' => -77.1187],
                    'Miraflores' => ['latitude' => -12.1219, 'longitude' => -77.0300],
                ],
            ],

            'Colombia' => [
                'Capital District' => [
                    'Bogotá' => ['latitude' => 4.7110, 'longitude' => -74.0721],
                    'Engativá' => ['latitude' => 4.6823, 'longitude' => -74.1290],
                    'Suba' => ['latitude' => 4.7803, 'longitude' => -74.0819],
                ],
                'Antioquia' => [
                    'Medellín' => ['latitude' => 6.2442, 'longitude' => -75.5812],
                    'Envigado' => ['latitude' => 6.1747, 'longitude' => -75.5906],
                    'Bello' => ['latitude' => 6.3374, 'longitude' => -75.5583],
                ],
            ],

            'Australia' => [
                'New South Wales' => [
                    'Sydney' => ['latitude' => -33.8688, 'longitude' => 151.2093],
                    'Newcastle' => ['latitude' => -32.9283, 'longitude' => 151.7817],
                    'Wollongong' => ['latitude' => -34.4278, 'longitude' => 150.8931],
                ],
                'Victoria' => [
                    'Melbourne' => ['latitude' => -37.8136, 'longitude' => 144.9631],
                    'Geelong' => ['latitude' => -38.1499, 'longitude' => 144.3617],
                    'Ballarat' => ['latitude' => -37.5622, 'longitude' => 143.8503],
                ],
                'Queensland' => [
                    'Brisbane' => ['latitude' => -27.4698, 'longitude' => 153.0251],
                    'Gold Coast' => ['latitude' => -28.0167, 'longitude' => 153.4000],
                    'Cairns' => ['latitude' => -16.9203, 'longitude' => 145.7710],
                ],
                'Western Australia' => [
                    'Perth' => ['latitude' => -31.9505, 'longitude' => 115.8605],
                    'Fremantle' => ['latitude' => -32.0569, 'longitude' => 115.7439],
                    'Mandurah' => ['latitude' => -32.5293, 'longitude' => 115.7214],
                ],
            ],

            'New Zealand' => [
                'Auckland Region' => [
                    'Auckland' => ['latitude' => -36.8485, 'longitude' => 174.7633],
                    'Manukau' => ['latitude' => -37.0658, 'longitude' => 174.9175],
                    'Waitakere' => ['latitude' => -36.8925, 'longitude' => 174.5422],
                ],
                'Wellington Region' => [
                    'Wellington' => ['latitude' => -41.2865, 'longitude' => 174.7762],
                    'Lower Hutt' => ['latitude' => -41.2179, 'longitude' => 174.9016],
                    'Porirua' => ['latitude' => -41.1605, 'longitude' => 174.9047],
                ],
            ],
        ];

        // Insert into DB (duplicates allowed if you re-run; adapt if you want idempotency)
        foreach ($structure as $country => $divisions) {
            foreach ($divisions as $division => $cities) {
                foreach ($cities as $city => $coords) {
                    Location::create([
                        'country'  => $country,
                        'division' => $division,
                        'city'     => $city,
                        'latitude' => $coords['latitude'],
                        'longitude' => $coords['longitude'],
                    ]);
                }
            }
        }
    }
}
