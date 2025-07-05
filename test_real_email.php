<?php
/**
 * Test Real Email Sending
 * Use this to test if actual emails are being sent
 */

require_once 'includes/smtp_config_working.php';

// Test email configuration - CHANGE THIS TO YOUR ACTUAL EMAIL
$test_email = 'uky171991@gmail.com'; // Change this to the email where you want to receive the test
$test_name = 'Test User';
$test_otp = '123456';

echo "Testing Real Email Sending...\n";
echo "============================\n";
echo "Sending test email to: $test_email\n\n";

// Test 1: Basic email sending
echo "Test 1: Basic Email Test\n";
$start_time = microtime(true);

$result = sendEmail($test_email, $test_name, 'Test Email from PathLab Pro', 'This is a test email from PathLab Pro to verify email functionality is working.', false);

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

// Test 3: Check PHP mail configuration
echo "Test 3: PHP Mail Configuration\n";
echo "SMTP Host: " . ini_get('SMTP') . "\n";
echo "SMTP Port: " . ini_get('smtp_port') . "\n";
echo "Sendmail From: " . ini_get('sendmail_from') . "\n\n";

echo "Testing completed!\n";
echo "If emails were sent successfully, check your inbox: $test_email\n";
echo "Note: Emails might take a few minutes to arrive.\n";
?>
