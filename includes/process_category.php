<?php
require_once '../config.php';
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in and has Admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    
    // Adding new category
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['category_id'])) {
        $categoryName = trim(filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING));
        
        if (empty($categoryName)) {
            throw new Exception('Category name is required');
        }
        
        // Check if category name already exists
        $stmt = $db->query(
            "SELECT COUNT(*) as count FROM Test_Categories WHERE category_name = :name",
            ['name' => $categoryName]
        );
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            throw new Exception('Category name already exists');
        }
        
        // Insert new category
        $db->query(
            "INSERT INTO Test_Categories (category_name) VALUES (:name)",
            ['name' => $categoryName]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Category added successfully'
        ]);
    }
    // Updating existing category
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        $categoryName = trim(filter_input(INPUT_POST, 'category_name', FILTER_SANITIZE_STRING));
        
        if ($categoryId === false || $categoryId === null) {
            throw new Exception('Invalid category ID');
        }
        
        if (empty($categoryName)) {
            throw new Exception('Category name is required');
        }
        
        // Check if new name already exists for different category
        $stmt = $db->query(
            "SELECT COUNT(*) as count FROM Test_Categories 
             WHERE category_name = :name AND category_id != :id",
            ['name' => $categoryName, 'id' => $categoryId]
        );
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
            throw new Exception('Category name already exists');
        }
        
        // Update category
        $db->query(
            "UPDATE Test_Categories SET category_name = :name WHERE category_id = :id",
            ['name' => $categoryName, 'id' => $categoryId]
        );
        
        echo json_encode([
            'success' => true,
            'message' => 'Category updated successfully'
        ]);
    }
    else {
        throw new Exception('Invalid request method');
    }
    
} catch (Exception $e) {
    error_log("Category processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 