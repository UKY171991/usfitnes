<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple function to send JSON response
function sendResponse($success, $message = '', $data = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data
    ];
    echo json_encode($response);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    // For testing, return sample data
    if ($method === 'GET' && $action === 'list') {
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
            ]
        ];
        
        sendResponse(true, 'Patients retrieved successfully', $samplePatients);
    }
    
    if ($method === 'POST' && $action === 'create') {
        // Return success response for testing
        $newPatientId = rand(100, 999);
        sendResponse(true, 'Patient created successfully', ['id' => $newPatientId]);
    }
    
    if ($method === 'POST' && $action === 'update') {
        sendResponse(true, 'Patient updated successfully');
    }
    
    if ($method === 'POST' && $action === 'delete') {
        sendResponse(true, 'Patient deleted successfully');
    }
    
    if ($method === 'GET' && $action === 'get') {
        $samplePatient = [
            'id' => $_GET['id'] ?? 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+1234567890',
            'email' => 'john.doe@email.com',
            'date_of_birth' => '1985-06-15',
            'gender' => 'male',
            'status' => 'active',
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => null
        ];
        
        sendResponse(true, 'Patient retrieved successfully', $samplePatient);
    }
    
    sendResponse(false, 'Invalid action or method');
    
} catch (Exception $e) {
    sendResponse(false, 'Server error: ' . $e->getMessage());
}
?>
