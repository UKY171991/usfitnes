<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized access', null, ['code' => 401]);
}

try {
    // Get all tests with categories
    $stmt = $pdo->prepare("
        SELECT t.*, tc.category_name 
        FROM tests t 
        LEFT JOIN test_categories tc ON t.category_id = tc.id 
        ORDER BY tc.category_name, t.name
    ");
    $stmt->execute();
    $tests = $stmt->fetchAll();
    
    jsonResponse(true, 'Tests retrieved successfully', ['tests' => $tests]);
    
} catch (Exception $e) {
    error_log("Tests API Error: " . $e->getMessage());
    jsonResponse(false, 'Internal server error', null, ['code' => 500]);
}
?>