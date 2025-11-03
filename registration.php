<?php
require_once 'db_connect.php';
session_start();
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name  = trim($_POST["last_name"]);
    $email      = trim($_POST["email"]);
    $phone      = trim($_POST["phone"]);
    $address    = trim($_POST["address"]);
    $password   = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    $role       = "Patient";

    if ($first_name && $last_name && $email && $phone && $address && $password) {
        try {
            $db   = new Database();
            $conn = $db->getConnect();

            // Stored procedure or direct insert
            $stmt = $conn->prepare("
                INSERT INTO users (first_name, last_name, email, phone, address, role, password)
                VALUES (:first_name, :last_name, :email, :phone, :address, :role, :password)
            ");
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':phone'      => $phone,
                ':address'    => $address,
                ':role'       => $role,
                ':password'   => $password
            ]);

            header("Location: login.php");
            exit();
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate')) {
                $message = "This email is already registered.";
            } else {
                $message = "Error: " . $e->getMessage();
            }
        }
    } else {
        $message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DentLink Registration</title>
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
                <h2>Registration Form</h2>
                <form method="POST" action="">
                    <div class="name-fields">
                        <input type="text" name="first_name" placeholder="First Name" required>
                        <input type="text" name="last_name" placeholder="Last Name" required>
                    </div>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="phone" placeholder="Phone Number" required>
                    <input type="text" name="address" placeholder="Address" required>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <input type="submit" value="Register">
                </form>

                <?php if (!empty($message)): ?>
                    <p class="message"><?php echo htmlspecialchars($message); ?></p>
                <?php endif; ?>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
