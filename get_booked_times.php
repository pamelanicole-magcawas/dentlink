<?php
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

$date = $_GET['date'] ?? '';
$location = $_GET['location'] ?? '';

if (empty($date) || empty($location)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT start_time FROM appointments 
        WHERE date = ? AND location = ? 
        AND status IN ('approved', 'pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $date, $location);
$stmt->execute();
$result = $stmt->get_result();

$bookedTimes = [];
while ($row = $result->fetch_assoc()) {
    // Force all booked times to HH:MM:SS format
    $bookedTimes[] = date("H:i:s", strtotime($row['start_time']));
}

header('Content-Type: application/json');
echo json_encode($bookedTimes);
?>
