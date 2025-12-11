<?php
require_once '../config.php';
requireLogin();

$userId = $_SESSION['user']['id'];
$message = '';
$messageType = '';

// --- 1. HANDLE BOOKING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    $bookingPayload = [
        'user_id'      => $userId,
        'property_id'  => $_POST['property_id'],
        'check_in'     => $_POST['check_in'],
        'check_out'    => $_POST['check_out'],
        'payment_method' => 'AEROPAY'
    ];

    $response = callApi('POST', AURELIYA_API . '/aureliya/bookings', $bookingPayload);

    if ($response['status'] === 201 || $response['status'] === 200) {
        $message = "Reservation confirmed! AeroPay transaction successful.";
        $messageType = 'success';
    } else {
        $apiError = $response['data']['message'] ?? 'Unknown error';
        $message = "Booking failed: " . $apiError;
        $messageType = 'error';
    }
}

// --- 2. FETCH COUNTRIES & PROPERTIES ---

// A. Get List of Countries for Dropdown
$countriesResponse = callApi('GET', AURELIYA_API . '/aureliya/countries');
$countries = ($countriesResponse['status'] === 200) ? $countriesResponse['data'] : [];

// B. Get Properties (Only if country selected)
$selectedCountry = $_GET['country'] ?? '';
$properties = [];

if (!empty($selectedCountry)) {
    $url = AURELIYA_API . '/aureliya/properties';
    $response = callApi('GET', $url, ['country' => $selectedCountry]);
    $properties = ($response['status'] === 200) ? $response['data'] : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Properties - AeroNexa</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .property-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; }
        .property-card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s; display: flex; flex-direction: column; }
        .property-card:hover { transform: translateY(-5px); }
        .property-img { height: 180px; background-color: #cbd5e1; display: flex; align-items: center; justify-content: center; color: #64748b; }
        .property-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .property-title { font-size: 1.1rem; font-weight: bold; color: #1e293b; margin-bottom: 5px; }
        .property-loc { color: #64748b; font-size: 0.9rem; margin-bottom: 10px; }
        .property-price { font-size: 1.25rem; font-weight: bold; color: #e67e22; margin-top: auto; }
        
        /* Modal */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; }
        .close-btn { float: right; cursor: pointer; font-size: 1.5rem; color: #94a3b8; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>Aureliya Properties</h3>
            <span><?= htmlspecialchars($_SESSION['user']['first_name']) ?></span>
        </header>

        <main class="content-area">

            <?php if ($message): ?>
                <div style="padding: 15px; margin-bottom: 20px; border-radius: 6px; background: <?= $messageType == 'success' ? '#dcfce7' : '#fee2e2' ?>; color: <?= $messageType == 'success' ? '#166534' : '#991b1b' ?>;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card" style="margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: #334155;">Where do you want to stay?</h4>
                <form method="GET" action="properties.php" style="display: flex; gap: 10px;">
                    <select name="country" class="form-control" style="max-width: 300px;" required>
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?= $selectedCountry == $c ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-primary" style="width: auto; padding: 0 25px;">Find Properties</button>
                    <?php if ($selectedCountry): ?>
                        <a href="properties.php" style="display: flex; align-items: center; padding: 0 15px; color: #64748b; text-decoration: none;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="property-grid">
                <?php if (empty($selectedCountry)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px; background: #f8fafc; border-radius: 8px; border: 2px dashed #e2e8f0;">
                        Please select a country to view available properties.
                    </div>
                <?php elseif (empty($properties)): ?>
                    <div style="grid-column: 1/-1; text-align: center; color: #94a3b8; padding: 40px;">
                        No properties found in <?= htmlspecialchars($selectedCountry) ?>.
                    </div>
                <?php else: ?>
                    <?php foreach ($properties as $prop): ?>
                        <div class="property-card">
                            <div class="property-img">
                                <svg width="50" height="50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            </div>
                            <div class="property-body">
                                <div class="property-title"><?= htmlspecialchars($prop['name'] ?? $prop['title']) ?></div>
                                <div class="property-loc">📍 <?= htmlspecialchars($prop['city'] ?? $prop['address']) ?>, <?= htmlspecialchars($prop['country']) ?></div>
                                <div style="display: flex; justify-content: space-between; align-items: end; margin-top: auto;">
                                    <div class="property-price">₱<?= number_format($prop['price_per_night'], 2) ?> <span>/night</span></div>
                                    <button class="btn-primary" style="width: auto; padding: 8px 16px;" 
                                        onclick='openBookingModal("<?= $prop['_id'] ?? $prop['id'] ?>", "<?= htmlspecialchars($prop['name'] ?? $prop['title']) ?>", <?= $prop['price_per_night'] ?>)'>
                                        Book
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </main>
    </div>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="closeBookingModal()">&times;</span>
            <h3 style="margin-bottom: 20px;">Reserve Your Stay</h3>
            <form method="POST" action="properties.php">
                <input type="hidden" name="action" value="create_booking">
                <input type="hidden" name="property_id" id="inputPropId">
                <input type="hidden" id="inputPricePerNight">

                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <strong style="display: block; margin-bottom: 5px;" id="modalTitle"></strong>
                    <div style="color: #64748b;">₱<span id="displayPrice"></span> per night</div>
                </div>

                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <label>Check-In</label>
                        <input type="date" name="check_in" id="checkIn" class="form-control" required onchange="calculateTotal()">
                    </div>
                    <div style="flex: 1;">
                        <label>Check-Out</label>
                        <input type="date" name="check_out" id="checkOut" class="form-control" required onchange="calculateTotal()">
                    </div>
                </div>

                <div style="padding: 15px; background: #fff7ed; border-radius: 8px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; font-weight: bold;">
                        <span>Total:</span>
                        <span style="color: #ea580c;">₱<span id="billTotal">0.00</span></span>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="background-color: #e67e22;">Confirm Reservation</button>
            </form>
        </div>
    </div>

    <script>
        function openBookingModal(id, title, price) {
            document.getElementById('bookingModal').style.display = 'flex';
            document.getElementById('inputPropId').value = id;
            document.getElementById('inputPricePerNight').value = price;
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('displayPrice').innerText = price.toLocaleString('en-US', {minimumFractionDigits: 2});
            calculateTotal();
        }
        function closeBookingModal() { document.getElementById('bookingModal').style.display = 'none'; }
        function calculateTotal() {
            const checkIn = new Date(document.getElementById('checkIn').value);
            const checkOut = new Date(document.getElementById('checkOut').value);
            const price = parseFloat(document.getElementById('inputPricePerNight').value);
            if (checkIn && checkOut && checkOut > checkIn) {
                const diffDays = Math.ceil(Math.abs(checkOut - checkIn) / (1000 * 60 * 60 * 24));
                document.getElementById('billTotal').innerText = (diffDays * price).toLocaleString('en-US', {minimumFractionDigits: 2});
            } else {
                document.getElementById('billTotal').innerText = "0.00";
            }
        }
    </script>
</body>
</html>