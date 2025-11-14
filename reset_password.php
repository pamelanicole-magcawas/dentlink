<?php
session_start();
include("db_connect.php");

if (!isset($_SESSION['reset_user'])) {
    header("Location: forgot_password.php");
    exit();
}

$message = "";
$messageType = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPass = $_POST["password"];
    $confirmPass = $_POST["confirm"];

    if ($newPass !== $confirmPass) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($newPass) < 5) {
        $message = "Password must be at least 5 characters.";
        $messageType = "error";
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_user']['email'];

        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed, $email);

        if ($stmt->execute()) {
            unset($_SESSION['reset_user']);
            unset($_SESSION['reset_otp']);
            unset($_SESSION['reset_otp_expiration']);
            unset($_SESSION['reset_resend']);

            $message = "Password updated successfully!";
            $messageType = "success";
        } else {
            $message = "Failed to update password. Try again.";
            $messageType = "error";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex justify-content-center align-items-center" style="min-height:100vh;">

<div class="card p-4 shadow" style="width:380px;">
    <h4 class="text-center mb-3">Reset Password</h4>

    <form method="POST">
        <input type="password" name="password" placeholder="New Password" class="form-control mb-2" required>
        <input type="password" name="confirm" placeholder="Confirm Password" class="form-control mb-3" required>
        <button class="btn btn-primary w-100">Update Password</button>
    </form>
</div>

<?php if (!empty($message)): ?>
<script>
Swal.fire({
    icon: '<?= $messageType ?>',
    title: '<?= $messageType === "success" ? "Success" : "Error" ?>',
    text: '<?= $message ?>',
}).then(() => {
    <?php if ($messageType === "success") echo "window.location='login.php';"; ?>
});
</script>
<?php endif; ?>

</body>
</html>
