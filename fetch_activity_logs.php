<?php
require 'db_connect.php';

// All logs (latest 50)
$logsResult = $conn->query("SELECT al.*, u.first_name, u.last_name
                            FROM activity_logs al
                            JOIN users u ON al.user_id = u.user_id
                            WHERE u.role != 'Admin'
                            ORDER BY al.timestamp DESC
                            LIMIT 50");

$allLogs = '';
while ($row = $logsResult->fetch_assoc()) {
    $color = "secondary";
    if (strpos($row['activity'], "Logged in") !== false) $color="success";
    elseif (strpos($row['activity'], "Logged out") !== false) $color="dark";
    elseif (strpos($row['activity'], "Visited") !== false) $color="primary";
    elseif (strpos($row['activity'], "Booked") !== false) $color="warning";
    elseif (strpos($row['activity'], "Updated") !== false) $color="info";
    elseif (strpos($row['activity'], "Canceled") !== false) $color="danger";

    $allLogs .= "<tr>
                    <td>{$row['user_id']}</td>
                    <td>".htmlspecialchars($row['first_name'].' '.$row['last_name'])."</td>
                    <td><span class='badge bg-{$color}'>".htmlspecialchars($row['activity'])."</span></td>
                    <td>{$row['timestamp']}</td>
                 </tr>";
}

// Total active users (all time)
$totalActiveUsers = $conn->query("SELECT COUNT(DISTINCT al.user_id) AS total_active_users
                                  FROM activity_logs al
                                  JOIN users u ON al.user_id = u.user_id
                                  WHERE u.role != 'Admin'")->fetch_assoc()['total_active_users'];

// Return JSON
echo json_encode([
    'allLogs' => $allLogs,
    'totalActiveUsers' => $totalActiveUsers,
]);

?>
