<?php
require_once '../config.php';
requireLogin();

$userId = $_SESSION['user']['id'] ?? $_SESSION['user']['_id'];
$userName = $_SESSION['user']['first_name'] . ' ' . ($_SESSION['user']['last_name'] ?? '');

$message = '';
$messageType = '';

// --- 1. HANDLE BOOKING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    
    // We expect passenger_id (MongoDB ID) and passenger_name from the form
    $bookingPayload = [
        'user_id'          => $userId,
        'package_id'       => $_POST['package_id'],
        'travel_date'      => $_POST['travel_date'],
        'passenger_name'   => $_POST['passenger_name'], // Name Text
        'passenger_id'     => $_POST['passenger_id'],   // Database ID (from PSA)
        'passenger_amount' => $_POST['passenger_amount'] ?? 1,
    ];

    $response = callApi('POST', TRUTRAVEL_API . '/trutravel/bookings', $bookingPayload);

    if ((isset($response['status']) && in_array($response['status'], [200, 201])) || isset($response['message'])) {
        $message = "Package booked successfully!";
        $messageType = 'success';
    } else {
        $apiError = $response['data']['message'] ?? $response['data']['error'] ?? 'Unknown error';
        $message = "Booking failed: " . $apiError;
        $messageType = 'error';
    }
}

