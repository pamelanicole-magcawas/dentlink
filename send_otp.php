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
    body { background:#f0f4f7; display:flex; justify-content:center; align-items:center; min-height:100vh; }
    .otp-card { background:#fff; padding:30px; border-radius:15px; box-shadow:0 8px 20px rgba(0,0,0,0.1); max-width:400px; width:90%; text-align:center; }
    .otp-card h3 { font-weight:700; margin-bottom:10px; color:#333; }
    .otp-card p { color:#555; font-size:0.95rem; }
    .otp-inputs { display:flex; justify-content:space-between; margin:25px 0; }
    .otp-inputs input { width:45px; height:55px; font-size:24px; text-align:center; border-radius:10px; border:1px solid #ccc; transition:0.2s; }
    .otp-inputs input:focus { border-color:#0d6efd; box-shadow:0 0 5px rgba(13,110,253,0.5); outline:none; }
    .btn-primary { background:#0d6efd; border:none; }
    #resendBtn { margin-top:15px; }
    @media(max-width:380px){ .otp-inputs input { width:40px; height:50px; font-size:22px; } }
</style>
</head>
<body>
<div class="otp-card">
    <h3>Verify Your Phone</h3>
    <p>Enter the 6-digit code sent to <b><?= htmlspecialchars($phone) ?></b></p>

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
// Countdown 5-minutes
let remaining = 300;
const countdownEl = document.getElementById("countdown");
const resendBtn = document.getElementById("resendBtn");

const timer = setInterval(() => {
    let minutes = Math.floor(remaining / 60);
    let seconds = remaining % 60;
    countdownEl.textContent = `${minutes}:${seconds < 10 ? "0"+seconds : seconds}`;
    remaining--;
    if (remaining < 0) {
        clearInterval(timer);
        document.getElementById("timerText").textContent = "OTP expired";
        resendBtn.disabled = <?= $_SESSION['otp_resend_count'] >= 3 ? 'true' : 'false' ?>;
        Swal.fire({ icon: 'warning', title: 'OTP Expired', text: 'Please request a new OTP.' });
    }
}, 1000);

// OTP input auto-advance/backspace
const inputs = document.querySelectorAll(".otp-inputs input");
inputs.forEach((input, i) => {
    input.addEventListener('input', () => {
        if (input.value.length === 1 && i < inputs.length - 1) inputs[i + 1].focus();
    });
    input.addEventListener('keydown', e => {
        if (e.key === "Backspace" && !input.value && i > 0) inputs[i - 1].focus();
    });
});

// Resend OTP AJAX
resendBtn.addEventListener('click', () => {
    if (<?= $_SESSION['otp_resend_count'] ?> >= 3) {
        Swal.fire({ icon: 'error', title: 'Limit Reached', text: 'You have reached maximum resend attempts.' });
        return;
    }
    fetch('send_otp_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone: '<?= $phone ?>' })
    }).then(r => r.json()).then(res => {
        if (res.status === 'success') {
            Swal.fire({ icon: 'success', title: 'OTP Sent', text: 'Check your phone for the new code.' });
            remaining = 300;
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
        }
    });
});
</script>
</body>
</html>
