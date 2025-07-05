<?php
/**
 * Test Email Sending Script
 * Use this to test if SMTP is working properly
 */

require_once 'includes/smtp_config.php';

// Test email configuration
$test_email = 'test@example.com'; // Change this to your test email
$test_name = 'Test User';
$test_otp = '123456';

echo "Testing SMTP Email Sending...\n";
echo "============================\n";

// Test 1: Basic email sending
echo "Test 1: Basic Email Test\n";
$start_time = microtime(true);

$result = sendEmail($test_email, $test_name, 'Test Email', 'This is a test email from PathLab Pro.', false);

$end_time = microtime(true);
$elapsed_time = round(($end_time - $start_time) * 1000, 2);

echo "Time taken: {$elapsed_time}ms\n";
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n\n";

// Test 2: OTP email sending
echo "Test 2: OTP Email Test\n";
$start_time = microtime(true);

$result = sendOTPEmail($test_email, $test_name, $test_otp);

$end_time = microtime(true);
$elapsed_time = round(($end_time - $start_time) * 1000, 2);

echo "Time taken: {$elapsed_time}ms\n";
echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . $result['message'] . "\n\n";

// Test 3: Check if PHPMailer is available
echo "Test 3: PHPMailer Availability\n";
if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    echo "PHPMailer: AVAILABLE\n";
} else {
    echo "PHPMailer: NOT AVAILABLE (using fallback method)\n";
}

echo "\nTesting completed!\n";
?>
