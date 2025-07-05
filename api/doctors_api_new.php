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
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ? OR doctor_id = ?");
        $stmt->execute([$_GET['id'], $_GET['id']]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doctor) {
            echo json_encode(['success' => true, 'data' => $doctor]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        }
    } else {
        // Get all doctors with optional search and pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR specialization LIKE ? OR phone LIKE ? OR email LIKE ? OR hospital LIKE ?";
            $searchParam = "%$search%";
            $params = [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM doctors $whereClause");
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        
        // Get doctors
        $stmt = $pdo->prepare("SELECT * FROM doctors $whereClause ORDER BY first_name, last_name LIMIT ? OFFSET ?");
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
    $requiredFields = ['first_name', 'last_name', 'phone'];
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
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ?");
    $stmt->execute([$input['phone']]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Doctor with this phone number already exists']);
        return;
    }
    
    // Check if doctor with same email already exists (if email provided)
    if (!empty($input['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Doctor with this email already exists']);
            return;
        }
    }
    
    // Generate unique doctor ID
    $doctorId = generateDoctorId($pdo);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO doctors (doctor_id, first_name, last_name, specialization, license_number, phone, email, address, hospital, referral_percentage, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $doctorId,
            trim($input['first_name']),
            trim($input['last_name']),
            trim($input['specialization'] ?? ''),
            trim($input['license_number'] ?? ''),
            trim($input['phone']),
            !empty($input['email']) ? trim($input['email']) : null,
            trim($input['address'] ?? ''),
            trim($input['hospital'] ?? ''),
            floatval($input['referral_percentage'] ?? 0),
            $input['status'] ?? 'active'
        ]);
        
        $newId = $pdo->lastInsertId();
        
        // Get the created doctor
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$newId]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Doctor added successfully',
            'data' => $doctor
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handlePut($pdo, $input) {
    // Check if ID is provided
    if (!isset($input['doctor_id']) && !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    $doctorId = $input['doctor_id'] ?? $input['id'];
    
    // Check if doctor exists
    $stmt = $pdo->prepare("SELECT id FROM doctors WHERE id = ? OR doctor_id = ?");
    $stmt->execute([$doctorId, $doctorId]);
    $existing = $stmt->fetch();
    
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        return;
    }
    
    $actualId = $existing['id'];
    
    // Validate required fields if provided
    $requiredFields = ['first_name', 'last_name', 'phone'];
    foreach ($requiredFields as $field) {
        if (isset($input[$field]) && empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' cannot be empty"]);
            return;
        }
    }
    
    // Validate email format if provided
    if (!empty($input['email']) && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check for duplicates (excluding current doctor)
    if (!empty($input['phone'])) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE phone = ? AND id != ?");
        $stmt->execute([$input['phone'], $actualId]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Another doctor with this phone number already exists']);
            return;
        }
    }
    
    if (!empty($input['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE email = ? AND id != ?");
        $stmt->execute([$input['email'], $actualId]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Another doctor with this email already exists']);
            return;
        }
    }
    
    try {
        // Build dynamic update query
        $updateFields = [];
        $updateValues = [];
        
        $allowedFields = ['first_name', 'last_name', 'specialization', 'license_number', 'phone', 'email', 'address', 'hospital', 'referral_percentage', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateFields[] = "$field = ?";
                if ($field === 'referral_percentage') {
                    $updateValues[] = floatval($input[$field]);
                } else {
                    $updateValues[] = $input[$field] === '' ? null : trim($input[$field]);
                }
            }
        }
        
        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
            return;
        }
        
        $updateValues[] = $actualId;
        
        $sql = "UPDATE doctors SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($updateValues);
        
        // Get updated doctor
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$actualId]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Doctor updated successfully',
            'data' => $doctor
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function handleDelete($pdo, $input) {
    // Check if ID is provided
    if (!isset($input['doctor_id']) && !isset($input['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    $doctorId = $input['doctor_id'] ?? $input['id'];
    
    try {
        // Check if doctor exists
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM doctors WHERE id = ? OR doctor_id = ?");
        $stmt->execute([$doctorId, $doctorId]);
        $doctor = $stmt->fetch();
        
        if (!$doctor) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Doctor not found']);
            return;
        }
        
        // Check if doctor has any orders (you might want to prevent deletion if they do)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE doctor_id = ?");
        $stmt->execute([$doctor['id']]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            // Instead of deleting, set status to inactive
            $stmt = $pdo->prepare("UPDATE doctors SET status = 'inactive' WHERE id = ?");
            $stmt->execute([$doctor['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Doctor has existing orders, so status was changed to inactive instead of deletion'
            ]);
        } else {
            // Safe to delete
            $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
            $stmt->execute([$doctor['id']]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Doctor deleted successfully'
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function generateDoctorId($pdo) {
    do {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM doctors");
        $stmt->execute();
        $count = $stmt->fetchColumn();
        $doctorId = 'DOC' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        // Check if this ID already exists
        $stmt = $pdo->prepare("SELECT id FROM doctors WHERE doctor_id = ?");
        $stmt->execute([$doctorId]);
        $exists = $stmt->fetch();
    } while ($exists);
    
    return $doctorId;
}
?>
