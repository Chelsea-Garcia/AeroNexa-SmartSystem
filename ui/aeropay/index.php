<?php
// ui/aeropay/index.php
require_once '../config.php';
requireLogin();

$userId = $_SESSION['user']['id'];

// 1. Fetch User Transactions from AeroPay Service (Port 8001)
// Route: GET /aeropay/transactions/user/{user_id}
$url = AEROPAY_API . '/aeropay/transactions/user/' . $userId;
$response = callApi('GET', $url);

$transactions = [];
$error = '';

if ($response['status'] === 200) {
    $transactions = $response['data'];
} else {
    $error = "Could not fetch transactions. Ensure AeroPay service (Port 8001) is running.";
}

// Calculate Total Spent for the widget
$totalSpent = 0;
foreach ($transactions as $t) {
    if (($t['status'] ?? '') === 'paid') {
        $totalSpent += ($t['amount'] ?? 0);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Transactions - AeroNexa</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-paid { background: #dcfce7; color: #15803d; }
        .status-pending { background: #fef9c3; color: #a16207; }
        .status-failed { background: #fee2e2; color: #b91c1c; }
        .status-cancelled { background: #f1f5f9; color: #64748b; }
        
        .amount-positive { font-weight: bold; color: #0f172a; }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <header class="top-bar">
            <h3>AeroPay Wallet</h3>
            <span><?= htmlspecialchars($_SESSION['user']['first_name']) ?></span>
        </header>

        <main class="content-area">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                    <div style="font-size: 0.9rem; opacity: 0.9;">Total Spent</div>
                    <div style="font-size: 2.5rem; font-weight: bold;">₱<?= number_format($totalSpent, 2) ?></div>
                    <div style="margin-top: 10px; font-size: 0.85rem; opacity: 0.8;">Lifetime Transactions</div>
                </div>
                
                <div class="card">
                    <div style="color: #64748b; font-size: 0.9rem;">Recent Activity</div>
                    <div style="font-size: 1.8rem; font-weight: bold; color: #334155;">
                        <?= count($transactions) ?>
                    </div>
                    <div style="color: #64748b; font-size: 0.85rem;">Transactions Found</div>
                </div>
            </div>

            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="card" style="padding: 0; overflow: hidden;">
                <div style="padding: 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                    <h4 style="margin: 0; color: #1e293b;">Transaction History</h4>
                    <button class="btn-primary" style="width: auto; padding: 5px 15px; font-size: 0.85rem;" onclick="location.reload()">Refresh</button>
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8fafc;">
                        <tr>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.9rem;">Reference Code</th>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.9rem;">Service</th>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.9rem;">Date</th>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.9rem;">Amount</th>
                            <th style="padding: 15px; text-align: left; color: #475569; font-size: 0.9rem;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="5" style="padding: 30px; text-align: center; color: #94a3b8;">
                                    No transactions found for this user.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $t): 
                                // Format Date
                                $date = date('M d, Y h:i A', strtotime($t['created_at']));
                                
                                // Determine Status Color
                                $status = strtolower($t['status'] ?? 'pending');
                                $badgeClass = match($status) {
                                    'paid', 'completed' => 'status-paid',
                                    'failed' => 'status-failed',
                                    'cancelled' => 'status-cancelled',
                                    default => 'status-pending'
                                };
                            ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 15px;">
                                        <span style="font-family: monospace; color: #334155; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
                                            <?= htmlspecialchars($t['transaction_code']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px;">
                                        <strong><?= htmlspecialchars($t['partner'] ?? 'Unknown') ?></strong>
                                    </td>
                                    <td style="padding: 15px; color: #64748b;">
                                        <?= $date ?>
                                    </td>
                                    <td style="padding: 15px;" class="amount-positive">
                                        ₱<?= number_format($t['amount'], 2) ?>
                                    </td>
                                    <td style="padding: 15px;">
                                        <span class="status-badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($t['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>
</html>