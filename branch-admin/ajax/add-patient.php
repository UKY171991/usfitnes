<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

header('Content-Type: application/json');

checkBranchAdminAccess(); // Ensure user is a branch admin

$response = ['success' => false, 'message' => 'An error occurred.'];
$branch_id = $_SESSION['branch_id'];
// $user_id = $_SESSION['user_id']; // Keep for potential future logging

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!empty($name) && !empty($age) && !empty($gender)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO patients (name, age, gender, phone, email, address, branch_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $age, $gender, $phone, $email, $address, $branch_id]);

            if ($stmt->rowCount() > 0) {
                 // Optional: Log activity here if needed in the future
                 // $activity = "New patient added via AJAX: $name";
                 // $log_stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
                 // $log_stmt->execute([$user_id, $activity]);
                $response['success'] = true;
                $response['message'] = 'Patient added successfully!';
            } else {
                $response['message'] = 'Failed to add patient.';
            }
        } catch (PDOException $e) {
            // Log the error for server admin, don't expose details to user
            error_log("Add Patient AJAX Error: " . $e->getMessage()); 
            $response['message'] = 'Database error occurred while adding patient.'; 
        }
    } else {
        $response['message'] = 'Please fill in all required fields (Name, Age, Gender).';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?> 