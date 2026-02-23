<?php
// modules/subscription/upgrade_process.php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/../audit/audit.php');

$audit = new AuditLog($db);

// Database Update (Plan ID 2 for Premium)
$query = "UPDATE subscriptions SET plan_id = 2 WHERE tenant_id = 1";

if ($db->query($query)) {
    // Audit Log (4 parameters as required by your audit.php)
    $audit->log(1, 1, "Upgraded to Premium Plan", "Subscription");

    // Seedha reports page par redirect
    header("Location: reports.php");
    exit();
} else {
    die("Database Error: " . $db->error);
}
?>