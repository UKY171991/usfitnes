<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'An error occurred.'];
$branch_id = $_SESSION['branch_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $test_id = $_POST['test_id'] ?? '';
    $price = trim($_POST['price'] ?? '');
    $reporting_time = trim($_POST['reporting_time'] ?? '');
    // Status: comes as '1' if checked, doesn't exist if unchecked
    $status = isset($_POST['status']) ? 1 : 0; 

    // Validation
    if (empty($test_id)) {
        $response['message'] = "Test selection is required.";
    } elseif (empty($price)) {
        $response['message'] = "Branch Price is required.";
    } elseif (!is_numeric($price) || $price < 0) {
        $response['message'] = "Price must be a valid positive number.";
    } elseif (strlen($reporting_time) > 50) {
        $response['message'] = "Reporting time must be less than 50 characters.";
    } else {
        // Validation passed, proceed with database operation
        try {
            $conn->beginTransaction();

            // Check if master test exists and is active
            $master_test_check = $conn->prepare("SELECT id FROM tests WHERE id = ? AND status = '1'");
            $master_test_check->execute([$test_id]);
            if ($master_test_check->fetch() === false) {
                throw new Exception("Invalid or inactive master test selected.");
            }

            // Check if branch test entry already exists
            $stmt_check = $conn->prepare("SELECT id FROM branch_tests WHERE branch_id = ? AND test_id = ?");
            $stmt_check->execute([$branch_id, $test_id]);
            $existing = $stmt_check->fetch();

            if ($existing) {
                // Update existing branch test
                $stmt = $conn->prepare("
                    UPDATE branch_tests 
                    SET price = ?, reporting_time = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                    WHERE branch_id = ? AND test_id = ?
                ");
                $stmt->execute([$price, $reporting_time, $status, $branch_id, $test_id]);
                $response['message'] = "Test settings updated successfully for this branch.";
            } else {
                // Add new branch test entry
                $stmt = $conn->prepare("
                    INSERT INTO branch_tests (branch_id, test_id, price, reporting_time, status)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$branch_id, $test_id, $price, $reporting_time, $status]);
                 $response['message'] = "Test added successfully to this branch with specified settings.";
            }
            
            $conn->commit();
            $response['success'] = true;

        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Update Branch Test AJAX Error (PDO): " . $e->getMessage()); 
            $response['message'] = 'Database error occurred.';
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Update Branch Test AJAX Error: " . $e->getMessage()); 
            $response['message'] = $e->getMessage(); // Show specific validation errors
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?> 