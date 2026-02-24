<?php
// modules/subscription/upgrade_process.php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/../audit/audit.php');

$audit = new AuditLog($db);

// --- NAYA HISSA: Date calculate karein ---
$new_expiry = date('Y-m-d', strtotime('+30 days'));

// Database Update (Plan ID 2 AND Date update)
$query = "UPDATE subscriptions SET 
          plan_id = 2, 
          expiry_date = '$new_expiry', 
          status = 'active' 
          WHERE tenant_id = 1";

if ($db->query($query)) {
    // Audit Log Entry
    $audit->log(1, 1, "Upgraded to Premium Plan", "Subscription");

    // Seedha reports page par redirect
    header("Location: reports.php");
    exit();
} else {
    die("Database Error: " . $db->error);
}
?>