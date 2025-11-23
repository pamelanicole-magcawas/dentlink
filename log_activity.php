<?php
require 'db_connect.php'; // $conn should be the mysqli object

function logActivity($user_id, $activity) {
    global $conn;

    $user_id = intval($user_id); // ensures it’s an integer

    // Optional: verify user exists
    $stmtCheck = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $stmtCheck->bind_param("i", $user_id);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    if ($result->num_rows === 0) {
        $stmtCheck->close();
        return false; // invalid user
    }
    $stmtCheck->close();

    // Insert log
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $activity);
    $stmt->execute();
    $stmt->close();

    return true;
}
?>
