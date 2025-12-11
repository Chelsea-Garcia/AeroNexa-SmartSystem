<?php
require_once 'config.php';

// If already logged in, go to dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Call your Laravel API Login Endpoint
    // Route defined in api.php: Route::prefix('aeronexa')->post('users/login', ...)
    $response = callApi('POST', AERONEXA_API . '/aeronexa/users/login', [
        'email' => $email,
        'password' => $password
    ]);

    // 2. Check response
    if ($response['status'] === 200) {
        // Success: The API returns the user object (UserController.php::login)
        $_SESSION['user'] = $response['data'];
        header('Location: dashboard.php');
        exit();
    } else {
        // Error: Show message from API (e.g., "Invalid credentials")
        $error = $response['data']['message'] ?? 'Login failed. Please check your connection.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AeroNexa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h2>AeroNexa SmartSystem</h2>
        
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.9rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="index.php" method="POST"> 
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="admin@aeronexa.com" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
        </form>
    </div>

</body>
</html>