<?php
session_start();
include_once(__DIR__ . '/../config/database.php');

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "index.php?module=user&action=login");
        exit();
    }
}
?>