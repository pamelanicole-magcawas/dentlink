<?php
session_start(); 

include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch only approved appointments
$result = $conn->query("SELECT a.*, d.name AS dentist_name 
                        FROM appointments a
                        LEFT JOIN dentists d ON a.dentist_id = d.id
                        WHERE a.status = 'approved'
                        ORDER BY a.date DESC, a.start_time DESC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Approved Appointments - DentLink</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .btn-back-dashboard {
            margin: 20px;
            display: inline-block;
        }

        .calendar-wrapper iframe {
            width: 100%;
            height: 400px;
            border: 0;
            margin-bottom: 20px;
        }
    </style>
</head>

<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="admin-page-header">
        <h2><i class="bi bi-calendar-check-fill"></i> Approved Appointments</h2>
        <p>View all approved appointments on Google Calendar</p>
    </div>

    <div class="calendar-wrapper">
        <iframe src="https://calendar.google.com/calendar/embed?src=sgdentalcliniccc%40gmail.com&ctz=Asia%2FManila"></iframe>
    </div>

    <div class="record-section">
        <table id="appointmentsTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Dentist</th>
                    <th>Location</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($appt = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $appt['id'] ?></td>
                        <td><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></td>
                        <td><?= date('F j, Y', strtotime($appt['date'])) ?></td>
                        <td><?= date('g:i A', strtotime($appt['start_time'])) ?></td>
                        <td><?= htmlspecialchars($appt['dentist_name'] ?? 'Unassigned') ?></td>
                        <td><?= htmlspecialchars($appt['location']) ?></td>
                        <td><span style="color: #059669; font-weight:600;">Approved</span></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#appointmentsTable').DataTable({
                "order": [
                    [2, "desc"]
                ], // order by date descending
                "pageLength": 10
            });
        });
    </script>
</body>
</html>
