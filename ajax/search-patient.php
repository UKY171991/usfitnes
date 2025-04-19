<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

// Check if user is logged in
checkUserAccess();

// Set content type to JSON
header('Content-Type: application/json');

// Get search term from request
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if(empty($search)) {
    echo json_encode(['error' => 'Search term is required']);
    exit();
}

try {
    // Search patients by name, phone, or email
    $search_term = "%{$search}%";
    $stmt = $conn->prepare("
        SELECT id, name, phone, email, gender, age, address 
        FROM patients 
        WHERE name LIKE ? 
        OR phone LIKE ? 
        OR email LIKE ? 
        ORDER BY name ASC 
        LIMIT 10
    ");
    $stmt->execute([$search_term, $search_term, $search_term]);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return patients as JSON
    echo json_encode([
        'success' => true,
        'patients' => $patients
    ]);

} catch(PDOException $e) {
    error_log("Error searching patients: " . $e->getMessage());
    echo json_encode([
        'error' => 'Failed to search patients'
    ]);
} 