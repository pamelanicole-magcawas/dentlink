<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get unread message count
$count_sql = "SELECT COUNT(*) as count FROM chat_messages 
              WHERE receiver_id = ? AND is_read = 0";
$stmt = $conn->prepare($count_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'count' => $row['count']
]);

$stmt->close();
$conn->close();
?>