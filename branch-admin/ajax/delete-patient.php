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

    if ($patient_id > 0) {
        try {
            // Optional: Add checks here if needed (e.g., check for associated reports)

            $stmt = $conn->prepare("DELETE FROM patients WHERE id = ? AND branch_id = ?");
            $stmt->execute([$patient_id, $branch_id]);

            if ($stmt->rowCount() > 0) {
                // Optional: Log activity here if needed
                // $activity = "Patient deleted via AJAX: ID $patient_id";
                // $log_stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
                // $log_stmt->execute([$user_id, $activity]);
                $response['success'] = true;
                $response['message'] = 'Patient deleted successfully!';
            } else {
                $response['message'] = 'Patient not found or already deleted.'; // More specific message
            }
        } catch (PDOException $e) {
             error_log("Delete Patient AJAX Error: " . $e->getMessage());
             $response['message'] = 'Database error occurred while deleting patient.';
        } catch (Exception $e) { // Catch other potential exceptions (e.g., from pre-delete checks)
             $response['message'] = $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid Patient ID for deletion.';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?> 