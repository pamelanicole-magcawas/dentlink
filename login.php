<?php
session_start();
include("db_connect.php");

$email = $password = "";
$emailErr = $passwordErr = $loginErr = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (empty($_POST["email"])) {
        $emailErr = "Please enter your email";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Please enter your password";
    } elseif (strlen($_POST["password"]) < 5 || strlen($_POST["password"]) > 12) {
        $passwordErr = "Password must be 5–12 characters long";
    } else {
        $password = $_POST["password"];
    }

    if (empty($emailErr) && empty($passwordErr)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_pic'] = $user['profile_pic']; 

                if ($user['role'] === 'Admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $loginErr = "Invalid email or password";
            }
        } else {
            $loginErr = "Invalid email or password";
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
                <p><strong>DentLink</strong> simplifies dental appointment scheduling and patient record management. Patients can book online, check time slots, and get email notifications.</p>
                <p>The system ensures accurate record-keeping by tracking treatment histories and identifying new or returning patients for reliable dental services.</p>
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
                        <input type="email" name="email" class="form-control <?= !empty($emailErr) ? 'is-invalid' : '' ?>" placeholder="Email Address" value="<?= htmlspecialchars($email); ?>">
                        <?php if (!empty($emailErr)) : ?>
                            <div class="invalid-feedback"><?= $emailErr; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="password" class="form-control <?= !empty($passwordErr) ? 'is-invalid' : '' ?>" placeholder="Password">
                        <?php if (!empty($passwordErr)) : ?>
                            <div class="invalid-feedback"><?= $passwordErr; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>

                </form>

                <div class="login-link text-center mt-3">
                    Don't have an account? <a href="registration.php">Sign up</a>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>