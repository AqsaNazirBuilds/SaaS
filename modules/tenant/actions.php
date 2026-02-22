<?php
include('../../config/db.php');
include('../../core/tenant_middleware.php'); // Security check

// Check karein ke kya ID aur Action URL mein mojood hain
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action == 'suspend') {
        // Tenants table mein status ko 'suspended' kar dein [cite: 75, 99]
        $stmt = $conn->prepare("UPDATE tenants SET status = 'suspended' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } 
    
    elseif ($action == 'delete') {
        // Tenant ko delete karna (Foreign keys ki wajah se users/subs bhi delete ho jayenge) [cite: 76]
        $stmt = $conn->prepare("DELETE FROM tenants WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    // Wapas list page par bhej dein
    header("Location: super_admin_list.php?msg=success");
    exit();
}
?>