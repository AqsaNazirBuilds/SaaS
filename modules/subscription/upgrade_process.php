<?php
// modules/subscription/upgrade_process.php
require_once(__DIR__ . '/../../config/db.php');

if (isset($_POST['payment_confirmed']) && $_POST['payment_confirmed'] == 'true') {
    
    $tenant_id = 1; 
    $target_plan_id = $_POST['plan_id']; // ID 2 (Basic) ya 3 (Premium)
    $amount = $_POST['amount']; // Price $50 ya $150
    $new_expiry = date('Y-m-d', strtotime('+30 days'));

    // STEP 1: Payment table mein entry
    $pay_query = "INSERT INTO payments (tenant_id, amount, payment_status) VALUES ($tenant_id, $amount, 'success')";
    
    if ($db->query($pay_query)) {
        // STEP 2: Plan upgrade karein
        $upgrade_query = "UPDATE subscriptions SET 
                          plan_id = $target_plan_id, 
                          expiry_date = '$new_expiry', 
                          status = 'active' 
                          WHERE tenant_id = $tenant_id";

        if ($db->query($upgrade_query)) {
            // Success! Seedha reports par bhejein taake revenue dikhe
            header("Location: reports.php"); 
            exit();
        } else {
            die("Subscription Update Failed: " . $db->error);
        }
    } else {
        die("Payment Record Failed: " . $db->error);
    }
} else {
    die("Ghalat rasta! Checkout se aein.");
}
?>