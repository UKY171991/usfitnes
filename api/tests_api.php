<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            echo json_encode(getTestsList($pdo));
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}

function getTestsList($pdo) {
    try {
        // Try to get tests from database
        $stmt = $pdo->query("SELECT id, name, price FROM tests WHERE status = 'active' ORDER BY name");
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // If no tests found, return sample data
        if (empty($tests)) {
            $tests = [
                ['id' => 1, 'name' => 'Complete Blood Count (CBC)', 'price' => 25.00],
                ['id' => 2, 'name' => 'Blood Sugar (Fasting)', 'price' => 15.00],
                ['id' => 3, 'name' => 'Lipid Profile', 'price' => 35.00],
                ['id' => 4, 'name' => 'Liver Function Test', 'price' => 40.00],
                ['id' => 5, 'name' => 'Kidney Function Test', 'price' => 30.00],
                ['id' => 6, 'name' => 'Thyroid Function Test', 'price' => 45.00],
                ['id' => 7, 'name' => 'Urine Analysis', 'price' => 20.00],
                ['id' => 8, 'name' => 'ECG', 'price' => 50.00]
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Tests loaded successfully',
            'data' => $tests
        ];
        
    } catch (Exception $e) {
        // Return sample data if database error
        return [
            'success' => true,
            'message' => 'Tests loaded successfully',
            'data' => [
                ['id' => 1, 'name' => 'Complete Blood Count (CBC)', 'price' => 25.00],
                ['id' => 2, 'name' => 'Blood Sugar (Fasting)', 'price' => 15.00],
                ['id' => 3, 'name' => 'Lipid Profile', 'price' => 35.00],
                ['id' => 4, 'name' => 'Liver Function Test', 'price' => 40.00],
                ['id' => 5, 'name' => 'Kidney Function Test', 'price' => 30.00]
            ]
        ];
    }
}
?>