<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session to check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Try to use working config first, fallback to regular config
if (file_exists('../config_working.php')) {
    require_once '../config_working.php';
} else {
    require_once '../config.php';
}

// Check if user is logged in (uncomment for production)
// requireLogin();

// Function to send JSON response
function sendResponse($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

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
    error_log('Patients API Error: ' . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}

function handleGet($action) {
    global $pdo;
    
    switch ($action) {
        case 'list':
            try {
                $stmt = $pdo->prepare("SELECT id, first_name, last_name, phone, email, 
                                      date_of_birth, gender, status, created_at 
                                      FROM patients WHERE status != 'deleted' 
                                      ORDER BY created_at DESC");
                $stmt->execute();
                $patients = $stmt->fetchAll();
                
                // Calculate age for each patient
                foreach ($patients as &$patient) {
                    if ($patient['date_of_birth']) {
                        $dob = new DateTime($patient['date_of_birth']);
                        $now = new DateTime();
                        $patient['age'] = $now->diff($dob)->y;
                    } else {
                        $patient['age'] = 'N/A';
                    }
                }
                
                sendResponse(true, 'Patients retrieved successfully', $patients);
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving patients: ' . $e->getMessage());
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                $patient = $stmt->fetch();
                
                if ($patient) {
                    // Calculate age
                    if ($patient['date_of_birth']) {
                        $dob = new DateTime($patient['date_of_birth']);
                        $now = new DateTime();
                        $patient['age'] = $now->diff($dob)->y;
                    }
                    sendResponse(true, 'Patient retrieved successfully', $patient);
                } else {
                    sendResponse(false, 'Patient not found');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving patient: ' . $e->getMessage());
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
            $required_fields = ['first_name', 'last_name', 'phone'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare and validate data
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $phone = sanitizeInput($_POST['phone']);
            $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
            $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
            $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
            $address = !empty($_POST['address']) ? sanitizeInput($_POST['address']) : null;
            
            // Validate email if provided
            if ($email && !validateEmail($email)) {
                sendResponse(false, 'Please enter a valid email address');
            }
            
            // Validate phone
            if (!validatePhone($phone)) {
                sendResponse(false, 'Please enter a valid phone number');
            }
            
            try {
                // Check if phone already exists
                $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? AND status != 'deleted'");
                $stmt->execute([$phone]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'A patient with this phone number already exists');
                }
                
                // Check if email already exists (if provided)
                if ($email) {
                    $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ? AND status != 'deleted'");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        sendResponse(false, 'A patient with this email address already exists');
                    }
                }
                
                // Insert new patient
                $stmt = $pdo->prepare("INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, address, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$first_name, $last_name, $phone, $email, $date_of_birth, $gender, $address]);
                
                $patient_id = $pdo->lastInsertId();
                sendResponse(true, 'Patient created successfully', ['id' => $patient_id]);
                
            } catch (Exception $e) {
                sendResponse(false, 'Error creating patient: ' . $e->getMessage());
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            $required_fields = ['first_name', 'last_name', 'phone'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $first_name = sanitizeInput($_POST['first_name']);
            $last_name = sanitizeInput($_POST['last_name']);
            $phone = sanitizeInput($_POST['phone']);
            $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
            $date_of_birth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
            $gender = !empty($_POST['gender']) ? $_POST['gender'] : null;
            $address = !empty($_POST['address']) ? sanitizeInput($_POST['address']) : null;
            
            // Validate email if provided
            if ($email && !validateEmail($email)) {
                sendResponse(false, 'Please enter a valid email address');
            }
            
            // Validate phone
            if (!validatePhone($phone)) {
                sendResponse(false, 'Please enter a valid phone number');
            }
            
            try {
                // Check if patient exists
                $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Patient not found');
                }
                
                // Check if phone already exists for another patient
                $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ? AND id != ? AND status != 'deleted'");
                $stmt->execute([$phone, $id]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'A patient with this phone number already exists');
                }
                
                // Check if email already exists for another patient (if provided)
                if ($email) {
                    $stmt = $pdo->prepare("SELECT id FROM patients WHERE email = ? AND id != ? AND status != 'deleted'");
                    $stmt->execute([$email, $id]);
                    if ($stmt->fetch()) {
                        sendResponse(false, 'A patient with this email address already exists');
                    }
                }
                
                // Update patient
                $stmt = $pdo->prepare("UPDATE patients SET first_name = ?, last_name = ?, phone = ?, email = ?, 
                                      date_of_birth = ?, gender = ?, address = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$first_name, $last_name, $phone, $email, $date_of_birth, $gender, $address, $id]);
                
                sendResponse(true, 'Patient updated successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error updating patient: ' . $e->getMessage());
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            try {
                // Check if patient exists
                $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Patient not found');
                }
                
                // Soft delete - just change status
                $stmt = $pdo->prepare("UPDATE patients SET status = 'deleted', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                sendResponse(true, 'Patient deleted successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error deleting patient: ' . $e->getMessage());
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePut($action) {
    // Parse PUT data
    parse_str(file_get_contents("php://input"), $_POST);
    handlePost($action);
}

function handleDelete($action) {
    // Parse DELETE data
    parse_str(file_get_contents("php://input"), $_POST);
    handlePost('delete');
}
            $last_name = trim($_POST['last_name']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email'] ?? '');
            $date_of_birth = $_POST['date_of_birth'] ?? null;
            $gender = $_POST['gender'] ?? '';
            $status = 'active';
            $created_at = date('Y-m-d H:i:s');
            
            // Check if patient already exists
            $check_query = "SELECT id FROM patients WHERE phone = ? AND status != 'deleted'";
            $check_stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, 's', $phone);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_fetch_assoc($check_result)) {
                sendResponse(false, 'A patient with this phone number already exists');
            }
            
            // Insert new patient
            $query = "INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ssssssss', 
                $first_name, $last_name, $phone, $email, $date_of_birth, 
                $gender, $status, $created_at
            );
            
            if (mysqli_stmt_execute($stmt)) {
                $patient_id = mysqli_insert_id($conn);
                sendResponse(true, 'Patient created successfully', ['id' => $patient_id]);
            } else {
                sendResponse(false, 'Failed to create patient: ' . mysqli_error($conn));
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            $required_fields = ['first_name', 'last_name', 'phone'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email'] ?? '');
            $date_of_birth = $_POST['date_of_birth'] ?? null;
            $gender = $_POST['gender'] ?? '';
            $updated_at = date('Y-m-d H:i:s');
            
            // Check if phone number is already used by another patient
            $check_query = "SELECT id FROM patients WHERE phone = ? AND id != ? AND status != 'deleted'";
            $check_stmt = mysqli_prepare($conn, $check_query);
            mysqli_stmt_bind_param($check_stmt, 'si', $phone, $id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_fetch_assoc($check_result)) {
                sendResponse(false, 'Another patient with this phone number already exists');
            }
            
            // Update patient
            $query = "UPDATE patients SET first_name = ?, last_name = ?, phone = ?, email = ?, date_of_birth = ?, gender = ?, updated_at = ? WHERE id = ? AND status != 'deleted'";
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'sssssssi', 
                $first_name, $last_name, $phone, $email, $date_of_birth, 
                $gender, $updated_at, $id
            );
            
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    sendResponse(true, 'Patient updated successfully');
                } else {
                    sendResponse(false, 'Patient not found or no changes made');
                }
            } else {
                sendResponse(false, 'Failed to update patient: ' . mysqli_error($conn));
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            // Soft delete - update status to deleted
            $query = "UPDATE patients SET status = 'deleted', updated_at = ? WHERE id = ? AND status != 'deleted'";
            $updated_at = date('Y-m-d H:i:s');
            
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $updated_at, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    sendResponse(true, 'Patient deleted successfully');
                } else {
                    sendResponse(false, 'Patient not found');
                }
            } else {
                sendResponse(false, 'Failed to delete patient: ' . mysqli_error($conn));
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}
?>
