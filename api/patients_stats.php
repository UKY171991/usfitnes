<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $response = ['success' => true, 'data' => []];
    
    // Get total patients count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
    $response['data']['total'] = $stmt->fetch()['count'];
    
    // Get active patients count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients WHERE status = 'active'");
    $response['data']['active'] = $stmt->fetch()['count'];
    
    // Get today's visits (patients with orders today)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT p.id) as count 
        FROM patients p 
        INNER JOIN test_orders t ON p.id = t.patient_id 
        WHERE DATE(t.created_at) = CURDATE()
    ");
    $response['data']['today_visits'] = $stmt->fetch()['count'];
    
    // Get pending tests count for all patients
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM test_orders 
        WHERE status IN ('pending', 'in_progress')
    ");
    $response['data']['pending_tests'] = $stmt->fetch()['count'];
    
    // Get new patients this month
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM patients 
        WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
        AND YEAR(created_at) = YEAR(CURRENT_DATE())
    ");
    $response['data']['new_this_month'] = $stmt->fetch()['count'];
    
    // Get gender distribution
    $stmt = $pdo->query("
        SELECT gender, COUNT(*) as count 
        FROM patients 
        WHERE status = 'active' 
        GROUP BY gender
    ");
    $genderStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['data']['gender_distribution'] = $genderStats;
    
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Failed to load patient statistics'
    ];
    error_log("Patient stats error: " . $e->getMessage());
}

echo json_encode($response);
?>
