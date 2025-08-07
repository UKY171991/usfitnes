<?php
require_once '../config.php';
require_once '../includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid patient ID']);
    exit;
}

$patient_id = (int)$_POST['id'];

try {
    $conn = getDbConnection();
    
    // Check if patient has any test orders
    $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $orderCount = $stmt->fetchColumn();
    
    if ($orderCount > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete patient with existing test orders. Please archive instead.'
        ]);
        exit;
    }
    
    // Soft delete - update status instead of actual delete
    $stmt = $conn->prepare("
        UPDATE patients 
        SET status = 'deleted', 
            updated_at = NOW(), 
            updated_by = ? 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $patient_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Patient deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Patient not found or already deleted'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
