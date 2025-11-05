<?php
// include 'calendar_api.php';

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $name = $_POST["name"];
//     $email = $_POST["email"];
//     $date = $_POST["date"];
//     $start_time = $_POST["start_time"];
//     $description = $_POST["description"];
//     $location = $_POST["location"];

//     $startDateTime = new DateTime("$date $start_time", new DateTimeZone("Asia/Manila"));
//     $endDateTime = clone $startDateTime;
//     $endDateTime->modify("+1 hour");

//     $event = [
//         "summary" => "Dental Appointment - $name",
//         "location" => $location,
//         "description" => $description,
//         "start" => ["dateTime" => $startDateTime->format(DateTime::RFC3339), "timeZone" => "Asia/Manila"],
//         "end" => ["dateTime" => $endDateTime->format(DateTime::RFC3339), "timeZone" => "Asia/Manila"],
//         "attendees" => [["email" => $email]]
//     ];

//     $data = create_event($event);

//     if (isset($data['htmlLink'])) {
//         echo "<h3>Appointment successfully booked!</h3>";
//         echo "<p><a href='{$data['htmlLink']}' target='_blank'>View in Google Calendar</a></p>";
//     } else {
//         echo "<p>Failed to create appointment.</p>";
//         echo "<pre>" . htmlspecialchars(json_encode($data)) . "</pre>";
//     }
// }
?>
