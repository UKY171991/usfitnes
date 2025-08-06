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

// Try to include the real database config
$db_available = false;
try {
    require_once '../config.php';
    // Test connection
    if (isset($conn) && $conn) {
        $test_query = mysqli_query($conn, "SELECT 1");
        if ($test_query) {
            $db_available = true;
        }
    }
} catch (Exception $e) {
    $db_available = false;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($db_available) {
        // Use real database
        include 'patients_api.php';
    } else {
        // Use fallback test data
        handleFallback($method, $action);
    }
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage());
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
