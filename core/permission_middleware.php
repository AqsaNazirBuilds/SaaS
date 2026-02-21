<?php
function hasPermission($conn, $user_id, $permission_key) {
    $sql = "SELECT p.permission_key FROM permissions p 
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = $user_id AND p.permission_key = '$permission_key'";
    
    $result = $conn->query($sql);
    return ($result->num_rows > 0);
}
?>