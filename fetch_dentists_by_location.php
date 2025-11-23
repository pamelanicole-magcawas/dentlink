<?php
include 'db_connect.php';
include 'schedule_helper.php'; // contains expandScheduleDays() + shortDay()

if (!isset($_POST['appointment_id'])) {
    echo json_encode(['error'=>'missing appointment_id']);
    exit;
}
$appointment_id = (int) $_POST['appointment_id'];

// get appointment location
$stmt = $conn->prepare("SELECT location FROM appointments WHERE id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) {
    echo json_encode(['error'=>'appointment not found']);
    exit;
}
$location = $r['location'];

// get dentists for this location
$stmt2 = $conn->prepare("SELECT id, name FROM dentists WHERE location = ? AND is_active = 1 ORDER BY name");
$stmt2->bind_param("s", $location);
$stmt2->execute();
$res = $stmt2->get_result();

$options = [];
while ($d = $res->fetch_assoc()) {
    $options[$d['id']] = $d['name'];
}

echo json_encode(['options' => $options]);

?>