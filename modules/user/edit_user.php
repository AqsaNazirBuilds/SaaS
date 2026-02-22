<?php
include('../../core/tenant_middleware.php'); 
include('../../config/db.php');

// 1. Security Check: URL se ID lein aur check karein ke wo isi tenant ka user hai
if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $tenant_id = $_SESSION['tenant_id'];

    // Aqsa's Isolation Logic: Ensure user belongs to the logged-in tenant
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
    $stmt->bind_param("ii", $user_id, $tenant_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    // Agar user nahi mila (ya kisi aur company ka hai), toh access block kar dein
    if (!$user) {
        die("<div class='container'><p class='badge inactive'>Security Alert: You do not have permission to edit this user.</p></div>");
    }
}

// 2. Update Logic: Jab form submit ho
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_name   = trim($_POST['name']);
    $new_status = $_POST['status'];
    $user_id    = intval($_POST['user_id']); // Hidden field se ID
    $tenant_id  = $_SESSION['tenant_id'];

    $update = $conn->prepare("UPDATE users SET name = ?, status = ? WHERE id = ? AND tenant_id = ?");
    $update->bind_param("ssii", $new_name, $new_status, $user_id, $tenant_id);
    
    if ($update->execute()) {
        header("Location: list_users.php?msg=User updated successfully!");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Aqsa's Module</title>
    <link rel="stylesheet" href="../../css/aqsa_core_style.css">
</head>
<body>
    <div class="container">
        <h2>Edit User Profile</h2>
        <p style="color: #64748b; margin-bottom: 20px;">Update information for: <strong><?php echo $user['email']; ?></strong></p>
        
        <form method="POST">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Full Name:</label>
                <input type="text" name="name" value="<?php echo $user['name']; ?>" 
                       style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;" required>
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Account Status:</label>
                <select name="status" style="width:100%; padding:10px; border:1px solid #e2e8f0; border-radius:6px;">
                    <option value="active" <?php echo ($user['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ($user['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive (Blocked)</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="list_users.php" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>