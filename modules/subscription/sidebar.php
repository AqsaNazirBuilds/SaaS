<?php
include_once('../../config/db.php'); // Aapka database connection file

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Session check karna zaroori hai
}

// Check karein ke user login hai ya nahi
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Database se user ka naam aur role nikalna
    $query = "SELECT name, role FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $user_data = mysqli_fetch_assoc($result);
    
    $display_name = $user_data['name'];
    $display_role = $user_data['role'];
} else {
    // Agar login nahi hai toh redirect ya default naam
    $display_name = "Guest User";
    $display_role = "No Role";
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="../../css/laiba/sidebar.css">

<div class="sidebar">
    <div class="sidebar-header">
        <div class="logo-icon"><i class="fas fa-rocket"></i></div>
        <div class="logo-text"><span>SAAS</span> PANEL</div>
    </div>

    <ul class="nav-links">
        <li class="nav-item">
            <a href="/SAAS_PROJECT/modules/subscription/reports.php" class="nav-link">
                <i class="fas fa-th-large"></i>
                <span class="link-name">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="/SAAS_PROJECT/modules/subscription/status.php" class="nav-link">
                <i class="fas fa-crown"></i>
                <span class="link-name">Plan & Billing</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="/SAAS_PROJECT/modules/audit/audit_view.php" class="nav-link">
                <i class="fas fa-history"></i>
                <span class="link-name">Security Logs</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="/SAAS_PROJECT/modules/subscription/reports.php" class="nav-link">
                <i class="fas fa-chart-pie"></i>
                <span class="link-name">Advanced Reports</span>
            </a>
        </li>
    </ul>
<div class="sidebar-footer">
    <div class="user-avatar">
        <i class="fas fa-user-shield" style="font-size: 30px; color: #fff;"></i>
    </div>
    
    <div class="user-info">
        <span class="user-name"><?php echo htmlspecialchars($display_name); ?></span>
        <span class="user-role"><?php echo htmlspecialchars($display_role); ?></span>
    </div>
</div>
</div>