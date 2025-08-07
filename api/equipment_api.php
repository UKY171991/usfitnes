<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized access', null, ['code' => 401]);
}

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
            jsonResponse(false, 'Method not allowed', null, ['code' => 405]);
    }
} catch (Exception $e) {
    error_log("Equipment API Error: " . $e->getMessage());
    jsonResponse(false, 'Internal server error', null, ['code' => 500]);
}

function handleGet() {
    global $pdo;
    
    if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
        // Get single equipment
        $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $equipment = $stmt->fetch();
        
        if ($equipment) {
            jsonResponse(true, 'Equipment retrieved successfully', $equipment);
        } else {
            jsonResponse(false, 'Equipment not found', null, ['code' => 404]);
        }
    } else {
        // Get all equipment with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (equipment_name LIKE ? OR equipment_code LIKE ? OR equipment_type LIKE ? OR manufacturer LIKE ? OR location LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_fill(0, 5, $searchTerm);
        }
        
        if (!empty($status)) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        if (!empty($category)) {
            $whereClause .= " AND category = ?";
            $params[] = $category;
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM equipment $whereClause");
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Get equipment
        $stmt = $pdo->prepare("
            SELECT id, equipment_code, equipment_name, equipment_type, model, 
                   serial_number, manufacturer, location, status, cost,
                   purchase_date, warranty_expiry, last_maintenance, next_maintenance,
                   maintenance_schedule, created_at
            FROM equipment 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $equipment = $stmt->fetchAll();
        
        $response = [
            'equipment' => $equipment,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
        jsonResponse(true, 'Equipment retrieved successfully', $response);
    }
}

function handlePost() {
    global $pdo;
    
    // Validate required fields
    $required = ['equipment_name'];
    $errors = validateInput($_POST, $required);
    
    if (!empty($errors)) {
        jsonResponse(false, implode(', ', $errors), null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($_POST);
    
    // Generate equipment code
    $equipmentCode = generateUniqueId('EQP');
    
    // Check if equipment code already exists
    while (true) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE equipment_code = ?");
        $checkStmt->execute([$equipmentCode]);
        if ($checkStmt->fetchColumn() == 0) break;
        $equipmentCode = generateUniqueId('EQP');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO equipment (
                equipment_code, equipment_name, equipment_type, model, 
                serial_number, manufacturer, category, location, 
                purchase_date, warranty_expiry, status, cost, 
                maintenance_schedule, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)
        ");
        
        $stmt->execute([
            $equipmentCode,
            $data['equipment_name'],
            $data['equipment_type'] ?? null,
            $data['model'] ?? null,
            $data['serial_number'] ?? null,
            $data['manufacturer'] ?? null,
            $data['category'] ?? null,
            $data['location'] ?? null,
            $data['purchase_date'] ?? null,
            $data['warranty_expiry'] ?? null,
            $data['cost'] ?? null,
            $data['maintenance_schedule'] ?? 'monthly',
            $data['description'] ?? null
        ]);
        
        $newEquipmentId = $pdo->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Equipment Created', "Created equipment: $equipmentCode");
        
        jsonResponse(true, 'Equipment created successfully', ['id' => $newEquipmentId, 'equipment_code' => $equipmentCode]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(false, 'Equipment with this code already exists', null, ['code' => 422]);
        }
        throw $e;
    }
}

function handlePut() {
    global $pdo;
    
    // Get PUT data
    parse_str(file_get_contents("php://input"), $putData);
    
    if (empty($putData['id'])) {
        jsonResponse(false, 'Equipment ID is required', null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($putData);
    $id = $data['id'];
    
    // Check if equipment exists
    $checkStmt = $pdo->prepare("SELECT equipment_code FROM equipment WHERE id = ?");
    $checkStmt->execute([$id]);
    $existingEquipment = $checkStmt->fetch();
    
    if (!$existingEquipment) {
        jsonResponse(false, 'Equipment not found', null, ['code' => 404]);
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = [
        'equipment_name', 'equipment_type', 'model', 'serial_number',
        'manufacturer', 'category', 'location', 'purchase_date',
        'warranty_expiry', 'status', 'cost', 'maintenance_schedule',
        'last_maintenance', 'next_maintenance', 'description'
    ];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    
    if (empty($updateFields)) {
        jsonResponse(false, 'No fields to update', null, ['code' => 422]);
    }
    
    $params[] = $id;
    
    try {
        $stmt = $pdo->prepare("
            UPDATE equipment 
            SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute($params);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Equipment Updated', "Updated equipment: {$existingEquipment['equipment_code']}");
        
        jsonResponse(true, 'Equipment updated successfully');
        
    } catch (PDOException $e) {
        throw $e;
    }
}

function handleDelete() {
    global $pdo;
    
    // Get DELETE data
    parse_str(file_get_contents("php://input"), $deleteData);
    
    if (empty($deleteData['id'])) {
        jsonResponse(false, 'Equipment ID is required', null, ['code' => 422]);
    }
    
    $id = (int)$deleteData['id'];
    
    // Check if equipment exists
    $checkStmt = $pdo->prepare("SELECT equipment_code FROM equipment WHERE id = ?");
    $checkStmt->execute([$id]);
    $equipment = $checkStmt->fetch();
    
    if (!$equipment) {
        jsonResponse(false, 'Equipment not found', null, ['code' => 404]);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Equipment Deleted', "Deleted equipment: {$equipment['equipment_code']}");
        
        jsonResponse(true, 'Equipment deleted successfully');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Error deleting equipment', null, ['code' => 500]);
    }
}
?>