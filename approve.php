<?php
require_once 'db_connect.php';
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['id'])) {
    echo "<div style='text-align:center;padding:40px;'><h2 style='color:red;'>⚠️ Invalid request.</h2><a href='admin_appointments.php'>Back</a></div>";
    exit;
}

$id = intval($_POST['id']);

// 1) Fetch appointment + user info (join on user_id)
$sql = "
    SELECT a.*, u.email AS user_email, u.first_name, u.last_name,
           CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
           d.name AS dentist_name
    FROM appointments a
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN dentists d ON a.dentist_id = d.id
    WHERE a.id = ?
    LIMIT 1
";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("<pre>Prepare failed: " . htmlspecialchars($conn->error) . "</pre>");
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$appointment = $result->fetch_assoc();
$stmt->close();

if (!$appointment) {
    die("<div style='text-align:center;padding:40px;'><h2 style='color:red;'> Appointment not found.</h2><a href='admin_appointments.php'>Back</a></div>");
}

// Get dentist info if assigned
$dentistName = $appointment['dentist_name'] ?? 'Unassigned';

// 2) Mark appointment approved
$updateStmt = $conn->prepare("UPDATE appointments SET status = 'approved' WHERE id = ?");
if (!$updateStmt) {
    die("<pre>Prepare failed (update): " . htmlspecialchars($conn->error) . "</pre>");
}
$updateStmt->bind_param("i", $id);
$updateStmt->execute();
$updateStmt->close();

// 3) Create Google Calendar event via NoCodeAPI (replace endpoint if different)
$startDT = new DateTime($appointment['date'] . ' ' . $appointment['start_time'], new DateTimeZone('Asia/Manila'));
$endDT = clone $startDT;
$endDT->modify('+1 hour'); // default 1 hour event

$calendarEvent = [
    "summary" => "🦷 " . $appointment['description'] . " — " . $appointment['patient_name'],
    "description" => "Patient: {$appointment['patient_name']}\nEmail: {$appointment['user_email']}\nService: {$appointment['description']}\nLocation: {$appointment['location']}\nStatus: Approved",
    "location" => $appointment['location'],
    "start" => [
        "dateTime" => $startDT->format(DateTime::RFC3339),
        "timeZone" => "Asia/Manila",
    ],
    "end" => [
        "dateTime" => $endDT->format(DateTime::RFC3339),
        "timeZone" => "Asia/Manila",
    ],
    "attendees" => [
        ["email" => $appointment['user_email']],
        ["email" => "sgdentalcliniccc@gmail.com"] // clinic calendar account
    ],
    // request reminders
    "reminders" => [
        "useDefault" => false,
        "overrides" => [
            ["method" => "email", "minutes" => 24*60],
            ["method" => "popup", "minutes" => 30]
        ]
    ],
    // ask NoCodeAPI to send invites (depends on NoCodeAPI support)
    "sendUpdates" => "all",
    "sendNotifications" => true
];

$calendarUrl = "https://v1.nocodeapi.com/sgdentalclinic/calendar/BjRLPQRVQhlpwKXa/event"; // keep your NoCodeAPI endpoint
$ch = curl_init($calendarUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($calendarEvent),
    CURLOPT_TIMEOUT => 30
]);
$calendarResponse = curl_exec($ch);
$calendarHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

$calendarData = json_decode($calendarResponse, true);
$calendarLink = $calendarData['htmlLink'] ?? '';
$eventId = $calendarData['id'] ?? '';
$calendarSuccess = ($calendarHttp === 200 || $calendarHttp === 201) && !empty($calendarLink);

// 4) Generate QR using JSON payload (best for scanners)
$dateFormatted = date('F j, Y (l)', strtotime($appointment['date']));
$timeFormatted = date('g:i A', strtotime($appointment['start_time']));

$qrPayload = [
    "id" => (int)$appointment['id'],
    "patient" => $appointment['patient_name'],
    "email" => $appointment['user_email'],
    "service" => $appointment['description'],
    "date" => $dateFormatted,
    "time" => $timeFormatted,
    "location" => $appointment['location'],
    "dentist" => $dentistName, // <-- added this line
    "status" => "approved"
];

// Convert JSON → string
$qrText = json_encode($qrPayload, JSON_UNESCAPED_UNICODE);

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Filename + Path
$fileName = "qr_appointment_" . $appointment['id'] . ".png";
$localPath = $uploadDir . '/' . $fileName;
$relativeUrl = "uploads/" . $fileName;

// QR API
$qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=800x800&data=" . urlencode($qrText);


// Download QR image
$qrImageData = @file_get_contents($qrApiUrl);

