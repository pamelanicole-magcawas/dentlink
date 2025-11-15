<?php
include 'db_config.php';
header('Content-Type: application/json');

if ($current_user_id === 0) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
$role = $current_user_type ?? ($_SESSION['role'] ?? null);

// If Admin requested a specific patient conversation
if ($role === 'Admin' && isset($_GET['patient_id'])) {
    $conv_user_id = (int)$_GET['patient_id'];
    if ($conv_user_id <= 0) {
        echo json_encode(['error' => 'Invalid patient_id']);
        exit;
    }
} else {
    // Patient views their own conversation
    $conv_user_id = $current_user_id;
}

$sql = "SELECT id, user_id, sender_type, message_text, timestamp, is_read
        FROM chat_messages
        WHERE user_id = ? AND id > ?
        ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $conv_user_id, $last_id);
$stmt->execute();
$res = $stmt->get_result();
$messages = [];
while ($row = $res->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();

// Mark as read:
// If Admin viewing -> mark Patient messages as read
// If Patient viewing -> mark Admin messages as read
$to_mark = [];
foreach ($messages as $m) {
    if ($role === 'Admin' && $m['sender_type'] === 'Patient' && !$m['is_read']) {
        $to_mark[] = (int)$m['id'];
    }
    if ($role === 'Patient' && $m['sender_type'] === 'Admin' && !$m['is_read']) {
        $to_mark[] = (int)$m['id'];
    }
}
if (!empty($to_mark)) {
    $ids = implode(',', array_map('intval', array_unique($to_mark)));
    $conn->query("UPDATE chat_messages SET is_read = 1 WHERE id IN ($ids)");
}

echo json_encode($messages);
$conn->close();
?>
