<?php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/plan_logic.php');

$plan_logic = new PlanLogic($db);
$usage = $plan_logic->get_user_usage(1); 

// Naya data fetch karna jo hum ne plan_logic mein add kiya tha
$monthly_data = $plan_logic->get_monthly_logins(1);
$reg_data = $plan_logic->get_monthly_registrations(1);
$sales_data = $plan_logic->get_premium_sales(1);
$top_users = $plan_logic->get_top_users(1);

$is_premium = ($usage['plan_id'] == 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advanced Analytics | Reports</title>
    <link rel="stylesheet" href="../../css/laiba/reports.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .graph-section { margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .graph-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .graph-card h3 { margin-bottom: 5px; color: #1f3b57; font-size: 1.1rem; }
        .graph-card p { margin-bottom: 15px; color: #666; font-size: 0.85rem; }
        .top-users-list { margin-top: 20px; background: #fff; padding: 20px; border-radius: 12px; }
        .user-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>

<div class="reports-container">
    <?php if (!$is_premium): ?>
        <div class="locked-overlay">
            <div class="lock-card">
                <i class="fas fa-lock fa-4x"></i>
                <h2>Premium Feature Locked</h2>
                <p>Advanced Reports and Analytics are only available for Premium Plan users.</p>
                <a href="status.php" class="btn-upgrade-now">Upgrade to Unlock</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="report-content <?php echo !$is_premium ? 'blurred' : ''; ?>">
        <div class="report-header">
            <h1><i class="fas fa-chart-line"></i> Advanced Business Analytics</h1>
            <p>Visual representation of your system usage</p>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <span class="stat-label">TOTAL USERS</span>
                <h2 class="stat-value"><?php echo $usage['current']; ?> / <?php echo $usage['limit']; ?></h2>
            </div>
            <div class="stat-box">
                <span class="stat-label">LOGIN COUNT</span>
                <h2 class="stat-value"><?php echo $usage['logins_total']; ?></h2>
            </div>
            <div class="stat-box">
                <span class="stat-label">SUBSCRIPTION STATUS</span>
                <h2 class="stat-value" style="color: #22c55e;">Active</h2>
            </div>
        </div>

        <div class="graph-section">
            <div class="graph-card">
                <h3><i class="fas fa-user-plus"></i> User Registration</h3>
                <p>New accounts created per month</p>
                <div style="height: 250px;"><canvas id="regChart"></canvas></div>
            </div>

            <div class="graph-card">
                <h3><i class="fas fa-shopping-cart"></i> Premium Sales</h3>
                <p>Premium subscriptions sold</p>
                <div style="height: 250px;"><canvas id="salesChart"></canvas></div>
            </div>
        </div>

        <div class="top-users-list">
            <h3><i class="fas fa-crown"></i> Most Active Users</h3>
            <p>Top 3 users with highest login activity</p>
            <?php foreach($top_users as $user): ?>
                <div class="user-row">
                    <span><strong><?php echo $user['username']; ?></strong></span>
                    <span class="badge"><?php echo $user['activity_count']; ?> Logins</span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="graph-card" style="margin-top: 20px;">
            <h3><i class="fas fa-history"></i> Monthly Login Activity</h3>
            <p>Overall system engagement</p>
            <div style="height: 250px;"><canvas id="usageChart"></canvas></div>
        </div>
    </div>
</div>

<script>
    // Utility to handle empty data for charts
    function getChartData(data) {
        return data.length > 0 ? data : [0, 0, 0, 0, 0];
    }
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May'];

    // 1. Registration Chart
    new Chart(document.getElementById('regChart'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'New Users',
                data: getChartData(<?php echo json_encode($reg_data['data']); ?>),
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 2. Sales Chart
    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Sales',
                data: getChartData(<?php echo json_encode($sales_data['data']); ?>),
                backgroundColor: '#22c55e',
                borderRadius: 5
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 3. Login Chart (Purana)
    new Chart(document.getElementById('usageChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [{
                label: 'Logins',
                data: getChartData(<?php echo json_encode($monthly_data['data']); ?>),
                backgroundColor: '#1f3b57',
                borderRadius: 5
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>

</body>
</html>