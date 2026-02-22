<?php
include('../../core/tenant_middleware.php'); 
include('../../config/db.php');

if (isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $tenant_id = $_SESSION['tenant_id'];

    // Aqsa's Security Check: Pata karo ke user usi tenant ka hai ya nahi
    $check = $conn->prepare("SELECT id FROM users WHERE id = ? AND tenant_id = ?");
    $check->bind_param("ii", $user_id, $tenant_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        // Agar record mil gaya, toh delete kar do
        $delete = $conn->prepare("DELETE FROM users WHERE id = ? AND tenant_id = ?");
        $delete->bind_param("ii", $user_id, $tenant_id);
        $delete->execute();
        header("Location: list_users.php?msg=User Deleted Successfully");
    } else {
        // Unauthorized attempt
        die("Security Alert: Unauthorized deletion attempt!");
    }
} else {
    header("Location: list_users.php");
}
?>