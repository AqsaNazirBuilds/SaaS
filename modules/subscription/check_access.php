<?php
// modules/subscription/check_access.php

// 1. Database connection aur logic ko bulana
require_once(__DIR__ . '/../../config/db.php'); 
require_once(__DIR__ . '/plan_logic.php'); 

$plan_logic = new PlanLogic($db);

// 2. Check karna ke kya subscription expired hai?
// Note: 1 ki jagah session se user_id/tenant_id lena behtar hai
$tenant_id = $_SESSION['tenant_id'] ?? 1; 

if ($plan_logic->is_subscription_blocked($tenant_id)) {
    // BASE_URL ka istemal karte hue redirect karein
    header("Location: " . BASE_URL . "modules/subscription/status.php");
    exit();
}
?>