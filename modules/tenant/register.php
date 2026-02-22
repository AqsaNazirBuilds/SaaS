<?php
include('../../config/db.php'); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $company_name = trim($_POST['company_name']);
    $domain_slug  = strtolower(trim($_POST['domain_slug'])); 
    $admin_name   = trim($_POST['name']);
    $admin_email  = trim($_POST['email']);
    $password     = $_POST['password'];

    // Input Validation
    if (empty($company_name) || empty($admin_email) || strlen($password) < 6) {
        die("Error: Form fill karein aur password kam se kam 6 characters ka ho.");
    }

    // Password Hashing [cite: 102]
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $conn->begin_transaction();

    try {
        // 1. Insert Tenant [cite: 92]
        $stmt = $conn->prepare("INSERT INTO tenants (company_name, domain_slug, status) VALUES (?, ?, 'trial')");
        $stmt->bind_param("ss", $company_name, $domain_slug);
        $stmt->execute();
        $tenant_id = $conn->insert_id;// Generate unique ID [cite: 15]

      // 2. Insert Admin User [cite: 16, 93]
        $stmt = $conn->prepare("INSERT INTO users (tenant_id, name, email, password, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("isss", $tenant_id, $admin_name, $admin_email, $hashed_password);
        $stmt->execute();

        // 3. Setup Subscription [cite: 45, 148]
        $start_date = date('Y-m-d');
        $expiry_date = date('Y-m-d', strtotime('+7 days')); // Calculate expiry [cite: 149]
        $plan_id = 1; 

        $stmt = $conn->prepare("INSERT INTO subscriptions (tenant_id, plan_id, start_date, expiry_date, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->bind_param("iiss", $tenant_id, $plan_id, $start_date, $expiry_date);
        $stmt->execute();

        $conn->commit();
        echo "Success: Company and Admin registered with a 7-day trial!"; 

    } catch (Exception $e) {
        $conn->rollback();
        echo "System Error: " . $e->getMessage();
    }
}
?>