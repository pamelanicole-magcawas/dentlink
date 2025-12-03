<?php
session_start();

if (!isset($_SESSION['pending_user'])) {
    header("Location: registration.php");
    exit();
}

$user = $_SESSION['pending_user'];

// Generate 6-digit OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiration'] = time() + 300; // 5 min
if (!isset($_SESSION['otp_resend_count'])) $_SESSION['otp_resend_count'] = 0;

// Send OTP via IPROG SMS API
$phone = $user['phone'];
$message = "Your verification code is: $otp";

$url = 'https://sms.iprogtech.com/api/v1/sms_messages';
$data = [
    'api_token' => '786ece1e7465ff9dd203978f63ef181c52e20982', // replace with your token
    'message' => $message,
    'phone_number' => $phone
];

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

$error_message = isset($_GET['error']) ? urldecode($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background: linear-gradient(135deg, #91C4C3 0%, #B4DEBD 100%);
            font-family: 'Poppins', 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .otp-card {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .otp-card h3 {
            font-weight: 700;
            margin-bottom: 10px;
            color: #80A1BA;
        }

        .otp-card p {
            color: #555;
            font-size: 0.95rem;
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
            transition: 0.2s;
        }

        .otp-inputs input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
            outline: none;
        }

        .btn-primary {
            width: 100%;
            padding: 15px;
            background: #80A1BA;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(111, 168, 167, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(111, 168, 167, 0.4);
            background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%);
        }

        #resendBtn {
            width: 100%;
            padding: 15px;
            background: #80A1BA;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(111, 168, 167, 0.3);
        }

        #resendBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(111, 168, 167, 0.4);
            background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%);
        }

        @media(max-width:380px) {
            .otp-inputs input {
                width: 40px;
                height: 50px;
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <div class="otp-card">
        <h3>Verify Your Phone</h3>
        <?php
        $maskedPhone = str_repeat('*', strlen($phone) - 4) . substr($phone, -4);
        ?>
        <p>Enter the 6-digit code sent to <b><?= $maskedPhone ?></b></p>

        <form action="verify_otp.php" method="POST" id="otpForm">
            <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>">
            <div class="otp-inputs">
                <input type="text" maxlength="1" name="otp1" required>
                <input type="text" maxlength="1" name="otp2" required>
                <input type="text" maxlength="1" name="otp3" required>
                <input type="text" maxlength="1" name="otp4" required>
                <input type="text" maxlength="1" name="otp5" required>
                <input type="text" maxlength="1" name="otp6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        </form>

        <div class="mt-3">
            <span id="timerText">OTP expires in: <span id="countdown">5:00</span></span>
            <button id="resendBtn" class="btn btn-secondary w-100" <?= $_SESSION['otp_resend_count'] >= 3 ? 'disabled' : '' ?>>Resend OTP</button>
        </div>
    </div>

    <script>
        let remaining = 300; // 5 minutes
        const countdownEl = document.getElementById("countdown");
        const resendBtn = document.getElementById("resendBtn");

        <?php if($error_message): ?>
            Swal.fire({icon:'error', title: '<?= $error_message ?>'});
        <?php endif; ?>

        resendBtn.disabled = true;

        function startCountdown() {
            clearInterval(timer);
            timer = setInterval(() => {
                if (remaining > 0) {
                    let minutes = Math.floor(remaining / 60);
                    let seconds = remaining % 60;
                    countdownEl.textContent = `${minutes}:${seconds < 10 ? "0"+seconds : seconds}`;
                    remaining--;
                } else {
                    clearInterval(timer);
                    countdownEl.textContent = "Expired";
                    // Enable resend only if user hasn't reached limit
                    if (<?= $_SESSION['otp_resend_count'] ?> < 3) {
                        resendBtn.disabled = false;
                    }
                    Swal.fire({
                        icon: 'warning',
                        title: 'OTP Expired',
                        text: 'You can resend a new OTP.'
                    });
                }
            }, 1000);
        }

        let timer;
        startCountdown();

        // Resend OTP
        resendBtn.addEventListener('click', () => {
            resendBtn.disabled = true; // disable immediately to prevent spam
            fetch('send_otp_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ phone: '<?= $phone ?>' })
            })
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success') {
                    Swal.fire({ icon: 'success', title: 'OTP Sent', text: 'Check your phone for the new code.' });
                    remaining = 300; // reset 5 minutes
                    startCountdown();  // restart countdown
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                    // Enable button if resend failed and user is under limit
                    if (<?= $_SESSION['otp_resend_count'] ?> < 3) resendBtn.disabled = false;
                }
            });
        });

        // Auto-advance OTP inputs
        document.querySelectorAll(".otp-inputs input").forEach((input, i, arr) => {
            input.addEventListener("input", () => {
                if (input.value && i < arr.length - 1) arr[i + 1].focus();
            });
            input.addEventListener("keydown", e => {
                if (e.key === "Backspace" && !input.value && i > 0) arr[i - 1].focus();
            });
        });
    </script>
</body>
</html>
