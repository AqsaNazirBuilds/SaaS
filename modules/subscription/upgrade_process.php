<?php
// modules/subscription/upgrade_process.php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/../audit/audit.php'); 

$audit = new AuditLog($db);

// Step A: Subscription plan ko update karna
$sql = "UPDATE subscriptions SET plan_id = 2 WHERE tenant_id = 1";

if ($db->query($sql)) {
    // Step B: CONNECTION - Ab 4 values bhej rahe hain (tenant_id, user_id, action, module)
    // Humne 1, 1 isliye likha kyunki T.jpg mein yahi IDs hain
    $audit->log(1, 1, "Upgraded Plan to Premium", "Subscription");
    
    echo "<script>alert('Congratulations! Plan Upgraded.'); window.location.href='status.php';</script>";
} else {
    echo "Database Error: " . $db->error;
}
?>