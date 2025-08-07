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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid patient ID']);
    exit;
}

$patient_id = (int)$_GET['id'];

try {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("
        SELECT 
            id, first_name, last_name, phone, email, 
            date_of_birth, gender, status 
        FROM patients 
        WHERE id = ?
    ");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $patient
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
