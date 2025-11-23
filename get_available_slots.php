<?php
// get_available_slots.php - Returns available appointment slots for a given date
header('Content-Type: application/json');
include 'db_config.php';

// Set timezone to local
date_default_timezone_set('Asia/Manila');

// Get parameters
$date = isset($_POST['date']) ? $_POST['date'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';

if (empty($date)) {
    echo json_encode(['success' => false, 'message' => 'Date is required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

// All possible time slots
$all_slots = [
    '09:00:00', '10:00:00', '11:00:00',
    '13:00:00', '14:00:00', '15:00:00', '16:00:00'
];

// Fetch booked slots for the date (excluding denied appointments)
// Optionally filter by location if your system supports multiple locations
$sql = "SELECT TIME_FORMAT(start_time, '%H:%i:%s') AS st 
        FROM appointments 
        WHERE date = ? AND status != 'denied'";

// If you want to filter by location as well, uncomment below:
// $sql .= " AND location = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);

// If filtering by location:
// $stmt->bind_param("ss", $date, $location);

$stmt->execute();
$res = $stmt->get_result();

$booked = [];
while ($r = $res->fetch_assoc()) {
    $booked[] = $r['st'];
}

// Remove booked slots
$available = array_values(array_diff($all_slots, $booked));

// Filter by 2-hour interval if date is today
$today = date('Y-m-d');
if ($date == $today) {
    $now = new DateTime();
    $two_hours_later = (clone $now)->modify('+2 hours');
    
    foreach ($available as $key => $slot) {
        $slot_time = new DateTime("$date $slot");
        if ($slot_time < $two_hours_later) {
            unset($available[$key]);
        }
    }
    $available = array_values($available); // reindex
}

// Build response with both 24-hour value and display format
$slots_response = [];
foreach ($available as $slot) {
    $slots_response[] = [
        'value' => $slot,  // 24-hour format for database
        'display' => date('h:i A', strtotime($slot))  // 12-hour format for display
    ];
}

echo json_encode([
    'success' => true,
    'date' => $date,
    'slots' => $slots_response,
    'booked_count' => count($booked),
    'available_count' => count($slots_response)
]);