<?php
// delete_event.php
if (!isset($_GET['id']) || !strlen($_GET['id'])) {
    die("No event ID provided.");
}
$event_id = urlencode($_GET['id']);
$API = "https://v1.nocodeapi.com/pamelanicole/calendar/fsNzvlDsMKVmoyNI/event/$event_id";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $API,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo "Error deleting event: " . htmlspecialchars($err);
} else {
    // NoCodeAPI returns empty body on success — show success and link back
    if (empty($response) || $response === 'null') {
        echo "<p style='color:green;'> Event deleted.</p>";
    } else {
        echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";
    }
}
echo "<p><a href='list_events.php'>Back to list</a></p>";
?>
