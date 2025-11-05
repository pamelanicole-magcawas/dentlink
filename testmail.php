<?php
$to = "your_email@example.com";
$subject = "Test Email from XAMPP";
$message = "If you receive this, your email setup works!";
$headers = "From: sgdentalcliniccc@gmail.com";

if(mail($to, $subject, $message, $headers)){
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Email sending failed.";
}
?>
