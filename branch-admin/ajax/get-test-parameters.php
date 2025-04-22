<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['test_id'])) {
        throw new Exception('Test ID is required');
    }

    $test_id = intval($_GET['test_id']);
    $branch_id = $_SESSION['branch_id']; // Ensure branch context if needed, though parameters are usually test-specific

    // Check if the test is actually available to this branch first (optional security check)
    $check_test_stmt = $conn->prepare("SELECT 1 FROM branch_tests WHERE test_id = ? AND branch_id = ?");
    $check_test_stmt->execute([$test_id, $branch_id]);
    if (!$check_test_stmt->fetchColumn()) {
        // Silently return empty or throw error, depending on desired behaviour
         echo json_encode(['success' => true, 'parameters' => []]);
         exit;
        // throw new Exception('Test not associated with this branch'); 
    }

    // Fetch parameters for the given test_id
    $stmt = $conn->prepare("
        SELECT parameter_name, default_unit 
        FROM test_parameters 
        WHERE test_id = ? 
        ORDER BY parameter_name ASC
    ");
    $stmt->execute([$test_id]);
    $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'parameters' => $parameters
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 