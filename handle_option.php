<?php
include 'db_config.php';
include 'functions.php';
header('Content-Type: application/json');

error_reporting(E_ERROR | E_PARSE);  // Remove warnings/notices
ob_clean();                         // Remove accidental output buffer

if (empty($current_user_id) || $current_user_id == 0) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if (!isset($_POST['query_id'])) {
    echo json_encode(['success' => false, 'error' => 'Missing query_id']);
    exit;
}

$query_id = (int)$_POST['query_id'];

$role = $current_user_type ?? ($_SESSION['role'] ?? 'Patient');

// Default conversation owner = patient sending the option
$conversation_user_id = $current_user_id;

// Admin can send option for a patient
if ($role === 'Admin' && isset($_POST['target_id'])) {
    $conversation_user_id = (int)$_POST['target_id'];
    if ($conversation_user_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid target patient']);
        exit;
    }
}

//Get Option Data
$stmt = $conn->prepare("
    SELECT button_label, response_text 
    FROM chat_options 
    WHERE query_id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $query_id);
$stmt->execute();
$result = $stmt->get_result();
$option = $result->fetch_assoc();
$stmt->close();

if (!$option) {
    echo json_encode(['success' => false, 'error' => 'Option not found']);
    exit;
}

$button_text = $option['button_label'];
$response_token = $option['response_text'];


$sender_type = ($role === 'Admin') ? 'Admin' : 'Patient';

$stmt1 = $conn->prepare("
    INSERT INTO chat_messages (user_id, sender_type, message_text, timestamp)
    VALUES (?, ?, ?, NOW())
");
$stmt1->bind_param("iss", $conversation_user_id, $sender_type, $button_text);
$ok1 = $stmt1->execute();
$stmt1->close();

if (!$ok1) {
    echo json_encode(['success' => false, 'error' => 'Failed to insert sender message']);
    exit;
}

$response_text = getDynamicResponse($conn, $response_token, $conversation_user_id);

if (!$response_text) {
    $response_text = "System Error: No response generated.";
}

$stmt2 = $conn->prepare("
    INSERT INTO chat_messages (user_id, sender_type, message_text, timestamp)
    VALUES (?, 'System', ?, NOW())
");
$stmt2->bind_param("is", $conversation_user_id, $response_text);
$ok2 = $stmt2->execute();
$stmt2->close();

if (!$ok2) {
    echo json_encode(['success' => false, 'error' => 'Failed to insert system message']);
    exit;
}

echo json_encode([
    'success' => true,
    'response' => $response_text
]);

$conn->close();
exit;
?>
