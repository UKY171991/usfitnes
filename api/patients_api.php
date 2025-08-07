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
    error_log("Patients API Error: " . $e->getMessage());
    jsonResponse(false, 'Internal server error', null, ['code' => 500]);
}

function handleGet() {
    global $pdo;
    
    if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
        // Get single patient
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $patient = $stmt->fetch();
        
        if ($patient) {
            jsonResponse(true, 'Patient retrieved successfully', $patient);
        } else {
            jsonResponse(false, 'Patient not found', null, ['code' => 404]);
        }
    } else {
        // Get all patients with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 25;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $offset = ($page - 1) * $limit;
        
        // Build query
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= " AND (first_name LIKE ? OR last_name LIKE ? OR patient_id LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_fill(0, 5, $searchTerm);
        }
        
        if (!empty($status)) {
            $whereClause .= " AND status = ?";
            $params[] = $status;
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM patients $whereClause");
        $countStmt->execute($params);
        $totalRecords = $countStmt->fetchColumn();
        
        // Get patients
        $stmt = $pdo->prepare("
            SELECT id, patient_id, first_name, last_name, phone, email, 
                   date_of_birth, gender, blood_group, status, created_at,
                   CONCAT(first_name, ' ', last_name) as full_name
            FROM patients 
            $whereClause 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $patients = $stmt->fetchAll();
        
        $response = [
            'patients' => $patients,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $totalRecords,
                'total_pages' => ceil($totalRecords / $limit)
            ]
        ];
        
        jsonResponse(true, 'Patients retrieved successfully', $response);
    }
}

function handlePost() {
    global $pdo;
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'phone'];
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
    
    // Generate patient ID
    $patientId = generateUniqueId('PAT');
    
    // Check if patient ID already exists
    while (true) {
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = ?");
        $checkStmt->execute([$patientId]);
        if ($checkStmt->fetchColumn() == 0) break;
        $patientId = generateUniqueId('PAT');
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO patients (
                patient_id, first_name, last_name, phone, email, 
                date_of_birth, gender, address, emergency_contact, 
                emergency_phone, blood_group, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        
        $stmt->execute([
            $patientId,
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['address'] ?? null,
            $data['emergency_contact'] ?? null,
            $data['emergency_phone'] ?? null,
            $data['blood_group'] ?? null
        ]);
        
        $newPatientId = $pdo->lastInsertId();
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Patient Created', "Created patient: $patientId");
        
        jsonResponse(true, 'Patient created successfully', ['id' => $newPatientId, 'patient_id' => $patientId]);
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(false, 'Patient with this phone number already exists', null, ['code' => 422]);
        }
        throw $e;
    }
}

function handlePut() {
    global $pdo;
    
    // Get PUT data
    parse_str(file_get_contents("php://input"), $putData);
    
    if (empty($putData['id'])) {
        jsonResponse(false, 'Patient ID is required', null, ['code' => 422]);
    }
    
    // Sanitize input
    $data = sanitizeInput($putData);
    $id = $data['id'];
    
    // Check if patient exists
    $checkStmt = $pdo->prepare("SELECT patient_id FROM patients WHERE id = ?");
    $checkStmt->execute([$id]);
    $existingPatient = $checkStmt->fetch();
    
    if (!$existingPatient) {
        jsonResponse(false, 'Patient not found', null, ['code' => 404]);
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
        'first_name', 'last_name', 'phone', 'email', 'date_of_birth',
        'gender', 'address', 'emergency_contact', 'emergency_phone', 
        'blood_group', 'medical_history', 'allergies', 'status'
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
            UPDATE patients 
            SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute($params);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Patient Updated', "Updated patient: {$existingPatient['patient_id']}");
        
        jsonResponse(true, 'Patient updated successfully');
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            jsonResponse(false, 'Phone number already exists for another patient', null, ['code' => 422]);
        }
        throw $e;
    }
}

function handleDelete() {
    global $pdo;
    
    // Get DELETE data
    parse_str(file_get_contents("php://input"), $deleteData);
    
    if (empty($deleteData['id'])) {
        jsonResponse(false, 'Patient ID is required', null, ['code' => 422]);
    }
    
    $id = (int)$deleteData['id'];
    
    // Check if patient exists
    $checkStmt = $pdo->prepare("SELECT patient_id FROM patients WHERE id = ?");
    $checkStmt->execute([$id]);
    $patient = $checkStmt->fetch();
    
    if (!$patient) {
        jsonResponse(false, 'Patient not found', null, ['code' => 404]);
    }
    
    // Check if patient has test orders
    $ordersStmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE patient_id = ?");
    $ordersStmt->execute([$id]);
    $orderCount = $ordersStmt->fetchColumn();
    
    if ($orderCount > 0) {
        jsonResponse(false, 'Cannot delete patient with existing test orders', null, ['code' => 422]);
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log activity
        logActivity($_SESSION['user_id'], 'Patient Deleted', "Deleted patient: {$patient['patient_id']}");
        
        jsonResponse(true, 'Patient deleted successfully');
        
    } catch (PDOException $e) {
        jsonResponse(false, 'Error deleting patient', null, ['code' => 500]);
    }
}
?>