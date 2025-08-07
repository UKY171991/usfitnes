<?php
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check database connection
if (!isset($pdo) || !$pdo instanceof PDO) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            getPatient();
            break;
        case 'create':
            createPatient();
            break;
        case 'update':
            updatePatient();
            break;
        case 'delete':
            deletePatient();
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getPatient() {
    global $pdo;
    
    $id = $_GET['id'] ?? '';
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            echo json_encode(['success' => true, 'data' => $patient]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function createPatient() {
    global $pdo;
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }
    
    try {
        // Check if phone number already exists
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ?");
        $stmt->execute([$_POST['phone']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Phone number already exists']);
            return;
        }
        
        // Check if email exists (if provided)
        if (!empty($_POST['email'])) {
            $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ?");
            $stmt->execute([$_POST['email']]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                return;
            }
        }
        
        // Insert patient
        $stmt = $pdo->prepare("
            INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, status, address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['phone'],
            $_POST['email'] ?? null,
            $_POST['date_of_birth'],
            $_POST['gender'],
            $_POST['status'] ?? 'Active',
            $_POST['address'] ?? null
        ]);
        
        if ($result) {
            $patientId = $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Patient created successfully',
                'data' => ['id' => $patientId]
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create patient']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updatePatient() {
    global $pdo;
    
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }
    
    try {
        // Check if phone number already exists for other patients
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? AND id != ?");
        $stmt->execute([$_POST['phone'], $id]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Phone number already exists']);
            return;
        }
        
        // Check if email exists for other patients (if provided)
        if (!empty($_POST['email'])) {
            $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ? AND id != ?");
            $stmt->execute([$_POST['email'], $id]);
            if ($stmt->fetch()) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email already exists']);
                return;
            }
        }
        
        // Update patient
        $stmt = $pdo->prepare("
            UPDATE patients 
            SET first_name = ?, last_name = ?, phone = ?, email = ?, date_of_birth = ?, 
                gender = ?, status = ?, address = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $result = $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['phone'],
            $_POST['email'] ?? null,
            $_POST['date_of_birth'],
            $_POST['gender'],
            $_POST['status'] ?? 'Active',
            $_POST['address'] ?? null,
            $id
        ]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update patient']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deletePatient() {
    global $pdo;
    
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    try {
        // Check if patient exists
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
            return;
        }
        
        // Check if patient has test orders (optional - you might want to prevent deletion)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM test_orders WHERE patient_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        if ($result && $result['count'] > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete patient with existing test orders']);
            return;
        }
        
        // Delete patient
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete patient']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
        case 'update':
            updatePatient();
            break;
        case 'delete':
            deletePatient();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Patients API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function listPatients() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id, patient_id, first_name, last_name, phone, email, 
               date_of_birth, gender, status, created_at,
               CASE 
                   WHEN date_of_birth IS NOT NULL 
                   THEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) 
                   ELSE NULL 
               END as age
        FROM patients 
        WHERE status = 'active' 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $patients,
        'total' => count($patients)
    ]);
}

function getPatient() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        throw new Exception('Patient ID is required');
    }
    
    $stmt = $pdo->prepare("
        SELECT *, 
               CASE 
                   WHEN date_of_birth IS NOT NULL 
                   THEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) 
                   ELSE NULL 
               END as age
        FROM patients 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        throw new Exception('Patient not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $patient
    ]);
}

function addPatient() {
    global $pdo;
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']) ?: null;
    $date_of_birth = $_POST['date_of_birth'] ?: null;
    $gender = $_POST['gender'] ?: null;
    
    // Validate email format
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate phone format (basic validation)
    if (!preg_match('/^[\d\-\+\(\)\s]+$/', $phone)) {
        throw new Exception('Invalid phone format');
    }
    
    // Generate unique patient ID
    $patient_id = generatePatientId();
    
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? AND status = 'active'");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        throw new Exception('Phone number already exists');
    }
    
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email address already exists');
        }
    }
    
    // Insert patient
    $stmt = $pdo->prepare("
        INSERT INTO patients (
            patient_id, first_name, last_name, phone, email, 
            date_of_birth, gender, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    $result = $stmt->execute([
        $patient_id,
        $first_name,
        $last_name,
        $phone,
        $email,
        $date_of_birth,
        $gender
    ]);
    
    if (!$result) {
        throw new Exception('Failed to add patient');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Patient added successfully',
        'patient_id' => $patient_id,
        'id' => $pdo->lastInsertId()
    ]);
}

function updatePatient() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Patient ID is required');
    }
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']) ?: null;
    $date_of_birth = $_POST['date_of_birth'] ?: null;
    $gender = $_POST['gender'] ?: null;
    
    // Validate email format
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate phone format
    if (!preg_match('/^[\d\-\+\(\)\s]+$/', $phone)) {
        throw new Exception('Invalid phone format');
    }
    
    // Check if patient exists
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Patient not found');
    }
    
    // Check for duplicate phone (excluding current patient)
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? AND id != ? AND status = 'active'");
    $stmt->execute([$phone, $id]);
    if ($stmt->fetch()) {
        throw new Exception('Phone number already exists');
    }
    
    // Check for duplicate email (excluding current patient)
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ? AND id != ? AND status = 'active'");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Email address already exists');
        }
    }
    
    // Update patient
    $stmt = $pdo->prepare("
        UPDATE patients SET 
            first_name = ?, last_name = ?, phone = ?, email = ?,
            date_of_birth = ?, gender = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $first_name,
        $last_name,
        $phone,
        $email,
        $date_of_birth,
        $gender,
        $id
    ]);
    
    if (!$result) {
        throw new Exception('Failed to update patient');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Patient updated successfully'
    ]);
}

function deletePatient() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Patient ID is required');
    }
    
    // Check if patient exists
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Patient not found');
    }
    
    // Soft delete (update status instead of actual delete)
    $stmt = $pdo->prepare("UPDATE patients SET status = 'deleted', updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if (!$result) {
        throw new Exception('Failed to delete patient');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Patient deleted successfully'
    ]);
}

function generatePatientId() {
    global $pdo;
    
    // Try to get the last patient ID number
    $stmt = $pdo->query("
        SELECT patient_id FROM patients 
        WHERE patient_id LIKE 'PAT%' 
        ORDER BY CAST(SUBSTRING(patient_id, 4) AS UNSIGNED) DESC 
        LIMIT 1
    ");
    
    $lastId = $stmt->fetchColumn();
    
    if ($lastId && preg_match('/PAT(\d+)/', $lastId, $matches)) {
        $nextNumber = intval($matches[1]) + 1;
    } else {
        $nextNumber = 1;
    }
    
    $newId = 'PAT' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    // Ensure uniqueness
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
        $stmt->execute([$newId]);
        if (!$stmt->fetch()) {
            return $newId;
        }
        $nextNumber++;
        $newId = 'PAT' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $attempts++;
    }
    
    // Fallback to random if sequential fails
    return 'PAT' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}
?>
