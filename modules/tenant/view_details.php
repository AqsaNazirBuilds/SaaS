<?php
include('../../config/db.php');
include('../../core/tenant_middleware.php');

// URL se company ki ID lein
if (!isset($_GET['id'])) {
    die("Error: No Tenant ID provided.");
}

$view_id = intval($_GET['id']);

// Complex Query: Company info + Plan name + Expiry + User count [cite: 77, 78, 112]
$query = "SELECT t.id, t.company_name, t.domain_slug, t.status, 
                 s.expiry_date, p.plan_name,
                 (SELECT COUNT(*) FROM users WHERE tenant_id = t.id) as total_users
          FROM tenants t
          LEFT JOIN subscriptions s ON t.id = s.tenant_id
          LEFT JOIN plans p ON s.plan_id = p.id
          WHERE t.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $view_id);
$stmt->execute();
$details = $stmt->get_result()->fetch_assoc();

if (!$details) {
    die("Error: Tenant not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Tenant Details - <?php echo $details['company_name']; ?></title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Tenant Details: <?php echo $details['company_name']; ?></h2>
        <hr>
        
        <p><strong>Tenant ID:</strong> <?php echo $details['id']; ?></p>
        <p><strong>Domain Slug:</strong> <?php echo $details['domain_slug']; ?></p>
        <p><strong>Status:</strong> <?php echo strtoupper($details['status']); ?></p>
        
        <br>
        <h3>Subscription Info</h3>
        <p><strong>Current Plan:</strong> <?php echo $details['plan_name'] ?? 'No Plan Assigned'; ?></p>
        <p><strong>Expiry Date:</strong> <?php echo $details['expiry_date'] ?? 'N/A'; ?></p>
        
        <br>
        <h3>Usage Statistics</h3>
        <p><strong>Total Users Registered:</strong> <?php echo $details['total_users']; ?></p>

        <br>
        <a href="super_admin_list.php">← Back to List</a>
    </div>
</body>
</html>