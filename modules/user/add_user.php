<?php
include('../../core/tenant_middleware.php'); 
include('../../config/db.php');

// Security Check: URL se ID lein aur check karein ke wo isi tenant ka user hai
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $tenant_id = $_SESSION['tenant_id'];

    // Aqsa's Isolation Logic: Ensure user belongs to the logged-in tenant
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
    $stmt->bind_param("ii", $user_id, $tenant_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        die("Security Alert: You do not have permission to edit this user.");
    }
}

// Update Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name   = trim($_POST['name']);
    $new_status = $_POST['status'];

    $update = $conn->prepare("UPDATE users SET name = ?, status = ? WHERE id = ? AND tenant_id = ?");
    $update->bind_param("ssii", $new_name, $new_status, $user_id, $tenant_id);
    
    if ($update->execute()) {
        header("Location: list_users.php?msg=User Updated");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User - Aqsa's Module</title>
    <link rel="stylesheet" href="../../css/aqsa_core_style.css">
</head>
<body>
    <div class="container">
        <h2>Edit User Details</h2>
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label>Name:</label><br>
                <input type="text" name="name" value="<?php echo $user['name']; ?>" style="width:100%; padding:8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label>Status:</label><br>
                <select name="status" style="width:100%; padding:8px;">
                    <option value="active" <?php if($user['status'] == 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if($user['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="list_users.php" class="btn btn-outline">Back</a>
        </form>
    </div>
</body>
</html>