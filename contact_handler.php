<?php
/**
 * Contact Form Handler
 * Processes contact form submissions from the home page
 * Supports both AJAX and regular form submissions
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Set content type based on request type
if ($isAjax) {
    header('Content-Type: application/json');
} else {
    header('Content-Type: text/html');
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    } else {
        header('Location: index.php?error=method_not_allowed');
    }
    exit();
}

// Get form data
$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$company = trim($_POST['company'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($firstName)) {
    $errors[] = 'First name is required';
}

if (empty($lastName)) {
    $errors[] = 'Last name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($subject)) {
    $errors[] = 'Subject is required';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

// Check for potential spam (simple honeypot and rate limiting)
if (isset($_POST['honeypot']) && !empty($_POST['honeypot'])) {
    $errors[] = 'Spam detected';
}

if (!empty($errors)) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Please correct the following errors: ' . implode(', ', $errors)
        ]);
    } else {
        header('Location: index.php?error=' . urlencode(implode(', ', $errors)));
    }
    exit();
}

// Rate limiting - check if same IP submitted recently
$rateLimit = checkRateLimit($_SERVER['REMOTE_ADDR']);
if (!$rateLimit['allowed']) {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Please wait before sending another message. You can submit again in ' . $rateLimit['wait_time'] . ' seconds.'
        ]);
    } else {
        header('Location: index.php?error=rate_limit');
    }
    exit();
}

// Sanitize inputs
$firstName = htmlspecialchars($firstName);
$lastName = htmlspecialchars($lastName);
$email = htmlspecialchars($email);
$phone = htmlspecialchars($phone);
$company = htmlspecialchars($company);
$subject = htmlspecialchars($subject);
$message = htmlspecialchars($message);

// Prepare email content
$to = 'info@pathlabpro.com'; // Change this to your actual email
$emailSubject = 'Contact Form: ' . $subject;
$timestamp = date('Y-m-d H:i:s');

$emailBody = "
New Contact Form Submission

Date: {$timestamp}
Name: {$firstName} {$lastName}
Email: {$email}
Phone: {$phone}
Company: {$company}
Subject: {$subject}

Message:
{$message}

--
This message was sent from the PathLab Pro contact form.
";

// Email headers
$headers = [
    'From: noreply@pathlabpro.com',
    'Reply-To: ' . $email,
    'X-Mailer: PHP/' . phpversion(),
    'Content-Type: text/plain; charset=UTF-8'
];

// Attempt to send email
$emailSent = mail($to, $emailSubject, $emailBody, implode("\r\n", $headers));

if ($emailSent) {
    // Log the submission
    logSubmission($firstName, $lastName, $email, $_SERVER['REMOTE_ADDR']);
    
    if ($isAjax) {
        echo json_encode([
            'success' => true,
            'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
        ]);
    } else {
        header('Location: index.php?success=1');
    }
} else {
    if ($isAjax) {
        echo json_encode([
            'success' => false,
            'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly at info@pathlabpro.com.'
        ]);
    } else {
        header('Location: index.php?error=send_failed');
    }
}

/**
 * Check rate limiting for IP address
 */
function checkRateLimit($ip) {
    $rateFile = 'rate_limit.json';
    $maxSubmissions = 3; // Max submissions per hour
    $timeWindow = 3600; // 1 hour in seconds
    
    // Read existing data
    $data = [];
    if (file_exists($rateFile)) {
        $data = json_decode(file_get_contents($rateFile), true) ?: [];
    }
    
    // Clean old entries
    $currentTime = time();
    foreach ($data as $key => $entry) {
        if ($currentTime - $entry['timestamp'] > $timeWindow) {
            unset($data[$key]);
        }
    }
    
    // Count submissions for this IP
    $ipSubmissions = array_filter($data, function($entry) use ($ip) {
        return $entry['ip'] === $ip;
    });
    
    if (count($ipSubmissions) >= $maxSubmissions) {
        $oldestSubmission = min(array_column($ipSubmissions, 'timestamp'));
        $waitTime = $timeWindow - ($currentTime - $oldestSubmission);
        return ['allowed' => false, 'wait_time' => $waitTime];
    }
    
    // Add current submission
    $data[] = ['ip' => $ip, 'timestamp' => $currentTime];
    
    // Save data
    file_put_contents($rateFile, json_encode($data), LOCK_EX);
    
    return ['allowed' => true, 'wait_time' => 0];
}

/**
 * Log submission for tracking
 */
function logSubmission($firstName, $lastName, $email, $ip) {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $firstName . ' ' . $lastName,
        'email' => $email,
        'ip' => $ip
    ];
    
    $logFile = 'contact_submissions.log';
    $logLine = json_encode($logEntry) . PHP_EOL;
    file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
}
?>
