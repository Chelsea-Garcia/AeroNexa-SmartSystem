<?php
// ui/psa/index.php
require_once '../config.php';
requireLogin();

// 1. Fetch Data for Widgets
// Note: We use the API port 8000 as defined in config.php
$flightsResponse = callApi('GET', PSA_API . '/psa/flights');
$airportsResponse = callApi('GET', PSA_API . '/psa/airports');

$totalFlights = is_array($flightsResponse['data']) ? count($flightsResponse['data']) : 0;
$totalAirports = is_array($airportsResponse['data']) ? count($airportsResponse['data']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PSA Overview - AeroNexa</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            text-align: center;
            border-bottom: 4px solid var(--accent);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary-bg);
            margin: 10px 0;
        }
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .action-btn {
            display: block;
            padding: 20px;
            background: white;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 600;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .action-btn:hover {
            transform: translateY(-3px);
            border-left: 5px solid var(--accent);
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>PSA - Philippine Sky Airway</h3>
            <span>Administrator</span>
        </header>

        <main class="content-area">
            
            <div class="stat-grid">
                <div class="stat-card">
                    <div style="color: #64748b;">Scheduled Flights</div>
                    <div class="stat-number"><?= $totalFlights ?></div>
                </div>
                <div class="stat-card" style="border-bottom-color: #10b981;">
                    <div style="color: #64748b;">Serviced Airports</div>
                    <div class="stat-number"><?= $totalAirports ?></div>
                </div>
                <div class="stat-card" style="border-bottom-color: #f59e0b;">
                    <div style="color: #64748b;">Total Passengers</div>
                    <div class="stat-number">-</div>
                </div>
            </div>

            <h4 style="margin-bottom: 15px; color: #475569;">Management</h4>
            <div class="action-grid">
                <a href="flights.php" class="action-btn">
                    ✈️ View All Flights
                </a>
                <a href="#" class="action-btn">
                    📅 Manage Bookings
                </a>
                <a href="#" class="action-btn">
                    🏢 View Airports
                </a>
            </div>

        </main>
    </div>

</body>
</html>