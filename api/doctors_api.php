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

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listDoctors();
            break;
        case 'get':
            getDoctor();
            break;
        case 'add':
        case 'create':
            addDoctor();
            break;
        case 'update':
            updateDoctor();
            break;
        case 'delete':
            deleteDoctor();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Doctors API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function listDoctors() {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id, doctor_id, first_name, last_name, specialization, 
               phone, email, license_number, status, created_at
        FROM doctors 
        WHERE status = 'active' 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $doctors,
        'total' => count($doctors)
    ]);
}

function getDoctor() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        throw new Exception('Doctor ID is required');
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM doctors 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        throw new Exception('Doctor not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $doctor
    ]);
}

function addDoctor() {
    global $pdo;
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'specialization', 'phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $specialization = trim($_POST['specialization']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']) ?: null;
    $license_number = trim($_POST['license_number']) ?: null;
    
    // Validate email format
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate phone format (basic validation)
    if (!preg_match('/^[\d\-\+\(\)\s]+$/', $phone)) {
        throw new Exception('Invalid phone format');
    }
    
    // Generate unique doctor ID
    $doctor_id = generateDoctorId();
    
    // Check for duplicates
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? AND status = 'active'");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        throw new Exception('Phone number already exists');
    }
    
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new Exception('Email address already exists');
        }
    }
    
    if ($license_number) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE license_number = ? AND status = 'active'");
        $stmt->execute([$license_number]);
        if ($stmt->fetch()) {
            throw new Exception('License number already exists');
        }
    }
    
    // Insert doctor
    $stmt = $pdo->prepare("
        INSERT INTO doctors (
            doctor_id, first_name, last_name, specialization, 
            phone, email, license_number, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    $result = $stmt->execute([
        $doctor_id,
        $first_name,
        $last_name,
        $specialization,
        $phone,
        $email,
        $license_number
    ]);
    
    if (!$result) {
        throw new Exception('Failed to add doctor');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Doctor added successfully',
        'doctor_id' => $doctor_id,
        'id' => $pdo->lastInsertId()
    ]);
}

function updateDoctor() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Doctor ID is required');
    }
    
    // Validate required fields
    $required = ['first_name', 'last_name', 'specialization', 'phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $specialization = trim($_POST['specialization']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']) ?: null;
    $license_number = trim($_POST['license_number']) ?: null;
    
    // Validate email format
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }
    
    // Validate phone format
    if (!preg_match('/^[\d\-\+\(\)\s]+$/', $phone)) {
        throw new Exception('Invalid phone format');
    }
    
    // Check if doctor exists
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Doctor not found');
    }
    
    // Check for duplicate phone (excluding current doctor)
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? AND id != ? AND status = 'active'");
    $stmt->execute([$phone, $id]);
    if ($stmt->fetch()) {
        throw new Exception('Phone number already exists');
    }
    
    // Check for duplicate email (excluding current doctor)
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ? AND id != ? AND status = 'active'");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Email address already exists');
        }
    }
    
    // Check for duplicate license (excluding current doctor)
    if ($license_number) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE license_number = ? AND id != ? AND status = 'active'");
        $stmt->execute([$license_number, $id]);
        if ($stmt->fetch()) {
            throw new Exception('License number already exists');
        }
    }
    
    // Update doctor
    $stmt = $pdo->prepare("
        UPDATE doctors SET 
            first_name = ?, last_name = ?, specialization = ?, 
            phone = ?, email = ?, license_number = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $first_name,
        $last_name,
        $specialization,
        $phone,
        $email,
        $license_number,
        $id
    ]);
    
    if (!$result) {
        throw new Exception('Failed to update doctor');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Doctor updated successfully'
    ]);
}

function deleteDoctor() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Doctor ID is required');
    }
    
    // Check if doctor exists
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ? AND status = 'active'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Doctor not found');
    }
    
    // Soft delete (update status instead of actual delete)
    $stmt = $pdo->prepare("UPDATE doctors SET status = 'deleted', updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if (!$result) {
        throw new Exception('Failed to delete doctor');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Doctor deleted successfully'
    ]);
}

function generateDoctorId() {
    global $pdo;
    
    // Try to get the last doctor ID number
    $stmt = $pdo->query("
        SELECT doctor_id FROM doctors 
        WHERE doctor_id LIKE 'DR%' 
        ORDER BY CAST(SUBSTRING(doctor_id, 3) AS UNSIGNED) DESC 
        LIMIT 1
    ");
    
    $lastId = $stmt->fetchColumn();
    
    if ($lastId && preg_match('/DR(\d+)/', $lastId, $matches)) {
        $nextNumber = intval($matches[1]) + 1;
    } else {
        $nextNumber = 1;
    }
    
    $newId = 'DR' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    // Ensure uniqueness
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$newId]);
        if (!$stmt->fetch()) {
            return $newId;
        }
        $nextNumber++;
        $newId = 'DR' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $attempts++;
    }
    
    // Fallback to random if sequential fails
    return 'DR' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}
?>
