<?php
include('../../core/tenant_middleware.php'); // Security check
include('../../config/db.php');

$tenant_id = $_SESSION['tenant_id'];

// Tenant ki details fetch karna
$stmt = $conn->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$tenant = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name = trim($_POST['company_name']);
    
    // Update query
    $update = $conn->prepare("UPDATE tenants SET company_name = ? WHERE id = ?");
    $update->bind_param("si", $new_name, $tenant_id);
    $update->execute();
    echo "Company details updated!";
}
?>

<!DOCTYPE html>
<html>
<body>
    <h2>Manage Company Settings</h2>
    <form method="POST">
        <label>Company Name:</label>
        <input type="text" name="company_name" value="<?php echo $tenant['company_name']; ?>">
        <br>
        <label>Domain Slug (Fixed):</label>
        <input type="text" value="<?php echo $tenant['domain_slug']; ?>" disabled>
        <br>
        <button type="submit">Update Name</button>
    </form>
</body>
</html>