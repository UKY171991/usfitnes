<?php
/**
 * Test SSL SMTP Email Sending
 */

require_once 'includes/smtp_config_ssl.php';

// Test email - CHANGE THIS TO YOUR EMAIL
$test_email = 'uky171991@gmail.com';
$test_name = 'Test User';
$test_otp = '123456';

echo "Testing SSL SMTP Email Sending...\n";
echo "==================================\n";
echo "Sending test email to: $test_email\n\n";

// Test OTP email sending
echo "Testing OTP Email...\n";
$start_time = microtime(true);

$result = sendOTPEmail($test_email, $test_name, $test_otp);

$end_time = microtime(true);
$elapsed_time = round(($end_time - $start_time), 2);

echo "Time taken: {$elapsed_time} seconds\n";
echo "Result: " . ($result['success'] ? '✓ SUCCESS' : '✗ FAILED') . "\n";
echo "Message: " . $result['message'] . "\n\n";

if ($result['success']) {
    echo "🎉 SUCCESS! OTP email has been sent to $test_email\n";
    echo "Please check your inbox (and spam folder) for the verification email.\n";
} else {
    echo "❌ FAILED! Email could not be sent.\n";
    echo "Error details: " . $result['message'] . "\n";
}

echo "\nTest completed.\n";
?>
