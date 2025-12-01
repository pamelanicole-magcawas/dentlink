<?php
include 'db_connect.php';
include 'schedule_helper.php'; 

if (!isset($_POST['appointment_id'])) {
    echo json_encode(['error'=>'missing appointment_id']);
    exit;
}

$appointment_id = (int) $_POST['appointment_id'];

// Get appointment location
$stmt = $conn->prepare("SELECT location FROM appointments WHERE id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();

if (!$r) {
    echo json_encode(['error'=>'appointment not found']);
    exit;
}

$location = trim($r['location']);

$dentists = getDentistsByLocation($conn, $location);

$options = [];
while ($d = $dentists->fetch_assoc()) {
    $options[$d['id']] = $d['name'] . ' (' . $d['schedule_days'] . ')';
}

echo json_encode(['options' => $options]);
?>