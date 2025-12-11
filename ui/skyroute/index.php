<?php
require_once '../config.php';
requireLogin();

// Fetch all locations to count them
$locResponse = callApi('GET', SKYROUTE_API . '/skyroute/locations');
$locations = ($locResponse['status'] === 200) ? $locResponse['data'] : [];
$totalLocations = count($locations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SkyRoute Overview - AeroNexa</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .stat-card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid #8b5cf6; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #1e293b; margin: 10px 0; }
        .action-btn { display: block; padding: 20px; background: white; border-radius: 8px; color: #334155; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: 0.2s; }
        .action-btn:hover { transform: translateY(-3px); border-left: 5px solid #8b5cf6; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>SkyRoute - Transport</h3>
            <span>Administrator</span>
        </header>

        <main class="content-area">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="stat-card">
                    <div style="color: #64748b;">Service Locations</div>
                    <div class="stat-number"><?= $totalLocations ?></div>
                </div>
                <div class="stat-card" style="border-bottom-color: #10b981;">
                    <div style="color: #64748b;">Active Trips</div>
                    <div class="stat-number">-</div>
                </div>
            </div>

            <h4 style="margin-bottom: 15px; color: #475569;">Management</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                <a href="vehicles.php" class="action-btn">🚖 Book a Ride</a>
                <a href="#" class="action-btn">🗺️ Manage Locations</a>
            </div>
        </main>
    </div>
</body>
</html>