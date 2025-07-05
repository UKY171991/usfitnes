<?php
/**
 * OTP Verification API for Email Registration
 * Handles OTP generation, verification, and registration process
 */

// Set PHP timeout to prevent hanging
set_time_limit(30);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config.php';
require_once '../includes/smtp_config_simple.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// Get data from either POST or JSON input
$data = [];
if ($method === 'POST') {
    $data = array_merge($_POST, $input ?? []);
}

try {
    if ($method === 'POST') {
        $action = $data['action'] ?? '';
        
        switch ($action) {
            case 'send_otp':
                sendOTP($pdo, $data);
                break;
            case 'verify_otp':
                verifyOTP($pdo, $data);
                break;
            case 'resend_otp':
                resendOTP($pdo, $data);
                break;
            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

/**
 * Send OTP for email verification
 */
function sendOTP($pdo, $data) {
    // Validate required fields
    $requiredFields = ['firstname', 'lastname', 'email', 'password'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    $firstname = trim($data['firstname']);
    $lastname = trim($data['lastname']);
    $email = trim($data['email']);
    $password = $data['password'];
    $confirm_password = $data['confirm_password'] ?? '';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Validate password
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
        return;
    }
    
    if ($password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }
    
    try {
        // Check if email already exists in users table
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            return;
        }
        
        // Generate OTP
        $otp = generateOTP();
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Store OTP and user data temporarily
        $stmt = $pdo->prepare("
            INSERT INTO email_verifications (email, otp, firstname, lastname, password_hash, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            otp = VALUES(otp),
            firstname = VALUES(firstname),
            lastname = VALUES(lastname),
            password_hash = VALUES(password_hash),
            expires_at = VALUES(expires_at),
            created_at = NOW(),
            attempts = 0
        ");
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([$email, $otp, $firstname, $lastname, $password_hash, $expiry]);
        
        // Send OTP email with timeout handling
        $fullName = $firstname . ' ' . $lastname;
        
        // Try to send email with timeout
        $start_time = time();
        $emailResult = sendOTPEmailWithTimeout($email, $fullName, $otp, 15); // 15 second timeout
        $elapsed_time = time() - $start_time;
        
        // Log email sending time for debugging
        error_log("Email sending took $elapsed_time seconds for $email");
        
        if ($emailResult['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'OTP sent successfully to your email address',
                'email' => $email
            ]);
        } else {
            // Log the error for debugging but don't remove the record immediately
            error_log("Email sending failed for $email: " . $emailResult['message']);
            
            // Still allow user to proceed - they can try resend
            echo json_encode([
                'success' => true,
                'message' => 'Registration initiated. If you don\'t receive the OTP email within 2 minutes, please click "Resend OTP".',
                'email' => $email,
                'warning' => 'Email delivery may be delayed'
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Verify OTP and complete registration
 */
function verifyOTP($pdo, $data) {
    $email = trim($data['email'] ?? '');
    $otp = trim($data['otp'] ?? '');
    
    if (empty($email) || empty($otp)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
        return;
    }
    
    try {
        // Get verification record
        $stmt = $pdo->prepare("
            SELECT firstname, lastname, password_hash, expires_at, attempts
            FROM email_verifications
            WHERE email = ? AND otp = ?
        ");
        $stmt->execute([$email, $otp]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$verification) {
            // Increment attempts
            $stmt = $pdo->prepare("
                UPDATE email_verifications 
                SET attempts = attempts + 1 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid OTP code']);
            return;
        }
        
        // Check if OTP has expired
        if (strtotime($verification['expires_at']) < time()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one']);
            return;
        }
        
        // Check attempts limit
        if ($verification['attempts'] >= 5) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please request a new OTP']);
            return;
        }
        
        // Create user account
        $username = strtolower($verification['firstname'] . '.' . $verification['lastname']);
        $counter = 1;
        $originalUsername = $username;
        
        // Ensure username uniqueness
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if (!$stmt->fetch()) {
                break;
            }
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, full_name, email, user_type, email_verified, created_at)
            VALUES (?, ?, ?, ?, 'user', 1, NOW())
        ");
        
        $full_name = $verification['firstname'] . ' ' . $verification['lastname'];
        $stmt->execute([$username, $verification['password_hash'], $full_name, $email]);
        $userId = $pdo->lastInsertId();
        
        // Delete verification record
        $stmt = $pdo->prepare("DELETE FROM email_verifications WHERE email = ?");
        $stmt->execute([$email]);
        
        // Send welcome email
        sendWelcomeEmail($email, $full_name);
        
        // Log user in
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['full_name'] = $full_name;
        $_SESSION['user_type'] = 'user';
        $_SESSION['email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration completed successfully! Welcome to PathLab Pro.',
            'data' => [
                'user_id' => $userId,
                'username' => $username,
                'full_name' => $full_name,
                'email' => $email
            ]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

/**
 * Resend OTP
 */
function resendOTP($pdo, $data) {
    $email = trim($data['email'] ?? '');
    
    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        return;
    }
    
    try {
        // Get existing verification record
        $stmt = $pdo->prepare("
            SELECT firstname, lastname, created_at
            FROM email_verifications
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $verification = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$verification) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No pending verification found for this email']);
            return;
        }
        
        // Check if enough time has passed since last OTP (rate limiting)
        $lastSent = strtotime($verification['created_at']);
        $timeSinceLastSent = time() - $lastSent;
        
        if ($timeSinceLastSent < 60) { // 1 minute rate limit
            $waitTime = 60 - $timeSinceLastSent;
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'message' => "Please wait $waitTime seconds before requesting a new OTP"
            ]);
            return;
        }
        
        // Generate new OTP
        $otp = generateOTP();
        $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Update verification record
        $stmt = $pdo->prepare("
            UPDATE email_verifications
            SET otp = ?, expires_at = ?, created_at = NOW(), attempts = 0
            WHERE email = ?
        ");
        $stmt->execute([$otp, $expiry, $email]);
        
        // Send OTP email
        $fullName = $verification['firstname'] . ' ' . $verification['lastname'];
        $emailResult = sendOTPEmailWithTimeout($email, $fullName, $otp, 15);
        
        if ($emailResult['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'New OTP sent successfully to your email address'
            ]);
        } else {
            error_log("Resend OTP email failed for $email: " . $emailResult['message']);
            
            // Don't fail completely - user might still receive delayed email
            echo json_encode([
                'success' => true,
                'message' => 'OTP resend initiated. If you don\'t receive the email within 2 minutes, please try again.',
                'warning' => 'Email delivery may be delayed'
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
