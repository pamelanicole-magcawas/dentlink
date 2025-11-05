<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appointment Approved</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #e3f2fd 0%, #f0f8ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            padding: 20px;
        }
        .result-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            max-width: 700px;
            width: 100%;
            text-align: center;
        }
        .success-icon { font-size: 64px; color: #4CAF50; }
        .qr-preview { 
            margin: 20px 0; 
            padding: 20px; 
            background: #f8f9fa; 
            border-radius: 10px;
            border: 2px solid #4CAF50;
        }
        .qr-preview img { 
            max-width: 250px; 
            border: 3px solid #4CAF50; 
            border-radius: 8px; 
            padding: 15px; 
            background: white; 
        }
        .status-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .status-success { color: #4CAF50; }
        .status-warning { color: #ff9800; }
    </style>
</head>
<body>
    <div class="result-card">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        
        <h2 class="text-success mt-3 mb-4">Appointment Approved Successfully!</h2>
        
        <div class="alert alert-success">
            <h5><i class="bi bi-person-check"></i> <?= htmlspecialchars($appointment['patient_name']) ?></h5>
            <p class="mb-1"><strong>Service:</strong> <?= htmlspecialchars($appointment['description']) ?></p>
            <p class="mb-1"><strong>Date:</strong> <?= htmlspecialchars($appointment['date']) ?></p>
            <p class="mb-1"><strong>Time:</strong> <?= htmlspecialchars($appointment['start_time']) ?></p>
            <p class="mb-0"><strong>Location:</strong> <?= htmlspecialchars($appointment['location']) ?></p>
        </div>

        <h5 class="mt-4 mb-3">Integration Status:</h5>
        
        <div class="status-item">
            <span><i class="bi bi-database-check"></i> Database Updated</span>
            <strong class="status-success">✓ Success</strong>
        </div>

        <div class="status-item">
            <span><i class="bi bi-calendar-plus"></i> Google Calendar Event</span>
            <strong class="<?= $calendarSuccess ? 'status-success' : 'status-warning' ?>">
                <?= $calendarSuccess ? '✓ Created' : '⚠ Failed' ?>
            </strong>
        </div>

        <div class="status-item">
            <span><i class="bi bi-qr-code"></i> QR Code Generated</span>
            <strong class="<?= !empty($qrUrl) ? 'status-success' : 'status-warning' ?>">
                <?= !empty($qrUrl) ? '✓ Generated' : '⚠ Failed' ?>
            </strong>
        </div>

        <?php if (!empty($qrUrl)): ?>
        <div class="qr-preview">
            <h6 class="text-success">QR Code Preview:</h6>
            <img src="<?= htmlspecialchars($qrUrl) ?>" alt="QR Code">
            <p class="text-muted small mt-2">Patient can view this in their dashboard</p>
        </div>
        <?php endif; ?>

        <?php if (!empty($calendarLink)): ?>
        <div class="mt-3">
            <a href="<?= htmlspecialchars($calendarLink) ?>" target="_blank" class="btn btn-primary">
                <i class="bi bi-calendar-check"></i> View in Google Calendar
            </a>
        </div>
        <?php endif; ?>

        <div class="alert alert-info mt-4">
            <p class="mb-0"><i class="bi bi-info-circle"></i> 
                Patient has been notified via email and can view their QR code in “View Appointments”
            </p>
        </div>

        <a href="admin_appointments.php" class="btn btn-success btn-lg mt-3">
            <i class="bi bi-arrow-left"></i> Back to Admin Dashboard
        </a>
    </div>
</body>
</html>
