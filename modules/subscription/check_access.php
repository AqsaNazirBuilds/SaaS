<?php
// modules/subscription/check_access.php

// 1. Database connection aur logic ko bulana
require_once(__DIR__ . '/../../config/db.php'); 
require_once(__DIR__ . '/plan_logic.php'); 

$plan_logic = new PlanLogic($db);

// 2. Check karna ke kya subscription expired hai?
if ($plan_logic->is_subscription_blocked(1)) {
    // Agar expired hai (date 2025 hai), toh user ko status page par bhej do
    header("Location: ../subscription/status.php");
    exit();
}
?>