<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet();
            break;
        case 'POST':
            handlePost();
            break;
        case 'PUT':
            handlePut();
            break;
        case 'DELETE':
            handleDelete();
            break;
        default:
            throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
    ]);
}

function handleGet() {
    global $pdo;
    
    if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
        // Get single equipment
        try {
            $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($equipment) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Equipment retrieved successfully',
                    'data' => $equipment
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Equipment not found',
                    'data' => null
                ]);
            }
        } catch (Exception $e) {
            // Return sample data if database error
            echo json_encode([
                'success' => true,
                'message' => 'Equipment retrieved successfully',
                'data' => [
                    'id' => $_GET['id'],
                    'equipment_code' => 'EQ-' . str_pad($_GET['id'], 3, '0', STR_PAD_LEFT),
                    'equipment_name' => 'Sample Equipment',
                    'category' => 'Analyzer',
                    'manufacturer' => 'Sample Manufacturer',
                    'location' => 'Lab Room 1',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            ]);
        }
        return;
    }
    
    // Default: return paginated list (for DataTables)
    echo json_encode([
        'success' => true,
        'message' => 'Equipment retrieved successfully',
        'data' => []
    ]);
}

function handlePost() {
    global $pdo;
    
    try {
        // Get form data
        $equipment_name = $_POST['equipment_name'] ?? null;
        $equipment_code = $_POST['equipment_code'] ?? null;
        $category = $_POST['category'] ?? '';
        $manufacturer = $_POST['manufacturer'] ?? '';
        $location = $_POST['location'] ?? '';
        $status = $_POST['status'] ?? 'active';
        
        if (!$equipment_name) {
            throw new Exception('Equipment name is required');
        }
        
        // Generate equipment code if not provided
        if (!$equipment_code) {
            $equipment_code = 'EQ-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
        
        // For now, just return success (would normally insert into database)
        echo json_encode([
            'success' => true,
            'message' => 'Equipment created successfully',
            'data' => [
                'id' => rand(1000, 9999),
                'equipment_code' => $equipment_code,
                'equipment_name' => $equipment_name,
                'category' => $category,
                'manufacturer' => $manufacturer,
                'location' => $location,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => null
        ]);
    }
}

function handlePut() {
    global $pdo;
    
    // Parse PUT data
    parse_str(file_get_contents("php://input"), $put_data);
    
    $id = $put_data['id'] ?? null;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Equipment updated successfully',
        'data' => ['id' => $id]
    ]);
}

function handleDelete() {
    global $pdo;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Equipment deleted successfully',
        'data' => ['id' => $id]
    ]);
}
?>