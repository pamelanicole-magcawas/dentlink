<?php
// functions.php

/** Fetch next approved appointment for the user (patient). */
function getNextAppointment($conn, $user_id) {
    $sql = "SELECT DATE_FORMAT(date, '%W, %M %D, %Y') AS formatted_date,
                   TIME_FORMAT(start_time, '%h:%i %p') AS formatted_time,
                   location, description
            FROM appointments
            WHERE user_id = ? AND date >= CURDATE() AND status = 'approved'
            ORDER BY date ASC, start_time ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        return "You don’t have any approved upcoming appointments at the moment.";
    }

    $msg = "**Upcoming Appointments:**\n\n";

    while ($row = $res->fetch_assoc()) {
        $msg .= "• Date: **{$row['formatted_date']}**\n";
        $msg .= "  Time: **{$row['formatted_time']}**\n";
        $msg .= "  Location: {$row['location']}\n";
        $msg .= "  Service: {$row['description']}\n\n";
    }

    return trim($msg);
}

/** Strict YYYY-MM-DD detection */
function extractDateFromMessage($message) {
    $message = trim($message);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $message)) {
        $parts = explode('-', $message);
        if (checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0])) {
            return $message;
        }
    }
    return false;
}

/** Get available slots for a date (returns string) */
function getAvailableSlots($conn, $date) {
    // Set timezone to local
    date_default_timezone_set('Asia/Manila');

    $all_slots = [
        '09:00:00','10:00:00','11:00:00',
        '13:00:00','14:00:00','15:00:00',
        '16:00:00'
    ];

    // Fetch booked slots for the date
    $sql = "SELECT TIME_FORMAT(start_time, '%H:%i:%s') AS st FROM appointments WHERE date = ? AND status != 'denied'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
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

    if (empty($available)) {
        return "❌ No slots available for " . date('M j, Y', strtotime($date)) . ".";
    }

    // Build message
    $msg = " **Available Slots for " . date('M j, Y', strtotime($date)) . ":**\n\n";
    foreach ($available as $s) {
        $msg .= "• " . date('h:i A', strtotime($s)) . "\n";
    }
    return $msg;
}

/** Interpret FUNC_ tokens or query static texts from chat_options */
function getDynamicResponse($conn, $tokenOrQueryId, $user_id = null) {
    // If numeric, fetch by query_id
    if (is_numeric($tokenOrQueryId)) {
        $qid = (int)$tokenOrQueryId;
        $stmt = $conn->prepare("SELECT response_text FROM chat_options WHERE query_id = ? LIMIT 1");
        $stmt->bind_param("i", $qid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $token = $row['response_text'];
        } else {
            return "Sorry, option not found.";
        }
    } else {
        $token = $tokenOrQueryId;
    }

    if (!is_string($token)) return "Sorry, invalid response.";

    switch ($token) {
        case 'FUNC_CHECK_APPOINTMENTS':
            return getNextAppointment($conn, $user_id);
        case 'FUNC_CLINIC_HOURS':
            return "We are open Monday to Sunday, from 9:00 AM to 5:00 PM.";
        case 'FUNC_EMERGENCY':
            return "If this is a dental emergency, please call (123) 456-7890 immediately.";
        case 'FUNC_CHECK_SLOTS':
            return "Please provide the date you want to check in **YYYY-MM-DD** format (e.g., 2025-11-20).";
        case 'FUNC_CLINIC_LOCATION':
            return "Our clinic is located on the 2nd Floor of CL Building, E. Mayo St., Lipa City (beside New Star).";
        default:
            // If it's not a FUNC token, return as static text
            return $token;
    }
}
?>
