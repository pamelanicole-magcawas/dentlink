<?php
include 'db_connect.php';

$id = intval($_GET['id']);
$stmt = $conn->prepare("
    SELECT a.*, d.name AS dentist_name
    FROM appointments a
    LEFT JOIN dentists d ON d.id = a.dentist_id
    WHERE a.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if ($row) {
    // If no dentist assigned, show 'Unassigned'
    $dentist = $row['dentist_name'] ?? 'Unassigned';

    // Format time as "8:30 AM"
    $time = date('g:i A', strtotime($row['start_time']));

    echo json_encode([
        'status' => 'success',
        'data' => [
            'id' => $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'date' => $row['date'],
            'start_time' => $time,
            'location' => $row['location'],
            'description' => $row['description'],
            'dentist' => $dentist,
            'status' => $row['status']
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Appointment not found.']);
}
