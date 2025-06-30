<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session to check authentication
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo, $input);
            break;
        case 'PUT':
            handlePut($pdo, $input);
            break;
        case 'DELETE':
            handleDelete($pdo, $input);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGet($pdo) {
    if (isset($_GET['id'])) {
        // Get single patient
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
        $stmt->execute([$_GET['id']]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            echo json_encode(['success' => true, 'data' => $patient]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
        }
    } else {
        // Get all patients with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?";
            $searchParam = "%$search%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM patients $whereClause");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get patients
        // LIMIT and OFFSET must be integers directly in the SQL string for MySQL/MariaDB
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql = "SELECT * FROM patients $whereClause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $patients,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'pages' => ceil($totalCount / $limit)
            ]
        ]);
    }
}

function handlePost($pdo, $input) {
    // Handle both JSON input and POST data
    if (empty($input)) {
        $input = $_POST;
    }
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender', 'phone'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    // Validate email format if provided
    if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if patient with same phone already exists
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE phone = ?");
    $stmt->execute([$input['phone']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Patient with this phone number already exists']);
        return;
    }
    
    // Generate unique patient ID
    $patient_id = generatePatientId($pdo);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, phone, email, address, emergency_contact, emergency_phone)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $patient_id,
            trim($input['first_name']),
            trim($input['last_name']),
            $input['date_of_birth'],
            $input['gender'],
            trim($input['phone']),
            !empty($input['email']) ? trim($input['email']) : null,
            !empty($input['address']) ? trim($input['address']) : null,
            !empty($input['emergency_contact']) ? trim($input['emergency_contact']) : null,
            !empty($input['emergency_phone']) ? trim($input['emergency_phone']) : null
        ]);
        
        $id = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Patient added successfully',
            'data' => ['id' => $id, 'patient_id' => $patient_id]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($pdo, $input) {
    // Handle both JSON input and POST data
    if (empty($input)) {
        $input = $_POST;
    }
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    // Check if patient exists
    $stmt = $pdo->prepare("SELECT id FROM patients WHERE id = ?");
    $stmt->execute([$input['id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
        return;
    }
    
    // Validate email format if provided
    if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE patients SET 
                first_name = ?, last_name = ?, date_of_birth = ?, gender = ?, 
                phone = ?, email = ?, address = ?, emergency_contact = ?, emergency_phone = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            trim($input['first_name']),
            trim($input['last_name']),
            $input['date_of_birth'],
            $input['gender'],
            trim($input['phone']),
            !empty($input['email']) ? trim($input['email']) : null,
            !empty($input['address']) ? trim($input['address']) : null,
            !empty($input['emergency_contact']) ? trim($input['emergency_contact']) : null,
            !empty($input['emergency_phone']) ? trim($input['emergency_phone']) : null,
            $input['id']        ]);
        
        echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo, $input) {
    // Handle both JSON input and POST data
    if (empty($input)) {
        $input = $_POST;
    }
    
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    try {
        // Check if patient has any test orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE patient_id = ?");
        $stmt->execute([$input['id']]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete patient with existing test orders']);
            return;
        }
          $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$input['id']]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function generatePatientId($pdo) {
    do {
        // Generate patient ID in format: PAT + current year + 4 digit random number
        $year = date('Y');
        $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $patientId = 'PAT' . $year . $randomNumber;
        
        // Check if this ID already exists
        $stmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
        $stmt->execute([$patientId]);
        $exists = $stmt->fetch();
        
    } while ($exists);
    
    return $patientId;
}
?>
