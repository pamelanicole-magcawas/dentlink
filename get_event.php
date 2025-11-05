<?php
include 'calendar_api.php';

if (!isset($_GET['id'])) {
    die("⚠️ No event ID provided.");
}

$event_id = $_GET['id'];
$data = get_event($event_id); // use your helper function

if (isset($data['summary'])) {
    echo "<h2>🦷 Appointment Details</h2>";
    echo "<p><b>Title:</b> " . htmlspecialchars($data['summary']) . "</p>";
    echo "<p><b>Description:</b> " . htmlspecialchars($data['description'] ?? 'N/A') . "</p>";
    echo "<p><b>Location:</b> " . htmlspecialchars($data['location'] ?? 'N/A') . "</p>";
    echo "<p><b>Start:</b> " . htmlspecialchars($data['start']['dateTime'] ?? $data['start']['date']) . "</p>";
    echo "<p><b>End:</b> " . htmlspecialchars($data['end']['dateTime'] ?? $data['end']['date']) . "</p>";
    echo "<p><a href='" . htmlspecialchars($data['htmlLink']) . "' target='_blank'>View on Google Calendar</a></p>";
} else {
    echo "<h3>Event not found or invalid ID.</h3>";
    echo "<pre>" . htmlspecialchars(json_encode($data, JSON_PRETTY_PRINT)) . "</pre>";
}

?>
