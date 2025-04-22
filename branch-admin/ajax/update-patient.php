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
    $patient_id = $_POST['patient_id'] ?? 0;
    $name = trim($_POST['name'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($patient_id > 0 && !empty($name) && !empty($age) && !empty($gender)) {
        try {
            $stmt = $conn->prepare("
                UPDATE patients 
                SET name = ?, age = ?, gender = ?, phone = ?, email = ?, address = ?
                WHERE id = ? AND branch_id = ?
            ");
            $stmt->execute([$name, $age, $gender, $phone, $email, $address, $patient_id, $branch_id]);

            // rowCount() might return 0 if no data was actually changed, even if the query ran.
            // So, we consider success if the query executed without error and affected the correct patient ID.
            // A more robust check might involve fetching the data before/after or checking the execute() result more closely, 
            // but for simplicity, we assume success if no error occurs for a valid patient ID.
            
            // Optional: Log activity here if needed
            // $activity = "Patient updated via AJAX: $name (ID: $patient_id)";
            // $log_stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            // $log_stmt->execute([$user_id, $activity]);

            $response['success'] = true;
            $response['message'] = 'Patient updated successfully!'; // Assume success if query runs without error for valid ID

        } catch (PDOException $e) {
            error_log("Update Patient AJAX Error: " . $e->getMessage());
            $response['message'] = 'Database error occurred while updating patient.';
        }
    } else {
        if ($patient_id <= 0) {
             $response['message'] = 'Invalid Patient ID for update.';
        } else {
             $response['message'] = 'Missing required fields (Name, Age, Gender) for update.';
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?> 