<?php
// get_event.php

if (!isset($_GET['id'])) {
    die("⚠️ No event ID provided.");
}

$event_id = $_GET['id'];

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://v1.nocodeapi.com/pamelanicole/calendar/fsNzvlDsMKVmoyNI/event/$event_id",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
));

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

if (isset($data['summary'])) {
    echo "<h2>🦷 Appointment Details</h2>";
    echo "<p><b>Title:</b> " . htmlspecialchars($data['summary']) . "</p>";
    echo "<p><b>Description:</b> " . htmlspecialchars($data['description'] ?? 'N/A') . "</p>";
    echo "<p><b>Location:</b> " . htmlspecialchars($data['location'] ?? 'N/A') . "</p>"; // Added location
    echo "<p><b>Start:</b> " . htmlspecialchars($data['start']['dateTime']) . "</p>";
    echo "<p><b>End:</b> " . htmlspecialchars($data['end']['dateTime']) . "</p>";
    echo "<p><a href='" . htmlspecialchars($data['htmlLink']) . "' target='_blank'>View on Google Calendar</a></p>";
} else {
    echo "<h3>Event not found or invalid ID.</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<br><a href='list_events.php'>Back to Appointments</a>";
?>
