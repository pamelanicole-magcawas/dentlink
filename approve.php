<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']); // sanitize input

    // Fetch appointment details
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if ($appointment) {
        $name = $appointment['name'];
        $email = $appointment['email'];
        $date = $appointment['date'];
        $time = $appointment['start_time'];
        $description = $appointment['description'];
        $location = $appointment['location'];

        // Check if another approved appointment exists at the same date, time, and location
        $check = $conn->prepare("SELECT * FROM appointments WHERE date = ? AND start_time = ? AND location = ? AND status = 'approved'");
        $check->bind_param("sss", $date, $time, $location);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();

        if ($existing) {
            echo "<p style='color:red; font-weight:bold;'>This date, time, and location is already booked. Please choose another schedule.</p>";
            echo "<a href='admin_appointments.php'>Back to Admin Dashboard</a>";
            exit;
        }

        // Google Calendar event (1-hour duration)
        $startDateTime = new DateTime("$date $time", new DateTimeZone("Asia/Manila"));
        $endDateTime = clone $startDateTime;
        $endDateTime->modify("+1 hour");

        $event = [
            "summary" => "Dental Appointment - $name",
            "description" => $description,
            "location" => $location,
            "start" => [
                "dateTime" => $startDateTime->format(DateTime::RFC3339),
                "timeZone" => "Asia/Manila"
            ],
            "end" => [
                "dateTime" => $endDateTime->format(DateTime::RFC3339),
                "timeZone" => "Asia/Manila"
            ],
            "attendees" => [
                ["email" => $email]
            ],
        ];

        // Send event to Google Calendar via NoCodeAPI
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://v1.nocodeapi.com/pamelanicole/calendar/fsNzvlDsMKVmoyNI/event",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($event),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($curl);
        $data = json_decode($response, true);
        curl_close($curl);

        // Update appointment status in database
        $stmt_update = $conn->prepare("UPDATE appointments SET status='approved' WHERE id=?");
        $stmt_update->bind_param("i", $id);
        $stmt_update->execute();

        if (isset($data['htmlLink'])) {
            echo "<p style='color:green; font-weight:bold;'>Appointment approved and synced with Google Calendar!</p>";
            echo "<p><a href='" . htmlspecialchars($data['htmlLink']) . "' target='_blank'>View in Google Calendar</a></p>";
        } else {
            echo "<p style='color:orange; font-weight:bold;'>Appointment approved but failed to sync with Google Calendar.</p>";
        }
    } else {
        echo "<p style='color:red;'>Appointment not found.</p>";
    }
}

echo "<br><a href='admin_appointments.php'>Back to Admin Dashboard</a>";
?>
