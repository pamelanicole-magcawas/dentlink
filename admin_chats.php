<?php
include 'db_config.php';

$sql = "SELECT DISTINCT 
            u.user_id, 
            u.first_name, 
            u.last_name,
            (SELECT MAX(timestamp) FROM chat_messages WHERE user_id = u.user_id) as last_activity,
            (SELECT COUNT(*) FROM chat_messages WHERE user_id = u.user_id AND sender_type = 'Patient' AND is_read = 0) as unread_count
        FROM users u 
        WHERE u.user_id != ? AND u.role = 'Patient'
        ORDER BY unread_count DESC, last_activity DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$active_chats = $result->fetch_all(MYSQLI_ASSOC);

$totalUnread = array_sum(array_column($active_chats, 'unread_count'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat Queue - DentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="admin-page-header">
        <h2><i class="bi bi-chat-dots-fill"></i> Chat Queue</h2>
        <p>Manage patient conversations 
            <?php if ($totalUnread > 0): ?>
                <span class="status-badge pending ms-2"><?= $totalUnread ?> unread message<?= $totalUnread > 1 ? 's' : '' ?></span>
            <?php endif; ?>
        </p>
    </div>

    <div class="chat-queue-table">
        <table>
            <thead>
                <tr>
                    <th><i class="bi bi-person me-2"></i>Patient</th>
                    <th><i class="bi bi-clock me-2"></i>Last Activity</th>
                    <th><i class="bi bi-envelope me-2"></i>Unread</th>
                    <th><i class="bi bi-gear me-2"></i>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($active_chats)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No active conversations
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($active_chats as $chat): ?>
                        <tr class="<?= ($chat['unread_count'] > 0) ? 'unread' : '' ?>">
                            <td>
                                <strong><?= htmlspecialchars($chat['first_name'] . ' ' . $chat['last_name']) ?></strong>
                            </td>
                            <td>
                                <?php if ($chat['last_activity']): ?>
                                    <i class="bi bi-clock-history me-1"></i>
                                    <?= date('M j, g:i A', strtotime($chat['last_activity'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">No activity</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($chat['unread_count'] > 0): ?>
                                    <span class="unread-badge">
                                        <?= $chat['unread_count'] ?> new
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin_chat_window.php?patient_id=<?= $chat['user_id'] ?>" class="btn-open-chat">
                                    <i class="bi bi-chat-left-text"></i> Open Chat
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>