<?php
require_once 'db_connect.php';
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    try {
        $db = new Database();
        $conn = $db->getConnect();

        // Check if user exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'user_id'    => $user['user_id'],
                'first_name' => $user['first_name'],
                'last_name'  => $user['last_name'],
                'email'      => $user['email'],
                'role'       => $user['role']
            ];

            // Redirect based on role
            if ($user['role'] === 'Admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $message = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>DentLink Login</title>
    <link rel="stylesheet" href="credentials.css">
</head>

<body>
    <div class="container">
        <div class="left-side">
            <img src="dentlink-logo.png" alt="DentLink Logo">
            <p><strong>DentLink: Dental Clinic Digital Appointment and Patient Records Management System</strong></p>
            <div class="info-box">
                <p>
                    <strong>DentLink</strong> is a web-based platform that simplifies dental appointment scheduling and
                    patient record management. Patients can easily book appointments online, view available time slots,
                    and receive email notifications for confirmations and reminders.
                </p>
                <br>
                <p>
                    The system ensures accurate record-keeping by tracking treatment histories and identifying new or returning patients,
                    resulting in efficient and reliable dental services.
                </p>
            </div>
        </div>

        <div class="right-side">
            <div class="form-box">
                <h2>Welcome!</h2>
                <p>Please login to your account.</p>
                <form method="post" action="">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="submit" value="Login">
                </form>

                <?php if (!empty($message)): ?>
                    <p class="message"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>

                <div class="login-link">
                    Don't have an account? <a href="registration.php">Sign up</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>