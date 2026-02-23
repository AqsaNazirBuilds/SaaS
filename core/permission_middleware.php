function hasPermission($conn, $user_id, $permission_key) {
    $stmt = $conn->prepare("
        SELECT p.permission_key 
        FROM permissions p 
        JOIN role_permissions rp ON p.id = rp.permission_id
        JOIN user_roles ur ON rp.role_id = ur.role_id
        WHERE ur.user_id = ? AND p.permission_key = ?
    ");

    $stmt->bind_param("is", $user_id, $permission_key);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0);
}