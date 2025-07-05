<?php
require_once 'includes/smtp_config_simple.php';

echo "Testing simplified email configuration...\n";

$result = sendOTPEmail('test@example.com', 'Test User', '123456');
echo "Result: " . json_encode($result) . "\n";

echo "Email configuration test completed.\n";
?>
