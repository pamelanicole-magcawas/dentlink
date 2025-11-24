<?php
require 'db_connect.php';

$today = date('Y-m-d');
$filter = isset($_GET['filter']) ? $_GET['filter'] : ''; // '' | 'login' | 'logout'

// Base query
$where = "u.role != 'Admin'";
if ($filter === 'login') {
    $where .= " AND al.activity LIKE '%Logged in%'";
} elseif ($filter === 'logout') {
    $where .= " AND al.activity LIKE '%Logged out%'";
}

// All logs (latest 50, filtered)
$logsResult = $conn->query("
    SELECT al.*, u.first_name, u.last_name
    FROM activity_logs al
    JOIN users u ON al.user_id = u.user_id
    WHERE $where
    ORDER BY al.timestamp DESC
    LIMIT 50
");

$allLogs = '';
while ($row = $logsResult->fetch_assoc()) {
    $color = (strpos($row['activity'], "Logged in") !== false) ? "success" : "dark";

    $allLogs .= "<tr>
                    <td>{$row['user_id']}</td>
                    <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                    <td><span class='badge bg-{$color}'>" . htmlspecialchars($row['activity']) . "</span></td>
                    <td>{$row['timestamp']}</td>
                 </tr>";
}

// Total active users (all time, only login/logout)
$totalActiveUsers = $conn->query("
    SELECT COUNT(DISTINCT al.user_id) AS total_active_users
    FROM activity_logs al
    JOIN users u ON al.user_id = u.user_id
    WHERE u.role != 'Admin' AND (al.activity LIKE '%Logged in%' OR al.activity LIKE '%Logged out%')
")->fetch_assoc()['total_active_users'];

// Today's activities (login/logout only)
$todayActivities = $conn->query("
    SELECT COUNT(*) AS count
    FROM activity_logs al
    JOIN users u ON al.user_id = u.user_id
    WHERE u.role != 'Admin' AND DATE(al.timestamp) = '$today' AND (al.activity LIKE '%Logged in%' OR al.activity LIKE '%Logged out%')
")->fetch_assoc()['count'];

// Today's logins (only 'Logged in')
$recentLogins = $conn->query("
    SELECT COUNT(*) AS count
    FROM activity_logs al
    JOIN users u ON al.user_id = u.user_id
    WHERE u.role != 'Admin' AND DATE(al.timestamp) = '$today' AND al.activity LIKE '%Logged in%'
")->fetch_assoc()['count'];

// Return JSON
echo json_encode([
    'allLogs' => $allLogs,
    'totalActiveUsers' => $totalActiveUsers,
    'todayActivities' => $todayActivities,
    'recentLogins' => $recentLogins,
]);
