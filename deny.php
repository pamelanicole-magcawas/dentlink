<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];

    $stmt = $conn->prepare("UPDATE appointments SET status = 'denied' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo "<p>Appointment denied.</p>";
}

echo "<br><a href='admin_appointments.php'>Back to Admin Panel</a>";
?>
