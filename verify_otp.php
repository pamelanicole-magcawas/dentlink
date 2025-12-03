<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['pending_user'])) {
    header("Location: registration.php");
    exit();
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_SESSION['pending_user'];
    $entered_otp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];

     if (!isset($_SESSION['otp']) || time() > $_SESSION['otp_expiration']) {
        $error = "OTP expired. Please resend.";
        header("Location: send_otp.php?error=" . urlencode($error));
        exit();
    } elseif ($entered_otp != $_SESSION['otp']) {
        $error = "Invalid OTP. Try again.";
        header("Location: send_otp.php?error=" . urlencode($error));
        exit();
    } else {

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, address, gender, password, role, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssssss",
            $_SESSION['pending_user']['first_name'],
            $_SESSION['pending_user']['last_name'],
            $_SESSION['pending_user']['email'],
            $_SESSION['pending_user']['phone'],
            $_SESSION['pending_user']['address'],
            $_SESSION['pending_user']['gender'], // ADD THIS LINE
            $_SESSION['pending_user']['password'],
            $_SESSION['pending_user']['role'],
            $_SESSION['pending_user']['profile_pic']
        );

        if ($stmt->execute()) {
            unset($_SESSION['pending_user'], $_SESSION['otp'], $_SESSION['otp_expiration'], $_SESSION['otp_resend_count']);
            $message = "success";
        } else {
            $message = "Database error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex justify-content-center align-items-center" style="min-height:100vh; background:#f5f5f5">
    <div class="container text-center">

        <?php if (!empty($message) && $message === "success"): ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Verified!',
                    text: 'Your account has been created successfully.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = "login.php";
                });
            </script>

        <?php elseif (!empty($message)): ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: '<?= $message ?>'
                });
            </script>
        <?php endif; ?>

    </div>
</body>

</html>
