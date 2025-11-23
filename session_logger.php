<?php
require "db_connect.php";
require "log_activity.php";

if (!isset($_SESSION)) session_start();

if (isset($_SESSION['user_id'])) {
    $currentPage = basename($_SERVER['PHP_SELF']);
    logActivity($conn, "Visited page: " . $currentPage);
}
?>
