<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/branch-admin-check.php';

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['report_id']) || !isset($_POST['status'])) {
        throw new Exception('Missing required fields');
    }

    $report_id = intval($_POST['report_id']);
    $result = trim($_POST['result'] ?? '');
    $status = trim($_POST['status']);
    $notes = trim($_POST['notes'] ?? '');
    $branch_id = $_SESSION['branch_id'];
    $test_results = isset($_POST['test_results']) ? $_POST['test_results'] : '[]';

    // Validate status
    if (!in_array($status, ['pending', 'completed'])) {
        throw new Exception('Invalid status');
    }

    // If status is completed, ensure there are test results
    if ($status === 'completed') {
        $hasResults = false;
        
        // Check if there are test results in JSON format
        if (!empty($test_results)) {
            $parsedResults = json_decode($test_results, true);
            if (is_array($parsedResults) && count($parsedResults) > 0) {
                $hasResults = true;
            }
        }
        
        // Also check if there's a traditional result
        if (!empty($result)) {
            $hasResults = true;
        }
        
        if (!$hasResults) {
            throw new Exception('Test result is required for completed reports');
        }
    }

    // Check if report exists and belongs to this branch
    $check_stmt = $conn->prepare("
        SELECT r.* 
        FROM reports r
        JOIN tests t ON r.test_id = t.id
        JOIN branch_tests bt ON t.id = bt.test_id
        WHERE r.id = ? AND bt.branch_id = ?
    ");
    $check_stmt->execute([$report_id, $branch_id]);
    
    if (!$check_stmt->fetch()) {
        throw new Exception('Report not found or access denied');
    }

    // Update the report
    $update_stmt = $conn->prepare("
        UPDATE reports 
        SET result = ?,
            test_results = ?,
            status = ?,
            notes = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $update_stmt->execute([$result, $test_results, $status, $notes, $report_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Report updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 