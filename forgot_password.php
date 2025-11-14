<?php
session_start();
include("db_connect.php");

$phone = "";
$phoneErr = "";
$step = "phone"; // default step

// ----------------------------
// 1. SEND OTP (from phone input)
// ----------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["phone"])) {

    if (!empty($_POST["phone"])) {
        $phone = trim($_POST["phone"]);
        $phone = preg_replace("/[^0-9]/", "", $phone); // numbers only
        if (substr($phone, 0, 2) === "63") $phone = "0" . substr($phone, 2);
    }

    if (empty($phoneErr)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['reset_user'] = $user;

            // Generate OTP only if not set or expired
            if (!isset($_SESSION['reset_otp']) || time() > $_SESSION['reset_otp_expiration']) {
                $otp = rand(100000, 999999);
                $_SESSION['reset_otp'] = $otp;
                $_SESSION['reset_otp_expiration'] = time() + 300; // 5 min
                $_SESSION['reset_resend'] = 0;

                // Send SMS
                $url = 'https://sms.iprogtech.com/api/v1/sms_messages';
                $data = [
                    'api_token' => '786ece1e7465ff9dd203978f63ef181c52e20982',
                    'message' => "Your DentLink password reset code is: $otp",
                    'phone_number' => $phone
                ];
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_exec($curl);
                curl_close($curl);
            }

            $step = "otp"; // move to OTP step
        } else {
            $phoneErr = "Phone number is not registered";
        }
    }
}

// ----------------------------
// 2. VERIFY OTP
// ----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp1'])) {
    $enteredOtp = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];

    if (time() > $_SESSION['reset_otp_expiration']) {
        $otpStatus = "expired";
    } elseif ($enteredOtp == $_SESSION['reset_otp']) {
        $step = "verified"; // mark OTP as verified
    } else {
        $otpStatus = "invalid";
        $step = "otp";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: #f0f4f7;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .card,
        .otp-card {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .otp-inputs {
            display: flex;
            justify-content: space-between;
            margin: 25px 0;
        }

        .otp-inputs input {
            width: 45px;
            height: 55px;
            font-size: 24px;
            text-align: center;
            border-radius: 10px;
            border: 1px solid #ccc;
        }

        .otp-inputs input:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
        }

        #resendBtn {
            margin-top: 15px;
        }
    </style>
</head>

<body>

    <?php if ($step === "phone"): ?>
        <div class="card">
            <h4 class="mb-3">Forgot Password</h4>
            <form method="POST">
                <input type="text" name="phone" class="form-control <?= !empty($phoneErr) ? 'is-invalid' : '' ?>" placeholder="Registered Phone Number" value="<?= htmlspecialchars($phone) ?>">
                <div class="invalid-feedback"><?= $phoneErr ?></div>
                <button class="btn btn-primary w-100 mt-3">Send OTP</button>
            </form>
        </div>

    <?php elseif ($step === "otp"): ?>
        <div class="otp-card">
            <h3>Reset Password</h3>
            <p>Enter the 6-digit code sent to <b><?= htmlspecialchars($_SESSION['reset_user']['phone']) ?></b></p>
            <form method="POST">
                <div class="otp-inputs">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <input type="text" maxlength="1" name="otp<?= $i ?>" required>
                    <?php endfor; ?>
                </div>
                <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
            </form>
            <div class="mt-3">
                <span id="timerText">OTP expires in: <span id="countdown"></span></span>
                <button id="resendBtn" class="btn btn-secondary w-100" <?= $_SESSION['reset_resend'] >= 3 ? 'disabled' : '' ?>>Resend OTP</button>
            </div>
        </div>

    <?php elseif ($step === "verified"): ?>
        <!-- SweetAlert handled below -->
        <div class="otp-card">
            <h3>OTP Verified</h3>
            <p>Redirecting to reset password...</p>
        </div>
    <?php endif; ?>

    <script>
        // Countdown
        <?php if ($step === "otp"): ?>
            let remaining = <?= $_SESSION['reset_otp_expiration'] - time() ?>;
            const countdownEl = document.getElementById("countdown");
            const resendBtn = document.getElementById("resendBtn");
            const timer = setInterval(() => {
                let m = Math.floor(remaining / 60);
                let s = remaining % 60;
                countdownEl.textContent = `${m}:${s<10?'0'+s:s}`;
                remaining--;
                if (remaining < 0) {
                    clearInterval(timer);
                    countdownEl.textContent = 'Expired';
                    Swal.fire({
                        icon: 'warning',
                        title: 'OTP Expired',
                        text: 'You can resend a new OTP.'
                    });
                }
            }, 1000);

            // Auto-advance OTP inputs
            document.querySelectorAll(".otp-inputs input").forEach((input, i, arr) => {
                input.addEventListener("input", () => {
                    if (input.value && i < arr.length - 1) arr[i + 1].focus();
                });
                input.addEventListener("keydown", e => {
                    if (e.key === "Backspace" && !input.value && i > 0) arr[i - 1].focus();
                });
            });

            // Resend OTP
            resendBtn.addEventListener("click", () => {
                fetch("forgot_resend_otp.php", {
                        method: "POST"
                    })
                    .then(r => r.json())
                    .then(res => {
                        Swal.fire({
                            icon: res.status === 'success' ? 'success' : 'error',
                            title: res.message
                        });
                        if (res.status === 'success') remaining = 300;
                    });
            });
        <?php endif; ?>

        <?php if (isset($otpStatus) && $otpStatus === 'expired'): ?>
            Swal.fire({
                icon: 'error',
                title: 'OTP Expired',
                text: 'Please resend a new OTP.'
            });
        <?php elseif (isset($otpStatus) && $otpStatus === 'invalid'): ?>
            Swal.fire({
                icon: 'error',
                title: 'Invalid OTP',
                text: 'Please try again.'
            });
        <?php elseif ($step === 'verified'): ?>
            Swal.fire({
                    icon: 'success',
                    title: 'OTP Verified',
                    text: 'You may now reset your password.'
                })
                .then(() => {
                    window.location = 'reset_password.php';
                });
        <?php endif; ?>
    </script>

</body>

</html>