<?php ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scan QR Code - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>

<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="admin-page-header" style="max-width: 700px; margin: 0 auto 25px auto;">
        <h2><i class="bi bi-qr-code-scan"></i> Scan Appointment QR</h2>
        <p>Scan patient QR codes to check-in or mark appointments as completed</p>
    </div>

    <div class="scanner-card">
        <div class="scan-mode-toggle">
            <button class="btn-toggle active" id="cameraModeBtn" data-mode="camera">
                <i class="bi bi-camera"></i> Camera
            </button>
            <button class="btn-toggle" id="fileModeBtn" data-mode="file">
                <i class="bi bi-upload"></i> Upload File
            </button>
        </div>

        <div class="camera-section" id="cameraSection">
            <p>Allow camera permission to start scanning</p>
            <div id="reader"></div>
        </div>

        <div class="file-upload-section" id="fileSection">
            <p>Select a QR code image file</p>
            <input type="file" id="fileInput" accept="image/*" style="display:none;">
            <button class="btn-action btn-primary" onclick="document.getElementById('fileInput').click()">
                <i class="bi bi-image"></i> Choose File
            </button>
            <p id="fileName" style="margin-top: 15px; color: #666;"></p>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalAppointment">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-calendar-event"></i> Appointment Details</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="appointmentDetails"></div>
                </div>
                <div class="modal-footer justify-content-center">
                    <div id="statusButtons">
                        <button class="btn-action btn-primary" data-status="checked-in">
                            <i class="bi bi-box-arrow-in-right"></i> Check-in
                        </button>
                        <button class="btn-action btn-success" data-status="completed">
                            <i class="bi bi-check-circle"></i> Completed
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let globalID = null,
            html5QrCode = null,
            currentMode = 'camera';

        function extractAppointmentId(text) {
            try {
                const j = JSON.parse(text);
                if (j.id) return j.id;
            } catch {}
            const m = text.match(/\d+/);
            return m ? m[0] : null;
        }

        function showAppointmentModal(a) {
            const date = new Date(a.date).toLocaleDateString('en-PH', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById("appointmentDetails").innerHTML = `
                <p><strong>Name:</strong> ${a.name}</p>
                <p><strong>Email:</strong> ${a.email}</p>
                <p><strong>Date:</strong> ${date}</p>
                <p><strong>Time:</strong> ${a.start_time}</p>
                <p><strong>Location:</strong> ${a.location}</p>
                <p><strong>Service:</strong> ${a.description}</p>
                <p><strong>Dentist:</strong> ${a.dentist ?? 'Unassigned'}</p>
                <p><strong>Status:</strong> <span class="status-badge ${a.status}">${a.status.replace('-', ' ').toUpperCase()}</span></p>
            `;
            new bootstrap.Modal(document.getElementById("modalAppointment")).show();
        }

        function updateStatus(id, status) {
            const text = status.replace('-', ' ').toUpperCase();
            Swal.fire({
                title: 'Confirm Status',
                text: `Mark as ${text}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, update!'
            }).then((r) => {
                if (r.isConfirmed) {
                    fetch('update_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}&status=${status}`
                    }).then(r => r.json()).then(res => {
                        if (res.status === 'success') {
                            const badge = document.querySelector('#appointmentDetails .status-badge');
                            badge.className = 'status-badge ' + status;
                            badge.textContent = res.new_status.replace('-', ' ').toUpperCase();
                            Swal.fire({
                                title: 'Updated!',
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    });
                }
            });
        }

        document.querySelectorAll('#statusButtons button').forEach(btn => {
            btn.addEventListener('click', function() {
                if (globalID) updateStatus(globalID, this.dataset.status);
            });
        });

        document.getElementById('cameraModeBtn').addEventListener('click', function() {
            currentMode = 'camera';
            document.getElementById('cameraSection').classList.remove('hidden');
            document.getElementById('fileSection').classList.remove('active');
            this.classList.add('active');
            document.getElementById('fileModeBtn').classList.remove('active');
            if (!html5QrCode || !html5QrCode.isScanning) startScanner();
            else html5QrCode.resume();
        });

        document.getElementById('fileModeBtn').addEventListener('click', function() {
            currentMode = 'file';
            if (html5QrCode?.isScanning) html5QrCode.stop();
            document.getElementById('cameraSection').classList.add('hidden');
            document.getElementById('fileSection').classList.add('active');
            this.classList.add('active');
            document.getElementById('cameraModeBtn').classList.remove('active');
        });

        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            document.getElementById('fileName').textContent = 'Processing: ' + file.name;
            if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
            html5QrCode.scanFile(file, true).then(text => {
                document.getElementById('fileName').textContent = 'QR found!';
                const id = extractAppointmentId(text);
                if (!id) {
                    Swal.fire('Invalid', 'No valid ID found.', 'warning');
                    return;
                }
                globalID = id;
                fetch("fetch_appointment.php?id=" + id).then(r => r.json()).then(res => {
                    res.status === "success" ? showAppointmentModal(res.data) : Swal.fire('Not Found', 'Appointment not found.', 'warning');
                });
            }).catch(() => {
                Swal.fire('Error', 'Could not read QR code.', 'error');
                document.getElementById('fileName').textContent = '';
            });
        });

        document.getElementById('modalAppointment').addEventListener('hidden.bs.modal', () => {
            if (html5QrCode && currentMode === 'camera') html5QrCode.resume();
        });

        function startScanner() {
            if (!html5QrCode) html5QrCode = new Html5Qrcode("reader");
            Html5Qrcode.getCameras().then(cameras => {
                if (cameras?.length) {
                    html5QrCode.start(cameras[0].id, {
                        fps: 10,
                        qrbox: 250
                    }, text => {
                        const id = extractAppointmentId(text);
                        if (!id) return;
                        globalID = id;
                        html5QrCode.pause();
                        fetch("fetch_appointment.php?id=" + id).then(r => r.json()).then(res => {
                            res.status === "success" ? showAppointmentModal(res.data) : (Swal.fire('Not Found', 'Appointment not found.', 'warning'), html5QrCode.resume());
                        }).catch(() => {
                            Swal.fire('Error', 'Fetch failed.', 'error');
                            html5QrCode.resume();
                        });
                    }, () => {});
                } else Swal.fire('Error', 'No camera found.', 'error');
            });
        }

        startScanner();
    </script>
</body>

</html>