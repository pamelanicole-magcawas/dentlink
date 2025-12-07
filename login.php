<?php
session_start();
include("db_connect.php");
require "log_activity.php";

$email = "";
$emailErr = "";
$passwordErr = "";
$loginErr = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- EMAIL VALIDATION ---
    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
    } else {
        $email = trim($_POST["email"]);
    }

    // --- PASSWORD VALIDATION ---
    if (empty($_POST["password"])) {
        $passwordErr = "Please enter your password";
    } elseif (strlen($_POST["password"]) < 5 || strlen($_POST["password"]) > 15) {
        $passwordErr = "Password must be 5–15 characters long";
    } else {
        $password = $_POST["password"];
    }

    // --- ONLY CHECK DATABASE IF NO ERRORS ---
    if (empty($emailErr) && empty($passwordErr)) {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                // Success
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic'];

                logActivity($user['user_id'], "Logged in");

                header("Location: " . ($user['role'] === 'Admin' ? "admin_dashboard.php" : "dashboard.php"));
                exit;
            } else {
                $passwordErr = "Incorrect password";
            }
        } else {
            $emailErr = "Email not registered";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentLink Login</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="credentials.css">
</head>

<body>
    <div class="container-fluid login-container d-flex flex-column flex-lg-row min-vh-100 p-0">
        <div class="left-side d-flex flex-column justify-content-center align-items-center text-center p-5">
            <img src="dentlink-logo.png" alt="DentLink Logo">
            <p class="mt-3"><strong>DentLink: Dental Clinic Digital Appointment and Patient Records Management System</strong></p>
            <div class="info-box mt-3">
                <p class="mb-3">DentLink makes dental care simple and convenient with our modern booking system.</p>

                <div class="mb-2">
                    <strong><i class="bi bi-mouse"></i> Easy Online Booking</strong>
                    <p class="mb-0">Book appointments anytime, anywhere with just a few clicks.</p>
                </div>

                <div class="mb-2">
                    <strong><i class="bi bi-bell"></i> Appointment Reminders</strong>
                    <p class="mb-0">Never miss an appointment with automated email notifications.</p>
                </div>

                <div class="mb-2">
                    <strong><i class="bi bi-chat-dots"></i> Live Chat Support</strong>
                    <p class="mb-0">Connect with our staff instantly through our chat system.</p>
                </div>

                <div class="mb-2">
                    <strong><i class="bi bi-qr-code"></i> QR Code Check-in</strong>
                    <p class="mb-0">Fast and contactless check-in with your unique QR code.</p>
                </div>
            </div>
        </div>

        <div class="right-side d-flex justify-content-center align-items-center p-5">
            <div class="form-box w-100" style="max-width:400px;">
                <h2 class="mb-3 text-center"><i class="bi bi-box-arrow-in-right"></i> Welcome!</h2>
                <p class="text-center mb-4">Please login to your account.</p>

                <?php if (!empty($loginErr)) : ?>
                    <p class="message text-center"><?= htmlspecialchars($loginErr); ?></p>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <input type="email" name="email"
                            class="form-control <?= !empty($emailErr) ? 'is-invalid' : '' ?>"
                            placeholder="Email Address"
                            value="<?= htmlspecialchars($email); ?>">
                        <div class="invalid-feedback"><?= $emailErr; ?></div>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="password"
                            class="form-control <?= !empty($passwordErr) ? 'is-invalid' : '' ?>"
                            placeholder="Password">
                        <div class="invalid-feedback"><?= $passwordErr; ?></div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>

                    <div class="text-end mb-3">
                        <a href="forgot_password.php" class="text-decoration-none" style="color: var(--primary-color);">
                            Forgot Password?
                        </a>
                    </div>
                </form>

                <div class="login-link text-center mt-3">
                    Don't have an account? <a href="registration.php">Sign up</a>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {

            $("input[name='email']").on("input", function() {
                let value = $(this).val().trim();
                let errorBox = $(this).siblings(".invalid-feedback");

                if (value === "") {
                    $(this).removeClass("is-valid").addClass("is-invalid");
                    errorBox.text("Please enter your email");
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    $(this).removeClass("is-valid").addClass("is-invalid");
                    errorBox.text("Invalid email format");
                } else {
                    $(this).removeClass("is-invalid").addClass("is-valid");
                    errorBox.text(""); 
                }
            });

            $("input[name='password']").on("input", function() {
                let value = $(this).val();
                let errorBox = $(this).siblings(".invalid-feedback");

                if (value === "") {
                    $(this).removeClass("is-valid").addClass("is-invalid");
                    errorBox.text("Please enter your password");
                } else if (value.length < 5 || value.length > 15) {
                    $(this).removeClass("is-valid").addClass("is-invalid");
                    errorBox.text("Password must be 5–15 characters long");
                } else {
                    $(this).removeClass("is-invalid").addClass("is-valid");
                    errorBox.text(""); 
                }
            });

            // Prevent submit if invalid
            $("form").on("submit", function(e) {
                let email = $("input[name='email']");
                let password = $("input[name='password']");
                let hasError = false;

                if (email.val().trim() === "" || email.hasClass("is-invalid")) {
                    email.addClass("is-invalid");
                    hasError = true;
                }

                if (password.val().trim() === "" || password.hasClass("is-invalid")) {
                    password.addClass("is-invalid");
                    hasError = true;
                }

                if (hasError) e.preventDefault();
            });

        });
    </script>
</body>

</html>