<?php
require_once(__DIR__ . '/../../config/db.php');
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "success";
}
?>