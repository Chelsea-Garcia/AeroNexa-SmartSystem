<?php
require_once '../config.php';
requireLogin();

$userId = $_SESSION['user']['id'] ?? $_SESSION['user']['_id'];
$userName = $_SESSION['user']['first_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TruTravel Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        .hero-section h2 { margin: 0 0 10px 0; font-size: 2rem; }
        .hero-section p { opacity: 0.9; font-size: 1.1rem; }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .dash-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: transform 0.2s, border-color 0.2s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
        }
        .dash-card:hover {
            transform: translateY(-5px);
            border-color: #f59e0b;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .card-icon { font-size: 3rem; margin-bottom: 15px; }
        .card-title { font-weight: bold; font-size: 1.2rem; color: #1e293b; margin-bottom: 8px; }
        .card-desc { font-size: 0.9rem; color: #64748b; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        
        <div class="hero-section">
            <h2>Welcome back, <?= htmlspecialchars($userName) ?>!</h2>
            <p>Ready to explore the world? Plan your next adventure with TruTravel.</p>
        </div>

        <div class="dashboard-grid">
            
            <a href="packages.php" class="dash-card">
                <div class="card-icon">🏝️</div>
                <div class="card-title">Browse Packages</div>
                <div class="card-desc">Explore flight + hotel bundles</div>
            </a>

            <a href="my_bookings.php" class="dash-card">
                <div class="card-icon">📅</div>
                <div class="card-title">My Bookings</div>
                <div class="card-desc">View your active trips and history</div>
            </a>

            <a href="../psa/passengers.php" class="dash-card">
                <div class="card-icon">🛂</div>
                <div class="card-title">My Passengers</div>
                <div class="card-desc">Manage saved passenger details</div>
            </a>

        </div>

    </div>

</body>
</html>