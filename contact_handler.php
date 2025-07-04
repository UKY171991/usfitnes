<?php
/**
 * Contact Form Handler
 * Processes contact form submissions from the home page
 */

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
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

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please correct the following errors: ' . implode(', ', $errors)
    ]);
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
    // Log the submission (optional)
    $logEntry = date('Y-m-d H:i:s') . " - Contact form submission from {$firstName} {$lastName} ({$email})" . PHP_EOL;
    file_put_contents('contact_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly at info@pathlabpro.com.'
    ]);
}
?>
