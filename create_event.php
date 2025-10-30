<?php
// create_event.php — Creates appointment in Google Calendar via NoCodeAPI

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $date = $_POST["date"];
    $start_time = $_POST["start_time"];
    $description = $_POST["description"];
    $location = $_POST["location"];

    // Default 1-hour duration
    $startDateTime = new DateTime("$date $start_time", new DateTimeZone("Asia/Manila"));
    $endDateTime = clone $startDateTime;
    $endDateTime->modify("+1 hour"); // default duration

    $event = [
        "summary" => "Dental Appointment - $name",
        "location" => $location, 
        "description" => $description,
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
        ]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://v1.nocodeapi.com/pamelanicole/calendar/fsNzvlDsMKVmoyNI/event",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($event),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

    if (isset($data['htmlLink'])) {
        echo "<h3>Appointment successfully booked!</h3>";
        echo "<p><a href='{$data['htmlLink']}' target='_blank'>View in Google Calendar</a></p>";
    } else {
        echo "<p>Failed to create appointment.</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}
?>
