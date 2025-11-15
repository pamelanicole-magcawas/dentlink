<?php
include 'db_config.php';
// Redirect if not Admin
// if ($current_user_type !== 'Admin') {
//     header('Location: index.php'); 
//     exit;
// }

$sql = "SELECT DISTINCT 
            u.user_id, 
            u.first_name, 
            u.last_name,
            (SELECT MAX(timestamp) FROM chat_messages 
             WHERE user_id = u.user_id
             ) as last_activity,
            (SELECT COUNT(*) FROM chat_messages 
             WHERE user_id = u.user_id AND sender_type = 'Patient' AND is_read = 0
             ) as unread_count
        FROM users u 
        WHERE u.user_id != ? AND u.role = 'Patient'
        ORDER BY unread_count DESC, last_activity DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$active_chats = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Chat Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container {
            width: 90%;
            max-width: 1000px;
            margin: 50px auto;
        }

        .admin-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .admin-container th,
        .admin-container td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .admin-container th {
            background-color: #f4f4f4;
        }

        .admin-container .unread {
            background-color: #fff3cd;
            font-weight: bold;
        }

        .admin-container .unread td:last-child a {
            color: red;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="admin_dashboard.php" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <div class="admin-container">
        <h1>Admin Chat Queue</h1>
        <table>
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Last Activity</th>
                    <th>Unread Count</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_chats as $chat): ?>
                    <tr class="<?php echo ($chat['unread_count'] > 0) ? 'unread' : ''; ?>">
                        <td><?php echo htmlspecialchars($chat['first_name'] . ' ' . $chat['last_name']); ?></td>
                        <td><?php echo $chat['last_activity'] ? htmlspecialchars(date('M j, g:i A', strtotime($chat['last_activity']))) : 'No activity'; ?></td>
                        <td><?php echo htmlspecialchars($chat['unread_count']); ?></td>
                        <td><a href="admin_chat_window.php?patient_id=<?php echo $chat['user_id']; ?>">Open Chat</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>