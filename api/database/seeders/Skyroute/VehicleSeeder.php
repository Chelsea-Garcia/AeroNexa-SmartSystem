<?php

namespace Database\Seeders\Skyroute;

use Illuminate\Database\Seeder;
use App\Models\skyroute\Vehicle;
use App\Models\skyroute\Location;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        // --- Real vehicle names ---
        $suvModels = [
            'Toyota Fortuner',
            'Mitsubishi Montero',
            'Nissan Terra',
            'Ford Everest',
            'Hyundai Santa Fe',
            'Kia Sorento',
            'Toyota Land Cruiser',
            'Honda CR-V',
            'Mazda CX-9',
            'Subaru Forester'
        ];

        $carModels = [
            'Toyota Vios',
            'Honda Civic',
            'Toyota Corolla Altis',
            'Mitsubishi Mirage G4',
            'Hyundai Elantra',
            'Kia Rio',
            'Nissan Almera',
            'Suzuki Swift',
            'Mazda 3',
            'Volkswagen Polo'
        ];

        $busModels = [
            'Isuzu Gala',
            'Hino Blue Ribbon',
            'Mitsubishi Fuso Aero Queen',
            'Hyundai Universe Space',
            'Volvo B8R'
        ];

        // Load all locations (your existing locations collection)
        $locations = Location::all();

        foreach ($locations as $location) {
            // ensure we have country string
            $country = isset($location->country) ? (string)$location->country : 'unknown';

            // 10 SUVs
            for ($i = 0; $i < 10; $i++) {
                Vehicle::create([
                    'location_id' => (string)$location->_id,
                    'name' => $suvModels[$i % count($suvModels)],
                    'type' => 'SUV',
                    'plate_number' => $this->plateForCountry($country),
                    'capacity' => 7,
                ]);
            }

            // 10 Cars
            for ($i = 0; $i < 10; $i++) {
                Vehicle::create([
                    'location_id' => (string)$location->_id,
                    'name' => $carModels[$i % count($carModels)],
                    'type' => 'Car',
                    'plate_number' => $this->plateForCountry($country),
                    'capacity' => 5,
                ]);
            }

            // 5 Buses
            for ($i = 0; $i < 5; $i++) {
                Vehicle::create([
                    'location_id' => (string)$location->_id,
                    'name' => $busModels[$i % count($busModels)],
                    'type' => 'Bus',
                    'plate_number' => $this->plateForCountry($country),
                    'capacity' => 56,
                ]);
            }
        }
    }

    /**
     * Return a plate number according to the given country (real/scripted when applicable).
     *
     * Country names should match the 'country' field stored in your locations.
     */
    private function plateForCountry(string $country): string
    {
        $c = strtolower(trim($country));

        switch ($c) {
            case 'philippines':
            case 'philippine':
            case 'ph':
                return $this->platePH();

            case 'japan':
            case 'jp':
                return $this->plateJP();

            case 'south korea':
            case 'korea':
            case 'republic of korea':
            case 'kr':
                return $this->plateKR();

            case 'china':
            case 'cn':
                return $this->plateCN();

            case 'thailand':
            case 'th':
                return $this->plateTH();

            case 'singapore':
                return $this->plateSG();

            case 'india':
                return $this->plateIN();

            case 'usa':
            case 'united states':
            case 'united states of america':
            case 'us':
                return $this->plateUS();

            case 'united kingdom':
            case 'uk':
            case 'great britain':
            case 'britain':
                return $this->plateUK();

            case 'france':
                return $this->plateFR();

            case 'germany':
                return $this->plateDE();

            case 'italy':
                return $this->plateIT();

            case 'spain':
                return $this->plateES();

            case 'netherlands':
                return $this->plateNL();

            case 'australia':
                return $this->plateAU();

            case 'canada':
                return $this->plateCA();

            case 'brazil':
                return $this->plateBR();

            case 'mexico':
                return $this->plateMX();

            case 'south africa':
                return $this->plateZA();

            case 'egypt':
                return $this->plateEG();

            case 'morocco':
                return $this->plateMA();

            case 'ethiopia':
                return $this->plateET();

            case 'kenya':
                return $this->plateKE();

            case 'nigeria':
                return $this->plateNG();

            case 'colombia':
                return $this->plateCO();

            case 'argentina':
                return $this->plateAR();

            case 'chile':
                return $this->plateCL();

            case 'peru':
                return $this->platePE();

            case 'turkiye':
            case 'turkey':
                return $this->plateTR();

            case 'uae':
            case 'united arab emirates':
                return $this->plateAE();

            case 'qatar':
                return $this->plateQA();

            case 'switzerland':
                return $this->plateCH();

            case 'sweden':
                return $this->plateSE();

            case 'ireland':
                return $this->plateIE();

            case 'pakistan':
                return $this->platePK();

            case 'vietnam':
                return $this->plateVN();

            case 'netherlands':
            default:
                return $this->plateDefault();
        }
    }

    // ===== PLATE FORMAT HELPERS =====

    // Philippines: "ABC 1234" (we will use a dash for separation: ABC-1234 is also fine)
    private function platePH(): string
    {
        $letters = $this->randLetters(3);
        $numbers = rand(1000, 9999);
        return "{$letters} {$numbers}";
    }

    // Japan: REAL format e.g. "品川 530 あ 12-34"
    private function plateJP(): string
    {
        $areas = ['品川', '横浜', '大阪', '名古屋', '札幌', '福岡', '川崎', '神戸', '千葉', '仙台'];
        $area = $areas[array_rand($areas)];
        $classNum = rand(100, 999); // classification number
        $hiragana = ['あ', 'い', 'う', 'え', 'お', 'か', 'き', 'く', 'け', 'こ', 'さ', 'し', 'す', 'せ', 'そ', 'た', 'ち', 'つ', 'て', 'と', 'な', 'に', 'ぬ', 'ね', 'の'];
        $hir = $hiragana[array_rand($hiragana)];
        $left = rand(10, 99);
        $right = rand(10, 99);
        return "{$area} {$classNum} {$hir} {$left}-{$right}";
    }

    // South Korea: REAL modern format e.g. "123가4567"
    private function plateKR(): string
    {
        $first = rand(100, 999);
        $hangul = ['가', '나', '다', '라', '마', '바', '사', '아', '자', '차', '카', '타', '파', '하'];
        $h = $hangul[array_rand($hangul)];
        $last = rand(1000, 9999);
        return "{$first}{$h}{$last}";
    }

    // China: REAL like "京A·12345" (province char + letter + numbers)
    private function plateCN(): string
    {
        $provinces = ['京', '沪', '津', '渝', '冀', '豫', '云', '辽', '黑', '湘', '皖', '鲁', '新', '苏', '浙', '赣', '鄂', '桂', '甘', '晋', '蒙', '陕', '吉', '闽', '贵', '粤', '青', '藏', '琼'];
        $prov = $provinces[array_rand($provinces)];
        $letter = $this->randLetters(1);
        $numbers = rand(10000, 99999);
        // Use middle dot
        return "{$prov}{$letter}·{$numbers}";
    }

    // Thailand: REAL style with Thai letters, e.g. "1กข 2345" (we'll produce simplified authentic-like)
    private function plateTH(): string
    {
        $digits = rand(1, 9);
        $thaiPairs = ['กข', 'กค', 'กต', 'กท', 'ขค', 'ขต', 'คข', 'คม', 'งว', 'จอ', 'ชย', 'ณย'];
        $pair = $thaiPairs[array_rand($thaiPairs)];
        $numbers = rand(1000, 9999);
        return "{$digits}{$pair} {$numbers}";
    }

    // Singapore: e.g. "SBA1234A"
    private function plateSG(): string
    {
        $letters1 = 'S' . $this->randLetters(2);
        $numbers = rand(1000, 9999);
        $suffix = $this->randLetters(1);
        return "{$letters1}{$numbers}{$suffix}";
    }

    // India: "KA 01 AB 1234"
    private function plateIN(): string
    {
        $states = ['DL', 'MH', 'KA', 'TN', 'UP', 'WB', 'GJ', 'RJ', 'PB'];
        $st = $states[array_rand($states)];
        $rto = str_pad((string)rand(1, 99), 2, '0', STR_PAD_LEFT);
        $letters = $this->randLetters(2);
        $num = rand(1000, 9999);
        return "{$st} {$rto} {$letters} {$num}";
    }

    // USA general: "ABC-1234"
    private function plateUS(): string
    {
        $letters = $this->randLetters(3);
        $nums = rand(1000, 9999);
        return "{$letters}-{$nums}";
    }

    // UK: "AA NN AAA"
    private function plateUK(): string
    {
        $a1 = $this->randLetters(2);
        $n = str_pad((string)rand(10, 99), 2, '0', STR_PAD_LEFT);
        $a2 = $this->randLetters(3);
        return "{$a1} {$n} {$a2}";
    }

    // France: "AA-123-AA"
    private function plateFR(): string
    {
        return $this->randLetters(2) . '-' . rand(100, 999) . '-' . $this->randLetters(2);
    }

    // Germany: "B MW 3921" (city code + letters + numbers)
    private function plateDE(): string
    {
        $city = ['B', 'M', 'F', 'HH', 'S', 'K', 'D'][array_rand(['B', 'M', 'F', 'HH', 'S', 'K', 'D'])] ?? 'B';
        $letters = $this->randLetters(2);
        $num = rand(100, 9999);
        return "{$city} {$letters} {$num}";
    }

    // Italy: "AA 123 BB"
    private function plateIT(): string
    {
        return $this->randLetters(2) . ' ' . rand(100, 999) . ' ' . $this->randLetters(2);
    }

    // Spain: "1234 ABC"
    private function plateES(): string
    {
        return rand(1000, 9999) . ' ' . $this->randLetters(3);
    }

    // Netherlands: a few valid patterns, choose one
    private function plateNL(): string
    {
        $patterns = [
            fn() => rand(10, 99) . '-' . $this->randLetters(2) . '-' . rand(10, 99),
            fn() => $this->randLetters(2) . '-' . rand(10, 99) . '-' . $this->randLetters(2),
            fn() => rand(10, 99) . '-' . rand(10, 99) . '-' . $this->randLetters(2),
        ];
        $p = $patterns[array_rand($patterns)];
        return $p();
    }

    // Australia: common
    private function plateAU(): string
    {
        return $this->randLetters(3) . '-' . rand(100, 999);
    }

    // Canada simple
    private function plateCA(): string
    {
        return $this->randLetters(3) . ' ' . rand(1000, 9999);
    }

    // Brazil - Mercosur: "ABC1D23"
    private function plateBR(): string
    {
        return $this->randLetters(3) . rand(1, 9) . $this->randLetters(1) . rand(10, 99);
    }

    // Mexico
    private function plateMX(): string
    {
        return $this->randLetters(3) . '-' . rand(100, 999) . '-' . $this->randLetters(1);
    }

    // South Africa
    private function plateZA(): string
    {
        return $this->randLetters(3) . ' ' . rand(1000, 9999) . ' ' . strtoupper(substr(md5((string)rand()), 0, 2));
    }

    // Egypt (example fallback style)
    private function plateEG(): string
    {
        return $this->randLetters(3) . '-' . rand(1000, 9999);
    }

    // Morocco
    private function plateMA(): string
    {
        return rand(1, 9) . $this->randLetters(1) . '-' . rand(100, 999);
    }

    // Ethiopia
    private function plateET(): string
    {
        return $this->randLetters(2) . ' ' . rand(1000, 9999);
    }

    // Kenya
    private function plateKE(): string
    {
        return 'K' . $this->randLetters(2) . ' ' . rand(100, 999) . $this->randLetters(1);
    }

    // Nigeria
    private function plateNG(): string
    {
        return strtoupper($this->randLetters(3)) . ' ' . rand(1000, 9999);
    }

    // Colombia
    private function plateCO(): string
    {
        return strtoupper($this->randLetters(3)) . '-' . rand(100, 999);
    }

    // Argentina
    private function plateAR(): string
    {
        return $this->randLetters(3) . ' ' . rand(100, 999) . ' ' . $this->randLetters(1);
    }

    // Chile
    private function plateCL(): string
    {
        return rand(10, 99) . '-' . $this->randLetters(2) . '-' . rand(100, 999);
    }

    // Peru
    private function platePE(): string
    {
        return $this->randLetters(3) . '-' . rand(1000, 9999);
    }

    // Turkey
    private function plateTR(): string
    {
        return rand(1, 81) . ' ' . $this->randLetters(2) . ' ' . rand(100, 999);
    }

    // UAE
    private function plateAE(): string
    {
        return $this->randLetters(2) . ' ' . rand(1000, 9999) . ' ' . strtoupper(substr(md5((string)rand()), 0, 2));
    }

    // Qatar
    private function plateQA(): string
    {
        return strtoupper($this->randLetters(3)) . '-' . rand(1000, 9999);
    }

    // Switzerland
    private function plateCH(): string
    {
        return strtoupper($this->randLetters(2)) . ' ' . rand(100, 999);
    }

    // Sweden
    private function plateSE(): string
    {
        return $this->randLetters(3) . ' ' . rand(100, 999);
    }

    // Ireland
    private function plateIE(): string
    {
        return rand(10, 99) . ' ' . $this->randLetters(2) . ' ' . rand(1, 9999);
    }

    // Pakistan
    private function platePK(): string
    {
        return $this->randLetters(3) . '-' . rand(1000, 9999);
    }

    // Vietnam
    private function plateVN(): string
    {
        return rand(10, 99) . '-' . strtoupper($this->randLetters(2)) . '-' . rand(100, 999);
    }

    // Default fallback
    private function plateDefault(): string
    {
        return $this->randLetters(3) . '-' . rand(1000, 9999);
    }

    // ===== Utility: random letters (upper ASCII) =====
    private function randLetters(int $len = 2): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($chars, (int)ceil($len / strlen($chars)))), 0, $len);
    }
}
