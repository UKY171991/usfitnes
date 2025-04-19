<?php
require_once '../config.php';
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

try {
    $db = Database::getInstance();
    
    // Validate required fields
    $required_fields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'phone'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("$field is required");
        }
    }

    // Sanitize and validate input
    $first_name = filter_var($_POST['first_name'], FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'], FILTER_SANITIZE_STRING);
    $date_of_birth = filter_var($_POST['date_of_birth'], FILTER_SANITIZE_STRING);
    $gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $address = filter_var($_POST['address'] ?? '', FILTER_SANITIZE_STRING);
    $branch_id = $_SESSION['branch_id'] ?? null;

    // Validate email if provided
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Insert patient
    $sql = "INSERT INTO Patients (first_name, last_name, date_of_birth, gender, phone, email, address, branch_id, created_at) 
            VALUES (:first_name, :last_name, :date_of_birth, :gender, :phone, :email, :address, :branch_id, NOW())";
    
    $params = [
        'first_name' => $first_name,
        'last_name' => $last_name,
        'date_of_birth' => $date_of_birth,
        'gender' => $gender,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
        'branch_id' => $branch_id
    ];

    $stmt = $db->query($sql, $params);
    
    echo json_encode(['success' => true, 'message' => 'Patient added successfully']);

} catch (Exception $e) {
    error_log("Add Patient Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 