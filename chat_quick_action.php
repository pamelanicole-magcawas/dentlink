<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action_key = $_POST['action_key'] ?? '';

if (empty($action_key)) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit();
}

// Get the action details
$action_sql = "SELECT * FROM chat_quick_actions WHERE action_key = ? AND is_active = 1";
$stmt = $conn->prepare($action_sql);
$stmt->bind_param("s", $action_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Action not found']);
    exit();
}

$action = $result->fetch_assoc();

// Get admin ID
$admin_sql = "SELECT user_id FROM users WHERE role = 'Admin' LIMIT 1";
$admin_result = $conn->query($admin_sql);
$admin = $admin_result->fetch_assoc();
$receiver_id = $admin['user_id'];

// Insert the message
$message = $action['action_text'];
$message_type = 'quick_action';

$insert_sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, message_type, created_at) 
               VALUES (?, ?, ?, ?, NOW())";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iiss", $user_id, $receiver_id, $message, $message_type);

if ($insert_stmt->execute()) {
    // Insert auto-reply if available
    if (!empty($action['auto_reply'])) {
        $auto_reply_sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, message_type, is_system_message, created_at) 
                          VALUES (?, ?, ?, 'auto_reply', 1, NOW())";
        $auto_stmt = $conn->prepare($auto_reply_sql);
        $auto_stmt->bind_param("iis", $receiver_id, $user_id, $action['auto_reply']);
        $auto_stmt->execute();
        $auto_stmt->close();
    }
    
    echo json_encode(['success' => true, 'message' => 'Action sent']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send action']);
}

$stmt->close();
$insert_stmt->close();
$conn->close();
?>