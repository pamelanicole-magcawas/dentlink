<?php
include 'db_connect.php';
$result = $conn->query("SELECT * FROM appointments ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approved Appointments - DentLink</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="admin-page-header">
        <h2><i class="bi bi-calendar-check-fill"></i> Approved Appointments</h2>
        <p>View all approved appointments on Google Calendar</p>
    </div>

    <div class="calendar-wrapper">
        <iframe src="https://calendar.google.com/calendar/embed?src=sgdentalcliniccc%40gmail.com&ctz=Asia%2FManila"></iframe>
    </div>

</body>
</html>