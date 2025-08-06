<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Try to use working config first, fallback to regular config
if (file_exists('../config_working.php')) {
    require_once '../config_working.php';
} else {
    require_once '../config.php';
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Function to send JSON response
function sendResponse($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

try {
    // Check database connection
    if (!isset($pdo) || !$pdo instanceof PDO) {
        sendResponse(false, 'Database connection failed');
    }
    
    switch ($method) {
        case 'GET':
            handleGet($action);
            break;
        case 'POST':
            handlePost($action);
            break;
        case 'PUT':
            handlePut($action);
            break;
        case 'DELETE':
            handleDelete($action);
            break;
        default:
            sendResponse(false, 'Method not allowed');
    }
} catch (Exception $e) {
    error_log('Equipment API Error: ' . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}

function handleGet($action) {
    global $pdo;
    
    switch ($action) {
        case 'list':
            try {
                $stmt = $pdo->prepare("SELECT * FROM equipment WHERE status != 'deleted' ORDER BY created_at DESC");
                $stmt->execute();
                $equipment = $stmt->fetchAll();
                sendResponse(true, 'Equipment retrieved successfully', $equipment);
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving equipment: ' . $e->getMessage());
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Equipment ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                $equipment = $stmt->fetch();
                
                if ($equipment) {
                    sendResponse(true, 'Equipment retrieved successfully', $equipment);
                } else {
                    sendResponse(false, 'Equipment not found');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving equipment: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePost($action) {
    global $pdo;
    
    switch ($action) {
        case 'create':
            $required_fields = ['name', 'category'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $name = sanitizeInput($_POST['name']);
            $model = !empty($_POST['model']) ? sanitizeInput($_POST['model']) : null;
            $brand = !empty($_POST['brand']) ? sanitizeInput($_POST['brand']) : null;
            $category = sanitizeInput($_POST['category']);
            $serial_number = !empty($_POST['serial_number']) ? sanitizeInput($_POST['serial_number']) : null;
            $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
            $warranty_expiry = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
            $location = !empty($_POST['location']) ? sanitizeInput($_POST['location']) : null;
            $notes = !empty($_POST['notes']) ? sanitizeInput($_POST['notes']) : null;
            
            try {
                // Check if serial number already exists (if provided)
                if ($serial_number) {
                    $stmt = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND status != 'deleted'");
                    $stmt->execute([$serial_number]);
                    if ($stmt->fetch()) {
                        sendResponse(false, 'Equipment with this serial number already exists');
                    }
                }
                
                // Insert new equipment
                $stmt = $pdo->prepare("INSERT INTO equipment (name, model, brand, category, serial_number, purchase_date, warranty_expiry, location, notes, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$name, $model, $brand, $category, $serial_number, $purchase_date, $warranty_expiry, $location, $notes]);
                
                $id = $pdo->lastInsertId();
                sendResponse(true, 'Equipment created successfully', ['id' => $id]);
                
            } catch (Exception $e) {
                sendResponse(false, 'Error creating equipment: ' . $e->getMessage());
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Equipment ID is required');
            }
            
            $required_fields = ['name', 'category'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $name = sanitizeInput($_POST['name']);
            $model = !empty($_POST['model']) ? sanitizeInput($_POST['model']) : null;
            $brand = !empty($_POST['brand']) ? sanitizeInput($_POST['brand']) : null;
            $category = sanitizeInput($_POST['category']);
            $serial_number = !empty($_POST['serial_number']) ? sanitizeInput($_POST['serial_number']) : null;
            $purchase_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
            $warranty_expiry = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
            $location = !empty($_POST['location']) ? sanitizeInput($_POST['location']) : null;
            $notes = !empty($_POST['notes']) ? sanitizeInput($_POST['notes']) : null;
            $status = !empty($_POST['status']) ? $_POST['status'] : 'active';
            
            try {
                // Check if equipment exists
                $stmt = $pdo->prepare("SELECT id FROM equipment WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Equipment not found');
                }
                
                // Check if serial number already exists for another equipment (if provided)
                if ($serial_number) {
                    $stmt = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND id != ? AND status != 'deleted'");
                    $stmt->execute([$serial_number, $id]);
                    if ($stmt->fetch()) {
                        sendResponse(false, 'Equipment with this serial number already exists');
                    }
                }
                
                // Update equipment
                $stmt = $pdo->prepare("UPDATE equipment SET name = ?, model = ?, brand = ?, category = ?, serial_number = ?, 
                                      purchase_date = ?, warranty_expiry = ?, location = ?, notes = ?, status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $model, $brand, $category, $serial_number, $purchase_date, $warranty_expiry, $location, $notes, $status, $id]);
                
                sendResponse(true, 'Equipment updated successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error updating equipment: ' . $e->getMessage());
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Equipment ID is required');
            }
            
            try {
                // Check if equipment exists
                $stmt = $pdo->prepare("SELECT id FROM equipment WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Equipment not found');
                }
                
                // Soft delete - just change status
                $stmt = $pdo->prepare("UPDATE equipment SET status = 'deleted', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                sendResponse(true, 'Equipment deleted successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error deleting equipment: ' . $e->getMessage());
            }
            break;
            
        case 'maintenance':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Equipment ID is required');
            }
            
            try {
                // Check if equipment exists
                $stmt = $pdo->prepare("SELECT id FROM equipment WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Equipment not found');
                }
                
                // Update maintenance dates
                $last_maintenance = date('Y-m-d');
                $next_maintenance = !empty($_POST['next_maintenance']) ? $_POST['next_maintenance'] : date('Y-m-d', strtotime('+6 months'));
                
                $stmt = $pdo->prepare("UPDATE equipment SET last_maintenance = ?, next_maintenance = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$last_maintenance, $next_maintenance, $id]);
                
                sendResponse(true, 'Equipment maintenance updated successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error updating maintenance: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePut($action) {
    parse_str(file_get_contents("php://input"), $_POST);
    handlePost($action);
}

function handleDelete($action) {
    parse_str(file_get_contents("php://input"), $_POST);
    handlePost('delete');
}
?>
