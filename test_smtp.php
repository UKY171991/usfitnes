<?php
/**
 * Quick SMTP Test Script
 * Tests the SMTP configuration to ensure it's working
 */

require_once 'includes/smtp_config.php';

// Set script timeout
set_time_limit(30);

echo "Testing SMTP Configuration...\n";
echo "Host: " . SMTP_HOST . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Username: " . SMTP_USERNAME . "\n";
echo "Security: " . SMTP_SECURE . "\n\n";

// Test basic connection
echo "Testing connection...\n";
$start_time = time();

$result = sendEmail(
    'test@example.com', 
    'Test User', 
    'SMTP Test - PathLab Pro', 
    'This is a test email to verify SMTP configuration.',
    false
);

$elapsed_time = time() - $start_time;

echo "Time taken: $elapsed_time seconds\n";
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n";

// Also test OTP email format
echo "\n\nTesting OTP Email...\n";
$start_time = time();

$otp_result = sendOTPEmail('test@example.com', 'Test User', '123456');

$elapsed_time = time() - $start_time;

echo "Time taken: $elapsed_time seconds\n";
echo "Result: " . ($otp_result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $otp_result['message'] . "\n";
?>
