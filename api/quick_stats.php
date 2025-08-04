<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    $stats = [];
    
    // Get patient count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
    $stats['patients'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get test count (today)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE DATE(created_at) = CURDATE()");
    $stats['tests'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get doctors count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors WHERE status = 'active'");
    $stats['doctors'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get pending orders count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE status = 'pending'");
    $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get completed orders count (today)
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM test_orders WHERE status = 'completed' AND DATE(completed_at) = CURDATE()");
    $stats['completed_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get equipment count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM equipment WHERE status = 'active'");
    $stats['equipment'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
