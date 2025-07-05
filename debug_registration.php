<?php
/**
 * Debug Registration Flow
 * Test the OTP registration process
 */

require_once 'config.php';
require_once 'includes/smtp_config.php';

// Test data
$test_data = [
    'firstname' => 'Test',
    'lastname' => 'User',
    'email' => 'test@example.com', // Change this to a real email for testing
    'password' => 'test123',
    'confirm_password' => 'test123',
    'action' => 'send_otp'
];

echo "Testing OTP Registration Flow\n";
echo "============================\n";

// Test 1: Database connection
echo "Test 1: Database Connection\n";
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✓ Database connection successful\n\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 2: Check if tables exist
echo "Test 2: Database Tables\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'email_verifications'");
    if ($stmt->rowCount() > 0) {
        echo "✓ email_verifications table exists\n";
    } else {
        echo "✗ email_verifications table missing\n";
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ users table exists\n";
    } else {
        echo "✗ users table missing\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "✗ Table check failed: " . $e->getMessage() . "\n\n";
}

// Test 3: Email sending
echo "Test 3: Email Sending\n";
$start_time = microtime(true);
$result = sendOTPEmail($test_data['email'], $test_data['firstname'] . ' ' . $test_data['lastname'], '123456');
$end_time = microtime(true);
$elapsed_time = round(($end_time - $start_time) * 1000, 2);

echo "Time taken: {$elapsed_time}ms\n";
echo "Result: " . ($result['success'] ? '✓ SUCCESS' : '✗ FAILED') . "\n";
echo "Message: " . $result['message'] . "\n\n";

// Test 4: API endpoint simulation
echo "Test 4: API Endpoint Simulation\n";
$start_time = microtime(true);

// Simulate the sendOTP function
try {
    // Validate email format
    if (!filter_var($test_data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$test_data['email']]);
    if ($stmt->fetch()) {
        echo "⚠ Email already exists in users table\n";
    } else {
        echo "✓ Email is available\n";
    }
    
    // Generate OTP
    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    echo "✓ OTP generated: $otp\n";
    echo "✓ Expiry time: $expiry\n";
    
    $end_time = microtime(true);
    $elapsed_time = round(($end_time - $start_time) * 1000, 2);
    
    echo "✓ API simulation completed in {$elapsed_time}ms\n\n";
    
} catch (Exception $e) {
    echo "✗ API simulation failed: " . $e->getMessage() . "\n\n";
}

echo "Debug testing completed!\n";
echo "If email sending failed, check your SMTP settings in includes/smtp_config.php\n";
?>
