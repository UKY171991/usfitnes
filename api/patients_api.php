<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session to check authentication
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

class PatientsAPI {
    private $pdo;
    
    public function __construct($connection) {
        $this->pdo = $connection;
    }
    
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_REQUEST['action'] ?? '';
        
        try {
            switch ($method) {
                case 'GET':
                    return $this->handleGet($action);
                case 'POST':
                    return $this->handlePost($action);
                default:
                    http_response_code(405);
                    return $this->errorResponse('Method not allowed');
            }
        } catch (Exception $e) {
            http_response_code(500);
            return $this->errorResponse('Server error: ' . $e->getMessage());
        }
    }
    
    private function handleGet($action) {
        switch ($action) {
            case 'list':
                return $this->getPatientsList();
            case 'stats':
                return $this->getPatientStats();
            case 'get':
                return $this->getPatient();
            case 'export':
                return $this->exportPatients();
            default:
                return $this->errorResponse('Invalid action');
        }
    }
    
    private function handlePost($action) {
        switch ($action) {
            case 'add':
                return $this->addPatient();
            case 'update':
                return $this->updatePatient();
            case 'delete':
                return $this->deletePatient();
            default:
                return $this->errorResponse('Invalid action');
        }
    }
    
    private function getPatientsList() {
        try {
            $sql = "SELECT p.*, 
                           TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) as age
                    FROM patients p 
                    ORDER BY p.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Generate patient ID if not exists
            foreach ($patients as &$patient) {
                if (!$patient['patient_id'] || empty($patient['patient_id'])) {
                    $patient['patient_id'] = 'PAT' . str_pad($patient['id'], 6, '0', STR_PAD_LEFT);
                }
            }
            
            return $this->successResponse($patients);
            
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching patients: ' . $e->getMessage());
        }
    }
    
    private function getPatientStats() {
        try {
            $stats = [];
            
            // Total patients
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM patients");
            $stmt->execute();
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Today's registrations
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as today FROM patients WHERE DATE(created_at) = CURDATE()");
            $stmt->execute();
            $stats['today'] = $stmt->fetch(PDO::FETCH_ASSOC)['today'] ?? 0;
            
            // Male patients
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as male FROM patients WHERE gender = 'male'");
            $stmt->execute();
            $stats['male'] = $stmt->fetch(PDO::FETCH_ASSOC)['male'] ?? 0;
            
            // Female patients
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as female FROM patients WHERE gender = 'female'");
            $stmt->execute();
            $stats['female'] = $stmt->fetch(PDO::FETCH_ASSOC)['female'] ?? 0;
            
            return $this->successResponse($stats);
            
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching statistics: ' . $e->getMessage());
        }
    }
    
    private function getPatient() {
        $id = intval($_GET['id'] ?? 0);
        
        if (!$id) {
            return $this->errorResponse('Patient ID is required');
        }
        
        try {
            $sql = "SELECT *, TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age FROM patients WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            if ($patient = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Generate patient ID if not exists
                if (!$patient['patient_id'] || empty($patient['patient_id'])) {
                    $patient['patient_id'] = 'PAT' . str_pad($patient['id'], 6, '0', STR_PAD_LEFT);
                }
                return $this->successResponse($patient);
            } else {
                return $this->errorResponse('Patient not found');
            }
            
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching patient: ' . $e->getMessage());
        }
    }
    
    private function addPatient() {
        // Validate required fields
        $name = trim($_POST['name'] ?? '');
        if (empty($name)) {
            return $this->errorResponse('Patient name is required');
        }
        
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $gender = $_POST['gender'] ?? null;
        $address = trim($_POST['address'] ?? '');
        
        // Validate email if provided
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->errorResponse('Invalid email address');
        }
        
        try {
            // Check if patient with same name and phone already exists
            if ($phone) {
                $checkStmt = $this->pdo->prepare("SELECT id FROM patients WHERE name = ? AND phone = ?");
                $checkStmt->execute([$name, $phone]);
                
                if ($checkStmt->fetch()) {
                    return $this->errorResponse('Patient with this name and phone number already exists');
                }
            }
            
            // Generate patient ID
            $stmt = $this->pdo->prepare("SELECT MAX(id) as max_id FROM patients");
            $stmt->execute();
            $maxId = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'] ?? 0;
            $patientId = 'PAT' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO patients (patient_id, name, phone, email, date_of_birth, gender, address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$patientId, $name, $phone, $email, $date_of_birth, $gender, $address]);
            
            if ($result) {
                return $this->successResponse(['id' => $this->pdo->lastInsertId()], 'Patient added successfully');
            } else {
                return $this->errorResponse('Failed to add patient');
            }
            
        } catch (Exception $e) {
            return $this->errorResponse('Error adding patient: ' . $e->getMessage());
        }
    }
    
    private function updatePatient() {
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        
        if (!$id) {
            return $this->errorResponse('Patient ID is required');
        }
        
        if (empty($name)) {
            return $this->errorResponse('Patient name is required');
        }
        
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $gender = $_POST['gender'] ?? null;
        $address = trim($_POST['address'] ?? '');
        
        // Validate email if provided
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->errorResponse('Invalid email address');
        }
        
        try {
            // Check if patient exists
            $checkStmt = $this->pdo->prepare("SELECT id FROM patients WHERE id = ?");
            $checkStmt->execute([$id]);
            
            if (!$checkStmt->fetch()) {
                return $this->errorResponse('Patient not found');
            }
            
            $sql = "UPDATE patients SET name = ?, phone = ?, email = ?, date_of_birth = ?, gender = ?, address = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$name, $phone, $email, $date_of_birth, $gender, $address, $id]);
            
            if ($result) {
                return $this->successResponse(['id' => $id], 'Patient updated successfully');
            } else {
                return $this->errorResponse('Failed to update patient');
            }
            
        } catch (Exception $e) {
            return $this->errorResponse('Error updating patient: ' . $e->getMessage());
        }
    }
    
    private function deletePatient() {
        $id = intval($_POST['id'] ?? 0);
        
        if (!$id) {
            return $this->errorResponse('Patient ID is required');
        }
        
        try {
            // Check if patient exists
            $checkStmt = $this->pdo->prepare("SELECT name FROM patients WHERE id = ?");
            $checkStmt->execute([$id]);
            
            $patient = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if (!$patient) {
                return $this->errorResponse('Patient not found');
            }
            
            $stmt = $this->pdo->prepare("DELETE FROM patients WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                return $this->successResponse(['id' => $id], 'Patient "' . $patient['name'] . '" deleted successfully');
            } else {
                return $this->errorResponse('Failed to delete patient');
            }
            
        } catch (Exception $e) {
            return $this->errorResponse('Error deleting patient: ' . $e->getMessage());
        }
    }
    
    private function exportPatients() {
        try {
            $sql = "SELECT patient_id, name, phone, email, date_of_birth, gender, address, created_at FROM patients ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="patients_export_' . date('Y-m-d_H-i-s') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($output, ['Patient ID', 'Name', 'Phone', 'Email', 'Date of Birth', 'Gender', 'Address', 'Registered Date']);
            
            foreach ($patients as $patient) {
                fputcsv($output, [
                    $patient['patient_id'] ?? 'PAT' . str_pad($patient['id'], 6, '0', STR_PAD_LEFT),
                    $patient['name'],
                    $patient['phone'],
                    $patient['email'],
                    $patient['date_of_birth'],
                    $patient['gender'],
                    $patient['address'],
                    $patient['created_at']
                ]);
            }
            
            fclose($output);
            exit;
            
        } catch (Exception $e) {
            return $this->errorResponse('Error exporting patients: ' . $e->getMessage());
        }
    }
    
    private function successResponse($data = null, $message = 'Success') {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }
    
    private function errorResponse($message = 'An error occurred') {
        return [
            'success' => false,
            'message' => $message,
            'data' => null
        ];
    }
}

