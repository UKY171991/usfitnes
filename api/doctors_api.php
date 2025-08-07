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
    error_log("Doctors API Error: " . $e->getMessage());
    jsonResponse(false, 'Internal server error', null, ['code' => 500]);
}

function handleGet() {
    global $pdo;
    
    if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
        // Get single doctor
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $doctor = $stmt->fetch();
        
        if ($doctor) {
            jsonResponse(true, 'Doctor retrieved successfully', $doctor);
        } else {
            jsonResponse(false, 'Doctor not found', null, ['code' => 404]);
        }
    } else {
        // Get all doctors with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (name LIKE ? OR doctor_id LIKE ? OR specialization LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_fill(0, 5, $searchTerm);
        }
        
        if (!empty($status)) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM doctors $whereClause");
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Get doctors
        $stmt = $pdo->prepare("
            SELECT id, doctor_id, name, email, phone, specialization, 
                   license_number, hospital, address, status, created_at
            FROM doctors 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $doctors = $stmt->fetchAll();
        
        $response = [
            'doctors' => $doctors,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
        jsonResponse(true, 'Doctors retrieved successfully', $response);
    }
}

function handlePost() {
    global $pdo;
    
    // Validate required fields
    $required = ['name', 'phone', 'specialization'];
    $errors = validateInput($_POST, $required);
    
    if (!empty($errors)) {
        jsonResponse(false, implode(', ', $errors), null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($_POST);
    
    // Validate email if provided
    if (!empty($data['email']) && !validateEmail($data['email'])) {
        jsonResponse(false, 'Invalid email format', null, ['code' => 422]);
    }
    
    // Validate phone
    if (!validatePhone($data['phone'])) {
        jsonResponse(false, 'Invalid phone number format', null, ['code' => 422]);
    }
    
    // Generate doctor ID
    $doctorId = generateUniqueId('DOC');
    
    // Check if doctor ID already exists
    while (true) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM doctors WHERE doctor_id = ?");
        $checkStmt->execute([$doctorId]);
        if ($checkStmt->fetchColumn() == 0) break;
        $doctorId = generateUniqueId('DOC');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO doctors (
                doctor_id, name, email, phone, specialization, 
                license_number, address, hospital, notes, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $doctorId,
            $data['name'],
            $data['email'] ?? null,
            $data['phone'],
            $data['specialization'],
            $data['license_number'] ?? null,
            $data['address'] ?? null,
            $data['hospital'] ?? null,
            $data['notes'] ?? null
        ]);
        
        $newDoctorId = $pdo->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Doctor Created', "Created doctor: $doctorId");
        
        jsonResponse(true, 'Doctor created successfully', ['id' => $newDoctorId, 'doctor_id' => $doctorId]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(false, 'Doctor with this phone number already exists', null, ['code' => 422]);
        }
        throw $e;
    }
}

function handlePut() {
    global $pdo;
    
    // Get PUT data
    parse_str(file_get_contents("php://input"), $putData);
    
    if (empty($putData['id'])) {
        jsonResponse(false, 'Doctor ID is required', null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($putData);
    $id = $data['id'];
    
    // Check if doctor exists
    $checkStmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE id = ?");
    $checkStmt->execute([$id]);
    $existingDoctor = $checkStmt->fetch();
    
    if (!$existingDoctor) {
        jsonResponse(false, 'Doctor not found', null, ['code' => 404]);
    }
    
    // Validate email if provided
    if (!empty($data['email']) && !validateEmail($data['email'])) {
        jsonResponse(false, 'Invalid email format', null, ['code' => 422]);
    }
    
    // Validate phone if provided
    if (!empty($data['phone']) && !validatePhone($data['phone'])) {
        jsonResponse(false, 'Invalid phone number format', null, ['code' => 422]);
    }
    
    // Build update query
    $updateFields = [];
    $params = [];
    
    $allowedFields = [
        'name', 'email', 'phone', 'specialization', 'license_number',
        'address', 'hospital', 'notes', 'status'
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
            UPDATE doctors 
            SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute($params);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Doctor Updated', "Updated doctor: {$existingDoctor['doctor_id']}");
        
        jsonResponse(true, 'Doctor updated successfully');
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(false, 'Phone number already exists for another doctor', null, ['code' => 422]);
        }
        throw $e;
    }
}

function handleDelete() {
    global $pdo;
    
    // Get DELETE data
    parse_str(file_get_contents("php://input"), $deleteData);
    
    if (empty($deleteData['id'])) {
        jsonResponse(false, 'Doctor ID is required', null, ['code' => 422]);
    }
    
    $id = (int)$deleteData['id'];
    
    // Check if doctor exists
    $checkStmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE id = ?");
    $checkStmt->execute([$id]);
    $doctor = $checkStmt->fetch();
    
    if (!$doctor) {
        jsonResponse(false, 'Doctor not found', null, ['code' => 404]);
    }
    
    // Check if doctor has test orders
    $ordersStmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE doctor_id = ?");
    $ordersStmt->execute([$id]);
    $orderCount = $ordersStmt->fetchColumn();
    
    if ($orderCount > 0) {
        jsonResponse(false, 'Cannot delete doctor with existing test orders', null, ['code' => 422]);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Doctor Deleted', "Deleted doctor: {$doctor['doctor_id']}");
        
        jsonResponse(true, 'Doctor deleted successfully');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Error deleting doctor', null, ['code' => 500]);
    }
}
?>