// --- 2. FETCH PACKAGES ---
$pResponse = callApi('GET', TRUTRAVEL_API . '/trutravel/packages');
$packages = $pResponse['data'] ?? (is_array($pResponse) ? $pResponse : []);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vacation Packages - TruTravel</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* (Same CSS as before) */
        .package-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); }
        .package-card { background: white; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; display: flex; flex-direction: column; transition: 0.2s; }
        .package-card:hover { transform: translateY(-3px); border-color: #f59e0b; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        .pkg-img { height: 180px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 3rem; }
        .pkg-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .pkg-title { font-weight: bold; font-size: 1.15rem; color: #1e293b; margin-bottom: 5px; }
        .pkg-price { font-size: 1.25rem; font-weight: 800; color: #f59e0b; }
        
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 25px; border-radius: 12px; width: 95%; max-width: 500px; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>TruTravel Packages</h3>
            <span><?= htmlspecialchars($userName) ?></span>
        </header>

        <main class="content-area">
            <?php if ($message): ?>
                <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-error' ?>" style="padding: 15px; margin-bottom: 20px; background: <?= $messageType=='success'?'#ecfccb':'#fee2e2' ?>; color: <?= $messageType=='success'?'#3f6212':'#991b1b' ?>; border-radius: 8px;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="package-grid">
                <?php foreach ($packages as $pkg): 
                    $price = $pkg['final_price'] ?? 0;
                    $nights = $pkg['nights'] ?? 3;
                ?>
                    <div class="package-card">
                        <div class="pkg-img">✈️</div>
                        <div class="pkg-body">
                            <div class="pkg-title"><?= htmlspecialchars($pkg['name']) ?></div>
                            <div style="font-size:0.9rem; color:#64748b; margin-bottom:15px; flex:1;">
                                <?= htmlspecialchars(mb_strimwidth($pkg['description'], 0, 100, "...")) ?>
                            </div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; border-top:1px dashed #e2e8f0; padding-top:15px;">
                                <div>
                                    <div class="pkg-price">₱<?= number_format($price) ?></div>
                                    <small><?= $nights ?> Nights</small>
                                </div>
                                <button class="btn-primary" style="background:#f59e0b;" onclick='openBookingModal(<?= json_encode($pkg) ?>)'>Book</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Confirm Booking</h3>
            
            <form method="POST" action="packages.php">
                <input type="hidden" name="action" value="create_booking">
                <input type="hidden" name="package_id" id="inputPackageId">
                <input type="hidden" id="basePrice">
                
                <input type="hidden" name="passenger_name" id="inputPassengerName">

                <div style="background:#f8fafc; padding:15px; border-radius:8px; margin:15px 0;">
                    <strong id="displayName"></strong><br>
                    <small id="displayDesc"></small>
                </div>

                <div class="form-group">
                    <label>Travel Start Date</label>
                    <input type="date" name="travel_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group">
                    <label>Select Main Passenger</label>
                    <select name="passenger_id" id="passengerSelect" class="form-control" required onchange="updatePassengerName()">
                        <option value="">-- Loading Passengers... --</option>
                    </select>
                    <div style="font-size:0.8rem; color:#64748b; margin-top:5px;">
                        Data fetched from your PSA profile.
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Travelers</label>
                    <input type="number" name="passenger_amount" id="paxInput" class="form-control" 
                           value="1" min="1" max="10" required onchange="calculateTotal()" onkeyup="calculateTotal()">
                </div>

                <div style="margin-top:15px; text-align:center;">
                    Total: <strong style="font-size:1.4rem; color:#b45309;">₱<span id="displayTotal">0.00</span></strong>
                </div>

                <button type="submit" class="btn-primary" style="width:100%; margin-top:15px; background:#f59e0b;">Confirm</button>
                <button type="button" onclick="closeBookingModal()" style="width:100%; margin-top:10px; background:none; border:none; cursor:pointer; color:#64748b;">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        const userId = "<?= $userId ?>";
        const psaApiUrl = "<?= PSA_API ?>"; // defined in config.php e.g. http://localhost:8000/api

        function openBookingModal(pkg) {
            document.getElementById('bookingModal').style.display = 'flex';
            document.getElementById('inputPackageId').value = pkg._id || pkg.id;
            document.getElementById('basePrice').value = pkg.final_price || 0;
            document.getElementById('displayName').innerText = pkg.name;
            document.getElementById('displayDesc').innerText = pkg.description;
            document.getElementById('paxInput').value = 1;
            
            calculateTotal();
            fetchPassengers(); // Load passengers dynamically
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        function calculateTotal() {
            const base = parseFloat(document.getElementById('basePrice').value) || 0;
            const pax = parseInt(document.getElementById('paxInput').value) || 1;
            document.getElementById('displayTotal').innerText = (base * pax).toLocaleString('en-US', {minimumFractionDigits: 2});
        }

        // FETCH PASSENGERS FROM PSA
        async function fetchPassengers() {
            const select = document.getElementById('passengerSelect');
            select.innerHTML = '<option value="">-- Loading... --</option>';

            try {
                // Call PSA API: GET /psa/passengers/user/{id}
                const res = await fetch(`${psaApiUrl}/psa/passengers/user/${userId}`);
                const data = await res.json();

                select.innerHTML = '<option value="">-- Select Passenger --</option>';

                // Handle array response or {data: [...]}
                const list = Array.isArray(data) ? data : (data.data || []);

                if (list.length === 0) {
                    select.innerHTML = '<option value="">No passengers found. Create one in PSA first.</option>';
                    return;
                }

                list.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p._id || p.id;
                    // Format: Name (Passport: XXX)
                    opt.text = `${p.first_name} ${p.last_name} (Passport: ${p.passport_number})`;
                    // Store name in dataset for easy retrieval
                    opt.dataset.name = `${p.first_name} ${p.last_name}`;
                    select.appendChild(opt);
                });

            } catch (err) {
                console.error(err);
                select.innerHTML = '<option value="">Error loading passengers</option>';
            }
        }

        // When dropdown changes, update the hidden name field
        function updatePassengerName() {
            const select = document.getElementById('passengerSelect');
            const selectedOpt = select.options[select.selectedIndex];
            const nameField = document.getElementById('inputPassengerName');
            
            if (selectedOpt && selectedOpt.dataset.name) {
                nameField.value = selectedOpt.dataset.name;
            } else {
                nameField.value = "";
            }
        }
    </script>

</body>
</html>