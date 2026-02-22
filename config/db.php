<?php
// 1. Check current host (Localhost or Online)
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    // Localhost settings (XAMPP / VS Code)
    // Aapke folder ka naam "SaaS_Project" hai
    define('BASE_URL', 'http://localhost/SaaS_Project/');
    $host = "localhost";
    $user = "root";
    $password = "";
    $dbname = "saas"; // Jo naam aapne Local database ka rakha hai
} else {
    // InfinityFree settings (Online)
    // Yahan main ne aapka naya path 'SaaS' update kar diya hai
    define('BASE_URL', 'http://laiba-lms.great-site.net/SaaS/');
    $host = "sql303.infinityfree.com"; 
    $user = "if0_40800821";         
    $password = "r7890laiba1"; 
    // Isko check kar lena ke naye project ke liye database wahi hai ya naya banaya hai
    $dbname = "if0_40800821_lms"; 
}

// 2. Create Connection
$conn = new mysqli($host, $user, $password, $dbname);

// 3. Check Connection
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Global variable for other members to use
$db = $conn; 
?>