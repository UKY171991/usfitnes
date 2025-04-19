<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

// Check if user is logged in
checkUserAccess();

// Set content type to JSON
header('Content-Type: application/json');

// Get category ID from request
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if($category_id <= 0) {
    echo json_encode(['error' => 'Invalid category ID']);
    exit();
}

try {
    // Get tests for the category
    $stmt = $conn->prepare("
        SELECT id, name, price, sample_type, reporting_time 
        FROM tests 
        WHERE category_id = ? 
        AND status = 'active'
        ORDER BY name ASC
    ");
    $stmt->execute([$category_id]);
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return tests as JSON
    echo json_encode([
        'success' => true,
        'tests' => $tests
    ]);

} catch(PDOException $e) {
    error_log("Error fetching tests: " . $e->getMessage());
    echo json_encode([
        'error' => 'Failed to fetch tests'
    ]);
} 