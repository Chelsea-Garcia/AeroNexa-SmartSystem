<?php
// ui/psa/flights.php
require_once '../config.php';
requireLogin();

$userId = $_SESSION['user']['id'] ?? 0;
$message = '';
$messageType = '';

// --- 1. HANDLE BOOKING SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_booking') {
    
    // Data strictly matching Psa/BookingController validation
    $bookingPayload = [
        'user_id'      => $userId,
        'passenger_id' => $_POST['passenger_id'],
        'flight_id'    => $_POST['flight_id'],
        'flight_date'  => $_POST['flight_date'], // User input date
        'payment_method' => $_POST['payment_method'] ?? 'AEROPAY',
    ];

    $response = callApi('POST', PSA_API . '/psa/bookings', $bookingPayload);

    if ($response['status'] === 201 || $response['status'] === 200) {
        $message = "Booking confirmed! Transaction processed via AeroPay.";
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

// --- 2. FETCH DATA ---
$passengersResponse = callApi('GET', PSA_API . '/psa/passengers/user/' . $userId);
$passengers = $passengersResponse['data'] ?? [];

$origin = $_GET['origin'] ?? '';
$destination = $_GET['destination'] ?? '';
$flights = [];

if (!empty($origin) || !empty($destination)) {
    $url = PSA_API . '/psa/flights/search';
    $params = ['origin' => $origin, 'destination' => $destination];
    $response = callApi('GET', $url, $params);
} else {
    $response = callApi('GET', PSA_API . '/psa/flights');
}

$flights = ($response['status'] === 200) ? $response['data'] : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Flights - AeroNexa</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .modal-overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 480px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); position: relative; animation: slideDown 0.3s ease;
        }
        @keyframes slideDown { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        .close-btn { position: absolute; top: 20px; right: 20px; font-size: 1.5rem; cursor: pointer; color: #94a3b8; }
        .modal-header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; }
        .flight-summary { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.95rem; color: #475569; }
        .summary-row.total { font-weight: bold; color: #1e293b; border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 8px; font-size: 1.1rem; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>PSA - Flight Booking</h3>
            <span><?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'Admin') ?></span>
        </header>

        <main class="content-area">
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form class="card" style="flex-direction: row; gap: 15px; align-items: end; display: flex;" method="GET">
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-size:0.9rem;">Origin</label>
                    <input type="text" name="origin" class="form-control" placeholder="Code (e.g. MNL)" value="<?= htmlspecialchars($origin) ?>">
                </div>
                <div style="flex: 1;">
                    <label style="display:block; margin-bottom:5px; font-size:0.9rem;">Destination</label>
                    <input type="text" name="destination" class="form-control" placeholder="Code (e.g. CEB)" value="<?= htmlspecialchars($destination) ?>">
                </div>
                <button type="submit" class="btn-primary" style="width: auto;">Search</button>
            </form>

            <div class="card" style="padding: 0; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f1f5f9;">
                        <tr>
                            <th style="padding: 15px; text-align: left; color: #475569;">Flight</th>
                            <th style="padding: 15px; text-align: left; color: #475569;">Route</th>
                            <th style="padding: 15px; text-align: left; color: #475569;">Schedule</th>
                            <th style="padding: 15px; text-align: left; color: #475569;">Price</th>
                            <th style="padding: 15px; text-align: left; color: #475569;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($flights)): ?>
                            <tr><td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8;">No flights available.</td></tr>
                        <?php else: ?>
                            <?php foreach ($flights as $flight): 
                                // Parse Times
                                $depTime = date('H:i', strtotime($flight['departure_time']));
                                $arrTime = date('H:i', strtotime($flight['arrival_time']));
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 15px;">
                                        <strong><?= htmlspecialchars($flight['flight_number']) ?></strong><br>
                                        <small style="color:#94a3b8;"><?= htmlspecialchars($flight['aircraft_model'] ?? 'Standard') ?></small>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span style="color:#3b82f6; font-weight:600;"><?= htmlspecialchars($flight['origin']) ?></span> 
                                        <span style="color:#94a3b8;">&rarr;</span> 
                                        <span style="color:#3b82f6; font-weight:600;"><?= htmlspecialchars($flight['destination']) ?></span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <?= $depTime ?> - <?= $arrTime ?>
                                    </td>
                                    <td style="padding: 15px; font-weight: bold; color: #0f172a;">
                                        ₱<?= number_format($flight['price'], 2) ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <button 
                                            class="btn-primary" 
                                            style="padding: 8px 20px; font-size: 0.85rem;"
                                            onclick='openBookingModal(
                                                "<?= $flight['id'] ?>", 
                                                "<?= htmlspecialchars($flight['flight_number']) ?>", 
                                                "<?= $flight['price'] ?>", 
                                                "<?= $depTime ?>", 
                                                "<?= $arrTime ?>",
                                                "<?= htmlspecialchars($flight['origin']) ?>",
                                                "<?= htmlspecialchars($flight['destination']) ?>"
                                            )'
                                        >
                                            Book
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="bookingModal" class="modal-overlay">
        <div class="modal-content">
            <span class="close-btn" onclick="closeBookingModal()">&times;</span>
            
            <div class="modal-header">
                <h3>Confirm Booking</h3>
                <p style="color: #64748b; font-size: 0.9rem;">Fill in the details to proceed.</p>
            </div>

            <form method="POST" action="flights.php">
                <input type="hidden" name="action" value="create_booking">
                <input type="hidden" name="flight_id" id="inputFlightId">

                <div class="flight-summary">
                    <div class="summary-row">
                        <span>Flight:</span>
                        <strong id="modalFlightNum"></strong>
                    </div>
                    <div class="summary-row">
                        <span>Route:</span>
                        <span id="modalRoute"></span>
                    </div>
                    <div class="summary-row">
                        <span>Time:</span>
                        <span id="modalTime"></span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span style="color: #16a34a;">₱<span id="modalPrice"></span></span>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #334155;">Travel Date</label>
                    <input type="date" name="flight_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #334155;">Select Passenger</label>
                    <select name="passenger_id" class="form-control" required>
                        <option value="">-- Select a Passenger --</option>
                        <?php foreach ($passengers as $p): ?>
                            <option value="<?= $p['id'] ?>">
                                <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(empty($passengers)): ?>
                        <small style="color: #ef4444; display: block; margin-top: 5px;">
                            No passengers found. <a href="#">Register a passenger</a> first.
                        </small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #334155;">Payment Method</label>
                    <select name="payment_method" class="form-control" style="background-color: #f1f5f9;" readonly>
                        <option value="AEROPAY" selected>AeroPay (Instant)</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="padding: 12px; font-size: 1rem; margin-top: 10px; background-color: #16a34a;">
                    Confirm & Pay
                </button>
            </form>
        </div>
    </div>

    <script>
        function openBookingModal(id, flightNum, price, depTime, arrTime, origin, destination) {
            let modal = document.getElementById('bookingModal');
            
            // Set Hidden ID
            document.getElementById('inputFlightId').value = id;

            // Set Visuals
            document.getElementById('modalFlightNum').innerText = flightNum;
            document.getElementById('modalRoute').innerText = origin + ' -> ' + destination;
            document.getElementById('modalTime').innerText = depTime + ' - ' + arrTime; // Showing Time Only
            document.getElementById('modalPrice').innerText = parseFloat(price).toFixed(2);

            // Show Modal
            modal.style.display = 'flex';
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
        }

        window.onclick = function(e) {
            if (e.target == document.getElementById('bookingModal')) {
                closeBookingModal();
            }
        }
    </script>

</body>
</html>