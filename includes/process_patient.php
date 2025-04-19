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
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    
    // Handle POST request for adding/updating patient
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if it's a delete request
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'delete') {
            if ($_SESSION['role'] !== 'Admin') {
                throw new Exception('Only administrators can delete patients');
            }
            
            $patientId = filter_var($input['patient_id'], FILTER_VALIDATE_INT);
            if (!$patientId) {
                throw new Exception('Invalid patient ID');
            }
            
            // Check if patient has any test requests
            $stmt = $db->query(
                "SELECT COUNT(*) as count FROM Test_Requests WHERE patient_id = :id",
                ['id' => $patientId]
            );
            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                throw new Exception('Cannot delete patient with existing test requests');
            }
            
            // Delete patient
            $db->query(
                "DELETE FROM Patients WHERE patient_id = :id",
                ['id' => $patientId]
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Patient deleted successfully'
            ]);
            exit;
        }
        
        // Validate required fields
        $requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'phone'];
        foreach ($requiredFields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("$field is required");
            }
        }
        
        // Sanitize and validate input
        $firstName = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
        $lastName = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
        $dateOfBirth = filter_var(trim($_POST['date_of_birth']), FILTER_SANITIZE_STRING);
        $gender = filter_var(trim($_POST['gender']), FILTER_SANITIZE_STRING);
        $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $address = filter_var(trim($_POST['address'] ?? ''), FILTER_SANITIZE_STRING);
        $branchId = filter_var($_POST['branch_id'] ?? null, FILTER_VALIDATE_INT);
        
        // Validate email if provided
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Validate date of birth
        if (!strtotime($dateOfBirth)) {
            throw new Exception('Invalid date of birth');
        }
        
        // Validate gender
        if (!in_array($gender, ['Male', 'Female', 'Other'])) {
            throw new Exception('Invalid gender');
        }
        
        // Check if phone number already exists
        $stmt = $db->query(
            "SELECT COUNT(*) as count FROM Patients WHERE phone = :phone",
            ['phone' => $phone]
        );
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            throw new Exception('Phone number already exists');
        }
        
        // Insert new patient
        $db->query(
            "INSERT INTO Patients (user_id, first_name, last_name, date_of_birth, gender, 
                                 phone, email, address, branch_id, created_at) 
             VALUES (:user_id, :first_name, :last_name, :dob, :gender, 
                     :phone, :email, :address, :branch_id, NOW())",
            [
                'user_id' => $_SESSION['user_id'],
                'first_name' => $firstName,
                'last_name' => $lastName,
                'dob' => $dateOfBirth,
                'gender' => $gender,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'branch_id' => $branchId
            ]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Patient added successfully'
        ]);
    }
    else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    error_log("Patient processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 