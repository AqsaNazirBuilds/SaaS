<?php
// modules/subscription/upgrade_action.php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/subscription.php');

$sub_logic = new Subscription($db);

// 1. Aaj ki date lein aur usmein 30 din add karein
$new_expiry = date('Y-m-d', strtotime('+30 days'));

// 2. Database mein Plan ID '2' (Premium) set karein aur Date barhaein
$sql = "UPDATE subscriptions SET 
        plan_id = 2, 
        expiry_date = '$new_expiry', 
        status = 'active' 
        WHERE tenant_id = 1";

if ($db->query($sql)) {
    // 3. Audit Log mein entry karein (Responsibility #3)
    $log_sql = "INSERT INTO audit_logs (user_id, action, module, timestamp) 
                VALUES (1, 'Upgraded to Premium Plan', 'Subscription', NOW())";
    $db->query($log_sql);

    // Kaam khatam hone ke baad wapas dashboard par bhej do
    header("Location: status.php?success=1");
} else {
    echo "Error updating subscription: " . $db->error;
}
?>