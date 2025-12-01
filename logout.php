<?php 
session_start(); 
require 'db_connect.php'; 
require 'log_activity.php';  

if (isset($_SESSION['user_id'])) {     
    logActivity($_SESSION['user_id'], "Logged out"); 
}

session_unset(); 
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="patient.css">
</head>
<body>

<script>
// Show SweetAlert then redirect to login page
Swal.fire({
    title: "You’ve Successfully Logged Out",
    text: "Thank you for using our service. We look forward to seeing you again.",
    icon: "success",
    confirmButtonText: "OK"
}).then(() => {
    window.location.href = "index.php";
});
</script>
</body>
</html>
