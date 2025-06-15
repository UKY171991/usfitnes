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
            $params_stmt = $conn->prepare("SELECT id, parameter_name, default_unit, price FROM test_parameters WHERE test_id = ? ORDER BY parameter_name ASC");
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
        $reference_range = $_POST['reference_range'] ?? null;
        $unit = $_POST['unit'] ?? null;
        $price = $_POST['price'] ?? null;
        $description = $_POST['description'] ?? null; // Added description

        if (!$test_id || !$parameter_name || $reference_range === null || $unit === null || $price === null) {
            $response = ['status' => 'error', 'message' => 'Missing required fields.'];
            echo json_encode($response);
            exit;
        }

        try {
            $stmt = $conn->prepare("INSERT INTO test_parameters (test_id, parameter_name, reference_range, unit, price, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$test_id, $parameter_name, $reference_range, $unit, $price, $description]);
            $new_param_id = $conn->lastInsertId();
            
            // Fetch the newly added parameter to return it (optional, but good for UI updates)
            $fetch_stmt = $conn->prepare("SELECT * FROM test_parameters WHERE id = ?");
            $fetch_stmt->execute([$new_param_id]);
            $new_parameter = $fetch_stmt->fetch(PDO::FETCH_ASSOC);

            $response = ['status' => 'success', 'message' => 'Parameter added successfully.', 'parameter' => $new_parameter];
        } catch (PDOException $e) {
            error_log("Error adding parameter: " . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'Database error: Could not add parameter. ' . $e->getMessage()];
        }
        echo json_encode($response);
        break;

    // Add cases for update_parameter and delete_parameter here later

    default:
        $response['message'] = 'Invalid action specified.';
        break;
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
