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
    error_log('Doctors API Error: ' . $e->getMessage());
    sendResponse(false, 'Server error: ' . $e->getMessage());
}

function handleGet($action) {
    global $pdo;
    
    switch ($action) {
        case 'list':
            try {
                $stmt = $pdo->prepare("SELECT * FROM doctors WHERE status != 'deleted' ORDER BY created_at DESC");
                $stmt->execute();
                $doctors = $stmt->fetchAll();
                sendResponse(true, 'Doctors retrieved successfully', $doctors);
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving doctors: ' . $e->getMessage());
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Doctor ID is required');
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                $doctor = $stmt->fetch();
                
                if ($doctor) {
                    sendResponse(true, 'Doctor retrieved successfully', $doctor);
                } else {
                    sendResponse(false, 'Doctor not found');
                }
            } catch (Exception $e) {
                sendResponse(false, 'Error retrieving doctor: ' . $e->getMessage());
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
            $required_fields = ['name', 'specialization'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $name = sanitizeInput($_POST['name']);
            $specialization = sanitizeInput($_POST['specialization']);
            $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
            $phone = !empty($_POST['phone']) ? sanitizeInput($_POST['phone']) : null;
            $license_number = !empty($_POST['license_number']) ? sanitizeInput($_POST['license_number']) : null;
            $address = !empty($_POST['address']) ? sanitizeInput($_POST['address']) : null;
            
            // Validate email if provided
            if ($email && !validateEmail($email)) {
                sendResponse(false, 'Please enter a valid email address');
            }
            
            // Validate phone if provided
            if ($phone && !validatePhone($phone)) {
                sendResponse(false, 'Please enter a valid phone number');
            }
            
            try {
                // Check if email already exists (if provided)
                if ($email) {
                    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ? AND status != 'deleted'");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        sendResponse(false, 'A doctor with this email already exists');
                    }
                }
                
                // Generate doctor ID
                $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(doctor_id, 3) AS UNSIGNED)) as max_id FROM doctors WHERE doctor_id LIKE 'DR%'");
                $stmt->execute();
                $result = $stmt->fetch();
                $next_id = ($result['max_id'] ?? 0) + 1;
                $doctor_id = 'DR' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
                
                // Insert new doctor
                $stmt = $pdo->prepare("INSERT INTO doctors (doctor_id, name, specialization, email, phone, license_number, address, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, 'active')");
                $stmt->execute([$doctor_id, $name, $specialization, $email, $phone, $license_number, $address]);
                
                $id = $pdo->lastInsertId();
                sendResponse(true, 'Doctor created successfully', ['id' => $id, 'doctor_id' => $doctor_id]);
                
            } catch (Exception $e) {
                sendResponse(false, 'Error creating doctor: ' . $e->getMessage());
            }
            break;
            
        case 'update':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Doctor ID is required');
            }
            
            $required_fields = ['name', 'specialization'];
            
            // Validate required fields
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            // Prepare data
            $name = sanitizeInput($_POST['name']);
            $specialization = sanitizeInput($_POST['specialization']);
            $email = !empty($_POST['email']) ? sanitizeInput($_POST['email']) : null;
            $phone = !empty($_POST['phone']) ? sanitizeInput($_POST['phone']) : null;
            $license_number = !empty($_POST['license_number']) ? sanitizeInput($_POST['license_number']) : null;
            $address = !empty($_POST['address']) ? sanitizeInput($_POST['address']) : null;
            
            // Validate email if provided
            if ($email && !validateEmail($email)) {
                sendResponse(false, 'Please enter a valid email address');
            }
            
            // Validate phone if provided
            if ($phone && !validatePhone($phone)) {
                sendResponse(false, 'Please enter a valid phone number');
            }
            
            try {
                // Check if doctor exists
                $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Doctor not found');
                }
                
                // Check if email already exists for another doctor (if provided)
                if ($email) {
                    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ? AND id != ? AND status != 'deleted'");
                    $stmt->execute([$email, $id]);
                    if ($stmt->fetch()) {
                        sendResponse(false, 'A doctor with this email already exists');
                    }
                }
                
                // Update doctor
                $stmt = $pdo->prepare("UPDATE doctors SET name = ?, specialization = ?, email = ?, phone = ?, 
                                      license_number = ?, address = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $specialization, $email, $phone, $license_number, $address, $id]);
                
                sendResponse(true, 'Doctor updated successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error updating doctor: ' . $e->getMessage());
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Doctor ID is required');
            }
            
            try {
                // Check if doctor exists
                $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ? AND status != 'deleted'");
                $stmt->execute([$id]);
                if (!$stmt->fetch()) {
                    sendResponse(false, 'Doctor not found');
                }
                
                // Soft delete - just change status
                $stmt = $pdo->prepare("UPDATE doctors SET status = 'deleted', updated_at = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                
                sendResponse(true, 'Doctor deleted successfully');
                
            } catch (Exception $e) {
                sendResponse(false, 'Error deleting doctor: ' . $e->getMessage());
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
