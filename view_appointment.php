<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];

$appointments_sql = "
    SELECT a.*, 
           u.first_name, u.last_name,
           CONCAT(u.first_name,' ',u.last_name) AS patient_name,
           d.name AS dentist_name
    FROM appointments a
    LEFT JOIN users u ON a.user_id = u.user_id
    LEFT JOIN dentists d ON a.dentist_id = d.id
    WHERE a.user_id = ?
    ORDER BY a.date DESC, a.start_time DESC
";

$stmt = $conn->prepare($appointments_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Group Appointments
$pending = [];
$approvedArr = [];
$checkedIn = [];
$completed = [];
$deniedArr = [];

while ($appt = $result->fetch_assoc()) {
    switch (strtolower($appt['status'])) {
        case 'pending':
            $pending[] = $appt;
            break;
        case 'approved':
            $approvedArr[] = $appt;
            break;
        case 'checked-in':
            $checkedIn[] = $appt;
            break;
        case 'completed':
            $completed[] = $appt;
            break;
        case 'denied':
            $deniedArr[] = $appt;
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="patient.css">
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #B4DEBD 0%, #FFF7DD 100%);
            min-height: 100vh;
            padding: 20px;
        }

        /* Status Section Cards */
        .status-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .status-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid;
        }

        .status-header h4 {
            margin: 0;
            font-weight: 600;
        }

        /* Count pill — softened */
        .status-count {
            background: rgba(0, 0, 0, 0.15);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Pastel Status Colors (Updated) */
        /* Pending */
        .status-section.pending .status-header {
            border-color: #E7C892;
        }

        .status-section.pending .status-header h4 {
            color: #B48945;
        }

        .status-section.pending .status-count {
            background: #E7C892;
        }

        /* Approved */
        .status-section.approved .status-header {
            border-color: #88C49F;
        }

        .status-section.approved .status-header h4 {
            color: #4E8A62;
        }

        .status-section.approved .status-count {
            background: #88C49F;
        }

        /* Checked-In */
        .status-section.checked-in .status-header {
            border-color: #80A1BA;
        }

        .status-section.checked-in .status-header h4 {
            color: #5D7C95;
        }

        .status-section.checked-in .status-count {
            background: #80A1BA;
        }

        /* Completed */
        .status-section.completed .status-header {
            border-color: #6FA8A3;
        }

        .status-section.completed .status-header h4 {
            color: #467974;
        }

        .status-section.completed .status-count {
            background: #6FA8A3;
        }

        /* Denied */
        .status-section.denied .status-header {
            border-color: #D9A5A5;
        }

        .status-section.denied .status-header h4 {
            color: #A85E5E;
        }

        .status-section.denied .status-count {
            background: #D9A5A5;
        }

        /* Grid Layout */
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        /* Titles */
        .page-header h1 {
            color: #2C3E50;
            font-weight: 600;
        }

        .page-header p {
            color: #5A6C7D;
        }

        /* Status Strips – Pastel Theme */
        .status-strip.status-pending {
            background: linear-gradient(135deg, #F3D9AA 0%, #E7C892 100%);
        }

        .status-strip.status-approved {
            background: linear-gradient(135deg, #A2D8B3 0%, #88C49F 100%);
        }

        .status-strip.status-checked-in {
            background: linear-gradient(135deg, #A8C2D3 0%, #80A1BA 100%);
        }

        .status-strip.status-completed {
            background: linear-gradient(135deg, #8BBDB8 0%, #6FA8A3 100%);
        }

        .status-strip.status-denied {
            background: linear-gradient(135deg, #E6BBBB 0%, #D9A5A5 100%);
        }

        /* Modal strips use the same aesthetic */
        .modal-status-strip.status-pending {
            background: linear-gradient(135deg, #F3D9AA 0%, #E7C892 100%);
        }

        .modal-status-strip.status-approved {
            background: linear-gradient(135deg, #A2D8B3 0%, #88C49F 100%);
        }

        .modal-status-strip.status-checked-in {
            background: linear-gradient(135deg, #A8C2D3 0%, #80A1BA 100%);
        }

        .modal-status-strip.status-completed {
            background: linear-gradient(135deg, #8BBDB8 0%, #6FA8A3 100%);
        }

        .modal-status-strip.status-denied {
            background: linear-gradient(135deg, #E6BBBB 0%, #D9A5A5 100%);
        }

        /* Status Badges – Soft Borders */
        .status-badge.status-pending {
            background: #FFF7DD;
            color: #B48945;
            border: 1px solid #E7C892;
        }

        .status-badge.status-approved {
            background: #EEF8F1;
            color: #4E8A62;
            border: 1px solid #88C49F;
        }

        .status-badge.status-checked-in {
            background: #EBF1F6;
            color: #5D7C95;
            border: 1px solid #80A1BA;
        }

        .status-badge.status-completed {
            background: #E7F4F3;
            color: #467974;
            border: 1px solid #6FA8A3;
        }

        .status-badge.status-denied {
            background: #FBECEC;
            color: #A85E5E;
            border: 1px solid #D9A5A5;
        }

        /* Card */
        .appointment-card {
            cursor: pointer;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: white;
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .status-strip {
            height: 6px;
            width: 100%;
        }

        .appointment-info {
            padding: 20px;
        }

        .appointment-info h5 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #2C3E50;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .appointment-info p {
            color: #5A6C7D;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .appointment-info p i {
            margin-right: 5px;
            color: #7B8FA1;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #95A5A6;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .empty-state p {
            margin: 0;
            font-size: 0.95rem;
        }

        /* Modal */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }

        .modal-status-strip {
            height: 8px;
            width: 100%;
        }

        .modal-header {
            border-bottom: 1px solid #E9ECEF;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-body p {
            margin-bottom: 12px;
            color: #495057;
        }

        .modal-body strong {
            color: #2C3E50;
        }

        .btn-back-dashboard {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .btn-back-dashboard:hover {
            background: var(--primary-color);
            color: white;
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(128, 161, 186, 0.25);
        }

        .btn-back-dashboard i {
            font-size: 1.1rem;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <a href="dashboard.php" class="btn-back-dashboard">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        <div class="page-header mb-4">
            <h1><i class="bi bi-calendar-check"></i> My Appointments</h1>
            <small class="text-muted">Click an appointment card to view full details</small>
        </div>

        <div class="mb-4">
            <label for="statusFilter" class="form-label fw-bold">Filter by Status:</label>
            <select id="statusFilter" class="form-select" style="max-width: 250px; background-color: #f8f9fa; color: #212529; border: 2px solid #6c757d;">
                <option value="all" selected>All</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="checked-in">Checked-In</option>
                <option value="completed">Completed</option>
                <option value="denied">Denied</option>
            </select>
        </div>

        <?php if (!empty($pending) || !empty($approvedArr) || !empty($checkedIn) || !empty($completed) || !empty($deniedArr)): ?>

            <!-- PENDING APPOINTMENTS -->
            <?php if (!empty($pending)): ?>
                <div class="status-section pending">
                    <div class="status-header">
                        <i class="bi bi-clock-history" style="font-size: 1.5rem; color: #E7C892;"></i>
                        <h4>Pending Appointments</h4>
                        <span class="status-count"><?= count($pending) ?></span>
                    </div>
                    <div class="appointments-grid">
                        <?php foreach ($pending as $appt): ?>
                            <?php $status_class = 'status-' . strtolower(str_replace(' ', '-', $appt['status'])); ?>
                            <div class="appointment-card" data-bs-toggle="modal" data-bs-target="#apptModal<?= $appt['id'] ?>" data-appt-id="<?= $appt['id'] ?>">
                                <div class="status-strip <?= $status_class ?>"></div>
                                <div class="appointment-info">
                                    <h5><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></h5>
                                    <p><i class="bi bi-calendar3"></i><?= (new DateTime($appt['date']))->format('F j, Y') ?></p>
                                    <p><i class="bi bi-clock"></i><?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                    <span class="status-badge <?= $status_class ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- MODAL -->
                            <div class="modal fade" id="apptModal<?= $appt['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-status-strip <?= $status_class ?>" id="modalStrip<?= $appt['id'] ?>"></div>
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($appt['description'] ?: 'Appointment Details') ?></h5>
                                            <span class="status-badge <?= $status_class ?> ms-2" id="statusBadge<?= $appt['id'] ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Date:</strong> <?= (new DateTime($appt['date']))->format('F j, Y (l)') ?></p>
                                            <p><strong>Time:</strong> <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                            <p><strong>Dentist:</strong> <?= htmlspecialchars($appt['dentist_name']) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                            <?php if (!empty($appt['description'])): ?>
                                                <p><strong>Description:</strong> <?= htmlspecialchars($appt['description']) ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($appt['qr_code_url'])): ?>
                                                <div class="text-center mt-3">
                                                    <img src="<?= htmlspecialchars($appt['qr_code_url']) ?>" alt="QR Code" id="qr_<?= $appt['id'] ?>" style="max-width: 250px;">
                                                    <div class="mt-3">
                                                        <a href="<?= htmlspecialchars($appt['qr_code_url']) ?>" download="dentlink_appointment_<?= $appt['id'] ?>.png" class="btn btn-success btn-sm me-2">
                                                            <i class="bi bi-download"></i> Download
                                                        </a>
                                                        <button onclick="printQR(<?= $appt['id'] ?>, <?= json_encode($appt['description']) ?>, <?= json_encode((new DateTime($appt['date']))->format('F j, Y')) ?>, <?= json_encode((new DateTime($appt['start_time']))->format('g:i A')) ?>, <?= json_encode($appt['dentist_name'] ?: 'Unassigned') ?>)" class="btn btn-outline-primary btn-sm me-2">
                                                            <i class="bi bi-printer"></i> Print
                                                        </button>
                                                        <button onclick="shareQR('<?= htmlspecialchars($appt['qr_code_url']) ?>')" class="btn btn-outline-secondary btn-sm">
                                                            <i class="bi bi-share"></i> Share
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning mt-3">QR code is being generated. Please refresh later.</div>
                                            <?php endif; ?>

                                            <?php if (!empty($appt['calendar_link'])): ?>
                                                <p class="mt-3">
                                                    <a href="<?= htmlspecialchars($appt['calendar_link']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-calendar-event"></i> View in Google Calendar
                                                    </a>
                                                </p>
                                            <?php endif; ?>

                                            <div class="alert alert-info mt-3">
                                                <h6>Important Reminders:</h6>
                                                <ul class="mb-0 small">
                                                    <li>Arrive 15 minutes before your appointment time</li>
                                                    <li>Kindly have your QR code ready for verification</li>
                                                    <li>Please use our phone line or chat system to request a reschedule</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- APPROVED APPOINTMENTS -->
            <?php if (!empty($approvedArr)): ?>
                <div class="status-section approved">
                    <div class="status-header">
                        <i class="bi bi-check-circle" style="font-size: 1.5rem; color: #88C49F;"></i>
                        <h4>Approved Appointments</h4>
                        <span class="status-count"><?= count($approvedArr) ?></span>
                    </div>
                    <div class="appointments-grid">
                        <?php foreach ($approvedArr as $appt): ?>
                            <?php $status_class = 'status-' . strtolower(str_replace(' ', '-', $appt['status'])); ?>
                            <div class="appointment-card" data-bs-toggle="modal" data-bs-target="#apptModal<?= $appt['id'] ?>" data-appt-id="<?= $appt['id'] ?>">
                                <div class="status-strip <?= $status_class ?>"></div>
                                <div class="appointment-info">
                                    <h5><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></h5>
                                    <p><i class="bi bi-calendar3"></i><?= (new DateTime($appt['date']))->format('F j, Y') ?></p>
                                    <p><i class="bi bi-clock"></i><?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                    <span class="status-badge <?= $status_class ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- MODAL -->
                            <div class="modal fade" id="apptModal<?= $appt['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-status-strip <?= $status_class ?>" id="modalStrip<?= $appt['id'] ?>"></div>
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($appt['description'] ?: 'Appointment Details') ?></h5>
                                            <span class="status-badge <?= $status_class ?> ms-2" id="statusBadge<?= $appt['id'] ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Date:</strong> <?= (new DateTime($appt['date']))->format('F j, Y (l)') ?></p>
                                            <p><strong>Time:</strong> <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                            <p><strong>Dentist:</strong> <?= htmlspecialchars($appt['dentist_name']) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                            <?php if (!empty($appt['description'])): ?>
                                                <p><strong>Description:</strong> <?= htmlspecialchars($appt['description']) ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($appt['qr_code_url'])): ?>
                                                <div class="text-center mt-3">
                                                    <img src="<?= htmlspecialchars($appt['qr_code_url']) ?>" alt="QR Code" id="qr_<?= $appt['id'] ?>" style="max-width: 250px;">
                                                    <div class="mt-3">
                                                        <a href="<?= htmlspecialchars($appt['qr_code_url']) ?>" download="dentlink_appointment_<?= $appt['id'] ?>.png" class="btn btn-success btn-sm me-2">
                                                            <i class="bi bi-download"></i> Download
                                                        </a>
                                                        <button onclick="printQR(<?= $appt['id'] ?>, <?= json_encode($appt['description']) ?>, <?= json_encode((new DateTime($appt['date']))->format('F j, Y')) ?>, <?= json_encode((new DateTime($appt['start_time']))->format('g:i A')) ?>, <?= json_encode($appt['dentist_name'] ?: 'Unassigned') ?>)" class="btn btn-outline-primary btn-sm me-2">
                                                            <i class="bi bi-printer"></i> Print
                                                        </button>
                                                        <button onclick="shareQR('<?= htmlspecialchars($appt['qr_code_url']) ?>')" class="btn btn-outline-secondary btn-sm">
                                                            <i class="bi bi-share"></i> Share
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-warning mt-3">QR code is being generated. Please refresh later.</div>
                                            <?php endif; ?>

                                            <?php if (!empty($appt['calendar_link'])): ?>
                                                <p class="mt-3">
                                                    <a href="<?= htmlspecialchars($appt['calendar_link']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="bi bi-calendar-event"></i> View in Google Calendar
                                                    </a>
                                                </p>
                                            <?php endif; ?>

                                            <div class="alert alert-info mt-3">
                                                <h6>Important Reminders:</h6>
                                                <ul class="mb-0 small">
                                                    <li>Arrive 15 minutes before your appointment time</li>
                                                    <li>Kindly have your QR code ready for verification</li>
                                                    <li>If you have any inquiries or concerns, you may message us through the chat</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CHECKED-IN APPOINTMENTS -->
            <?php if (!empty($checkedIn)): ?>
                <div class="status-section checked-in">
                    <div class="status-header">
                        <i class="bi bi-person-check" style="font-size: 1.5rem; color: #80A1BA;"></i>
                        <h4>Checked-In Appointments</h4>
                        <span class="status-count"><?= count($checkedIn) ?></span>
                    </div>
                    <div class="appointments-grid">
                        <?php foreach ($checkedIn as $appt): ?>
                            <?php $status_class = 'status-' . strtolower(str_replace(' ', '-', $appt['status'])); ?>
                            <div class="appointment-card" data-bs-toggle="modal" data-bs-target="#apptModal<?= $appt['id'] ?>" data-appt-id="<?= $appt['id'] ?>">
                                <div class="status-strip <?= $status_class ?>"></div>
                                <div class="appointment-info">
                                    <h5><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></h5>
                                    <p><i class="bi bi-calendar3"></i><?= (new DateTime($appt['date']))->format('F j, Y') ?></p>
                                    <p><i class="bi bi-clock"></i><?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                    <span class="status-badge <?= $status_class ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- MODAL -->
                            <div class="modal fade" id="apptModal<?= $appt['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-status-strip <?= $status_class ?>" id="modalStrip<?= $appt['id'] ?>"></div>
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($appt['description'] ?: 'Appointment Details') ?></h5>
                                            <span class="status-badge <?= $status_class ?> ms-2" id="statusBadge<?= $appt['id'] ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Date:</strong> <?= (new DateTime($appt['date']))->format('F j, Y (l)') ?></p>
                                            <p><strong>Time:</strong> <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                            <p><strong>Dentist:</strong> <?= htmlspecialchars($appt['dentist_name']) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                            <?php if (!empty($appt['description'])): ?>
                                                <p><strong>Description:</strong> <?= htmlspecialchars($appt['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- COMPLETED APPOINTMENTS -->
            <?php if (!empty($completed)): ?>
                <div class="status-section completed">
                    <div class="status-header">
                        <i class="bi bi-check2-all" style="font-size: 1.5rem; color: #6FA8A3;"></i>
                        <h4>Completed Appointments</h4>
                        <span class="status-count"><?= count($completed) ?></span>
                    </div>
                    <div class="appointments-grid">
                        <?php foreach ($completed as $appt): ?>
                            <?php $status_class = 'status-' . strtolower(str_replace(' ', '-', $appt['status'])); ?>
                            <div class="appointment-card" data-bs-toggle="modal" data-bs-target="#apptModal<?= $appt['id'] ?>" data-appt-id="<?= $appt['id'] ?>">
                                <div class="status-strip <?= $status_class ?>"></div>
                                <div class="appointment-info">
                                    <h5><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></h5>
                                    <p><i class="bi bi-calendar3"></i><?= (new DateTime($appt['date']))->format('F j, Y') ?></p>
                                    <p><i class="bi bi-clock"></i><?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                    <span class="status-badge <?= $status_class ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- MODAL -->
                            <div class="modal fade" id="apptModal<?= $appt['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-status-strip <?= $status_class ?>" id="modalStrip<?= $appt['id'] ?>"></div>
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($appt['description'] ?: 'Appointment Details') ?></h5>
                                            <span class="status-badge <?= $status_class ?> ms-2" id="statusBadge<?= $appt['id'] ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Date:</strong> <?= (new DateTime($appt['date']))->format('F j, Y (l)') ?></p>
                                            <p><strong>Time:</strong> <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                            <p><strong>Dentist:</strong> <?= htmlspecialchars($appt['dentist_name']) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                            <?php if (!empty($appt['description'])): ?>
                                                <p><strong>Description:</strong> <?= htmlspecialchars($appt['description']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- DENIED APPOINTMENTS -->
            <?php if (!empty($deniedArr)): ?>
                <div class="status-section denied">
                    <div class="status-header">
                        <i class="bi bi-x-circle" style="font-size: 1.5rem; color: #D9A5A5;"></i>
                        <h4>Denied Appointments</h4>
                        <span class="status-count"><?= count($deniedArr) ?></span>
                    </div>
                    <div class="appointments-grid">
                        <?php foreach ($deniedArr as $appt): ?>
                            <?php $status_class = 'status-' . strtolower(str_replace(' ', '-', $appt['status'])); ?>
                            <div class="appointment-card" data-bs-toggle="modal" data-bs-target="#apptModal<?= $appt['id'] ?>" data-appt-id="<?= $appt['id'] ?>">
                                <div class="status-strip <?= $status_class ?>"></div>
                                <div class="appointment-info">
                                    <h5><?= htmlspecialchars($appt['description'] ?: 'Appointment') ?></h5>
                                    <p><i class="bi bi-calendar3"></i><?= (new DateTime($appt['date']))->format('F j, Y') ?></p>
                                    <p><i class="bi bi-clock"></i><?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                    <span class="status-badge <?= $status_class ?>">
                                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- MODAL -->
                            <div class="modal fade" id="apptModal<?= $appt['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-status-strip <?= $status_class ?>" id="modalStrip<?= $appt['id'] ?>"></div>
                                        <div class="modal-header">
                                            <h5 class="modal-title"><?= htmlspecialchars($appt['description'] ?: 'Appointment Details') ?></h5>
                                            <span class="status-badge <?= $status_class ?> ms-2" id="statusBadge<?= $appt['id'] ?>">
                                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $appt['status']))) ?>
                                            </span>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Date:</strong> <?= (new DateTime($appt['date']))->format('F j, Y (l)') ?></p>
                                            <p><strong>Time:</strong> <?= (new DateTime($appt['start_time']))->format('g:i A') ?></p>
                                            <p><strong>Dentist:</strong> <?= htmlspecialchars($appt['dentist_name']) ?></p>
                                            <p><strong>Location:</strong> <?= htmlspecialchars($appt['location']) ?></p>
                                            <?php if (!empty($appt['description'])): ?>
                                                <p><strong>Description:</strong> <?= htmlspecialchars($appt['description']) ?></p>
                                            <?php endif; ?>
                                            <div class="mt-2 p-2" style="border-left: 4px solid #dc2626; background-color: #fee2e2; border-radius: 8px;">
                                                <p class="mb-0">
                                                    <strong style="color: #991b1b;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Remarks:</strong>
                                                    <span style="color: #7f1d1d;"><?= htmlspecialchars($appt['denial_reason']) ?></span>
                                                </p>
                                            </div>
                                            <div class="alert alert-danger mt-3 p-3" style="border-left: 4px solid #dc2626;">
                                                <i class="bi bi-x-circle-fill me-2"></i>This appointment has been <strong>denied</strong>. Please contact the clinic for more information.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="status-section">
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <h4>No Appointments Found</h4>
                    <p>You haven't booked any appointments yet.</p>
                    <a href="book_appointment.php" class="btn btn-muted mt-3">
                        <i class="bi bi-plus-circle"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    if (!button) return;

                    const apptId = button.dataset.apptId;
                    if (!apptId) return;

                    // Fetch latest status from server
                    fetch(`fetch_appointment.php?id=${apptId}`)
                        .then(r => r.json())
                        .then(res => {
                            if (res.status === 'success') {
                                const a = res.data;
                                const statusSlug = 'status-' + a.status.toLowerCase().replace(/ /g, '-');
                                const statusText = a.status.replace(/-/g, ' ').toUpperCase();

                                // Update modal status badge
                                const modalBadge = document.getElementById('statusBadge' + apptId);
                                if (modalBadge) {
                                    modalBadge.textContent = statusText;
                                    modalBadge.className = 'status-badge ' + statusSlug + ' ms-2';
                                }

                                // Update modal strip
                                const modalStrip = document.getElementById('modalStrip' + apptId);
                                if (modalStrip) {
                                    modalStrip.className = 'modal-status-strip ' + statusSlug;
                                }

                                // Update the card in the main view
                                const cardStrip = document.getElementById('cardStrip' + apptId);
                                const cardBadge = document.getElementById('cardBadge' + apptId);

                                if (cardStrip) cardStrip.className = 'status-strip ' + statusSlug;
                                if (cardBadge) {
                                    cardBadge.textContent = statusText;
                                    cardBadge.className = 'status-badge ' + statusSlug;
                                }
                            }
                        })
                        .catch(err => console.error('Error fetching status:', err));
                });
            });
        });

        function printQR(id, service, date, time, dentist) {
            var qrImage = document.getElementById('qr_' + id).src;
            var printWindow = window.open('', '', 'height=700,width=800');
            printWindow.document.write('<html><head><title>Print QR</title><style>body{text-align:center;font-family:Arial;padding:50px}img{max-width:350px;border:3px solid #4CAF50;padding:20px;border-radius:10px}</style></head><body>');
            printWindow.document.write('<h1>DentLink Dental Clinic</h1><h3>Appointment QR Code</h3>');
            printWindow.document.write('<p><strong>Patient:</strong> <?= htmlspecialchars($full_name) ?></p>');
            printWindow.document.write('<p><strong>Service:</strong>' + service + '</p>');
            printWindow.document.write('<p><strong>Date:</strong>' + date + '</p>');
            printWindow.document.write('<p><strong>Time:</strong>' + time + '</p>');
            printWindow.document.write('<p><strong>Dentist:</strong>' + dentist + '</p>');
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

        document.addEventListener('DOMContentLoaded', function() {
            const filter = document.getElementById('statusFilter');
            const sections = document.querySelectorAll('.status-section');

            filter.addEventListener('change', function() {
                const value = this.value.toLowerCase();

                sections.forEach(section => {
                    // Check section class: 'pending', 'approved', etc.
                    if (value === 'all') {
                        section.style.display = 'block';
                    } else if (section.classList.contains(value)) {
                        section.style.display = 'block';
                    } else {
                        section.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>

</html>