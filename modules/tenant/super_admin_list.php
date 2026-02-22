<?php
include('../../config/db.php');

// Super Admin ke liye saaray tenants ka data fetch karna
// Is query mein hum subscriptions aur plans ko join kar rahay hain taake mukammal info milay
$query = "SELECT t.id, t.company_name, t.domain_slug, t.status AS tenant_status, 
                 s.expiry_date, p.plan_name 
          FROM tenants t
          LEFT JOIN subscriptions s ON t.id = s.tenant_id
          LEFT JOIN plans p ON s.plan_id = p.id";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Super Admin - Tenant Management</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <h2>All Registered Companies (Tenants)</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Company Name</th>
                <th>Domain Slug</th>
                <th>Plan</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['company_name']; ?></td>
                <td><?php echo $row['domain_slug']; ?></td>
                <td><?php echo $row['plan_name'] ?? 'No Plan'; ?></td>
                <td><?php echo $row['expiry_date'] ?? 'N/A'; ?></td>
                <td><?php echo strtoupper($row['tenant_status']); ?></td>
                <td>
                    <a href="actions.php?action=suspend&id=<?php echo $row['id']; ?>">Suspend</a> | 
                    <a href="actions.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>