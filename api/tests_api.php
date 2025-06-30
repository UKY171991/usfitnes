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
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
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
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
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
        // Support pagination and search
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        
        $params = [];
        $whereClause = '';
        
        // Add search query
        if (!empty($search)) {
            $whereClause .= " WHERE (test_name LIKE ? OR test_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        // Add category filter if provided
        if (!empty($category)) {
            $whereClause = empty($whereClause) ? " WHERE category = ?" : $whereClause . " AND category = ?";
            $params[] = $category;
        }
        
        // Count total tests
        $countQuery = "SELECT COUNT(*) FROM lab_tests" . $whereClause;
        $stmt = $pdo->prepare($countQuery);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get tests
        $query = "SELECT * FROM lab_tests" . $whereClause . " ORDER BY test_name LIMIT ?, ?";
        $stmt = $pdo->prepare($query);
        
        // Add pagination params
        $params[] = $offset;
        $params[] = $limit;
        
        $stmt->execute($params);
        $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'data' => $tests,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit)
            ]
        ]);
        
    } catch (PDOException $e) {
        // Check if table doesn't exist
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            // Create table and insert sample data
            createLabTestsTable($pdo);
            // Try fetching again
            getTests($pdo);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
}

function getTestCategories($pdo) {
    try {
        // Check if the table exists and create if needed
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'lab_tests'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            createLabTestsTable($pdo);
        }
        
        // Get distinct categories
        $stmt = $pdo->query("SELECT DISTINCT category FROM lab_tests ORDER BY category");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['success' => true, 'data' => $categories]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createTest($pdo, $data) {
    // Check required fields
    $requiredFields = ['test_name', 'test_code', 'category', 'price'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        // Create table if not exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'lab_tests'");
        $stmt->execute();
        if ($stmt->rowCount() === 0) {
            createLabTestsTable($pdo);
        }
        
        // Insert new test
        $stmt = $pdo->prepare("
            INSERT INTO lab_tests (test_name, test_code, category, description, price, turn_around_time, sample_type, normal_range)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $data['test_name'],
            $data['test_code'],
            $data['category'],
            $data['description'] ?? '',
            $data['price'],
            $data['turn_around_time'] ?? '',
            $data['sample_type'] ?? '',
            $data['normal_range'] ?? ''
        ]);
        
        $testId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Test created successfully',
            'data' => ['id' => $testId]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateTest($pdo, $data) {
    // Check required fields
    if (!isset($data['test_id']) || empty($data['test_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Test ID is required"]);
        return;
    }
    
    try {
        // Update test
        $query = "UPDATE lab_tests SET ";
        $params = [];
        $fields = ['test_name', 'test_code', 'category', 'description', 'price', 'turn_around_time', 'sample_type', 'normal_range'];
        
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $query .= "$field = ?, ";
                $params[] = $data[$field];
            }
        }
        
        // Remove trailing comma and space
        $query = rtrim($query, ', ');
        $query .= " WHERE id = ?";
        $params[] = $data['test_id'];
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'Test updated successfully'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteTest($pdo, $data) {
    // Check test ID (support both 'id' and 'test_id' parameters)
    $testId = $data['id'] ?? $data['test_id'] ?? null;
    if (!$testId || empty($testId)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Test ID is required"]);
        return;
    }
    
    try {
        // Check if test is referenced in test_orders (skip if table doesn't exist)
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE test_id = ?");
            $stmt->execute([$testId]);
            if ($stmt->fetchColumn() > 0) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Cannot delete: Test is linked to one or more orders']);
                return;
            }
        } catch (PDOException $e) {
            // Table might not exist, continue with deletion
        }
        
        // Delete test
        $stmt = $pdo->prepare("DELETE FROM lab_tests WHERE id = ?");
        $stmt->execute([$testId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Test deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Test not found'
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createLabTestsTable($pdo) {
    // Create table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lab_tests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            test_name VARCHAR(100) NOT NULL,
            test_code VARCHAR(20) NOT NULL UNIQUE,
            category VARCHAR(50) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            turn_around_time VARCHAR(50),
            sample_type VARCHAR(50),
            normal_range TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insert sample data
    $sampleTests = [
        ['Complete Blood Count (CBC)', 'CBC001', 'Hematology', 'Complete blood count with differential', 45.00, '1 day', 'Blood', 'WBC: 4.5-11.0 x10^9/L, RBC: 4.5-6.5 x10^12/L, Hgb: 14.0-18.0 g/dL'],
        ['Basic Metabolic Panel', 'BMP002', 'Chemistry', 'Basic metabolic panel for kidney function', 35.00, '1 day', 'Serum', 'Sodium: 135-145 mmol/L, Potassium: 3.5-5.0 mmol/L'],
        ['Lipid Profile', 'LIP003', 'Chemistry', 'Cholesterol and lipids panel', 55.00, '1 day', 'Serum', 'Total Cholesterol: <200 mg/dL, HDL: >60 mg/dL, LDL: <100 mg/dL'],
        ['Thyroid Function Tests', 'TFT004', 'Endocrinology', 'Thyroid panel including TSH, T3, T4', 75.00, '2 days', 'Serum', 'TSH: 0.4-4.0 mIU/L, T4: 5.0-12.0 μg/dL'],
        ['HbA1c', 'HBA005', 'Endocrinology', 'Glycated hemoglobin for diabetes monitoring', 40.00, '2 days', 'Blood', '4.0-5.6% (normal), 5.7-6.4% (prediabetic), >6.5% (diabetic)'],
        ['Urinalysis', 'URN006', 'Urinalysis', 'Complete urine examination', 25.00, '1 day', 'Urine', 'pH: 4.5-8.0, Specific gravity: 1.005-1.030'],
        ['Liver Function Tests', 'LFT007', 'Chemistry', 'Liver enzyme panel', 60.00, '1 day', 'Serum', 'ALT: 7-55 U/L, AST: 8-48 U/L, ALP: 45-115 U/L'],
        ['Culture & Sensitivity', 'CNS008', 'Microbiology', 'Bacterial culture with antibiotic sensitivity', 90.00, '3 days', 'Various', 'No growth or specific pathogen identification'],
        ['COVID-19 PCR Test', 'COV009', 'Molecular', 'RT-PCR for SARS-CoV-2 detection', 120.00, '1 day', 'Nasopharyngeal swab', 'Detected/Not Detected'],
        ['Vitamin D', 'VTD010', 'Chemistry', '25-hydroxyvitamin D measurement', 65.00, '2 days', 'Serum', '30-100 ng/mL']
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO lab_tests (test_name, test_code, category, description, price, turn_around_time, sample_type, normal_range) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($sampleTests as $test) {
        $stmt->execute($test);
    }
}
?>
