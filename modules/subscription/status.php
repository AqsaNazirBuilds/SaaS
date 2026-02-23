<?php
// modules/subscription/status.php

// 1. Database aur Models ko link karna
require_once(__DIR__ . '/../../config/db.php'); 
require_once(__DIR__ . '/subscription.php');
require_once(__DIR__ . '/../audit/audit.php'); // Phase 1: Step 6 ka Audit System

$sub_logic = new Subscription($db);
$audit_obj = new AuditLog($db);

// 2. Data Fetch Karna (Testing ke liye Tenant 1 aur User 1)
$my_sub = $sub_logic->get_active_subscription(1);
$is_active = $sub_logic->is_valid($my_sub);

// 3. Activity Record Karna (Professional monitoring)
$audit_obj->log(1, 1, 'Checked Subscription Status', 'Subscription');

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Dashboard | Saas Project</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/laiba/status.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="main-wrapper">
    <div class="status-card">
        <div class="card-header">
            <div class="header-left">
                <h2>Manage Your Business</h2>
                <p>Track your subscription & features</p>
            </div>
            <span class="status-badge <?php echo $is_active ? 'active' : 'expired'; ?>">
                <?php echo $is_active ? '● Active' : '● Expired'; ?>
            </span>
        </div>

        <div class="card-content">
            <div class="info-grid">
                <div class="info-box">
                    <span class="box-label">Current Plan</span>
                    <h3 class="box-value"><?php echo $my_sub['plan_name'] ?? 'Free Trial'; ?></h3>
                </div>
                <div class="info-box">
                    <span class="box-label">Expiry Date</span>
                    <h3 class="box-value"><?php echo $my_sub['expiry_date'] ?? 'N/A'; ?></h3>
                </div>
                <div class="info-box">
                    <span class="box-label">User Limit</span>
                    <h3 class="box-value"><?php echo $my_sub['user_limit'] ?? '5'; ?> Users</h3>
                </div>
            </div>

            <div class="features-section">
                <h4>Included in your plan:</h4>
                <ul class="feature-list">
                    <li><span class="check-icon">✓</span> Automation workflows enabled</li>
                    <li><span class="check-icon">✓</span> Advanced analytics dashboard</li>
                    <li><span class="check-icon">✓</span> Enterprise-grade security</li>
                </ul>
            </div>

            <div class="action-area">
                <button class="btn-manage">Manage Subscription & Billing</button>
                <a href="<?php echo BASE_URL; ?>index.php" class="learn-more">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>