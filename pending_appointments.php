<?php
include 'db_connect.php';
require "log_activity.php";
include 'schedule_helper.php';

// Fetch pending appointments
$result = $conn->query("
    SELECT a.*, d.name AS dentist_name
    FROM appointments a
    LEFT JOIN dentists d ON d.id = a.dentist_id
    WHERE a.status = 'pending'
    ORDER BY a.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pending Appointments - DentLink</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-badge.approved {
            background: #ccfbf1;
            color: #0d9488;
        }

        .status-badge.denied {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-badge.checked-in {
            background: #dbeafe;
            color: #2563eb;
        }

        .status-badge.completed {
            background: #d1fae5;
            color: #059669;
        }
    </style>
</head>

<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="admin-page-header">
        <h2><i class="bi bi-calendar-check"></i> Appointment Requests</h2>
        <p>Review and manage pending appointment requests from patients</p>
    </div>

    <div class="admin-table-wrapper">
        <table id="appointmentsTable" class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient</th>
                    <th>Email</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Dentist</th>
                    <th>Service</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()):
                    $dentistName = !empty($row['dentist_id']) ? $row['dentist_name'] : "Unassigned";
                    $statusClass = strtolower(str_replace('-', ' ', $row['status']));
                ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= date('g:i A', strtotime(substr($row['start_time'], 0, 5))) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($dentistName) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars(ucwords(str_replace('-', ' ', $row['status']))) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <button class="btn-action approve" data-id="<?= $row['id'] ?>"><i class="bi bi-check-lg"></i> Approve</button>
                                <button class="btn-action deny" data-id="<?= $row['id'] ?>"><i class="bi bi-x-lg"></i> Deny</button>
                                <button class="btn-action changeDentist" data-id="<?= $row['id'] ?>"><i class="bi bi-person-gear"></i> Dentist</button>
                            <?php elseif ($row['status'] === 'approved'): ?>
                                <span class="status-badge approved">APPROVED</span>
                            <?php elseif ($row['status'] === 'denied'): ?>
                                <span class="status-badge denied">DENIED</span>
                            <?php elseif ($row['status'] === 'checked-in'): ?>
                                <span class="status-badge checked-in">CHECKED IN</span>
                            <?php elseif ($row['status'] === 'completed'): ?>
                                <span class="status-badge completed">COMPLETED</span>
                            <?php else: ?>
                                <span class="status-badge <?= $statusClass ?>"><?= strtoupper($row['status']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#appointmentsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [0, 'desc']
                ]
            });

            $('.approve').click(function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Approving...',
                    text: 'Please wait.',
                    icon: 'info',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();

                        $.ajax({
                            url: 'approve.php',
                            method: 'POST',
                            data: {
                                id
                            },
                            success: function() {
                                Swal.fire('Approved!', 'Appointment confirmed.', 'success')
                                    .then(() => location.reload());
                            },
                            error: function() {
                                Swal.fire('Error', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });

            $('.deny').click(async function() {
                const id = $(this).data('id');

                const {
                    value: choice
                } = await Swal.fire({
                    title: 'Deny Appointment?',
                    input: 'select',
                    inputOptions: {
                        'emergency': 'Emergency case taken first',
                        'unavailable': 'Dentist unavailable',
                        'no-show': 'Patient has a previous no-show record',
                        'verification': 'Details could not be verified',
                        'policy': 'Policy violation',
                        'other': 'Other remarks'
                    },
                    inputPlaceholder: 'Select remarks',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444'
                });

                if (!choice) return;

                let remarks = {
                    'emergency': 'Emergency case taken first.',
                    'unavailable': 'Dentist unavailable.',
                    'no-show': 'Patient has a previous no-show record.',
                    'verification': 'Details could not be verified.',
                    'policy': 'Policy violation.'
                } [choice];

                if (choice === 'other') {
                    const {
                        value: custom
                    } = await Swal.fire({
                        title: 'Enter remarks',
                        input: 'text',
                        showCancelButton: true
                    });
                    if (!custom) return;
                    remarks = custom;
                }

                Swal.fire({
                    title: 'Confirm Denial',
                    html: `<strong>Remarks:</strong> ${remarks}`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    confirmButtonText: 'Yes, deny!'
                }).then((r) => {
                    if (r.isConfirmed) {
                        $.post('deny.php', {
                            id,
                            remarks
                        }, () => {
                            Swal.fire('Denied!', 'Appointment denied.', 'success')
                                .then(() => location.reload());
                        });
                    }
                });
            });

            $('.changeDentist').click(function() {
                const apptId = $(this).data('id');
                $.post('fetch_dentists_by_location.php', {
                    appointment_id: apptId
                }, function(resp) {
                    if (!resp?.options) {
                        Swal.fire('No dentists', 'None available for this location.', 'info');
                        return;
                    }
                    Swal.fire({
                        title: 'Select Dentist',
                        input: 'select',
                        inputOptions: resp.options,
                        showCancelButton: true
                    }).then(c => {
                        if (c.isConfirmed && c.value) {
                            $.post('update_dentist.php', {
                                appointment_id: apptId,
                                dentist_id: c.value
                            }, () => {
                                Swal.fire('Updated!', 'Dentist assigned.', 'success').then(() => location.reload());
                            });
                        }
                    });
                }, 'json');
            });
        });
    </script>
</body>
</html>
