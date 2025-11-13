<?php
require_once 'db_connect.php';
require_once 'twilio-php-main\src\Twilio\autoload.php';

use Twilio\Rest\Client;

session_start();
$message = "";

// Check if pending user session exists
if (!isset($_SESSION['pending_user'])) {
    header("Location: registration.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code = trim($_POST['otp']);
    $user = $_SESSION['pending_user'];

    // Twilio credentials
    $sid = "AC9c75d0e89a750bdc4ff2f0c894326a16";
    $token = "82c5eba7b9824daf086b35d8606a7589";
    $verify_sid = "VA19c9a06196b042d6ee7b2b44e23a5850";
    $to = '+63' . ltrim($user['phone'], '0');

    $client = new Client($sid, $token);

    try {
        $check = $client->verify->v2->services($verify_sid)
            ->verificationChecks
            ->create(['to' => $to, 'code' => $code]);

        if ($check->status === 'approved') {
            // ✅ Verified successfully → Save to DB using MySQLi
            global $conn;

            $stmt = $conn->prepare("
                INSERT INTO users (first_name, last_name, email, phone, address, role, password)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "sssssss",
                $user['first_name'],
                $user['last_name'],
                $user['email'],
                $user['phone'],
                $user['address'],
                $user['role'],
                $user['password']
            );

            if ($stmt->execute()) {
                unset($_SESSION['pending_user']); // clear session
                header("Location: login.php");
                exit();
            } else {
                $message = "Database error: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $message = "❌ Invalid code. Please try again.";
        }
    } catch (Exception $e) {
        $message = "Error verifying code: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify Phone</title>
    <link rel="stylesheet" href="credentials.css">
</head>

<body>
    <div class="container">
        <div class="form-box">
            <h2>Verify Your Phone</h2>
            <p>We’ve sent an OTP to your phone number. Enter it below to complete registration.</p>

            <form method="POST" action="">
                <input type="text" name="otp" placeholder="Enter OTP code" required>
                <input type="submit" value="Verify">
            </form>

            <?php if (!empty($message)): ?>
                <p class="message"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
