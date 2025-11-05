<?php
include 'calendar_api.php';

if (!isset($_GET['id']) || empty($_GET['id'])) die("No event ID provided.");

$data = delete_event($_GET['id']);

if (isset($data['error'])) {
    echo "Error deleting event: " . htmlspecialchars($data['error']);
} else {
    echo "<p style='color:green;'> Event deleted successfully.</p>";
}

echo "<p><a href='list_events.php'>Back to Appointments</a></p>";
?>
