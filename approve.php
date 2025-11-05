<?php
require_once 'db_connect.php';
date_default_timezone_set('Asia/Manila');

// Replace with your actual site URL (used for QR scanning)
$qrLinkBase = "https://yourdomain.com/view_qr.php?id=";

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // 1️⃣ Fetch appointment and user info
    $stmt = $conn->prepare("
        SELECT a.*, u.email AS user_email, CONCAT(u.first_name, ' ', u.last_name) AS patient_name
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.user_id
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $appointment = $stmt->get_result()->fetch_assoc();

    if (!$appointment) {
        die("<h2 style='color:red;text-align:center;'>❌ Appointment not found.</h2>");
    }

    // 2️⃣ Mark appointment as approved
    $updateStmt = $conn->prepare("UPDATE appointments SET status='approved' WHERE id=?");
    $updateStmt->bind_param("i", $id);
    $updateStmt->execute();

    // 3️⃣ Prepare Google Calendar event data
    $start = new DateTime($appointment['date'] . ' ' . $appointment['start_time'], new DateTimeZone('Asia/Manila'));
    $end = clone $start;
    $end->modify('+1 hour');

    $eventData = [
        "summary" => "🦷 {$appointment['description']} - {$appointment['patient_name']}",
        "description" => "Patient: {$appointment['patient_name']}\nService: {$appointment['description']}\nLocation: {$appointment['location']}\nStatus: Approved",
        "start" => [
            "dateTime" => $start->format(DateTime::RFC3339),
            "timeZone" => "Asia/Manila"
        ],
        "end" => [
            "dateTime" => $end->format(DateTime::RFC3339),
            "timeZone" => "Asia/Manila"
        ],
        "attendees" => [
            ["email" => $appointment['user_email']],
            ["email" => "sgdentalcliniccc@gmail.com"]
        ]
    ];

    // 4️⃣ Create event in Google Calendar (NoCodeAPI)
    $calendarUrl = "https://v1.nocodeapi.com/sgdentalclinic/calendar/BjRLPQRVQhlpwKXa/event";
    $ch = curl_init($calendarUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($eventData)
    ]);
    $calendarResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $calendarData = json_decode($calendarResponse, true);
    $calendarLink = $calendarData['htmlLink'] ?? '';
    $calendarSuccess = ($httpCode == 200 && !empty($calendarLink));

    // 5️⃣ Generate QR code that links to appointment details
    $qrLink = $qrLinkBase . $appointment['id']; // e.g. https://yourdomain.com/view_qr.php?id=12
    $qrPayload = ["data" => $qrLink];

    $qrCurl = curl_init("https://v1.nocodeapi.com/sgdentalclinic/qrCode/WVWWQXdPMxuoPhnV/qrimage");
    curl_setopt_array($qrCurl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($qrPayload)
    ]);
    $qrResponse = curl_exec($qrCurl);
    $qrHttp = curl_getinfo($qrCurl, CURLINFO_HTTP_CODE);
    curl_close($qrCurl);

    $qrResult = json_decode($qrResponse, true);
    $qrData = $qrResult['blob']['data'] ?? null;
    $qrUrl = '';

    if ($qrHttp === 200 && $qrData) {
        $uploadDir = __DIR__ . "/uploads";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $fileName = "qr_appointment_{$appointment['id']}.png";
        file_put_contents("$uploadDir/$fileName", base64_decode($qrData));
        $qrUrl = "uploads/$fileName";
    }

    // 6️⃣ Save QR URL + Calendar Link
    $saveStmt = $conn->prepare("UPDATE appointments SET qr_code_url=?, calendar_link=? WHERE id=?");
    $saveStmt->bind_param("ssi", $qrUrl, $calendarLink, $id);
    $saveStmt->execute();

    // 7️⃣ Send confirmation email via NoCodeAPI Gmail
    $emailBody = "
        <h2>🦷 DentLink Appointment Approved</h2>
        <p>Hello <strong>{$appointment['patient_name']}</strong>,</p>
        <p>Your dental appointment has been <strong>approved</strong>.</p>
        <ul>
            <li><b>Service:</b> {$appointment['description']}</li>
            <li><b>Date:</b> {$appointment['date']}</li>
            <li><b>Time:</b> {$appointment['start_time']}</li>
            <li><b>Location:</b> {$appointment['location']}</li>
        </ul>
        <p>You can check your appointment on Google Calendar <a href='{$calendarLink}'>here</a>.</p>
        <p>Scan your QR code for quick check-in at the clinic.</p>
        <br>
        <p>🦷 <strong>DentLink Dental Clinic</strong></p>
    ";

    $emailPayload = [
        "to" => $appointment['user_email'],
        "subject" => "DentLink Appointment Approved",
        "html" => $emailBody
    ];

    $emailCurl = curl_init("https://v1.nocodeapi.com/sgdentalclinic/gmail/BjRLPQRVQhlpwKXa/sendEmail");
    curl_setopt_array($emailCurl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS => json_encode($emailPayload)
    ]);
    $emailResponse = curl_exec($emailCurl);
    curl_close($emailCurl);

    // 8️⃣ Done — Show confirmation page
    include 'approve_success_view.php';

} else {
    echo "<div style='text-align:center;'><h2>⚠️ Invalid request.</h2></div>";
}
?>
