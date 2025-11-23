<?php
// chat_book_appointment.php - Handle appointment booking from chat
header('Content-Type: application/json');
include 'db_config.php';

date_default_timezone_set('Asia/Manila');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

$user_id = intval($input['user_id'] ?? 0);
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$date = trim($input['date'] ?? '');
$time = trim($input['time'] ?? '');
$location = trim($input['location'] ?? '');
$service = trim($input['service'] ?? '');

// Validate required fields
if (!$user_id || !$name || !$email || !$date || !$time || !$location || !$service) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

// Validate time format (should be HH:MM:SS)
if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
    // Try to convert if it's in different format
    $time = date('H:i:s', strtotime($time));
}

// Check if slot is still available
$check_sql = "SELECT id FROM appointments WHERE date = ? AND start_time = ? AND status != 'denied'";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ss", $date, $time);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This slot has already been booked. Please select another time.']);
    exit;
}

// Generate unique QR code data
$qr_code = 'DENT-' . strtoupper(uniqid()) . '-' . date('Ymd');

// Insert appointment with pending status
$sql = "INSERT INTO appointments (user_id, name, email, date, start_time, location, service, status, qr_code, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssss", $user_id, $name, $email, $date, $time, $location, $service, $qr_code);

if ($stmt->execute()) {
    $appointment_id = $conn->insert_id;
    
    // Format for display
    $formatted_date = date('F j, Y', strtotime($date));
    $formatted_time = date('h:i A', strtotime($time));
    
    // Save system message to chat_messages
    $system_msg = "✅ **Appointment Booked Successfully!**\n\n";
    $system_msg .= "📋 **Booking Details:**\n";
    $system_msg .= "• Reference: #APT-" . str_pad($appointment_id, 5, '0', STR_PAD_LEFT) . "\n";
    $system_msg .= "• Date: $formatted_date\n";
    $system_msg .= "• Time: $formatted_time\n";
    $system_msg .= "• Location: $location\n";
    $system_msg .= "• Service: $service\n\n";
    $system_msg .= "⏳ Status: **Pending Approval**\n";
    $system_msg .= "📧 You will receive an email notification once approved.";
    
    $msg_sql = "INSERT INTO chat_messages (user_id, sender_type, message_text) VALUES (?, 'System', ?)";
    $msg_stmt = $conn->prepare($msg_sql);
    $msg_stmt->bind_param("is", $user_id, $system_msg);
    $msg_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Appointment booked successfully',
        'appointment_id' => $appointment_id,
        'reference' => '#APT-' . str_pad($appointment_id, 5, '0', STR_PAD_LEFT),
        'qr_code' => $qr_code,
        'details' => [
            'date' => $formatted_date,
            'time' => $formatted_time,
            'location' => $location,
            'service' => $service,
            'status' => 'pending'
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}

$conn->close();