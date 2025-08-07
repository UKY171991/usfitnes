<?php
require_once '../config.php';
require_once '../includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Validate required fields
$required_fields = ['first_name', 'last_name', 'phone', 'date_of_birth', 'gender'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

try {
    $conn = getDbConnection();
    
    // Check if patient with same phone already exists
    $stmt = $conn->prepare("SELECT id FROM patients WHERE phone = ? AND id != ?");
    $patient_id = isset($_POST['patient_id']) ? (int)$_POST['patient_id'] : 0;
    $stmt->execute([$_POST['phone'], $patient_id]);
    
    if ($stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Patient with this phone number already exists'
        ]);
        exit;
    }
    
    $data = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'phone' => trim($_POST['phone']),
        'email' => trim($_POST['email'] ?? ''),
        'date_of_birth' => $_POST['date_of_birth'],
        'gender' => $_POST['gender'],
        'status' => 'active',
        'updated_at' => date('Y-m-d H:i:s'),
        'updated_by' => $_SESSION['user_id']
    ];
    
    if ($patient_id > 0) {
        // Update existing patient
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        $values[] = $patient_id;
        
        $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($values);
        
        $message = 'Patient updated successfully';
    } else {
        // Insert new patient
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $_SESSION['user_id'];
        
        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO patients ($fields) VALUES ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        
        $patient_id = $conn->lastInsertId();
        $message = 'Patient added successfully';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'patient_id' => $patient_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
