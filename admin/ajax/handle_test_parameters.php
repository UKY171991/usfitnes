<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

$action = $_POST['action'] ?? $_GET['action'] ?? null;
$response = ['success' => false, 'message' => 'Invalid action.', 'data' => []];

if (!$action) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    if ($action === 'load_parameters') {
        $test_id = $_GET['test_id'] ?? null;
        if ($test_id) {
            // Corrected SQL to fetch all necessary columns, including reference_range, unit, and description
            $params_stmt = $conn->prepare("SELECT id, parameter_name, reference_range, unit, price, description FROM test_parameters WHERE test_id = ? ORDER BY parameter_name ASC");
            $params_stmt->execute([$test_id]);
            $parameters = $params_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $test_name_stmt = $conn->prepare("SELECT test_name FROM tests WHERE id = ?");
            $test_name_stmt->execute([$test_id]);
            $test_name = $test_name_stmt->fetchColumn();

            $response['success'] = true;
            $response['message'] = 'Parameters loaded successfully.';
            $response['data'] = ['parameters' => $parameters, 'test_name' => $test_name];
        } else {
            $response['message'] = 'Test ID is required to load parameters.';
        }
    } elseif ($action === 'add_parameter') {
        $test_id = $_POST['test_id'] ?? null;
        $parameter_name = $_POST['parameter_name'] ?? null;
        $reference_range = $_POST['reference_range'] ?? null; // Assuming this column exists
        $unit = $_POST['unit'] ?? null; // Assuming this column is 'unit', not 'default_unit' for new entries
        $price = $_POST['price'] ?? null;
        $description = $_POST['description'] ?? null;

        if (!$test_id || !$parameter_name || $reference_range === null || $unit === null || $price === null) {
            $response['success'] = false;
            $response['message'] = 'Missing required fields (test_id, parameter_name, reference_range, unit, price).';
        } else {
            // The outer try-catch will handle PDOExceptions
            $stmt = $conn->prepare("INSERT INTO test_parameters (test_id, parameter_name, reference_range, unit, price, description) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$test_id, $parameter_name, $reference_range, $unit, $price, $description])) {
                $new_param_id = $conn->lastInsertId();
                
                // Fetch the newly added parameter to return it
                $fetch_stmt = $conn->prepare("SELECT id, parameter_name, reference_range, unit, price, description FROM test_parameters WHERE id = ?");
                $fetch_stmt->execute([$new_param_id]);
                $new_parameter = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

                $response['success'] = true;
                $response['message'] = 'Parameter added successfully.';
                $response['data'] = ['parameter' => $new_parameter];
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: Could not add parameter.';
                error_log("Error adding parameter: Statement execution failed.");
            }
        }
    } elseif ($action === 'update_parameter') {
        $parameter_id = $_POST['parameter_id'] ?? null;
        $parameter_name = $_POST['parameter_name'] ?? null;
        $reference_range = $_POST['reference_range'] ?? null;
        $unit = $_POST['unit'] ?? null;
        $price = $_POST['price'] ?? null;
        $description = $_POST['description'] ?? null;

        if (!$parameter_id || !$parameter_name || $reference_range === null || $unit === null || $price === null) {
            $response['success'] = false;
            $response['message'] = 'Missing required fields for update (parameter_id, parameter_name, reference_range, unit, price).';
        } else {
            $stmt = $conn->prepare("UPDATE test_parameters SET parameter_name = ?, reference_range = ?, unit = ?, price = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$parameter_name, $reference_range, $unit, $price, $description, $parameter_id])) {
                if ($stmt->rowCount() > 0) {
                    // Fetch the updated parameter to return it
                    $fetch_stmt = $conn->prepare("SELECT id, parameter_name, reference_range, unit, price, description FROM test_parameters WHERE id = ?");
                    $fetch_stmt->execute([$parameter_id]);
                    $updated_parameter = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
                    $response['success'] = true;
                    $response['message'] = 'Parameter updated successfully.';
                    $response['data'] = ['parameter' => $updated_parameter];
                } else {
                    $response['success'] = false;
                    $response['message'] = 'No changes made or parameter not found.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: Could not update parameter.';
                error_log("Error updating parameter: Statement execution failed.");
            }
        }
    } elseif ($action === 'delete_parameter') {
        $parameter_id = $_POST['parameter_id'] ?? null;

        if (!$parameter_id) {
            $response['success'] = false;
            $response['message'] = 'Parameter ID is required for deletion.';
        } else {
            $stmt = $conn->prepare("DELETE FROM test_parameters WHERE id = ?");
            if ($stmt->execute([$parameter_id])) {
                if ($stmt->rowCount() > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Parameter deleted successfully.';
                    $response['data'] = ['parameter_id' => $parameter_id]; // Return the ID of the deleted parameter
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Parameter not found or already deleted.';
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: Could not delete parameter.';
                error_log("Error deleting parameter: Statement execution failed.");
            }
        }
    } else { // Changed from default to else for clarity with if/elseif
        $response['message'] = 'Invalid action specified.';
        // No echo or break here, let the main script handle it
}

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("AJAX Test Parameters Error: " . $e->getMessage());
} catch (Exception $e) {
    $response['message'] = "General error: " . $e->getMessage();
    error_log("AJAX Test Parameters Error: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
