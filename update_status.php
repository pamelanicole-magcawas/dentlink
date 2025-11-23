<?php
// update_status.php
require 'db_connect.php';

if (!isset($_POST['id'], $_POST['status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

$id = intval($_POST['id']);
$status = $_POST['status'];

// Only allow valid statuses
$validStatuses = ['approved', 'checked-in', 'completed'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
    exit;
}

// Update status in DB
$stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$success = $stmt->execute();
$stmt->close();

echo json_encode([
    'status' => $success ? 'success' : 'error',
    'new_status' => $success ? $status : null
]);
?>
