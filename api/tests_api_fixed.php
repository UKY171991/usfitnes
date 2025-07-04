<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'list':
                    getTests($pdo);
                    break;
                case 'categories':
                    getTestCategories($pdo);
                    break;
                default:
                    getTests($pdo); // Default to list
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'create':
                    createTest($pdo, $_POST);
                    break;
                case 'update':
                    updateTest($pdo, $_POST);
                    break;
                case 'delete':
                    deleteTest($pdo, $_POST);
                    break;
                default:
                    createTest($pdo, $_POST); // Default to create
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getTests($pdo) {
    try {
        // Get tests with category information
        $stmt = $pdo->query("
            SELECT t.*, tc.category_name 
            FROM tests t 
            LEFT JOIN test_categories tc ON t.category_id = tc.id 
            ORDER BY t.test_name
        ");
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'data' => $tests
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getTestCategories($pdo) {
    try {
        // Get test categories
        $stmt = $pdo->query("SELECT * FROM test_categories ORDER BY category_name");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $categories]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createTest($pdo, $data) {
    // Check required fields (simplified)
    $requiredFields = ['test_name', 'test_code', 'category_id', 'price'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        // Insert new test (simplified fields)
        $stmt = $pdo->prepare("
            INSERT INTO tests (test_code, test_name, category_id, price, description, sample_type, normal_range)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['test_code'],
            $data['test_name'],
            $data['category_id'],
            $data['price'],
            $data['description'] ?? '',
            $data['sample_type'] ?? 'Blood',
            $data['normal_range'] ?? ''
        ]);
        
        $testId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Test created successfully',
            'data' => ['id' => $testId]
        ]);
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Test code already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}

function updateTest($pdo, $data) {
    // Check required fields
    if (!isset($data['id']) || empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Test ID is required"]);
        return;
    }
    
    try {
        // Update test (simplified fields)
        $stmt = $pdo->prepare("
            UPDATE tests SET 
                test_code = ?, test_name = ?, category_id = ?, price = ?, 
                description = ?, sample_type = ?, normal_range = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $data['test_code'],
            $data['test_name'],
            $data['category_id'],
            $data['price'],
            $data['description'] ?? '',
            $data['sample_type'] ?? 'Blood',
            $data['normal_range'] ?? '',
            $data['id']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Test updated successfully'
        ]);
        
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Test code already exists']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}

function deleteTest($pdo, $data) {
    // Check test ID
    $testId = $data['id'] ?? null;
    if (!$testId || empty($testId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Test ID is required"]);
        return;
    }
    
    try {
        // Check if test is used in orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_order_items WHERE test_id = ?");
        $stmt->execute([$testId]);
        $usage = $stmt->fetchColumn();
        
        if ($usage > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete test that is used in orders']);
            return;
        }
        
        // Delete test
        $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
        $stmt->execute([$testId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Test deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Test not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
