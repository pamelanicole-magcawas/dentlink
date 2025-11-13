<?php
include 'db_connect.php';
$result = $conn->query("SELECT * FROM appointments ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Approved Appointments</title>
    <style>
        iframe {
            border: 1px solid #ccc;
            width: 100%;
            height: 600px;
            margin-top: 30px;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <h3>📅 Google Calendar (Full Details View - Admin Only)</h3>
    <iframe src="https://calendar.google.com/calendar/embed?src=sgdentalcliniccc%40gmail.com&ctz=Asia%2FManila"></iframe>
</body>
</html>