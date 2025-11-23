<?php
// send_message.php - Save messages from both Patient and Admin
header('Content-Type: application/json');
include 'db_config.php';

if ($current_user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$message_text = trim($_POST['message_text'] ?? '');

if (empty($message_text)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

// Determine sender type and target user
$user_type = $current_user_type ?? ($_SESSION['role'] ?? 'Patient');

if ($user_type === 'Admin') {
    // Admin is sending a message to a patient
    $target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;
    
    if ($target_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid target patient']);
        exit;
    }
    
    // Save message under the patient's conversation (user_id = patient_id)
    $sql = "INSERT INTO chat_messages (user_id, sender_type, message_text, created_at) VALUES (?, 'Admin', ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("is", $target_id, $message_text);
    
} else {
    // Patient is sending a message
    $sql = "INSERT INTO chat_messages (user_id, sender_type, message_text, created_at) VALUES (?, 'Patient', ?, NOW())";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("is", $current_user_id, $message_text);
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message_id' => $conn->insert_id,
        'timestamp' => date('Y-m-d H:i:s'),
        'sender_type' => ($user_type === 'Admin' ? 'Admin' : 'Patient')
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save message: ' . $stmt->error]);
}

$stmt->close();
$conn->close();