<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DentLink - Appointment Details</title>
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <style>
    body {
      background: linear-gradient(135deg, #e3f2fd 0%, #f0f8ff 100%);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    .appointment-card {
      background: white;
      padding: 40px;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
      max-width: 500px;
      width: 100%;
    }
    .header {
      text-align: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 2px solid #4CAF50;
    }
    .detail-row {
      display: flex;
      align-items: center;
      padding: 12px 15px;
      margin: 10px 0;
      background: #f8f9fa;
      border-radius: 8px;
      border-left: 4px solid #4CAF50;
    }
    .detail-row i {
      font-size: 24px;
      color: #4CAF50;
      margin-right: 15px;
      width: 30px;
    }
    .detail-content {
      flex: 1;
    }
    .detail-label {
      font-size: 12px;
      color: #666;
      font-weight: 600;
      text-transform: uppercase;
      margin-bottom: 2px;
    }
    .detail-value {
      font-size: 16px;
      color: #333;
      font-weight: 500;
    }
    .status-badge {
      display: inline-block;
      padding: 8px 16px;
      background: #4CAF50;
      color: white;
      border-radius: 20px;
      font-weight: 600;
      font-size: 14px;
    }
    .verified-badge {
      background: #e8f5e9;
      color: #2e7d32;
      padding: 10px 15px;
      border-radius: 8px;
      text-align: center;
      margin-top: 20px;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="appointment-card">
    <div class="header">
      <h2 style="color: #4CAF50; margin: 0;">
        <i class="bi bi-calendar-check-fill"></i> DentLink
      </h2>
      <p style="color: #666; margin: 5px 0 0 0;">Appointment Verification</p>
    </div>

    <div class="detail-row">
      <i class="bi bi-person-fill"></i>
      <div class="detail-content">
        <div class="detail-label">Patient Name</div>
        <div class="detail-value"><?= htmlspecialchars($appt['name']) ?></div>
      </div>
    </div>

    <div class="detail-row">
      <i class="bi bi-heart-pulse-fill"></i>
      <div class="detail-content">
        <div class="detail-label">Service</div>
        <div class="detail-value"><?= htmlspecialchars($appt['description']) ?></div>
      </div>
    </div>

    <div class="detail-row">
      <i class="bi bi-calendar-event-fill"></i>
      <div class="detail-content">
        <div class="detail-label">Date</div>
        <div class="detail-value">
          <?php 
          $date = new DateTime($appt['date']);
          echo $date->format('F j, Y (l)');
          ?>
        </div>
      </div>
    </div>

    <div class="detail-row">
      <i class="bi bi-clock-fill"></i>
      <div class="detail-content">
        <div class="detail-label">Time</div>
        <div class="detail-value">
          <?php 
          $time = new DateTime($appt['start_time']);
          echo $time->format('g:i A');
          ?>
        </div>
      </div>
    </div>

    <div class="detail-row">
      <i class="bi bi-geo-alt-fill"></i>
      <div class="detail-content">
        <div class="detail-label">Location</div>
        <div class="detail-value"><?= htmlspecialchars($appt['location']) ?></div>
      </div>
    </div>

    <div class="detail-row">
      <i class="bi bi-envelope-fill"></i>
      <div class="detail-content">
        <div class="detail-label">Email</div>
        <div class="detail-value"><?= htmlspecialchars($appt['email']) ?></div>
      </div>
    </div>

    <div class="text-center mt-4">
      <span class="status-badge">
        <i class="bi bi-check-circle-fill"></i> APPROVED
      </span>
    </div>

    <div class="verified-badge">
      <i class="bi bi-shield-check"></i> Verified Appointment #<?= $appt['id'] ?>
    </div>

    <p style="text-align: center; color: #999; font-size: 12px; margin-top: 20px;">
      This appointment has been confirmed and is valid.<br>
      Scanned at: <?= date('F j, Y g:i A') ?>
    </p>
  </div>
</body>
</html>