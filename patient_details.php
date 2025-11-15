<?php
// patient_details.php

include 'db_connect.php';

function esc($v)
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$user_id = intval($_GET['id'] ?? 0);

// Fetch patient
$stmt = $conn->prepare("
    SELECT user_id, first_name, last_name, email, phone, address, profile_pic, created_at 
    FROM users 
    WHERE user_id = ? AND role='Patient'
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    echo "Patient not found.";
    exit;
}

// Fetch appointments
$appt = $conn->prepare("
    SELECT id, date, start_time, location, description, status, created_at
    FROM appointments
    WHERE user_id = ?
    ORDER BY date DESC, start_time DESC
");
$appt->bind_param("i", $user_id);
$appt->execute();
$appointments = $appt->get_result();
$appt->close();

// Profile picture
$pic = $patient['profile_pic']
    ? "upload/" . urlencode($patient['profile_pic'])
    : "default-avatar.png";
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= esc($patient['first_name'] . " " . $patient['last_name']) ?> — Patient Details</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .patient-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 6px;
            text-transform: capitalize;
            font-size: 0.85rem;
            color: white;
        }

        .status-pending {
            background: #ffc107;
        }

        .status-approved {
            background: #28a745;
        }

        .status-denied {
            background: #dc3545;
        }
    </style>
</head>

<body class="p-4">

    <div class="container">

        <a href="patient_records.php" class="btn btn-light mb-3">&larr; Back to Patients</a>

        <!-- PATIENT CARD -->
        <div class="card p-4 shadow-sm mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-3 text-center">
                    <img src="<?= esc($pic) ?>" class="patient-img" alt="Profile">
                </div>
                <div class="col-md-9">
                    <h3><?= esc($patient['first_name'] . " " . $patient['last_name']) ?></h3>
                    <p><strong>Email:</strong> <?= esc($patient['email']) ?></p>
                    <p><strong>Phone:</strong> <?= esc($patient['phone']) ?></p>
                    <p><strong>Address:</strong> <?= esc($patient['address']) ?></p>
                    <p class="text-muted"><small>Registered: <?= esc($patient['created_at']) ?></small></p>
                </div>
            </div>
        </div>

        <!-- APPOINTMENTS LIST -->
        <h4 class="mb-3">📅 Appointment History</h4>

        <?php if ($appointments->num_rows === 0): ?>
            <div class="alert alert-info">No appointments found for this patient.</div>
        <?php else: ?>

            <div class="list-group">

                <?php while ($a = $appointments->fetch_assoc()): ?>

                    <div class="list-group-item p-3 mb-2 shadow-sm rounded">

                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="mb-1">
                                    <?= date("F d, Y", strtotime($a['date'])) ?>
                                    @ <?= date("h:i A", strtotime($a['start_time'])) ?>
                                </h5>

                                <?php if ($a['location']): ?>
                                    <p class="mb-1"><strong>Location:</strong> <?= esc($a['location']) ?></p>
                                <?php endif; ?>

                                <?php if ($a['description']): ?>
                                    <p class="mb-1"><strong>Description:</strong> <?= esc($a['description']) ?></p>
                                <?php endif; ?>

                                <p class="text-muted mb-0">
                                    <small>Created: <?= esc($a['created_at']) ?></small>
                                </p>
                            </div>

                            <!-- STATUS BADGE -->
                            <div>
                                <span class="status-badge status-<?= esc($a['status']) ?>">
                                    <?= esc($a['status']) ?>
                                </span>
                            </div>
                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>