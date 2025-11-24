<?php
// send_message.php - Save messages from both Patient and Admin
header('Content-Type: application/json');
include 'db_config.php';
include 'functions.php';  

if ($current_user_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$message_text = trim($_POST['message_text'] ?? '');

if (empty($message_text)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

$user_type = $current_user_type ?? ($_SESSION['role'] ?? 'Patient');

/* ----------------------------------------------------
   SAVE USER MESSAGE FIRST (Admin → Patient or Patient)
-----------------------------------------------------*/

if ($user_type === 'Admin') {
    
    $target_id = isset($_POST['target_id']) ? (int)$_POST['target_id'] : 0;

    if ($target_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid target patient']);
        exit;
    }

    // save Admin message into patient’s conversation
    $sql = "INSERT INTO chat_messages (user_id, sender_type, message_text, created_at) 
            VALUES (?, 'Admin', ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $target_id, $message_text);

} else {

    // save Patient message
    $sql = "INSERT INTO chat_messages (user_id, sender_type, message_text, created_at) 
            VALUES (?, 'Patient', ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $current_user_id, $message_text);
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Failed to save message: ' . $stmt->error]);
    exit;
}

$stmt->close();


/* ----------------------------------------------------
   AUTO SYSTEM REPLY: Check if patient message is a date
-----------------------------------------------------*/

if ($user_type !== 'Admin') {  // Only patients trigger AI auto-response

    $detected_date = extractDateFromMessage($message_text);

    if ($detected_date !== false) {

        // generate slots
        $system_reply = getAvailableSlots($conn, $detected_date);

        // save system reply
        $sql2 = "INSERT INTO chat_messages (user_id, sender_type, message_text, created_at)
                 VALUES (?, 'System', ?, NOW())";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("is", $current_user_id, $system_reply);
        $stmt2->execute();
        $stmt2->close();
    }
}

echo json_encode([
    'success' => true,
    'message_id' => $conn->insert_id,
    'timestamp' => date('Y-m-d H:i:s'),
    'sender_type' => ($user_type === 'Admin' ? 'Admin' : 'Patient')
]);

$conn->close();
?>
