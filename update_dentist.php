<?php
include 'db_connect.php';

if (!isset($_POST['appointment_id'], $_POST['dentist_id'])) {
    http_response_code(400);
    echo "missing parameters";
    exit;
}

$appointment_id = (int) $_POST['appointment_id'];
$dentist_id = (int) $_POST['dentist_id'];

$stmt = $conn->prepare("UPDATE appointments SET dentist_id = ? WHERE id = ?");
$stmt->bind_param("ii", $dentist_id, $appointment_id);
if ($stmt->execute()) {
    echo "ok";
} else {
    http_response_code(500);
    echo "db error";
}
