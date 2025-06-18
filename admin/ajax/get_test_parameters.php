<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

header('Content-Type: application/json');

if (!isset($_GET['test_id']) || empty($_GET['test_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Test ID is required']);
    exit();
}

$test_id = (int) $_GET['test_id'];

try {
    $stmt = $conn->prepare("
        SELECT id, parameter_name, specimen, default_result, unit, reference_range, 
               min_value, max_value, testcode
        FROM test_parameters 
        WHERE test_id = ? 
        ORDER BY id ASC
    ");
    $stmt->execute([$test_id]);
    $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'status' => 'success', 
        'parameters' => $parameters
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
