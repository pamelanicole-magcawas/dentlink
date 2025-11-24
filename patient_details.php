<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id === 0) {
    header("Location: patient_records.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Add Prescription
    if ($action === 'add_prescription') {
        $prescription_date = $_POST['prescription_date'];
        $medication_id = intval($_POST['medication_id']);
        $dosage = $_POST['dosage'];
        $frequency = $_POST['frequency'];
        $duration = $_POST['duration'];
        $prescribed_by = intval($_POST['prescribed_by']);
        $instructions = $_POST['instructions'];

        $stmt = $conn->prepare("INSERT INTO prescriptions (user_id, prescription_date, medication_id, dosage, frequency, duration, prescribed_by, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssis", $patient_id, $prescription_date, $medication_id, $dosage, $frequency, $duration, $prescribed_by, $instructions);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Prescription added successfully!";
    }

    // Delete Prescription
    elseif ($action === 'delete_prescription') {
        $prescription_id = intval($_POST['prescription_id']);
        $stmt = $conn->prepare("DELETE FROM prescriptions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $prescription_id, $patient_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['success_message'] = "Prescription deleted successfully!";
    }

    header("Location: patient_details.php?id=" . $patient_id);
    exit();
}

// Fetch patient information
$patient_info_sql = "SELECT * FROM users WHERE user_id = ? AND role = 'Patient'";
$stmt = $conn->prepare($patient_info_sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient_info) {
    header("Location: patient_records.php");
    exit();
}

// Fetch common medications
$medications_result = $conn->query("SELECT * FROM common_medications WHERE is_active = 1 ORDER BY medication_name ASC");
$medications = [];
while ($row = $medications_result->fetch_assoc()) {
    $medications[] = $row;
}

// Fetch prescriptions
$prescriptions_sql = "
SELECT p.*, c.medication_name, d.name AS prescribed_by_name
FROM prescriptions p
LEFT JOIN common_medications c ON p.medication_id = c.id
LEFT JOIN dentists d ON p.prescribed_by = d.id
WHERE p.user_id = ?
ORDER BY p.prescription_date DESC;
";
$stmt = $conn->prepare($prescriptions_sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$prescriptions = $stmt->get_result();
$stmt->close();

// Fetch appointments
$appointments_sql = "
SELECT a.*, d.name AS dentist_name
FROM appointments a
LEFT JOIN dentists d ON a.dentist_id = d.id
WHERE a.user_id = ?
ORDER BY a.date DESC, a.start_time DESC
";
$stmt = $conn->prepare($appointments_sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$appointments = $stmt->get_result();
$stmt->close();

$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records - <?= htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']) ?></title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #80A1BA;
            --secondary-color: #91C4C3;
            --accent-color: #22c55e;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #B4DEBD 0%, #FFF7DD 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .admin-records-page {
            padding: 20px;
            background: linear-gradient(135deg, #FFF7DD 0%, #f8f9fa 100%);
            min-height: 100vh;
        }

        /* Sections */
        .record-section {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(128, 161, 186, 0.15);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .section-header h4 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .patient-header {
            display: flex;
            align-items: center;
            gap: 20px;
            background: white;
            padding: 20px;
            border-radius: 20px;
            color: var(--primary-color);
            box-shadow: 0 8px 24px rgba(128, 161, 186, 0.2);
            margin-bottom: 24px;
        }

        .patient-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
        }

        .patient-info-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 3rem;
        }

        .patient-info-header p {
            margin: 0;
            font-size: 0.95rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: rgba(180, 222, 189, 0.1);
            border-radius: 10px;
            border-left: 3px solid var(--accent-color);
        }

        .info-label {
            font-size: 0.85rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            color: #333;
            font-size: 1.05rem;
        }

        .count-badge {
            background: var(--accent-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .85rem;
        }

        /* Appointment Cards */
        .appointment-cards-grid,
        .prescription-cards-grid {
            display: grid;
            gap: 20px;
        }

        .appointment-cards-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        .prescription-cards-grid {
            grid-template-columns: repeat(3, 1fr);
        }

        @media(max-width:992px) {

            .appointment-cards-grid,
            .prescription-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:576px) {

            .appointment-cards-grid,
            .prescription-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .appointment-card,
        .prescription-card {
            background: white;
            border-radius: 15px;
            border: 1px solid #eee;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .appointment-card:hover,
        .prescription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(128, 161, 186, 0.2);
        }

        .appointment-card-strip,
        .prescription-card-strip {
            height: 6px;
            width: 100%;
            margin-bottom: 10px;
            border-radius: 6px 6px 0 0;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .appointment-card-body,
        .prescription-card-body {
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
            font-size: .9rem;
            color: #666;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .appointment-card-desc {
            font-size: .9rem;
            color: #555;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .appointment-card-footer {
            display: flex;
            justify-content: space-between;
            font-size: .8rem;
            color: #888;
        }

        .appointment-status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Status Colors */
        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-approved {
            background: #d1fae5;
            color: #059669;
        }

        .badge-denied {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-checked-in {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-in-treatment {
            background: #ede9fe;
            color: #7c3aed;
        }

        .badge-completed {
            background: #f3f4f6;
            color: #4b5563;
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

        /* Modals */
        .modal-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border-bottom: none;
        }

        .modal-body strong {
            color: var(--primary-color);
        }

        /* Back Button */
        .btn-back-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 8px 15px;
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border-radius: 12px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .btn-back-dashboard:hover {
            transform: translateY(-2px);
        }

        /* Prescription Card Delete Button */
        .btn-delete-small {
            background: #f87171;
            /* red */
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .btn-delete-small:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        /* Adjust prescription card hover to lift up */
        .prescription-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(128, 161, 186, 0.25);
        }

        /* Optional: Add padding-top so delete button doesn't cover title */
        .prescription-card-body {
            padding: 20px;
            padding-top: 40px;
        }

        .btn-add-prescription {
            background: linear-gradient(135deg, #91C4C3, #80A1BA);
            color: white;
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.25rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-add-prescription:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(128, 161, 186, 0.3);
        }
    </style>
</head>

<body class="admin-records-page">

    <a href="patient_records.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Patient Records
    </a>

    <?php if ($success_message): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?= $success_message ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    <?php endif; ?>

    <!-- Patient Header -->
    <div class="patient-header">
        <img src="upload/<?= htmlspecialchars($patient_info['profile_pic'] ?? 'default-avatar.png') ?>" alt="Profile" class="patient-avatar">
        <div class="patient-info-header">
            <h2><?= htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']) ?></h2>
        </div>
    </div>

    <!-- Patient Information Section -->
    <div class="record-section">
        <div class="section-header">
            <h4 class="section-title"><i class="bi bi-person-badge"></i> Patient Information</h4>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size: 0.875rem;">
                            <i class="bi bi-person-fill me-1"></i> Full Name
                        </div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size: 0.875rem;">
                            <?php if (!empty($patient_info['gender'])): ?>
                                <?php
                                $gender = strtolower($patient_info['gender']);
                                if ($gender === 'male') {
                                    echo '<i class="bi bi-gender-male me-1"></i>';
                                } elseif ($gender === 'female') {
                                    echo '<i class="bi bi-gender-female me-1"></i>';
                                }
                                // "Prefer not to say" or any other value will show no icon
                                ?>
                            <?php endif; ?>
                            Gender
                        </div>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($patient_info['gender'] ?? 'Not provided') ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size: 0.875rem;">
                            <i class="bi bi-calendar-check me-1"></i>Registered
                        </div>
                        <div class="fw-semibold"><?= htmlspecialchars(date('M d, Y', strtotime($patient_info['created_at']))) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size: 0.875rem;">
                            <i class="bi bi-envelope me-1"></i>Email
                        </div>
                        <div class="fw-semibold text-break"><?= htmlspecialchars($patient_info['email']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size: 0.875rem;">
                            <i class="bi bi-telephone me-1"></i>Contact
                        </div>
                        <div class="fw-semibold"><?= htmlspecialchars($patient_info['phone']) ?></div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted mb-1" style="font-size: 0.875rem;">
                            <i class="bi bi-geo-alt me-1"></i>Address
                        </div>
                        <div class="fw-semibold"><?= htmlspecialchars($patient_info['address']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Appointments Section -->
    <div class="record-section">
        <div class="section-header">
            <h4><i class="bi bi-calendar-event"></i> Appointments</h4>
            <span class="count-badge"><?= $appointments->num_rows ?> Total</span>
        </div>

        <?php if ($appointments->num_rows === 0): ?>
            <div class="empty-state">
                <i class="bi bi-calendar-x"></i>
                <h5>No Appointments Found</h5>
            </div>
        <?php else: ?>
            <div class="appointment-cards-grid">
                <?php while ($a = $appointments->fetch_assoc()):
                    $statusSlug = strtolower(str_replace(' ', '-', $a['status']));
                    $statusText = ucwords(str_replace('-', ' ', $a['status']));
                ?>
                    <div class="appointment-card"
                        data-bs-toggle="modal" data-bs-target="#appointmentModal"
                        data-id="<?= $a['id'] ?>"
                        data-date="<?= date('F j, Y', strtotime($a['date'])) ?>"
                        data-time="<?= date('h:i A', strtotime($a['start_time'])) ?>"
                        data-location="<?= htmlspecialchars($a['location']) ?>"
                        data-description="<?= htmlspecialchars($a['description'] ?: 'No description') ?>"
                        data-dentist="<?= htmlspecialchars($a['dentist_name'] ?: 'Unassigned') ?>"
                        data-status="<?= $statusText ?>"
                        data-status-slug="<?= $statusSlug ?>">
                        <span class="appointment-status-badge badge-<?= $statusSlug ?>"><?= $statusText ?></span>
                        <div class="appointment-card-strip strip-<?= $statusSlug ?>"></div>
                        <div class="appointment-card-body">
                            <div class="appointment-card-date"><i class="bi bi-calendar3"></i> <?= date('F j, Y', strtotime($a['date'])) ?></div>
                            <div class="appointment-card-time"><i class="bi bi-clock"></i> <?= date('h:i A', strtotime($a['start_time'])) ?></div>
                            <div class="appointment-card-desc"><?= htmlspecialchars($a['description'] ?: 'No description') ?></div>
                            <div class="appointment-card-footer">
                                <span><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($a['location']) ?></span>
                                <span><i class="bi bi-person"></i> <?= htmlspecialchars($a['dentist_name'] ?: 'Unassigned') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Date:</strong> <span id="modalDate"></span></p>
                    <p><strong>Time:</strong> <span id="modalTime"></span></p>
                    <p><strong>Location:</strong> <span id="modalLocation"></span></p>
                    <p><strong>Description:</strong> <span id="modalDescription"></span></p>
                    <p><strong>Dentist:</strong> <span id="modalDentist"></span></p>
                    <p><span id="modalStatus" class="appointment-status-badge"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="markCompleteBtn" class="btn btn-success" style="display: none;">
                        <i class="bi bi-check-circle"></i> Mark as Complete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Populate modal with appointment data
        document.getElementById('appointmentModal').addEventListener('show.bs.modal', function(event) {
            const card = event.relatedTarget;
            const appointmentId = card.getAttribute('data-id');
            const date = card.getAttribute('data-date');
            const time = card.getAttribute('data-time');
            const location = card.getAttribute('data-location');
            const description = card.getAttribute('data-description');
            const dentist = card.getAttribute('data-dentist');
            const status = card.getAttribute('data-status');
            const statusSlug = card.getAttribute('data-status-slug');

            // Populate modal fields
            document.getElementById('modalDate').textContent = date;
            document.getElementById('modalTime').textContent = time;
            document.getElementById('modalLocation').textContent = location;
            document.getElementById('modalDescription').textContent = description;
            document.getElementById('modalDentist').textContent = dentist;

            const statusBadge = document.getElementById('modalStatus');
            statusBadge.textContent = status;
            statusBadge.className = 'appointment-status-badge badge-' + statusSlug;

            // Show "Mark as Complete" button only for checked-in appointments
            const completeBtn = document.getElementById('markCompleteBtn');
            if (statusSlug === 'checked-in') {
                completeBtn.style.display = 'inline-block';
                completeBtn.setAttribute('data-appointment-id', appointmentId);
            } else {
                completeBtn.style.display = 'none';
            }
        });

        // Handle "Mark as Complete" button click
        document.getElementById('markCompleteBtn').addEventListener('click', function() {
            const appointmentId = this.getAttribute('data-appointment-id');

            if (confirm('Are you sure you want to mark this appointment as completed?')) {
                // Send AJAX request to update status
                fetch('update_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + appointmentId + '&status=completed'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Appointment marked as completed!');
                            location.reload(); // Reload to show updated status
                        } else {
                            alert('Error: ' + (data.message || 'Could not update appointment'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while updating the appointment');
                    });
            }
        });
    </script>

    <!-- Prescriptions Section -->
    <div class="record-section">
        <div class="section-header">
            <h4><i class="bi bi-capsule"></i> Prescriptions</h4>
            <button class="btn-add-prescription" data-bs-toggle="modal" data-bs-target="#addPrescriptionModal">
                <i class="bi bi-plus-circle"></i> Add Prescription
            </button>
            <span class="count-badge"><?= $prescriptions->num_rows ?> Total</span>
        </div>

        <?php if ($prescriptions->num_rows === 0): ?>
            <div class="empty-state">
                <i class="bi bi-file-earmark-medical"></i>
                <h5>No Prescriptions Found</h5>
            </div>
        <?php else: ?>
            <div class="prescription-cards-grid">
                <?php while ($p = $prescriptions->fetch_assoc()): ?>
                    <div class="prescription-card"
                        data-bs-toggle="modal"
                        data-bs-target="#prescriptionModal"
                        data-medication="<?= htmlspecialchars($p['medication_name']) ?>"
                        data-date="<?= date('M j, Y', strtotime($p['prescription_date'])) ?>"
                        data-dosage="<?= htmlspecialchars($p['dosage']) ?>"
                        data-frequency="<?= htmlspecialchars($p['frequency']) ?>"
                        data-duration="<?= htmlspecialchars($p['duration']) ?>"
                        data-instructions="<?= htmlspecialchars($p['instructions']) ?>"
                        data-prescribed-by="<?= htmlspecialchars($p['prescribed_by_name'] ?? 'N/A') ?>">

                        <form method="POST" style="position:absolute; top:10px; right:10px;">
                            <input type="hidden" name="action" value="delete_prescription">
                            <input type="hidden" name="prescription_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-delete-small" onclick="return confirm('Delete this prescription?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                        <div class="prescription-card-strip"></div>

                        <div class="prescription-card-body">
                            <h5><?= htmlspecialchars($p['medication_name']) ?></h5>
                            <small><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($p['prescription_date'])) ?></small>
                        </div>
                    </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>
</div>

<!-- Prescription Modal -->
<div class="modal fade" id="prescriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prescription Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Medication:</strong> <span id="modalMedication"></span></p>
                <p><strong>Date:</strong> <span id="modalPrescriptionDate"></span></p>
                <p><strong>Dosage:</strong> <span id="modalDosage"></span></p>
                <p><strong>Frequency:</strong> <span id="modalFrequency"></span></p>
                <p><strong>Duration:</strong> <span id="modalDuration"></span></p>
                <p><strong>Instructions:</strong> <span id="modalInstructions"></span></p>
                <p><strong>Prescribed By:</strong> <span id="modalPrescribedBy"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Prescription Modal -->
<div class="modal fade" id="addPrescriptionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content">
            <input type="hidden" name="action" value="add_prescription">
            <div class="modal-header">
                <h5 class="modal-title">Add Prescription</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Prescription Date</label>
                    <input type="date" name="prescription_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Medication</label>
                    <select name="medication_id" id="medicationSelect" class="form-control" required>
                        <option value="">Select Medication</option>
                        <?php foreach ($medications as $med): ?>
                            <option value="<?= $med['id'] ?>"
                                data-dosage="<?= htmlspecialchars($med['common_dosage']) ?>"
                                data-frequency="<?= htmlspecialchars($med['common_frequency']) ?>"
                                data-duration="<?= htmlspecialchars($med['common_duration']) ?>">
                                <?= htmlspecialchars($med['medication_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dosage</label>
                    <input type="text" name="dosage" id="dosage" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Frequency</label>
                    <input type="text" name="frequency" id="frequency" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Duration</label>
                    <input type="text" name="duration" id="duration" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Prescribed By</label>
                    <select name="prescribed_by" class="form-control" required>
                        <option value="">Select Dentist</option>
                        <?php
                        $dentists = $conn->query("SELECT id, name FROM dentists");
                        while ($dentist = $dentists->fetch_assoc()):
                        ?>
                            <option value="<?= $dentist['id'] ?>"><?= htmlspecialchars($dentist['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Instructions</label>
                    <textarea name="instructions" class="form-control" rows="3" required placeholder="Take after meals..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Prescription</button>
            </div>
        </form>
    </div>
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script>
    function markAsComplete(appointmentId) {
        Swal.fire({
            title: 'Complete Appointment?',
            text: 'This will mark the appointment as completed and create a treatment record.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, complete it!',
            confirmButtonColor: '#22c55e',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Completing appointment',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send request to update status
                const formData = new FormData();
                formData.append('id', appointmentId);
                formData.append('status', 'completed');

                fetch('update_status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.status === 'success') {
                            // Update UI
                            const badge = document.getElementById('badge' + appointmentId);
                            const modalBadge = document.getElementById('modalBadge' + appointmentId);
                            const completeSection = document.getElementById('completeSection' + appointmentId);

                            if (badge) {
                                badge.className = 'status-badge completed';
                                badge.textContent = 'COMPLETED';
                            }

                            if (modalBadge) {
                                modalBadge.className = 'status-badge completed';
                                modalBadge.textContent = 'COMPLETED';
                            }

                            if (completeSection) {
                                completeSection.innerHTML = '<p class="text-center text-success mt-3" style="font-size: 1.1rem;"><i class="bi bi-check-circle-fill"></i> This appointment has been completed</p>';
                            }

                            Swal.fire({
                                title: 'Completed!',
                                text: 'Appointment marked as completed successfully.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Optionally reload to refresh all data
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', res.message || 'Failed to complete appointment', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        Swal.fire('Error', 'Failed to complete appointment: ' + err.message, 'error');
                    });
            }
        });
    }

    document.querySelectorAll('.appointment-card').forEach(card => {
        card.addEventListener('click', () => {
            document.getElementById('modalDate').textContent = card.dataset.date;
            document.getElementById('modalTime').textContent = card.dataset.time;
            document.getElementById('modalLocation').textContent = card.dataset.location;
            document.getElementById('modalDescription').textContent = card.dataset.description;
            document.getElementById('modalDentist').textContent = card.dataset.dentist;
            const status = document.getElementById('modalStatus');
            status.textContent = card.dataset.status;
            status.className = 'appointment-status-badge badge-' + card.dataset.statusSlug;
        });
    });

    document.querySelectorAll('.prescription-card').forEach(card => {
        card.addEventListener('click', () => {
            document.getElementById('modalMedication').textContent = card.dataset.medication;
            document.getElementById('modalPrescriptionDate').textContent = card.dataset.date;
            document.getElementById('modalDosage').textContent = card.dataset.dosage;
            document.getElementById('modalFrequency').textContent = card.dataset.frequency;
            document.getElementById('modalDuration').textContent = card.dataset.duration;
            document.getElementById('modalInstructions').textContent = card.dataset.instructions;
            document.getElementById('modalPrescribedBy').textContent = card.dataset.prescribedBy;
        });
    });


    document.getElementById('medicationSelect').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        document.getElementById('dosage').value = selected.getAttribute('data-dosage') || '';
        document.getElementById('frequency').value = selected.getAttribute('data-frequency') || '';
        document.getElementById('duration').value = selected.getAttribute('data-duration') || '';
    });
</script>
</body>

</html>
