<?php
// functions.php

/** Fetch next approved appointment for the user (patient). */
function getNextAppointment($conn, $user_id) {
    $sql = "SELECT DATE_FORMAT(date, '%W, %M %D, %Y') AS formatted_date,
                   TIME_FORMAT(start_time, '%h:%i %p') AS formatted_time,
                   location, description
            FROM appointments
            WHERE user_id = ? AND date >= CURDATE() AND status = 'approved'
            ORDER BY date ASC, start_time ASC
            LIMIT 2";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return "📅 **Appointment Found!**\nDate: **{$row['formatted_date']}**\nTime: **{$row['formatted_time']}**\nLocation: {$row['location']}\nService: {$row['description']}";
    }
    return "❌ I couldn't find any approved upcoming appointments for your record.";
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
    $all_slots = [
        '09:00:00','10:00:00','11:00:00',
        '13:00:00','14:00:00','15:00:00',
        '16:00:00','17:00:00'
    ];

    $sql = "SELECT TIME_FORMAT(start_time, '%H:%i:%s') AS st FROM appointments WHERE date = ? AND status != 'denied'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $res = $stmt->get_result();
    $booked = [];
    while ($r = $res->fetch_assoc()) {
        $booked[] = $r['st'];
    }

    $available = array_values(array_diff($all_slots, $booked));
    if (empty($available)) {
        return "❌ No slots available for " . date('M j, Y', strtotime($date)) . ".";
    }
    $msg = "✅ **Available Slots for " . date('M j, Y', strtotime($date)) . ":**\n\n";
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
            return "Our clinic hours are Monday–Friday 9:00 AM–5:00 PM, Saturday 9:00 AM–12:00 PM.";
        case 'FUNC_EMERGENCY':
            return "If this is a dental emergency, please call (123) 456-7890 immediately.";
        case 'FUNC_CHECK_SLOTS':
            return "Please provide the date you want to check in **YYYY-MM-DD** format (e.g., 2025-11-20).";
        default:
            // If it's not a FUNC token, return as static text
            return $token;
    }
}
?>
