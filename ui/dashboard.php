<?php
require_once 'config.php';
requireLogin(); // Protects this page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AeroNexa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>System Dashboard</h3>
            <span>Welcome, <?= htmlspecialchars($_SESSION['user']['first_name'] ?? 'User') ?></span>
        </header>

        <main class="content-area">
            <div class="card">
                <h2>Welcome Back</h2>
                <p>You are logged in as <strong><?= htmlspecialchars($_SESSION['user']['email']) ?></strong>.</p>
                <p style="margin-top: 10px; color: #64748b;">Select a module from the sidebar to manage data.</p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div class="card" style="border-top: 4px solid #3b82f6;">
                    <h4>PSA Flights</h4>
                    <p style="color: #64748b; margin-top: 5px;">Manage schedules & bookings</p>
                </div>
                <div class="card" style="border-top: 4px solid #10b981;">
                    <h4>Aeropay</h4>
                    <p style="color: #64748b; margin-top: 5px;">Monitor global transactions</p>
                </div>
                <div class="card" style="border-top: 4px solid #f59e0b;">
                    <h4>Aureliya</h4>
                    <p style="color: #64748b; margin-top: 5px;">Hotel & Property listings</p>
                </div>
            </div>
        </main>
    </div>

</body>
</html>