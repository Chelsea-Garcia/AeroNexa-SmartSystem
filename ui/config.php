<?php
session_start();

define('AERONEXA_API', 'http://127.0.0.1:8006/api');
define('PSA_API', 'http://127.0.0.1:8000/api');
define('AEROPAY_API', 'http://127.0.0.1:8001/api');
define('AURELIYA_API', 'http://127.0.0.1:8002/api');
define('SKYROUTE_API', 'http://127.0.0.1:8003/api');
define('TRUTRAVEL_API', 'http://127.0.0.1:8004/api');

// 2. UI Base URL Auto-Detection
// This calculates the web path to the 'ui' folder (e.g., /FinalProject/AeroNexa-SmartSystem/ui)
$projectDir = str_replace('\\', '/', __DIR__); // Get current directory of config.php
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']); // Get server root
$basePath = str_replace($docRoot, '', $projectDir); // Subtract root from project

define('UI_BASE_URL', $basePath);

// 3. Helper function to call the API
function callApi($method, $url, $data = false) {
    $curl = curl_init();

    switch ($method) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ['data' => json_decode($result, true), 'status' => $httpCode];
}

// 4. Require Login Helper
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        // Redirect to login page using the absolute base URL
        header('Location: ' . UI_BASE_URL . '/index.php');
        exit();
    }
}
?>