<?php
// modules/subscription/upgrade_process.php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/../audit/audit.php'); // Connection with Audit Module

$audit = new AuditLog($db);

// 1. Database mein Plan Update karna
// Maan lete hain Tenant ID 1 ka plan Premium (ID: 2) kar rahe hain
$sql = "UPDATE subscriptions SET plan_id = 2, updated_at = NOW() WHERE tenant_id = 1";

if ($db->query($sql)) {
    // 2. CONNECTION POINT: Audit mein entry khud-ba-khud jaye
    $audit->log(1, "Upgraded Plan to Premium", "Subscription");
    
    // Success Message aur Wapsi
    echo "<script>alert('Congratulations! Plan Upgraded.'); window.location.href='status.php';</script>";
} else {
    echo "Error: " . $db->error;
}
?>