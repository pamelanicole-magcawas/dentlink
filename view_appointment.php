<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Fetch user's approved appointments
$approved_sql = "SELECT * FROM appointments WHERE user_id = ? AND status = 'approved' ORDER BY date DESC, start_time DESC";
$stmt = $conn->prepare($approved_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$approved = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e3f2fd 0%, #f0f8ff 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .header {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
            margin-bottom: 30px;
        }
        .appointment-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        .qr-section {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 2px solid #4CAF50;
        }
        .qr-section img {
            max-width: 250px;
            border: 3px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            background: white;
            margin: 15px 0;
        }
        .appointment-details {
            background: #e8f5e9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .appointment-details p {
            margin: 8px 0;
            color: #2e7d32;
        }
        .no-qr-message {
            color: #f57c00;
            font-weight: 500;
            padding: 15px;
            background: #fff3e0;
            border-radius: 8px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>

        <div class="header">
            <h1><i class="bi bi-calendar-check text-primary"></i> My Appointments</h1>
            <p class="text-muted mb-0">Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></p>
        </div>

        <?php if ($approved->num_rows > 0): ?>
            <?php while ($appt = $approved->fetch_assoc()): ?>
                <div class="appointment-card">
                    <h4 class="text-primary mb-3">
                        <i class="bi bi-clipboard2-pulse"></i> 
                        <?= htmlspecialchars($appt['description']) ?>
                    </h4>

                    <div class="appointment-details">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-calendar-date"></i> Date:</strong> 
                                    <?php 
                                    $date = new DateTime($appt['date']);
                                    echo $date->format('F j, Y (l)');
                                    ?>
                                </p>
                                <p><strong><i class="bi bi-clock"></i> Time:</strong> 
                                    <?php 
                                    $time = new DateTime($appt['start_time']);
                                    echo $time->format('g:i A');
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="bi bi-geo-alt"></i> Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                <p><strong><i class="bi bi-check-circle"></i> Status:</strong> 
                                    <span class="badge bg-success">Approved</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($appt['calendar_link'])): ?>
    <div class="mt-3 text-center">
        <a href="<?= htmlspecialchars($appt['calendar_link']) ?>" 
           target="_blank" 
           class="btn btn-outline-primary">
            <i class="bi bi-calendar-event"></i> View in Google Calendar
        </a>
    </div>
<?php endif; ?>

                    <?php if (!empty($appt['qr_code_url'])): ?>
                        <div class="qr-section">
                            <h5 class="text-success mb-3">
                                <i class="bi bi-qr-code"></i> Your Appointment QR Code
                            </h5>
                            <p class="text-muted">Present this QR code at the clinic for quick check-in</p>
                            <img src="<?= htmlspecialchars($appt['qr_code_url']) ?>" 
                                 alt="Appointment QR Code"
                                 id="qr_<?= $appt['id'] ?>">
                            <div class="mt-3">
                                <a href="<?= htmlspecialchars($appt['qr_code_url']) ?>" 
                                   download="dentlink_appointment_<?= $appt['id'] ?>.png" 
                                   class="btn btn-success me-2">
                                    <i class="bi bi-download"></i> Download QR Code
                                </a>
                                <button onclick="printQR(<?= $appt['id'] ?>, '<?= htmlspecialchars($appt['description']) ?>', '<?= $date->format('F j, Y') ?>', '<?= $time->format('g:i A') ?>')" 
                                        class="btn btn-outline-primary">
                                    <i class="bi bi-printer"></i> Print QR Code
                                </button>
                                <button onclick="shareQR('<?= htmlspecialchars($appt['qr_code_url']) ?>')" 
                                        class="btn btn-outline-secondary">
                                    <i class="bi bi-share"></i> Share
                                </button>
                            </div>
                            <p class="text-muted small mt-3">
                                <i class="bi bi-info-circle"></i> Save this QR code to your phone or take a screenshot
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="no-qr-message">
                            <i class="bi bi-exclamation-triangle"></i> 
                            QR code is being generated. Please refresh this page in a moment.
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info mt-3 mb-0">
                        <h6><i class="bi bi-info-circle"></i> Important Reminders:</h6>
                        <ul class="mb-0 small">
                            <li>Arrive 15 minutes before your appointment time</li>
                            <li>Bring a valid ID along with your QR code</li>
                            <li>Contact the clinic if you need to reschedule</li>
                        </ul>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="appointment-card text-center">
                <div style="font-size: 64px; color: #ccc; margin: 20px 0;">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h4 class="text-muted">No Approved Appointments Yet</h4>
                <p class="text-muted">Your approved appointments will appear here with QR codes</p>
                <a href="book_appointment.php" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Book New Appointment
                </a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="book_appointment.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Book Another Appointment
            </a>
        </div>
    </div>

    <script>
    function printQR(id, service, date, time) {
        var qrImage = document.getElementById('qr_' + id).src;
        var printWindow = window.open('', '', 'height=700,width=800');
        printWindow.document.write('<html><head><title>Print Appointment QR Code</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }');
        printWindow.document.write('.header { background: #4CAF50; color: white; padding: 20px; margin-bottom: 30px; }');
        printWindow.document.write('.details { background: #f5f5f5; padding: 20px; margin: 20px; border-radius: 10px; }');
        printWindow.document.write('img { max-width: 350px; border: 3px solid #4CAF50; padding: 20px; margin: 20px; background: white; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="header">');
        printWindow.document.write('<h1>🦷 DentLink Dental Clinic</h1>');
        printWindow.document.write('<h3>Appointment QR Code</h3>');
        printWindow.document.write('</div>');
        printWindow.document.write('<div class="details">');
        printWindow.document.write('<p><strong>Patient:</strong> <?= htmlspecialchars($full_name) ?></p>');
        printWindow.document.write('<p><strong>Service:</strong> ' + service + '</p>');
        printWindow.document.write('<p><strong>Date:</strong> ' + date + '</p>');
        printWindow.document.write('<p><strong>Time:</strong> ' + time + '</p>');
        printWindow.document.write('</div>');
        printWindow.document.write('<img src="' + qrImage + '" alt="QR Code">');
        printWindow.document.write('<p><strong>Please present this QR code at the clinic</strong></p>');
        printWindow.document.write('<p style="color: #666; font-size: 12px;">DentLink Dental Clinic | Lipa & San Pablo City</p>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
        }, 250);
    }

    function shareQR(qrUrl) {
        if (navigator.share) {
            navigator.share({
                title: 'My DentLink Appointment QR Code',
                text: 'Here is my dental appointment QR code',
                url: qrUrl
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback: Copy to clipboard
            navigator.clipboard.writeText(qrUrl).then(() => {
                alert('QR code link copied to clipboard!');
            }).catch(err => {
                alert('Share link: ' + qrUrl);
            });
        }
    }

    // Auto-refresh if QR code is missing (check every 30 seconds)
    <?php 
    $hasEmptyQR = false;
    mysqli_data_seek($approved, 0);
    while ($check = $approved->fetch_assoc()) {
        if (empty($check['qr_code_url'])) {
            $hasEmptyQR = true;
            break;
        }
    }
    if ($hasEmptyQR): 
    ?>
    setTimeout(function() {
        location.reload();
    }, 30000);
    <?php endif; ?>
    </script>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>