$qrSuccess = false;
if ($qrImageData !== false) {
    if (@file_put_contents($localPath, $qrImageData) !== false) {
        $qrSuccess = true;
        $qrUrlToSave = $relativeUrl;
    }
}

if (!$qrSuccess) {
    $qrUrlToSave = '';
}

// Save QR path & calendar link to DB
$saveStmt = $conn->prepare("UPDATE appointments SET qr_code_url = ?, calendar_link = ? WHERE id = ?");
$saveStmt->bind_param("ssi", $qrUrlToSave, $calendarLink, $id);
$saveStmt->execute();
$saveStmt->close();

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Appointment Approved</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: linear-gradient(135deg,#eaf6ff 0%, #fff 100%); font-family: Poppins, Arial; padding: 30px; }
        .card { max-width: 820px; margin: 0 auto; padding: 30px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); background: white; }
        .qr-img { max-width: 320px; border: 3px solid #4CAF50; padding: 12px; background: #fff; border-radius: 8px; }
        pre.debug { background: #f8f9fa; padding: 12px; border-radius: 8px; overflow:auto; }
    </style>
</head>
<body>
<div class="card">
    <div class="text-center mb-3">
        <h2 class="text-success"><i class="bi bi-check-circle-fill"></i> Appointment Approved</h2>
        <p class="mb-0"><strong><?= htmlspecialchars($appointment['patient_name']) ?></strong></p>
        <small class="text-muted"><?= htmlspecialchars($appointment['user_email']) ?></small>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="mb-3">
                <p><strong>Service:</strong> <?= htmlspecialchars($appointment['description']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($dateFormatted) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($timeFormatted) ?></p>
                <p><strong>Dentist:</strong> <?= htmlspecialchars($dentistName) ?></p>             
                <p><strong>Location:</strong> <?= htmlspecialchars($appointment['location']) ?></p>
            </div>

            <div class="mt-3">
                <h6>Integration Status</h6>
                <ul>
                    <li>Database updated — <strong class="text-success">OK</strong></li>
                    <li>Google Calendar — 
                        <?php if ($calendarSuccess): ?>
                            <strong class="text-success">Created</strong>
                        <?php else: ?>
                            <strong class="text-warning">Failed</strong>
                        <?php endif; ?>
                    </li>
                    <li>Email invite — <strong class="<?= $calendarSuccess ? 'text-success' : 'text-warning' ?>"><?= $calendarSuccess ? 'Sent (Google Calendar invite)' : 'Not sent' ?></strong></li>
                    <li>QR Code — <strong class="<?= $qrSuccess ? 'text-success' : 'text-warning' ?>"><?= $qrSuccess ? 'Generated' : 'Failed' ?></strong></li>
                </ul>

                <?php if (!empty($calendarLink)): ?>
                    <p><a href="<?= htmlspecialchars($calendarLink) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="bi bi-calendar-check"></i> View event in Google Calendar</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6 text-center">
            <?php if ($qrSuccess && !empty($qrUrlToSave)): ?>
                <h6 class="mb-2">QR Code (scan to view details)</h6>
                <img src="<?= htmlspecialchars($qrUrlToSave) ?>" alt="QR Code" class="qr-img mb-2">
                <p><a href="<?= htmlspecialchars($qrUrlToSave) ?>" download="appointment_<?= $appointment['id'] ?>_qr.png" class="btn btn-success btn-sm"><i class="bi bi-download"></i> Download QR</a></p>

                <details class="mt-3 text-start mx-auto" style="max-width:420px;">
                    <summary class="btn btn-outline-secondary btn-sm">Preview QR contents</summary>
                    <pre class="debug"><?= htmlspecialchars($qrText) ?></pre>
                </details>
            <?php else: ?>
                <div class="alert alert-warning"> QR generation failed. See debug info below.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$calendarSuccess || !$qrSuccess): ?>
        <hr>
        <h6>Debug</h6>
        <?php if (!$calendarSuccess): ?>
            <p><strong>Calendar API response (HTTP <?= htmlspecialchars($calendarHttp) ?>):</strong></p>
            <pre class="debug"><?= htmlspecialchars($calendarResponse) ?></pre>
        <?php endif; ?>

        <?php if (!$qrSuccess): ?>
            <p><strong>QR API call URL:</strong> <code><?= htmlspecialchars($qrApiUrl) ?></code></p>
            <?php if (isset($qrApiError)): ?>
                <p><strong>QR error:</strong> <?= htmlspecialchars($qrApiError) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <div class="mt-4 text-center">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Admin</a>
    </div>
</div>
</body>
</html>