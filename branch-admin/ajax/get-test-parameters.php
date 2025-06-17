<?php
// Suppress output of errors and log them instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt'); // Ensure this directory is writable

require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Test ID not provided.'];

if (isset($_GET['test_id']) && !empty($_GET['test_id'])) {
    $test_id = filter_var($_GET['test_id'], FILTER_VALIDATE_INT);

    if ($test_id === false) {
        $response['message'] = 'Invalid Test ID format.';
        echo json_encode($response);
        exit;
    }

    try {
        // Fetch test-specific details (method, default_report_heading)
        // Assuming 'method' and 'default_report_heading' columns might exist in the 'tests' table
        $stmt_test_details = $conn->prepare("
            SELECT 
                t.test_name, 
                t.price, 
                COALESCE(t.method, NULL) as method, 
                COALESCE(t.default_report_heading, t.test_name) as default_report_heading 
            FROM tests t 
            WHERE t.id = :test_id AND t.status = 1
        ");
        $stmt_test_details->bindParam(':test_id', $test_id, PDO::PARAM_INT);
        $stmt_test_details->execute();
        $test_details = $stmt_test_details->fetch(PDO::FETCH_ASSOC);

        if (!$test_details) {
            $response['message'] = 'Test not found or is inactive.';
            echo json_encode($response);
            exit;
        }

        // Fetch parameters for the given test_id
        // Assuming 'specimen', 'min_value', 'max_value', 'is_bold_default', 'id' as 'test_parameter_id' might exist
        $stmt_params = $conn->prepare("
            SELECT 
                tp.id as test_parameter_id, 
                tp.parameter_name, 
                COALESCE(tp.specimen, '') as specimen,
                tp.default_unit, 
                tp.normal_value as ref_range,
                COALESCE(tp.min_value, '') as min_value,
                COALESCE(tp.max_value, '') as max_value,
                COALESCE(tp.is_bold_default, 0) as is_bold_default,
                COALESCE(tp.sort_order, 0) as sort_order
            FROM test_parameters tp
            WHERE tp.test_id = :test_id 
            ORDER BY COALESCE(tp.sort_order, 0) ASC, tp.parameter_name ASC
        ");
        $stmt_params->bindParam(':test_id', $test_id, PDO::PARAM_INT);
        $stmt_params->execute();
        $parameters = $stmt_params->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['message'] = 'Parameters fetched successfully.';
        $response['test_details'] = $test_details;
        $response['parameters'] = $parameters;

    } catch (PDOException $e) {
        error_log('Error fetching test parameters (PDO): ' . $e->getMessage() . " for test_id: " . $test_id);
        $response['message'] = 'Database error. Please check logs.';
    } catch (Exception $e) {
        error_log('Error fetching test parameters: ' . $e->getMessage() . " for test_id: " . $test_id);
        $response['message'] = 'An unexpected error occurred. Please check logs.';
    }
}

echo json_encode($response);
?>