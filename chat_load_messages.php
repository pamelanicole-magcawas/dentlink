<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Build query based on role
if ($user_role === 'Admin') {
    // Admin sees all messages
    $messages_sql = "SELECT m.*, 
                     u1.first_name as sender_first, u1.last_name as sender_last,
                     u2.first_name as receiver_first, u2.last_name as receiver_last
                     FROM chat_messages m
                     LEFT JOIN users u1 ON m.sender_id = u1.user_id
                     LEFT JOIN users u2 ON m.receiver_id = u2.user_id
                     ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($messages_sql);
} else {
    // Patient sees only their messages with admin
    $messages_sql = "SELECT m.*, 
                     u1.first_name as sender_first, u1.last_name as sender_last,
                     u2.first_name as receiver_first, u2.last_name as receiver_last
                     FROM chat_messages m
                     LEFT JOIN users u1 ON m.sender_id = u1.user_id
                     LEFT JOIN users u2 ON m.receiver_id = u2.user_id
                     WHERE (m.sender_id = ? OR m.receiver_id = ?)
                     ORDER BY m.created_at ASC";
    $stmt = $conn->prepare($messages_sql);
    $stmt->bind_param("ii", $user_id, $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Mark messages as read for current user
$mark_read_sql = "UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND is_read = 0";
$mark_stmt = $conn->prepare($mark_read_sql);
$mark_stmt->bind_param("i", $user_id);
$mark_stmt->execute();
$mark_stmt->close();

echo json_encode([
    'success' => true,
    'messages' => $messages
]);

$stmt->close();
$conn->close();
?>