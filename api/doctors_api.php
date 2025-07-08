<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        // Get single doctor
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$_GET['id']]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doctor) {
            echo json_encode(['success' => true, 'data' => $doctor]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        }
    } else {
        // Get all doctors with pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE first_name LIKE ? OR last_name LIKE ? OR specialization LIKE ? OR phone LIKE ? OR email LIKE ? OR hospital LIKE ?";
            $searchParam = "%$search%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM doctors $whereClause");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get doctors
        $stmt = $pdo->prepare("SELECT * FROM doctors $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $params[] = $limit;
        $params[] = $offset;
        $stmt->execute($params);
        $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $doctors,
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
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'specialization', 'phone'];
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
    
    // Check if doctor with same phone already exists
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE phone = ?");
    $stmt->execute([$input['phone']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Doctor with this phone number already exists']);
        return;
    }
    
    // Check if doctor with same email already exists (if email provided)
    if (!empty($input['email'])) {
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Doctor with this email already exists']);
            return;
        }
    }
    
    try {
        // Generate unique doctor_id
        $doctor_id = 'DOC' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO doctors (doctor_id, first_name, last_name, specialization, license_number, phone, email, address, hospital, referral_percentage, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $doctor_id,
            trim($input['first_name']),
            trim($input['last_name']),
            trim($input['specialization']),
            !empty($input['license_number']) ? trim($input['license_number']) : null,
            trim($input['phone']),
            !empty($input['email']) ? trim($input['email']) : null,
            !empty($input['address']) ? trim($input['address']) : null,
            !empty($input['hospital']) ? trim($input['hospital']) : null,
            isset($input['referral_percentage']) ? floatval($input['referral_percentage']) : 0.00,
            isset($input['status']) ? $input['status'] : 'active'
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Doctor added successfully', 'doctor_id' => $doctor_id]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($pdo, $input) {
    if (!isset($input['doctor_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    // Check if doctor exists
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ?");
    $stmt->execute([$input['doctor_id']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        return;
    }
    
    // Validate email format if provided
    if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if another doctor has the same phone (excluding current doctor)
    if (isset($input['phone'])) {
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE phone = ? AND doctor_id != ?");
        $stmt->execute([$input['phone'], $input['doctor_id']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Another doctor with this phone number already exists']);
            return;
        }
    }
    
    // Check if another doctor has the same email (excluding current doctor)
    if (!empty($input['email'])) {
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE email = ? AND doctor_id != ?");
        $stmt->execute([$input['email'], $input['doctor_id']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Another doctor with this email already exists']);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE doctors SET 
                first_name = COALESCE(?, first_name),
                last_name = COALESCE(?, last_name),
                specialization = COALESCE(?, specialization),
                license_number = ?,
                phone = COALESCE(?, phone),
                email = ?,
                address = ?,
                hospital = ?,
                referral_percentage = ?,
                status = COALESCE(?, status)
            WHERE doctor_id = ?
        ");
        
        $stmt->execute([
            isset($input['first_name']) ? trim($input['first_name']) : null,
            isset($input['last_name']) ? trim($input['last_name']) : null,
            isset($input['specialization']) ? trim($input['specialization']) : null,
            isset($input['license_number']) ? trim($input['license_number']) : null,
            isset($input['phone']) ? trim($input['phone']) : null,
            isset($input['email']) ? trim($input['email']) : null,
            isset($input['address']) ? trim($input['address']) : null,
            isset($input['hospital']) ? trim($input['hospital']) : null,
            isset($input['referral_percentage']) ? floatval($input['referral_percentage']) : null,
            isset($input['status']) ? $input['status'] : null,
            $input['doctor_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo, $input) {
    if (!isset($input['doctor_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    try {
        // Check if doctor has any test orders
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE doctor_id = ?");
        $stmt->execute([$input['doctor_id']]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cannot delete doctor with existing test orders']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$input['doctor_id']]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
