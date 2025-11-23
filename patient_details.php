<?php
include 'db_connect.php';

function esc($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$user_id = intval($_GET['id'] ?? 0);

$stmt = $conn->prepare("
    SELECT user_id, first_name, last_name, email, phone, address, profile_pic, created_at 
    FROM users WHERE user_id = ? AND role='Patient' LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient) {
    echo "Patient not found.";
    exit;
}

$appt = $conn->prepare("
    SELECT id, date, start_time, location, description, status, created_at
    FROM appointments WHERE user_id = ? ORDER BY date DESC, start_time DESC
");
$appt->bind_param("i", $user_id);
$appt->execute();
$appointments = $appt->get_result();
$appt->close();

$pic = $patient['profile_pic'] ? "upload/" . urlencode($patient['profile_pic']) : "default-avatar.png";
$fullName = esc($patient['first_name'] . " " . $patient['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $fullName ?> - Patient Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* Patient Profile Card */
        .patient-profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(128, 161, 186, 0.15);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .patient-profile-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .patient-profile-img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            margin-bottom: 15px;
        }

        .patient-profile-name {
            color: white;
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
        }

        .patient-profile-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-top: 10px;
        }

        .patient-profile-body {
            padding: 30px;
        }

        .patient-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .patient-info-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 18px;
            background: linear-gradient(135deg, rgba(180, 222, 189, 0.08), rgba(255, 247, 221, 0.08));
            border-radius: 12px;
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
        }

        .patient-info-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(128, 161, 186, 0.12);
        }

        .patient-info-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .patient-info-content label {
            display: block;
            font-size: 0.8rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .patient-info-content span {
            font-weight: 500;
            color: #333;
        }

        /* Appointment Section */
        .appointments-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(128, 161, 186, 0.15);
            padding: 30px;
        }

        .appointments-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .appointments-header h4 {
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .appointments-header .count-badge {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .appointment-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            border: 1px solid #eee;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .appointment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(128, 161, 186, 0.2);
        }

        .appointment-card-strip {
            height: 6px;
            width: 100%;
        }

        .appointment-card-body {
            padding: 20px;
        }

        .appointment-card-date {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .appointment-card-time {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .appointment-card-desc {
            color: #555;
            font-size: 0.9rem;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .appointment-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .appointment-card-location {
            font-size: 0.8rem;
            color: #888;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Status Colors */
        .strip-pending { background: linear-gradient(90deg, #fbbf24, #f59e0b); }
        .strip-approved { background: linear-gradient(90deg, #4ade80, #22c55e); }
        .strip-denied { background: linear-gradient(90deg, #f87171, #ef4444); }
        .strip-checked-in { background: linear-gradient(90deg, #60a5fa, #3b82f6); }
        .strip-in-treatment { background: linear-gradient(90deg, #a78bfa, #8b5cf6); }
        .strip-completed { background: linear-gradient(90deg, #9ca3af, #6b7280); }

        .badge-pending { background: #fef3c7; color: #d97706; }
        .badge-approved { background: #d1fae5; color: #059669; }
        .badge-denied { background: #fee2e2; color: #dc2626; }
        .badge-checked-in { background: #dbeafe; color: #2563eb; }
        .badge-in-treatment { background: #ede9fe; color: #7c3aed; }
        .badge-completed { background: #f3f4f6; color: #4b5563; }

        .appointment-status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #888;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 15px;
        }

        .empty-state h5 {
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body class="admin-page">

    <a href="patient_records.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Patient Records
    </a>

    <!-- Patient Profile Card -->
    <div class="patient-profile-card">
        <div class="patient-profile-header">
            <img src="<?= esc($pic) ?>" class="patient-profile-img" alt="Profile">
            <h2 class="patient-profile-name"><?= $fullName ?></h2>
            <span class="patient-profile-badge">
                <i class="bi bi-person-check me-1"></i> Patient
            </span>
        </div>
        <div class="patient-profile-body">
            <div class="patient-info-grid">
                <div class="patient-info-item">
                    <div class="patient-info-icon"><i class="bi bi-envelope"></i></div>
                    <div class="patient-info-content">
                        <label>Email Address</label>
                        <span><?= esc($patient['email']) ?></span>
                    </div>
                </div>
                <div class="patient-info-item">
                    <div class="patient-info-icon"><i class="bi bi-telephone"></i></div>
                    <div class="patient-info-content">
                        <label>Phone Number</label>
                        <span><?= esc($patient['phone']) ?></span>
                    </div>
                </div>
                <div class="patient-info-item">
                    <div class="patient-info-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="patient-info-content">
                        <label>Address</label>
                        <span><?= esc($patient['address']) ?></span>
                    </div>
                </div>
                <div class="patient-info-item">
                    <div class="patient-info-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="patient-info-content">
                        <label>Registered On</label>
                        <span><?= date('F j, Y', strtotime($patient['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Section -->
    <div class="appointments-section">
        <div class="appointments-header">
            <h4><i class="bi bi-calendar-event"></i> Appointment History</h4>
            <span class="count-badge"><?= $appointments->num_rows ?> Total</span>
        </div>

        <?php if ($appointments->num_rows === 0): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>No Appointments Found</h5>
                <p>This patient hasn't booked any appointments yet.</p>
            </div>
        <?php else: ?>
            <div class="appointment-cards-grid">
                <?php while ($a = $appointments->fetch_assoc()): 
                    $statusSlug = strtolower(str_replace(' ', '-', $a['status']));
                    $statusText = ucwords(str_replace('-', ' ', $a['status']));
                ?>
                <div class="appointment-card"
                     data-bs-toggle="modal" data-bs-target="#appointmentModal"
                     data-id="<?= esc($a['id']) ?>"
                     data-name="<?= $fullName ?>"
                     data-email="<?= esc($patient['email']) ?>"
                     data-date="<?= date('F j, Y', strtotime($a['date'])) ?>"
                     data-time="<?= date('h:i A', strtotime($a['start_time'])) ?>"
                     data-location="<?= esc($a['location']) ?>"
                     data-description="<?= esc($a['description']) ?>"
                     data-status="<?= esc($statusText) ?>"
                     data-status-slug="<?= esc($statusSlug) ?>">
                    <div class="appointment-card-strip strip-<?= $statusSlug ?>"></div>
                    <div class="appointment-card-body">
                        <div class="appointment-card-date">
                            <i class="bi bi-calendar3"></i>
                            <?= date('F j, Y', strtotime($a['date'])) ?>
                        </div>
                        <div class="appointment-card-time">
                            <i class="bi bi-clock"></i>
                            <?= date('h:i A', strtotime($a['start_time'])) ?>
                        </div>
                        <div class="appointment-card-desc">
                            <?= esc($a['description'] ?: 'No description provided') ?>
                        </div>
                        <div class="appointment-card-footer">
                            <span class="appointment-card-location">
                                <i class="bi bi-geo-alt"></i> <?= esc($a['location']) ?>
                            </span>
                            <span class="appointment-status-badge badge-<?= $statusSlug ?>">
                                <?= $statusText ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Patient:</strong> <span id="modalName"></span></p>
                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    <p><strong>Time:</strong> <span id="modalTime"></span></p>
                    <p><strong>Location:</strong> <span id="modalLocation"></span></p>
                    <p><strong>Service:</strong> <span id="modalDescription"></span></p>
                    <p><strong>Status:</strong> <span id="modalStatus" class="appointment-status-badge"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.appointment-card').forEach(card => {
            card.addEventListener('click', () => {
                document.getElementById('modalName').textContent = card.dataset.name;
                document.getElementById('modalEmail').textContent = card.dataset.email;
                document.getElementById('modalDate').textContent = card.dataset.date;
                document.getElementById('modalTime').textContent = card.dataset.time;
                document.getElementById('modalLocation').textContent = card.dataset.location;
                document.getElementById('modalDescription').textContent = card.dataset.description;
                
                const statusEl = document.getElementById('modalStatus');
                statusEl.textContent = card.dataset.status;
                statusEl.className = 'appointment-status-badge badge-' + card.dataset.statusSlug;
            });
        });
    </script>
</body>
</html>