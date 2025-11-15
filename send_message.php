<?php
include 'db_config.php';
include 'functions.php';
header('Content-Type: application/json');

// Ensure we have a logged-in user id and type
if ($current_user_id === 0) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if (!isset($_POST['message_text'])) {
    echo json_encode(['success' => false, 'error' => 'message_text required']);
    exit;
}

$message_text = trim($_POST['message_text']);

// Determine effective role: prefer $current_user_type, but fallback to session role
$role = $current_user_type ?? ($_SESSION['role'] ?? null);

// === ADMIN sending reply to a patient (must include target_id) ===
if ($role === 'Admin') {
    if (!isset($_POST['target_id'])) {
        echo json_encode(['success' => false, 'error' => 'target_id required for Admin messages']);
        exit;
    }
    $patient_id = (int)$_POST['target_id'];
    if ($patient_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid target_id']);
        exit;
    }

    $sql = "INSERT INTO chat_messages (user_id, sender_type, message_text, timestamp) VALUES (?, 'Admin', ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $patient_id, $message_text);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// === PATIENT sending ===
$conversation_user_id = $current_user_id; // patient sends to their own conversation
$sender_type = 'Patient';

$sql = "INSERT INTO chat_messages (user_id, sender_type, message_text, timestamp) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $conversation_user_id, $sender_type, $message_text);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    $conn->close();
    exit;
}

// Check if message is a strict date -> auto query available slots
$detected_date = extractDateFromMessage($message_text);
if ($detected_date) {
    $response_text = getAvailableSlots($conn, $detected_date);
    $sql2 = "INSERT INTO chat_messages (user_id, sender_type, message_text, timestamp) VALUES (?, 'System', ?, NOW())";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("is", $conversation_user_id, $response_text);
    $stmt2->execute();
    $stmt2->close();
}

echo json_encode(['success' => true]);
$conn->close();
?>