// Handle the request
try {
    $api = new PatientsAPI($pdo);
    $response = $api->handleRequest();
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>
    switch ($action) {
        case 'list':
            getPatientsLis‍t($pdo);
            break;
        case 'get':
            getPatient($pdo, $_GET['id']);
            break;
        case 'stats':
            getPatientsStats($pdo);
            break;
        case 'export':
            exportPatients($pdo);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePost($pdo, $action) {
    switch ($action) {
        case 'add':
            addPatient($pdo, $_POST);
            break;
        case 'update':
            updatePatient($pdo, $_POST);
            break;
        case 'get':
            getPatient($pdo, $_POST['id']);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

function handlePut($pdo, $input) {
    updatePatient($pdo, $input);
}

function handleDelete($pdo, $input) {
    deletePatient($pdo, $input['id']);
}

function getPatientsLis‍t($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT id, patient_id, name, phone, email, date_of_birth, gender, address, created_at 
            FROM patients 
            ORDER BY created_at DESC
        ");
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $patients
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function getPatient($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            echo json_encode([
                'success' => true,
                'data' => $patient
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Patient not found'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function getPatientsStats($pdo) {
    try {
        // Total patients
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM patients");
        $total = $stmt->fetch()['total'];
        
        // Today's registrations
        $stmt = $pdo->query("SELECT COUNT(*) as today FROM patients WHERE DATE(created_at) = CURDATE()");
        $today = $stmt->fetch()['today'];
        
        // Male patients
        $stmt = $pdo->query("SELECT COUNT(*) as male FROM patients WHERE gender = 'male'");
        $male = $stmt->fetch()['male'];
        
        // Female patients
        $stmt = $pdo->query("SELECT COUNT(*) as female FROM patients WHERE gender = 'female'");
        $female = $stmt->fetch()['female'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'total' => $total,
                'today' => $today,
                'male' => $male,
                'female' => $female
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function addPatient($pdo, $data) {
    try {
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '') ?: null;
        $email = trim($data['email'] ?? '') ?: null;
        $date_of_birth = $data['date_of_birth'] ?: null;
        $gender = $data['gender'] ?: null;
        $address = trim($data['address'] ?? '') ?: null;
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Patient name is required']);
            return;
        }
        
        // Generate unique patient ID
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
        $count = $stmt->fetch()['count'] + 1;
        $patient_id = 'PAT-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        
        // Check if patient ID already exists
        while (true) {
            $checkStmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
            $checkStmt->execute([$patient_id]);
            if (!$checkStmt->fetch()) break;
            $count++;
            $patient_id = 'PAT-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO patients (patient_id, name, phone, email, date_of_birth, gender, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$patient_id, $name, $phone, $email, $date_of_birth, $gender, $address])) {
            echo json_encode([
                'success' => true,
                'message' => 'Patient added successfully',
                'patient_id' => $patient_id
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add patient']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updatePatient($pdo, $data) {
    try {
        $id = $data['id'] ?? '';
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '') ?: null;
        $email = trim($data['email'] ?? '') ?: null;
        $date_of_birth = $data['date_of_birth'] ?: null;
        $gender = $data['gender'] ?: null;
        $address = trim($data['address'] ?? '') ?: null;
        
        if (empty($id) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Patient ID and name are required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE patients 
            SET name = ?, phone = ?, email = ?, date_of_birth = ?, gender = ?, address = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        if ($stmt->execute([$name, $phone, $email, $date_of_birth, $gender, $address, $id])) {
            echo json_encode(['success' => true, 'message' => 'Patient updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update patient']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deletePatient($pdo, $id) {
    try {
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
            return;
        }
        
        // Check if patient has any test orders first
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM test_orders WHERE patient_id = ?");
        $stmt->execute([$id]);
        $testCount = $stmt->fetch()['count'];
        
        if ($testCount > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Cannot delete patient with existing test orders. Please remove test orders first.'
            ]);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true, 'message' => 'Patient deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete patient']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function exportPatients($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT patient_id, name, phone, email, date_of_birth, gender, address, created_at 
            FROM patients 
            ORDER BY created_at DESC
        ");
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Create CSV content
        $csv = "Patient ID,Name,Phone,Email,Date of Birth,Gender,Address,Registered Date\n";
        
        foreach ($patients as $patient) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $patient['patient_id'],
                $patient['name'],
                $patient['phone'] ?? '',
                $patient['email'] ?? '',
                $patient['date_of_birth'] ?? '',
                $patient['gender'] ?? '',
                $patient['address'] ?? '',
                $patient['created_at']
            );
        }
        
        echo json_encode([
            'success' => true,
            'data' => $csv,
            'filename' => 'patients_export_' . date('Y-m-d_H-i-s') . '.csv'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Export error: ' . $e->getMessage()]);
    }
}
?>

function handleGet($pdo) {
    if (isset($_GET['id'])) {
        // Get single patient
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($patient) {
            echo json_encode(['success' => true, 'data' => $patient]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found']);
        }
    } else {
        // Get all patients with pagination for DataTables
        $draw = isset($_GET['draw']) ? (int)$_GET['draw'] : 1;
        $start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
        $length = isset($_GET['length']) ? (int)$_GET['length'] : 10;
        $search = isset($_GET['search']['value']) ? $_GET['search']['value'] : '';
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause = "WHERE full_name LIKE ? OR phone LIKE ? OR patient_id LIKE ?";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Get total count for filtering
        $countStmt = $pdo->prepare("SELECT COUNT(id) FROM patients $whereClause");
        $countStmt->execute($params);
        $recordsFiltered = $countStmt->fetchColumn();

        // Get total count without filtering
        $totalStmt = $pdo->query("SELECT COUNT(id) FROM patients");
        $recordsTotal = $totalStmt->fetchColumn();
        
        // Get patients
        $length = (int)$length;
        $start = (int)$start;
        $sql = "SELECT * FROM patients $whereClause ORDER BY created_at DESC LIMIT $length OFFSET $start";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return DataTables format
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => (int)$recordsTotal,
            'recordsFiltered' => (int)$recordsFiltered,
            'data' => $patients
        ]);
    }
}

function handlePost($pdo, $input) {
    // Handle both JSON input and POST data
    if (empty($input)) {
        $input = $_POST;
    }
    
    // Validate required fields
    $requiredFields = ['full_name', 'date_of_birth', 'gender', 'phone'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
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
            INSERT INTO patients (patient_id, full_name, date_of_birth, gender, phone, address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $patient_id,
            trim($input['full_name']),
            $input['date_of_birth'],
            $input['gender'],
            trim($input['phone']),
            !empty($input['address']) ? trim($input['address']) : null
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
    
    // Validate required fields
    $requiredFields = ['full_name', 'date_of_birth', 'gender', 'phone'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
            return;
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE patients SET 
                full_name = ?, date_of_birth = ?, gender = ?, 
                phone = ?, address = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            trim($input['full_name']),
            $input['date_of_birth'],
            $input['gender'],
            trim($input['phone']),
            !empty($input['address']) ? trim($input['address']) : null,
            $input['id']
        ]);
        
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
