<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];
$branch_id = $_SESSION['branch_id'];
// $user_id = $_SESSION['user_id']; // For potential future logging

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $test_id = $_POST['test_id'] ?? null;
    $result = trim($_POST['result'] ?? ''); // Optional initial result
    $notes = trim($_POST['comments'] ?? ''); // Changed from comments
    $status = 'pending'; // Default status for new reports

    if (!empty($patient_id) && !empty($test_id)) {
        try {
            // Validate Patient ID belongs to this branch
            $patient_check = $conn->prepare("SELECT id FROM patients WHERE id = ? AND branch_id = ?");
            $patient_check->execute([$patient_id, $branch_id]);
            if ($patient_check->fetch() === false) {
                throw new Exception("Invalid patient selected for this branch.");
            }

            // Validate Test ID exists (master test validation)
            $test_check = $conn->prepare("SELECT id FROM tests WHERE id = ? AND status = 'active'");
            $test_check->execute([$test_id]);
            if ($test_check->fetch() === false) {
                 throw new Exception("Invalid test selected.");
            }

            // Insert the new report
            $stmt = $conn->prepare("
                INSERT INTO reports (patient_id, test_id, branch_id, result, notes, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$patient_id, $test_id, $branch_id, $result, $notes, $status]);

            if ($stmt->rowCount() > 0) {
                $report_id = $conn->lastInsertId();
                $response['success'] = true;
                $response['message'] = "Report (ID: $report_id) created successfully with status 'pending'.";
                // Optional: Log activity
            } else {
                $response['message'] = 'Failed to create the report.';
            }
        } catch (PDOException $e) {
            error_log("Add Report AJAX Error (PDO): " . $e->getMessage()); 
            $response['message'] = 'Database error occurred while creating the report.'; 
        } catch (Exception $e) {
             error_log("Add Report AJAX Error: " . $e->getMessage()); 
            $response['message'] = $e->getMessage(); // Show specific validation errors
        }
    } else {
        $response['message'] = 'Missing required fields (Patient, Test).';
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?> 