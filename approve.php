<?php
require_once 'config/db_connect.php';
date_default_timezone_set('Asia/Manila');

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // 🔹 Get appointment info + user details
    $sql = "
        SELECT a.*, u.email, CONCAT(u.first_name, ' ', u.last_name) AS patient_name
        FROM appointments a
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $appointment = $result->fetch_assoc();

    if (!$appointment) {
        die("❌ Appointment not found.");
    }

    // 🔹 Update status to approved
    $update = $conn->prepare("UPDATE appointments SET status='approved' WHERE id=?");
    $update->bind_param("i", $id);
    $update->execute();

    // 🔹 Prepare QR data (for NoCodeAPI)
    $qrData = [
        "appointmentId" => $appointment['id'],
        "patientName"   => $appointment['patient_name'],
        "service"       => $appointment['description'],
        "date"          => $appointment['date'],
        "time"          => $appointment['start_time'],
        "location"      => $appointment['location'],
        "status"        => "approved"
    ];

    // 🔹 Generate QR code via NoCodeAPI
    $ch = curl_init("https://v1.nocodeapi.com/jimae/qrCode/PJMrLDGrQIFIfWEW/qrimage");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($qrData));
    $qrResponse = curl_exec($ch);
    curl_close($ch);

    $qr = json_decode($qrResponse, true);
    $qrUrl = $qr['data'] ?? '';

    // 🔹 Save QR URL to database if generated
    if (!empty($qrUrl)) {
        $saveQr = $conn->prepare("UPDATE appointments SET qr_code_url = ? WHERE id = ?");
        $saveQr->bind_param("si", $qrUrl, $id);
        $saveQr->execute();
        $saveQr->close();
        echo "<p style='color:green;font-weight:bold;'>✅ Appointment approved! QR code saved to database.</p>";
    } else {
        echo "<p style='color:orange;font-weight:bold;'>⚠️ Appointment approved but QR code not generated.</p>";
    }

    // 🔹 Email setup
    $to = $appointment['email'];
    $subject = "DentLink Appointment Approved";
    $message = "
        <html>
        <body style='font-family: Arial, sans-serif; background: #f7f9fa; padding: 20px;'>
            <div style='background: #fff; padding: 20px; border-radius: 8px;'>
                <h2 style='color:#4CAF50;'>DentLink Appointment Confirmation</h2>
                <p>Dear <strong>{$appointment['patient_name']}</strong>,</p>
                <p>Your dental appointment has been <strong style='color:green;'>approved</strong>.</p>
                <p><b>Date:</b> {$appointment['date']}<br>
                <b>Time:</b> {$appointment['start_time']}<br>
                <b>Service:</b> {$appointment['description']}<br>
                <b>Location:</b> {$appointment['location']}</p>
    ";

    if (!empty($qrUrl)) {
        $message .= "
            <p>Please present this QR code at the clinic for verification:</p>
            <img src='{$qrUrl}' alt='Appointment QR' width='200' style='border:1px solid #ccc; padding:10px; border-radius:8px;'><br><br>
        ";
    }

    $message .= "
                <p>Thank you,<br><strong>DentLink Team</strong></p>
            </div>
        </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: DentLink <no-reply@dentlink.com>";

    // Send email (optional)
    @mail($to, $subject, $message, $headers);

    echo "<a href='admin_appointments.php' style='text-decoration:none; display:inline-block; margin-top:20px; background:#4CAF50; color:white; padding:8px 16px; border-radius:4px;'>Back to Dashboard</a>";

} else {
    echo "<p style='color:red;'>⚠️ Invalid request.</p>";
}
?>
