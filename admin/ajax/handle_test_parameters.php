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
        $parameter_name = trim($_POST['parameter_name'] ?? '');
        $reference_range = trim($_POST['reference_range'] ?? '');
        $unit = trim($_POST['unit'] ?? '');
        $price = $_POST['price'] ?? null; // Keep as null if empty, or handle conversion
        $description = trim($_POST['description'] ?? '');

        if (empty($test_id) || empty($parameter_name)) { // Price can be 0 or empty, other optionals too
            $response['success'] = false;
            $response['message'] = 'Test ID and Parameter Name are required.';
        } else {
            // Ensure price is a valid number or null
            $price_to_insert = !empty($price) ? filter_var($price, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) : null;
            if ($price !== null && !empty($price) && $price_to_insert === null) {
                 $response['success'] = false;
                 $response['message'] = 'Invalid price format.';
            } else {
                $stmt = $conn->prepare("INSERT INTO test_parameters (test_id, parameter_name, reference_range, unit, price, description) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$test_id, $parameter_name, $reference_range, $unit, $price_to_insert, $description])) {
                    $new_param_id = $conn->lastInsertId();
                    $fetch_stmt = $conn->prepare("SELECT id, parameter_name, reference_range, unit, price, description FROM test_parameters WHERE id = ?");
                    $fetch_stmt->execute([$new_param_id]);
                    $new_parameter = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

                    $response['success'] = true;
                    $response['message'] = 'Parameter added successfully.';
                    $response['data'] = ['parameter' => $new_parameter];
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Database error: Could not add parameter.';
                    error_log("Error adding parameter: Statement execution failed. Test ID: {$test_id}, Param Name: {$parameter_name}");
                }
            }
        }
    } elseif ($action === 'update_parameter') {
        $parameter_id = $_POST['parameter_id'] ?? null;
        // Use edit_parameter_name etc. to match the form fields
        $parameter_name = $_POST['edit_parameter_name'] ?? null;
        $reference_range = $_POST['edit_reference_range'] ?? null;
        $unit = $_POST['edit_unit'] ?? null;
        $price = $_POST['edit_price'] ?? null;
        $description = $_POST['edit_description'] ?? null;

        // Validate that parameter_id is provided
        if (!$parameter_id) {
            $response['success'] = false;
            $response['message'] = 'Parameter ID is required for update.';
        } elseif ($parameter_name === null || $reference_range === null || $unit === null || $price === null || $description === null) {
            // Check if any of the editable fields are missing, which might indicate an issue if they are expected
            // However, an empty string is a valid value for some fields.
            // For simplicity, we'll assume that if parameter_id is present, the user intends to update.
            // More robust validation could be added here if certain fields become mandatory for update.
            $response['success'] = false;
            $response['message'] = 'Missing some parameter fields for update. Ensure all fields are submitted.';
        } else {
            $stmt = $conn->prepare("UPDATE test_parameters SET parameter_name = ?, reference_range = ?, unit = ?, price = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$parameter_name, $reference_range, $unit, $price, $description, $parameter_id])) {
                $response['success'] = true; // Operation was successful
                if ($stmt->rowCount() > 0) {
                    // Fetch the updated parameter to return it
                    $fetch_stmt = $conn->prepare("SELECT id, parameter_name, reference_range, unit, price, description FROM test_parameters WHERE id = ?");
                    $fetch_stmt->execute([$parameter_id]);
                    $updated_parameter = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
                    $response['message'] = 'Parameter updated successfully.';
                    $response['data'] = ['parameter' => $updated_parameter];
                } else {
                    // No rows affected, but the query itself was successful
                    $response['message'] = 'No changes were made to the parameter.'; 
                    // Optionally, fetch and return the current state of the parameter
                    $fetch_stmt = $conn->prepare("SELECT id, parameter_name, reference_range, unit, price, description FROM test_parameters WHERE id = ?");
                    $fetch_stmt->execute([$parameter_id]);
                    $current_parameter = $fetch_stmt->fetch(PDO::FETCH_ASSOC);
                    $response['data'] = ['parameter' => $current_parameter, 'no_changes' => true];
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: Could not update parameter.';
                error_log("Error updating parameter: Statement execution failed. ID: {$parameter_id}");
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
                    $response['data'] = ['parameter_id' => $parameter_id]; 
                } else {
                    // Technically a success if the query ran, but no row was found to delete
                    $response['success'] = true; // Or false, depending on desired strictness
                    $response['message'] = 'Parameter not found or already deleted.';
                     $response['data'] = ['parameter_id' => $parameter_id, 'not_found' => true];
                }
            } else {
                $response['success'] = false;
                $response['message'] = 'Database error: Could not delete parameter.';
                error_log("Error deleting parameter: Statement execution failed. ID: {$parameter_id}");
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
