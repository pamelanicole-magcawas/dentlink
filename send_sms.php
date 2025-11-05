<?php
// Include the Twilio SDK autoload file
require_once __DIR__ . '\twilio-php-main\src\Twilio\autoload.php';
// or use require_once __DIR__ . '/vendor/autoload.php'; if you used Composer

use Twilio\Rest\Client;

// Your Twilio credentials
$sid    = "AC9c75d0e89a750bdc4ff2f0c894326a16";
$token  = "a7baaa3371668f5864cf6f74f7724a24"; 

// Initialize Twilio client
$twilio = new Client($sid, $token);

// Send verification SMS
try {
    $verification = $twilio->verify->v2->services("VA30a9bde26a895cd8b4b664d328a9a55d")
                                       ->verifications
                                       ->create("+639944683904", "sms");

    echo "Verification SID: " . $verification->sid;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

