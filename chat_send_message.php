<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$sender_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$message = trim($_POST['message'] ?? '');
$message_type = $_POST['message_type'] ?? 'text';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit();
}

// Determine receiver
// If sender is Patient, receiver is Admin
// If sender is Admin, receiver can be specified or default to the patient who last messaged
if ($user_role === 'Patient') {
    // Get admin ID
    $admin_query = "SELECT user_id FROM users WHERE role = 'Admin' LIMIT 1";
    $admin_result = $conn->query($admin_query);
    $admin = $admin_result->fetch_assoc();
    $receiver_id = $admin['user_id'];
} else {
    // Admin sending to patient - get the patient from last conversation
    $patient_query = "SELECT DISTINCT sender_id 
                      FROM chat_messages 
                      WHERE receiver_id = ? OR sender_id != ?
                      ORDER BY created_at DESC 
                      LIMIT 1";
    $stmt = $conn->prepare($patient_query);
    $stmt->bind_param("ii", $sender_id, $sender_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        $receiver_id = $patient['sender_id'];
    } else {
        echo json_encode(['success' => false, 'message' => 'No active conversation']);
        exit();
    }
}

// Insert message
$insert_sql = "INSERT INTO chat_messages (sender_id, receiver_id, message, message_type, created_at) 
               VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($insert_sql);
$stmt->bind_param("iiss", $sender_id, $receiver_id, $message, $message_type);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Message sent']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message']);
}

$stmt->close();
$conn->close();
?>