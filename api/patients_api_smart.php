<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session to check authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to send JSON response
function sendResponse($success, $message = '', $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Try to include the real database config safely
require_once 'safe_config.php';
list($db_available, $conn) = tryDatabaseConnection();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($db_available) {
        // Use real database by including the original API functions
        handleRealDatabase($method, $action);
    } else {
        // Use fallback test data
        handleFallback($method, $action);
    }
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage());
}

function handleRealDatabase($method, $action) {
    global $conn;
    
    switch ($method) {
        case 'GET':
            handleGet($action);
            break;
        case 'POST':
            handlePost($action);
            break;
        default:
            sendResponse(false, 'Method not allowed');
    }
}

function handleGet($action) {
    global $conn;
    
    switch ($action) {
        case 'list':
            $query = "SELECT * FROM patients WHERE status != 'deleted' ORDER BY created_at DESC";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                $patients = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $patients[] = $row;
                }
                sendResponse(true, 'Patients retrieved successfully', $patients);
            } else {
                sendResponse(false, 'Failed to retrieve patients: ' . mysqli_error($conn));
            }
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            if (!$id) {
                sendResponse(false, 'Patient ID is required');
            }
            
            $query = "SELECT * FROM patients WHERE id = ? AND status != 'deleted'";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($patient = mysqli_fetch_assoc($result)) {
                sendResponse(true, 'Patient retrieved successfully', $patient);
            } else {
                sendResponse(false, 'Patient not found');
            }
            break;
            
        default:
            sendResponse(false, 'Invalid action');
    }
}

function handlePost($action) {
    global $conn;
    
    switch ($action) {
        case 'create':
            $required_fields = ['first_name', 'last_name', 'phone'];
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, "Field $field is required");
                }
            }
            
            $first_name = trim($_POST['first_name']);
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
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    sendResponse(false, "Field $field is required");
                }
            }
            
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
            
            // Soft delete
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

function handleFallback($method, $action) {
    // Sample patients data for fallback
    $samplePatients = [
        [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890',
            'email' => 'john.doe@email.com',
            'date_of_birth' => '1985-06-15',
            'gender' => 'male',
            'status' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => null
        ],
        [
            'id' => 2,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'phone' => '+1234567891',
            'email' => 'jane.smith@email.com',
            'date_of_birth' => '1990-03-22',
            'gender' => 'female',
            'status' => 'active',
            'created_at' => '2024-01-02 11:00:00',
            'updated_at' => null
        ],
        [
            'id' => 3,
            'first_name' => 'Robert',
            'last_name' => 'Johnson',
            'phone' => '+1234567892',
            'email' => 'robert.j@email.com',
            'date_of_birth' => '1978-11-08',
            'gender' => 'male',
            'status' => 'active',
            'created_at' => '2024-01-03 12:00:00',
            'updated_at' => null
        ]
    ];
    
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                sendResponse(true, 'Patients retrieved successfully (fallback mode)', $samplePatients);
            } elseif ($action === 'get') {
                $id = $_GET['id'] ?? 1;
                $patient = null;
                foreach ($samplePatients as $p) {
                    if ($p['id'] == $id) {
                        $patient = $p;
                        break;
                    }
                }
                if ($patient) {
                    sendResponse(true, 'Patient retrieved successfully (fallback mode)', $patient);
                } else {
                    sendResponse(false, 'Patient not found');
                }
            } else {
                sendResponse(false, 'Invalid action');
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $newPatientId = rand(100, 999);
                sendResponse(true, 'Patient created successfully (fallback mode - not saved to database)', ['id' => $newPatientId]);
            } elseif ($action === 'update') {
                sendResponse(true, 'Patient updated successfully (fallback mode - not saved to database)');
            } elseif ($action === 'delete') {
                sendResponse(true, 'Patient deleted successfully (fallback mode - not saved to database)');
            } else {
                sendResponse(false, 'Invalid action');
            }
            break;
            
        default:
            sendResponse(false, 'Method not allowed');
    }
}
?>
