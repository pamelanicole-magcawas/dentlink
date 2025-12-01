<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

//Fetch Prescriptions
$prescriptions_sql = "
    SELECT p.*, c.medication_name AS medication, d.name AS prescribed_by_name
    FROM prescriptions p
    LEFT JOIN common_medications c ON p.medication_id = c.id
    LEFT JOIN dentists d ON p.prescribed_by = d.id
    WHERE p.user_id = ? 
    ORDER BY p.prescription_date DESC
";
$stmt = $conn->prepare($prescriptions_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$prescriptions = $stmt->get_result();
$stmt->close();

//Fetch Appointments
$appt = $conn->prepare("
    SELECT a.id, a.date, a.start_time, a.location, a.description, a.status,
           a.denial_reason,
           d.name AS dentist_name
    FROM appointments a
    LEFT JOIN dentists d ON d.id = a.dentist_id
    WHERE a.user_id = ?
    ORDER BY a.date DESC, a.start_time DESC
");
$appt->bind_param("i", $user_id);
$appt->execute();
$appointments = $appt->get_result();
$appt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dental Records - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #80A1BA;
            --secondary-color: #91C4C3;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #B4DEBD 0%, #FFF7DD 100%);
            min-height: 100vh;
            padding: 20px;
        }

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
            color: #80A1BA;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .count-badge {
            background: #80A1BA;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: .85rem;
        }

        .appointment-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        @media(max-width:992px) {
            .appointment-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:576px) {
            .appointment-cards-grid {
                grid-template-columns: 1fr;
            }
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
            margin-bottom: 10px;
            border-radius: 6px 6px 0 0;
        }

        .appointment-card-body { 
            padding: 20px; 
            padding-top: 45px; 
        }

        .appointment-card-date {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2C3E50;
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

        .strip-pending {
            background: linear-gradient(135deg, #F3D9AA 0%, #E7C892 100%);
        }

        .strip-approved {
            background: linear-gradient(135deg, #A2D8B3 0%, #88C49F 100%);
        }

        .strip-denied {
            background: linear-gradient(135deg, #E6BBBB 0%, #D9A5A5 100%);
        }

        .strip-checked-in {
            background: linear-gradient(135deg, #A8C2D3 0%, #80A1BA 100%);
        }

        .strip-in-treatment {
            background: linear-gradient(90deg, #a78bfa, #8b5cf6);
        }

        .strip-completed {
            background: linear-gradient(135deg, #8BBDB8 0%, #6FA8A3 100%);
        }

        .badge-pending {
            background: #FFF7DD;
            color: #B48945;
            border: 1px solid #E7C892;
        }

        .badge-approved {
            background: #EEF8F1;
            color: #4E8A62;
            border: 1px solid #88C49F;
        }

        .badge-denied {
            background: #FBECEC;
            color: #A85E5E;
            border: 1px solid #D9A5A5;
        }

        .badge-checked-in {
            background: #EBF1F6;
            color: #5D7C95;
            border: 1px solid #80A1BA;
        }

        .badge-in-treatment {
            background: #ede9fe;
            color: #7c3aed;
        }

        .badge-completed {
            background: #E7F4F3;
            color: #467974;
            border: 1px solid #6FA8A3;
        }

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

        .prescription-cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .prescription-card {
            background: white;
            border-radius: 15px;
            border: 1px solid #eee;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .prescription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(128, 161, 186, 0.2);
        }

        .prescription-card-strip {
            height: 6px;
            width: 100%;
            margin-bottom: 10px;
            border-radius: 6px 6px 0 0;
            background: linear-gradient(90deg, #80A1BA, #91C4C3);
        }

        .prescription-card-body {
            padding: 20px;
        }

        .prescription-card h5 {
            font-weight: 600;
            color: #80A1BA;
            margin-bottom: 5px;
        }

        .prescription-card small {
            color: #666;
        }

        .prescription-card .details {
            font-size: .9rem;
            color: #555;
            margin-top: 8px;
        }

        @media(max-width:992px) {
            .prescription-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:576px) {
            .prescription-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            border-bottom: none;
        }

        .modal-status-strip {
            height: 8px;
            width: 100%;
        }

        .modal-status-strip.strip-pending {
            background: linear-gradient(135deg, #F3D9AA 0%, #E7C892 100%);
        }

        .modal-status-strip.strip-approved {
            background: linear-gradient(135deg, #A2D8B3 0%, #88C49F 100%);
        }

        .modal-status-strip.strip-denied {
            background: linear-gradient(135deg, #E6BBBB 0%, #D9A5A5 100%);
        }

        .modal-status-strip.strip-checked-in {
            background: linear-gradient(135deg, #A8C2D3 0%, #80A1BA 100%);
        }

        .modal-status-strip.strip-in-treatment {
            background: linear-gradient(90deg, #a78bfa, #8b5cf6);
        }

        .modal-status-strip.strip-completed {
            background: linear-gradient(135deg, #8BBDB8 0%, #6FA8A3 100%);
        }

        .modal-body strong {
            color: var(--primary-color)
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

        .page-title {
            font-size: 3rem;
            font-weight: 600;
            color: var(--primary-color);
            background: white;
            border-radius: 20px;
            padding: 20px 30px;
            box-shadow: 0 8px 24px rgba(128, 161, 186, 0.15);
            text-align: center;
            margin-bottom: 24px;
            position: relative;
            overflow: hidden;
        }

        .page-title::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: var(--primary-color);
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .page-title:hover {
            transform: translateY(-3px);
        }
    </style>
</head>

<body>

    <a href="dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <h2 class="page-title">My Records</h2>

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
                        data-bs-toggle="modal" data-bs-target="#appointmentModal<?= $a['id'] ?>">
                        <span class="appointment-status-badge badge-<?= $statusSlug ?>"><?= $statusText ?></span>
                        <div class="appointment-card-strip strip-<?= $statusSlug ?>"></div>
                        <div class="appointment-card-body">
                            <div class="appointment-card-date"><i class="bi bi-calendar3"></i> <?= date('F j, Y', strtotime($a['date'])) ?></div>
                            <div class="appointment-card-time"><i class="bi bi-clock"></i> <?= date('h:i A', strtotime($a['start_time'])) ?></div>
                            <div class="appointment-card-desc"><?= htmlspecialchars($a['description'] ?: 'No description') ?></div>
                        </div>
                    </div>

                    <!-- Modal for this appointment -->
                    <div class="modal fade" id="appointmentModal<?= $a['id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-calendar-event me-2"></i>Appointment Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Date:</strong> <?= date('F j, Y (l)', strtotime($a['date'])) ?></p>
                                    <p><strong>Time:</strong> <?= date('h:i A', strtotime($a['start_time'])) ?></p>
                                    <p><strong>Location:</strong> <?= htmlspecialchars($a['location']) ?></p>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($a['description'] ?: 'No description') ?></p>
                                    <p><strong>Dentist:</strong> <?= htmlspecialchars($a['dentist_name'] ?: 'Unassigned') ?></p>
                                    
                                    <?php if (strtolower($a['status']) === 'denied'): ?>
                                        <div class="mt-2 p-2" style="border-left: 4px solid #dc2626; background-color: #fee2e2; border-radius: 8px;">
                                            <p class="mb-0">
                                                <strong style="color: #991b1b;"><i class="bi bi-exclamation-triangle-fill me-1"></i> Remarks:</strong> 
                                                <span style="color: #7f1d1d;"><?= htmlspecialchars($a['denial_reason'] ?: 'N/A') ?></span>
                                            </p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <p><span class="appointment-status-badge badge-<?= $statusSlug ?>"><?= $statusText ?></span></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="record-section">
        <div class="section-header">
            <h4><i class="bi bi-capsule"></i> Prescriptions</h4>
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
                        data-medication="<?= htmlspecialchars($p['medication']) ?>"
                        data-date="<?= date('M j, Y', strtotime($p['prescription_date'])) ?>"
                        data-dosage="<?= htmlspecialchars($p['dosage']) ?>"
                        data-frequency="<?= htmlspecialchars($p['frequency']) ?>"
                        data-duration="<?= htmlspecialchars($p['duration']) ?>"
                        data-instructions="<?= htmlspecialchars($p['instructions']) ?>"
                        data-prescribed-by="<?= htmlspecialchars($p['prescribed_by_name'] ?? 'Not specified') ?>">
                        <div class="prescription-card-strip"></div>
                        <div class="prescription-card-body">
                            <h5><?= htmlspecialchars($p['medication']) ?></h5>
                            <small><i class="bi bi-calendar3"></i> <?= date('M j, Y', strtotime($p['prescription_date'])) ?></small>
                            <div class="details mt-2">
                                <strong>Prescribed By:</strong> <?= htmlspecialchars($p['prescribed_by_name'] ?? 'Not specified') ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Prescription Modal -->
    <div class="modal fade" id="prescriptionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-capsule me-2"></i>Prescription Details</h5>
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

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
    </script>
</body>
</html>
