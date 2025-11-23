<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

// Fetch user's appointments
$approved_sql = "SELECT * FROM appointments WHERE user_id = ? ORDER BY date DESC, start_time DESC";
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            margin-bottom: 30px;
        }

        .appointment-card {
            position: relative;
            /* needed for the strip to align left */
            padding: 20px 20px 20px 20px;
            /* extra space for strip */
            margin-bottom: 15px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: transform 0.3s;
            display: flex;
            align-items: center;
        }

        .appointment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
        }

        .status-strip {
            position: absolute;
            /* makes strip appear on the left */
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
            border-radius: 12px 0 0 12px;
        }

        .status-approved {
            background-color: #28a745;
        }

        .status-pending {
            background-color: #fd7e14;
        }

        .status-denied {
            background-color: #dc3545;
        }

        .status-checked-in {
            background-color: #0d6efd;
        }

        .status-in-treatment {
            background-color: #6f42c1;
        }

        .status-completed {
            background-color: #6c757d;
        }

        .appointment-info h5 {
            margin: 0;
        }

        .appointment-info p {
            margin: 3px 0 0;
            color: #555;
        }

        .modal-body img {
            max-width: 200px;
            border: 3px solid #4CAF50;
            border-radius: 8px;
            padding: 10px;
            background: white;
            margin-bottom: 15px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 6px;
            text-transform: capitalize;
            font-size: 0.85rem;
            color: white;
        }

        .status-approved {
            background-color: #28a745;
        }

        .status-pending {
            background-color: #fd7e14;
        }

        .status-denied {
            background-color: #dc3545;
        }

        .status-checked-in {
            background-color: #0d6efd;
        }

        .status-in-treatment {
            background-color: #6f42c1;
        }

        .status-completed {
            background-color: #6c757d;
        }

        .modal-content.d-flex {
            display: flex;
            padding: 0;
        }

        .modal-status-strip {
            width: 8px;
            height: 100%;
            border-radius: 0;
            margin-right: 10px;
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
            <small class="text-muted">Click an appointment card to view full details</small>
        </div>

        <?php if ($approved->num_rows > 0): ?>
            <?php while ($appt = $approved->fetch_assoc()): ?>
                <?php $status_class = 'status-' . strtolower(str_replace(' ', '-', $appt['status'])); ?>
                <div class="appointment-card" data-bs-toggle="modal" data-bs-target="#apptModal<?= $appt['id'] ?>">
                    <div class="status-strip <?= $status_class ?>"></div>
                    <div class="appointment-info">
                        <h5><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></h5>
                        <p><?= (new DateTime($appt['date']))->format('F j, Y') ?> at <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                        <!-- Removed status badge from card -->
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="apptModal<?= $appt['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content d-flex">
                            <div class="modal-status-strip <?= $status_class ?>"></div>
                            <div class="modal-body flex-grow-1">
                                <div class="modal-header d-flex align-items-center">
                                    <h5 class="modal-title"><?= htmlspecialchars($appt['description'] ?: 'Appointment Details') ?></h5>
                                    <span class="status-badge <?= $status_class ?> ms-2"><?= htmlspecialchars($appt['status']) ?></span>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <p><strong>Date:</strong> <?= (new DateTime($appt['date']))->format('F j, Y (l)') ?></p>
                                <p><strong>Time:</strong> <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                <p><strong>Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                <?php if (!empty($appt['description'])): ?>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($appt['description']) ?></p>
                                <?php endif; ?>

                                <?php if (!empty($appt['qr_code_url'])): ?>
                                    <div class="text-center">
                                        <img src="<?= htmlspecialchars($appt['qr_code_url']) ?>" alt="QR Code" id="qr_<?= $appt['id'] ?>">
                                        <div class="mt-2">
                                            <a href="<?= htmlspecialchars($appt['qr_code_url']) ?>" download="dentlink_appointment_<?= $appt['id'] ?>.png" class="btn btn-success btn-sm me-2"><i class="bi bi-download"></i> Download</a>
                                            <button onclick="printQR(<?= $appt['id'] ?>,'<?= htmlspecialchars($appt['description']) ?>','<?= (new DateTime($appt['date']))->format('F j, Y') ?>','<?= (new DateTime($appt['start_time']))->format('g:i A') ?>')" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-printer"></i> Print</button>
                                            <button onclick="shareQR('<?= htmlspecialchars($appt['qr_code_url']) ?>')" class="btn btn-outline-secondary btn-sm"><i class="bi bi-share"></i> Share</button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mt-2">QR code is being generated. Please refresh later.</div>
                                <?php endif; ?>

                                <?php if (!empty($appt['calendar_link'])): ?>
                                    <p class="mt-2"><a href="<?= htmlspecialchars($appt['calendar_link']) ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-event"></i> View in Google Calendar</a></p>
                                <?php endif; ?>

                                <div class="alert alert-info mt-2">
                                    <h6>Important Reminders:</h6>
                                    <ul class="mb-0 small">
                                        <li>Arrive 15 minutes before your appointment time</li>
                                        <li>Bring a valid ID along with your QR code</li>
                                        <li>Contact the clinic if you need to reschedule</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="appointment-card text-center">
                <div style="font-size: 64px; color: #ccc; margin: 20px 0;"><i class="bi bi-calendar-x"></i></div>
                <h4 class="text-muted">No Appointments Found</h4>
                <p class="text-muted">Book your appointment to see it here.</p>
                <a href="book_appointment.php" class="btn btn-primary mt-3"><i class="bi bi-plus-circle"></i> Book Appointment</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function printQR(id, service, date, time) {
            var qrImage = document.getElementById('qr_' + id).src;
            var printWindow = window.open('', '', 'height=700,width=800');
            printWindow.document.write('<html><head><title>Print QR</title><style>body{text-align:center;font-family:Arial;padding:50px}img{max-width:350px;border:3px solid #4CAF50;padding:20px;border-radius:10px}</style></head><body>');
            printWindow.document.write('<h1>DentLink Dental Clinic</h1><h3>Appointment QR Code</h3>');
            printWindow.document.write('<p><strong>Patient:</strong> <?= htmlspecialchars($full_name) ?></p>');
            printWindow.document.write('<p><strong>Service:</strong>' + service + '</p>');
            printWindow.document.write('<p><strong>Date:</strong>' + date + '</p>');
            printWindow.document.write('<p><strong>Time:</strong>' + time + '</p>');
            printWindow.document.write('<img src="' + qrImage + '" alt="QR Code">');
            printWindow.document.write('<p><strong>Please present this QR code at the clinic</strong></p>');
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => printWindow.print(), 250);
        }

        function shareQR(qrUrl) {
            if (navigator.share) {
                navigator.share({
                    title: 'My DentLink Appointment QR Code',
                    text: 'Here is my dental appointment QR code',
                    url: qrUrl
                }).catch(err => console.log(err));
            } else {
                navigator.clipboard.writeText(qrUrl).then(() => alert('QR code link copied!')).catch(() => alert('QR code link: ' + qrUrl));
            }
        }
    </script>
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>