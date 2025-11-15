<?php
// db_config.php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'dental_clinic');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION)) { session_start(); }

// Logged-in user id
$current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// User type (Admin or Patient)
if (isset($_SESSION['user_type'])) {
    $current_user_type = $_SESSION['user_type'];
} elseif (isset($_SESSION['role'])) {
    // fallback — login.php sets "role"
    $current_user_type = $_SESSION['role'];
} else {
    $current_user_type = null;
}
