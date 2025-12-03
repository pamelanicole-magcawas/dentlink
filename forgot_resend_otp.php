<?php
session_start();
include("db_connect.php");

// Default response
$response = ['status' => 'error', 'message' => 'Something went wrong.'];

// Check if reset user exists
if (isset($_SESSION['reset_user']['phone'])) {
    // Limit to 3 resends
    if (!isset($_SESSION['reset_resend'])) {
        $_SESSION['reset_resend'] = 0;
    }

    if ($_SESSION['reset_resend'] >= 3) {
        $response = ['status' => 'error', 'message' => 'You have reached the maximum number of OTP resends.'];
    } else {
        $phone = $_SESSION['reset_user']['phone'];

        // Generate new OTP
        $otp = rand(100000, 999999);
        $_SESSION['reset_otp'] = $otp;
        $_SESSION['reset_otp_expiration'] = time() + 300; // 5 minutes
        $_SESSION['reset_resend']++;

        // Send OTP via SMS API
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

        $response = ['status' => 'success', 'message' => 'A new OTP has been sent to your phone.'];
    }
} else {
    $response = ['status' => 'error', 'message' => 'No user session found. Please start over.'];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>