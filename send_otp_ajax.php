<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['pending_user'])) {
    echo json_encode(['status'=>'error','message'=>'No pending registration']);
    exit;
}

if (!isset($_SESSION['otp_resend_count'])) $_SESSION['otp_resend_count'] = 0;

if ($_SESSION['otp_resend_count'] >= 3) {
    echo json_encode(['status'=>'error','message'=>'Maximum resend attempts reached']);
    exit;
}

$user = $_SESSION['pending_user'];
$phone = $user['phone'];

// Generate new OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiration'] = time() + 300;
$_SESSION['otp_resend_count']++;

// Send via IPROG SMS API
$url = 'https://sms.iprogtech.com/api/v1/sms_messages';
$data = [
    'api_token' => '786ece1e7465ff9dd203978f63ef181c52e20982', // replace
    'message' => "Your verification code is: $otp",
    'phone_number' => $phone
];

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl);
curl_close($curl);

echo json_encode(['status'=>'success']);
