<?php
if (!isset($_GET['id'])) {
    die("No event ID provided.");
}

$event_id = $_GET['id'];

// You can replace these with dynamic POST data from a form
$summary = $_POST['summary'] ?? "Updated Dental Appointment - Patient Name";
$description = $_POST['description'] ?? "Rescheduled appointment with the dentist";
$start_datetime = $_POST['start_datetime'] ?? "2025-10-31T10:00:00+08:00";
$end_datetime = $_POST['end_datetime'] ?? "2025-10-31T11:00:00+08:00";

$updatedEvent = [
    "summary" => $summary,
    "description" => $description,
    "start" => [
        "dateTime" => $start_datetime,
        "timeZone" => "Asia/Manila"
    ],
    "end" => [
        "dateTime" => $end_datetime,
        "timeZone" => "Asia/Manila"
    ]
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://v1.nocodeapi.com/pamelanicole/calendar/fsNzvlDsMKVmoyNI/event/$event_id",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => json_encode($updatedEvent),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);

$response = curl_exec($curl);
$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$data = json_decode($response, true);

if ($http_status == 200 && isset($data['htmlLink'])) {
    echo "Event updated successfully!<br>";
    echo "<a href='" . htmlspecialchars($data['htmlLink']) . "' target='_blank'>View Updated Event on Google Calendar</a><br>";
} else {
    echo "Failed to update event.<br>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<br><a href='list_events.php'>Back to Appointments</a>";
?>
