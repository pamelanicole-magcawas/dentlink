<?php
session_start();
require 'db_connect.php';
require 'log_activity.php';

if (isset($_SESSION['user_id'])) {
    logActivity($_SESSION['user_id'], "Logged out");
}

session_unset();
session_destroy();

header("Location: login.php");
exit();
?>
