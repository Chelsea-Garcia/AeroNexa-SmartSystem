<?php
require_once '../config.php';
requireLogin();

// User Data
$userId = $_SESSION['user']['id'] ?? $_SESSION['user']['_id'];
$message = '';
$messageType = '';

// =================================================================
// 1. HANDLE BOOKING FORM (Now includes passenger_amount)
// =================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    
    $bookingPayload = [
        'user_id'                 => $userId,
        'vehicle_id'              => $_POST['vehicle_id'],
        'origin_location_id'      => $_POST['origin_location_id'],
        'destination_location_id' => $_POST['destination_location_id'],
        'date'                    => $_POST['date'],
        'time'                    => $_POST['time'],
        'passenger_name'          => $_POST['passenger_name'],
        'passenger_amount'        => $_POST['passenger_amount'], // <--- PASS TO CONTROLLER
        'payment_method'          => 'AEROPAY'
    ];

    $response = callApi('POST', SKYROUTE_API . '/skyroute/bookings', $bookingPayload);

    if ((isset($response['status']) && in_array($response['status'], [200, 201])) || isset($response['message'])) {
        $message = "Ride booked successfully! Driver is on the way.";
        $messageType = 'success';
    } else {
        $apiError = $response['data']['message'] ?? $response['data']['error'] ?? 'Unknown error';
        if (isset($response['data']['errors'])) {
            $apiError .= ' (' . implode(', ', array_map(fn($e) => $e[0], $response['data']['errors'])) . ')';
        }
        $message = "Booking failed: " . $apiError;
        $messageType = 'error';
    }
}

// =================================================================
// 2. FETCH LOCATIONS
// =================================================================
$locResponse = callApi('GET', SKYROUTE_API . '/skyroute/locations');

$locations = [];
if (isset($locResponse['data'])) {
    $locations = $locResponse['data'];
} elseif (is_array($locResponse)) {
    $locations = $locResponse;
}

// =================================================================
// 3. FETCH VEHICLES
// =================================================================
$selectedCityId = $_GET['city_id'] ?? '';
$vehicles = [];
$currentCity = null;

