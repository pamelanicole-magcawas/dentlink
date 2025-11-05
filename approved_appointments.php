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
    <iframe src="https://calendar.google.com/calendar/embed?height=600&wkst=1&bgcolor=%23ffffff&ctz=Asia%2FManila&showTitle=1&showNav=1&showDate=1&showPrint=0&showTabs=1&showCalendars=0&showTz=0&mode=MONTH&src=c2dkZW50YWxjbGluaWNjY0BnbWFpbC5jb20&color=%234CAF50" style="border:solid 1px #777; width:100%; height:600px;" frameborder="0" scrolling="no"></iframe>
</body>
</html>