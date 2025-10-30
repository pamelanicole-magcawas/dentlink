<?php
include 'db_connect.php';

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $date = $_POST['date'];
    $location = $_POST['location'];
    $time = $_POST['start_time'];
    $description = trim($_POST['description']);

    // Check conflict with approved appointments
    $conflict_sql = "SELECT * FROM appointments 
                     WHERE status = 'approved' 
                     AND date = ? 
                     AND start_time = ? 
                     AND location = ?";
    $stmt = $conn->prepare($conflict_sql);
    $stmt->bind_param("sss", $date, $time, $location);
    $stmt->execute();
    $conflict_result = $stmt->get_result();

    if ($conflict_result->num_rows > 0) {
        echo "<p style='color: red; font-weight: bold;'>Sorry, this time slot is already booked at $location. Please choose another time.</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO appointments (name, email, date, location, start_time, description, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssssss", $name, $email, $date, $location, $time, $description);
        if ($stmt->execute()) {
            echo "<p style='color: green; font-weight: bold;'>Appointment request sent for $location! Please wait for admin approval.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>Failed to submit appointment. Please try again.</p>";
        }
    }
}

// Fetch pending and denied appointments
$pending = $conn->query("SELECT * FROM appointments WHERE status = 'pending' ORDER BY date DESC");
$denied = $conn->query("SELECT * FROM appointments WHERE status = 'denied' ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Your Dental Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        iframe {
            border: 1px solid #ccc;
            border-radius: 8px;
            width: 100%;
            height: 500px;
            margin-bottom: 30px;
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            gap: 20px;
        }
        form {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            flex: 2;
        }
        .sidebar {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            max-height: 600px;
        }
        .sidebar h3 { text-align: center; margin-bottom: 10px; }
        .appointment-item {
            border-bottom: 1px solid #ddd;
            padding: 8px 0;
            font-size: 14px;
        }
        .appointment-item:last-child { border-bottom: none; }
        label { font-weight: bold; }
        input, textarea, select, button {
            width: 100%;
            margin: 8px 0;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>

<h1>🦷 Book Your Dental Appointment</h1>

<!-- Show approved appointments in Google Calendar -->
<iframe
    src="https://calendar.google.com/calendar/embed?src=allimagcawas%40gmail.com&ctz=Asia%2FManila"
    frameborder="0" scrolling="no"></iframe>

<div class="container">
    <!-- Booking Form -->
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Date:</label>
        <input type="date" name="date" required>

        <label>Location:</label>
        <select name="location" required>
            <option value="">--Select Location--</option>
            <option value="Dental Clinic, Lipa City">Lipa</option>
            <option value="Dental Clinic, San Pablo City">San Pablo</option>
        </select>

        <label>Start Time:</label>
        <input type="time" name="start_time" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <button type="submit">Submit Booking</button>
    </form>

    <!-- Sidebar with pending and denied -->
    <div class="sidebar">
        <h3>Pending Requests</h3>
        <?php if ($pending->num_rows > 0) { ?>
            <?php while ($p = $pending->fetch_assoc()) { ?>
                <div class="appointment-item">
                    <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                    <?= htmlspecialchars($p['date']) ?> @ <?= htmlspecialchars($p['start_time']) ?><br>
                    <em><?= htmlspecialchars($p['description']) ?></em><br>
                    <small><b>Location:</b> <?= htmlspecialchars($p['location']) ?></small>
                </div>
            <?php } ?>
        <?php } else { echo "<p>No pending requests.</p>"; } ?>

        <h3 style="margin-top: 20px;">Denied Requests</h3>
        <?php if ($denied->num_rows > 0) { ?>
            <?php while ($d = $denied->fetch_assoc()) { ?>
                <div class="appointment-item" style="color: #b30000;">
                    <strong><?= htmlspecialchars($d['name']) ?></strong><br>
                    <?= htmlspecialchars($d['date']) ?> @ <?= htmlspecialchars($d['start_time']) ?><br>
                    <em><?= htmlspecialchars($d['description']) ?></em><br>
                    <small><b>Location:</b> <?= htmlspecialchars($d['location']) ?></small>
                </div>
            <?php } ?>
        <?php } else { echo "<p>No denied requests.</p>"; } ?>
    </div>
</div>

</body>
</html>