if (!empty($selectedCityId)) {
    foreach ($locations as $loc) {
        $locId = $loc['_id'] ?? $loc['id'];
        if ($locId == $selectedCityId) {
            $currentCity = $loc;
            break;
        }
    }

    if ($currentCity) {
        $vResponse = callApi('GET', SKYROUTE_API . '/skyroute/vehicles/city/' . $selectedCityId);
        
        if (isset($vResponse['data'])) {
            $vehicles = $vResponse['data'];
        } elseif (is_array($vResponse) && !isset($vResponse['error'])) {
            $vehicles = $vResponse;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book a Ride - SkyRoute</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .vehicle-card { background: white; padding: 20px; border-radius: 10px; display: flex; align-items: center; gap: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 15px; transition: 0.2s; }
        .vehicle-card:hover { transform: translateX(5px); border-left: 5px solid #8b5cf6; }
        .vehicle-icon { font-size: 2rem; background: #f3f4f6; width: 60px; height: 60px; display: flex; justify-content: center; align-items: center; border-radius: 50%; }
        .vehicle-info { flex: 1; }
        .vehicle-price { text-align: right; }
        .price-tag { font-size: 1.1rem; font-weight: bold; color: #8b5cf6; }
        .base-price { font-size: 0.8rem; color: #64748b; }

        .location-selectors { display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end; }
        .selector-group { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 200px; }
        .selector-group label { font-size: 0.9rem; font-weight: 600; color: #64748b; }

        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; animation: slideDown 0.3s ease; }
        @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .close-btn { float: right; cursor: pointer; font-size: 1.5rem; color: #94a3b8; }
        
        .est-box { background: #f5f3ff; border: 1px solid #ddd6fe; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: center; }
        .est-price { font-size: 1.5rem; font-weight: bold; color: #7c3aed; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>SkyRoute Booking</h3>
            <span><?= htmlspecialchars($_SESSION['user']['first_name']) ?></span>
        </header>

        <main class="content-area">

            <?php if ($message): ?>
                <div style="padding: 15px; margin-bottom: 20px; border-radius: 6px; background: <?= $messageType == 'success' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $messageType == 'success' ? '#166534' : '#991b1b' ?>;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: #334155;">📍 Select Pickup Location</h4>
                
                <form method="GET" action="vehicles.php">
                    <div class="location-selectors">
                        <div class="selector-group">
                            <label>Country</label>
                            <select id="countrySelect" class="form-control" onchange="filterDivisions()">
                                <option value="">-- Select Country --</option>
                            </select>
                        </div>
                        <div class="selector-group">
                            <label>Division/State</label>
                            <select id="divisionSelect" class="form-control" disabled onchange="filterCities()">
                                <option value="">-- Select Division --</option>
                            </select>
                        </div>
                        <div class="selector-group">
                            <label>Origin City</label>
                            <select name="city_id" id="citySelect" class="form-control" disabled onchange="this.form.submit()">
                                <option value="">-- Select City --</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <?php if ($currentCity): ?>
                <h4 style="margin-bottom: 15px; color: #64748b;">Available Vehicles in <span style="color: #7c3aed;"><?= htmlspecialchars($currentCity['city']) ?></span></h4>
                
                <?php if (empty($vehicles)): ?>
                    <div style="text-align: center; padding: 40px; color: #94a3b8; border: 1px dashed #cbd5e1; border-radius: 8px;">
                        No vehicles found nearby.
                    </div>
                <?php else: ?>
                    <?php foreach ($vehicles as $v): 
                        $base = $v['base_price'] ?? 50;
                        $rate = $v['fare_per_km'] ?? 10;
                    ?>
                        <div class="vehicle-card">
                            <div class="vehicle-icon">
                                <?= ($v['type'] == 'Bus') ? '🚌' : (($v['type'] == 'SUV') ? '🚙' : '🚗') ?>
                            </div>
                            <div class="vehicle-info">
                                <div style="font-weight: bold; font-size: 1.1rem;"><?= htmlspecialchars($v['name']) ?></div>
                                <div style="color: #64748b; font-size: 0.9rem;">Plate: <?= htmlspecialchars($v['plate_number']) ?></div>
                                <div style="font-size: 0.8rem; background: #e0e7ff; color: #3730a3; display: inline-block; padding: 2px 8px; border-radius: 4px; margin-top: 5px;">
                                    <?= htmlspecialchars($v['type']) ?>
                                </div>
                            </div>
                            <div class="vehicle-price">
                                <div class="price-tag">₱<?= $rate ?> <span style="font-size:0.8rem; font-weight:normal;">/km</span></div>
                                <div class="base-price">Base: ₱<?= $base ?></div>
                                <button class="btn-primary" style="margin-top: 10px; padding: 8px 15px; background: #8b5cf6;"
                                    onclick='openBookingModal(
                                        <?= json_encode($v) ?>, 
                                        <?= json_encode($currentCity) ?>
                                    )'>
                                    Select
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            <?php elseif (!$selectedCityId): ?>
                <div style="text-align: center; padding: 50px; color: #94a3b8; border: 2px dashed #cbd5e1; border-radius: 8px;">
                    Please select your location above to see available rides.
                </div>
            <?php endif; ?>

        </main>
    </div>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="closeBookingModal()">&times;</span>
            <h3 style="margin-bottom: 20px; color: #1e293b;">Confirm Ride</h3>
            
            <form method="POST" action="vehicles.php">
                <input type="hidden" name="action" value="create_booking">
                <input type="hidden" name="vehicle_id" id="inputVehicleId">
                <input type="hidden" name="origin_location_id" id="inputOriginId">
                
                <input type="hidden" id="vBasePrice">
                <input type="hidden" id="vPerKm">
                <input type="hidden" id="originLat">
                <input type="hidden" id="originLon">

                <div class="form-group">
                    <label>Vehicle</label>
                    <input type="text" id="displayVehicle" class="form-control" readonly style="background: #f1f5f9; font-weight: bold;">
                </div>

                <div class="form-group">
                    <label>Destination (Within <span id="displayDivision"></span>)</label>
                    <select name="destination_location_id" id="destSelect" class="form-control" required onchange="calculateFare()">
                        <option value="">-- Select Destination --</option>
                    </select>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Time</label>
                        <input type="time" name="time" class="form-control" required value="<?= date('H:i') ?>">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Passenger Name</label>
                        <input type="text" name="passenger_name" class="form-control" required 
                               value="<?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . ($_SESSION['user']['last_name'] ?? '')) ?>">
                    </div>
                    
                    <div class="form-group" style="width: 120px;">
                        <label>Passengers</label>
                        <input type="number" name="passenger_amount" id="paxInput" class="form-control" 
                               value="1" min="1" max="5" required onchange="calculateFare()" onkeyup="calculateFare()">
                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 2px;">Max: <span id="maxPaxDisplay">5</span></div>
                    </div>
                </div>

                <div class="est-box">
                    <div style="font-size: 0.9rem; color: #6b7280; margin-bottom: 5px;">Estimated Fare</div>
                    <div class="est-price">₱<span id="displayEst">0.00</span></div>
                    <div style="font-size: 0.8rem; color: #9ca3af;">~<span id="displayDist">0</span> km distance</div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 15px; background: #7c3aed;">Book Now</button>
            </form>
        </div>
    </div>

    <script>
        const allLocations = <?= json_encode($locations) ?>;
        const currentCityId = "<?= $selectedCityId ?>";

        // --- CASCADING DROPDOWNS ---
        const countrySelect = document.getElementById('countrySelect');
        const divisionSelect = document.getElementById('divisionSelect');
        const citySelect = document.getElementById('citySelect');

        window.addEventListener('DOMContentLoaded', () => {
            populateCountries();
            if (currentCityId) {
                const cityObj = allLocations.find(l => (l._id || l.id) == currentCityId);
                if (cityObj) {
                    countrySelect.value = cityObj.country;
                    filterDivisions(); 
                    divisionSelect.value = cityObj.division;
                    filterCities();    
                    citySelect.value = currentCityId;
                }
            }
        });

        function populateCountries() {
            const countries = [...new Set(allLocations.map(l => l.country))].sort();
            countries.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c;
                opt.innerText = c;
                countrySelect.appendChild(opt);
            });
        }

        function filterDivisions() {
            const selectedCountry = countrySelect.value;
            divisionSelect.innerHTML = '<option value="">-- Select Division --</option>';
            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            divisionSelect.disabled = true;
            citySelect.disabled = true;

            if (!selectedCountry) return;

            const divisions = [...new Set(
                allLocations.filter(l => l.country === selectedCountry).map(l => l.division)
            )].sort();

            divisions.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d;
                opt.innerText = d;
                divisionSelect.appendChild(opt);
            });

            divisionSelect.disabled = false;
        }

        function filterCities() {
            const selectedCountry = countrySelect.value;
            const selectedDivision = divisionSelect.value;
            citySelect.innerHTML = '<option value="">-- Select City --</option>';
            citySelect.disabled = true;

            if (!selectedDivision) return;

            const cities = allLocations
                .filter(l => l.country === selectedCountry && l.division === selectedDivision)
                .sort((a, b) => a.city.localeCompare(b.city));

            cities.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c._id || c.id;
                opt.innerText = c.city;
                citySelect.appendChild(opt);
            });

            citySelect.disabled = false;
        }

        // --- BOOKING MODAL ---
        function openBookingModal(vehicle, originCity) {
            document.getElementById('bookingModal').style.display = 'flex';
            
            const vId = vehicle._id || vehicle.id;
            const oId = originCity._id || originCity.id;

            // Basic Info
            document.getElementById('inputVehicleId').value = vId;
            document.getElementById('inputOriginId').value = oId;
            document.getElementById('displayVehicle').value = vehicle.name + " (" + vehicle.plate_number + ")";
            document.getElementById('displayDivision').innerText = originCity.division;

            // Pricing Params
            document.getElementById('vBasePrice').value = vehicle.base_price || 0;
            document.getElementById('vPerKm').value = vehicle.fare_per_km || 0;
            document.getElementById('originLat').value = originCity.latitude;
            document.getElementById('originLon').value = originCity.longitude;

            // Set Passenger Max Limits (Dynamic per vehicle)
            const type = vehicle.type || 'Car';
            let maxPax = 5;
            if (type === 'SUV') maxPax = 7;
            if (type === 'Bus') maxPax = 56;

            const paxInput = document.getElementById('paxInput');
            paxInput.max = maxPax;
            paxInput.value = 1; // Reset to 1
            document.getElementById('maxPaxDisplay').innerText = maxPax;

            // Destinations
            const destSelect = document.getElementById('destSelect');
            destSelect.innerHTML = '<option value="">-- Select Destination --</option>';
            
            allLocations.forEach(loc => {
                const locId = loc._id || loc.id;
                if (loc.division === originCity.division && locId !== oId) {
                    let option = document.createElement('option');
                    option.value = locId;
                    option.text = loc.city;
                    option.dataset.lat = loc.latitude;
                    option.dataset.lon = loc.longitude;
                    destSelect.appendChild(option);
                }
            });

            calculateFare();
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function calculateFare() {
            const destSelect = document.getElementById('destSelect');
            const selectedOpt = destSelect.options[destSelect.selectedIndex];

            if (!selectedOpt.value) {
                document.getElementById('displayEst').innerText = "0.00";
                document.getElementById('displayDist').innerText = "0";
                return;
            }

            // 1. Get Distance
            const lat1 = parseFloat(document.getElementById('originLat').value);
            const lon1 = parseFloat(document.getElementById('originLon').value);
            const lat2 = parseFloat(selectedOpt.dataset.lat);
            const lon2 = parseFloat(selectedOpt.dataset.lon);

            const R = 6371; 
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            const distance = R * c;

            // 2. Get Pricing Base
            const base = parseFloat(document.getElementById('vBasePrice').value);
            const perKm = parseFloat(document.getElementById('vPerKm').value);
            
            // 3. Apply Passenger Logic (Matches Controller)
            // Logic: 20% increase for every extra passenger
            const pax = parseInt(document.getElementById('paxInput').value) || 1;
            const extraPerPassenger = 0.005; 
            const multiplier = 1 + (extraPerPassenger * (pax - 1));
            const adjustedRate = perKm * multiplier;

            const total = base + (distance * adjustedRate);

            document.getElementById('displayDist').innerText = distance.toFixed(1);
            document.getElementById('displayEst').innerText = total.toFixed(2);
        }

        window.onclick = function(e) {
            if (e.target == document.getElementById('bookingModal')) closeBookingModal();
        }
    </script>
</body>
</html>