<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'has_new' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Check for new messages
$check_sql = "SELECT COUNT(*) as count FROM chat_messages 
              WHERE message_id > ? AND (sender_id = ? OR receiver_id = ?)";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("iii", $last_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$has_new = $row['count'] > 0;

echo json_encode([
    'success' => true,
    'has_new' => $has_new
]);

$stmt->close();
$conn->close();
?>