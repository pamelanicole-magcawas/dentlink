<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['id'];
    $remarks = $_POST['remarks']; 

    $stmt = $conn->prepare("
        UPDATE appointments 
        SET status = 'denied', denial_reason = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("si", $remarks, $id);
    $stmt->execute();

    echo "OK";
    exit;
}
?>
