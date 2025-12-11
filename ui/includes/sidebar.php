<?php
// Helper to highlight active link
if (!function_exists('isActive')) {
    function isActive($path) {
        // Check if the current URL contains the specific path keyword
        return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'active' : '';
    }
}
?>
<nav class="sidebar">
    <div class="brand">AeroNexa UI</div>
    
    <ul class="nav-links">
        <li>
            <a href="<?= UI_BASE_URL ?>/dashboard.php" class="nav-item <?= isActive('dashboard.php') ?>">
                Dashboard
            </a>
        </li>

        <li class="nav-category">PSA (Flights)</li>
        <li>
            <a href="<?= UI_BASE_URL ?>/psa/index.php" class="nav-item <?= isActive('/psa/') ?>">
                Overview
            </a>
        </li>
        <li>
            <a href="<?= UI_BASE_URL ?>/psa/flights.php" class="nav-item <?= isActive('flights.php') ?>">
                Flights
            </a>
        </li>
        
        <li class="nav-category">Aeropay</li>
        <li>
            <a href="<?= UI_BASE_URL ?>/aeropay/index.php" class="nav-item <?= isActive('/aeropay/') ?>">
                Transactions
            </a>
        </li>

        <li class="nav-category">Aureliya</li>
        <li>
            <a href="<?= UI_BASE_URL ?>/aureliya/index.php" class="nav-item <?= isActive('/aureliya/') ?>">
                Overview
            </a>
        </li>
        <li>
            <a href="<?= UI_BASE_URL ?>/aureliya/properties.php" class="nav-item <?= isActive('properties.php') ?>">
                Properties
            </a>
        </li>

        <li class="nav-category">Skyroute</li>
        <li>
            <a href="<?= UI_BASE_URL ?>/skyroute/index.php" class="nav-item <?= isActive('/skyroute/') ?>">
                Overview
            </a>
        </li>
        <li>
            <a href="<?= UI_BASE_URL ?>/skyroute/vehicles.php" class="nav-item <?= isActive('vehicles.php') ?>">
                Vehicles
            </a>
        </li>

        <li class="nav-category">TruTravel</li>
        <li>
            <a href="<?= UI_BASE_URL ?>/trutravel/index.php" class="nav-item <?= isActive('/trutravel/') ?>">
                Overview
            </a>
        </li>
        <li>
            <a href="<?= UI_BASE_URL ?>/trutravel/packages.php" class="nav-item <?= isActive('packages.php') ?>">
                Packages
            </a>
        </li>
        
        <li class="nav-category">Admin</li>
        <li>
            <a href="<?= UI_BASE_URL ?>/logout.php" class="nav-item">
                Logout
            </a>
        </li>
    </ul>
</nav>