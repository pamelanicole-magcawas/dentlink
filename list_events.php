<?php
include 'calendar_api.php';

$events = list_events();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dental Clinic Appointments</title>
<style>
 body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f8f9fa;
        }
        h1 {
            text-align: center;
            color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        th {
            background: #007bff;
            color: white;
        }
        a.button {
            padding: 6px 10px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .view { background: #28a745; }
        .update { background: #ffc107; }
        .delete { background: #dc3545; }
        .create {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 10px 14px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .create:hover { background: #0056b3; }
</style>
</head>
<body>
<h1>🦷 Dental Clinic Appointments</h1>
<a href="create_event.php">Create New Appointment</a>

<?php
if (empty($events['items'])) {
    echo "<p>No appointments found.</p>";
} else {
    echo "<table>";
    echo "<tr><th>Title</th><th>Description</th><th>Location</th><th>Start</th><th>End</th><th>Actions</th></tr>";

    foreach ($events['items'] as $event) {
        $id = htmlspecialchars($event['id']);
        $title = htmlspecialchars($event['summary'] ?? 'Untitled');
        $desc = htmlspecialchars($event['description'] ?? 'N/A');
        $location = htmlspecialchars($event['location'] ?? 'N/A');
        $start = htmlspecialchars($event['start']['dateTime'] ?? $event['start']['date'] ?? '');
        $end = htmlspecialchars($event['end']['dateTime'] ?? $event['end']['date'] ?? '');

        echo "<tr>
                <td>$title</td>
                <td>$desc</td>
                <td>$location</td>
                <td>$start</td>
                <td>$end</td>
                <td>
                    <a href='get_event.php?id=$id'>View</a>
                    <a href='delete_event.php?id=$id' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </td>
              </tr>";
    }

    echo "</table>";
}
?>
</body>
</html>