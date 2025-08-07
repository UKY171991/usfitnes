<?php
require_once '../config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    // Check if database connection exists
    if (!isset($pdo) || !$pdo instanceof PDO) {
        // Return empty data for development
        echo json_encode([
            'draw' => intval($_POST['draw'] ?? 1),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Database connection not available'
        ]);
        exit;
    }
    
    // DataTables parameters
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';
    $orderColumn = $_POST['order'][0]['column'] ?? 0;
    $orderDir = $_POST['order'][0]['dir'] ?? 'desc';
    
    // Column mapping
    $columns = ['id', 'full_name', 'phone', 'email', 'age', 'gender', 'status', 'actions'];
    $orderBy = $columns[$orderColumn] ?? 'id';
    
    // Base query
    $whereClause = "WHERE 1=1";
    $params = [];
    
    // Search functionality
    if (!empty($search)) {
        $whereClause .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search 
                              OR phone LIKE :search 
                              OR email LIKE :search 
                              OR gender LIKE :search 
                              OR status LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Count total records
    $totalQuery = "SELECT COUNT(*) as count FROM patients $whereClause";
    $stmt = $pdo->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get data with pagination
    $dataQuery = "SELECT id, first_name, last_name, phone, email, date_of_birth, gender, status, address, created_at
                  FROM patients 
                  $whereClause 
                  ORDER BY $orderBy $orderDir 
                  LIMIT :start, :length";
    
    $stmt = $pdo->prepare($dataQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($patients as $patient) {
        // Calculate age
        $age = '';
        if (!empty($patient['date_of_birth'])) {
            $dob = new DateTime($patient['date_of_birth']);
            $now = new DateTime();
            $age = $now->diff($dob)->y;
        }
        
        // Status badge
        $statusClass = $patient['status'] === 'Active' ? 'success' : 'secondary';
        $statusBadge = '<span class="badge badge-' . $statusClass . '">' . htmlspecialchars($patient['status']) . '</span>';
        
        // Actions
        $actions = '
            <div class="btn-group btn-group-sm">
                <button class="btn btn-info btn-sm" onclick="editPatient(' . $patient['id'] . ')" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="deletePatient(' . $patient['id'] . ')" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>';
        
        $data[] = [
            'id' => $patient['id'],
            'full_name' => htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']),
            'phone' => htmlspecialchars($patient['phone']),
            'email' => htmlspecialchars($patient['email'] ?: '-'),
            'age' => $age ?: '-',
            'gender' => htmlspecialchars($patient['gender']),
            'status' => $statusBadge,
            'actions' => $actions
        ];
    }
    
    // Response
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'draw' => intval($_POST['draw'] ?? 1),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
    $params = [];
    
    // Search functionality
    if (!empty($search)) {
        $baseQuery .= " AND (
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            phone LIKE ? OR 
            email LIKE ?
        )";
        $searchParam = "%$search%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    // Count total records
    $totalRecords = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    
    // Count filtered records
    $filteredQuery = "SELECT COUNT(*) $baseQuery";
    $stmt = $conn->prepare($filteredQuery);
    $stmt->execute($params);
    $filteredRecords = $stmt->fetchColumn();
    
    // Get data
    $dataQuery = "
        SELECT 
            id,
            CONCAT(first_name, ' ', last_name) as name,
            phone,
            COALESCE(email, '-') as email,
            TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age,
            gender,
            status,
            date_of_birth
        $baseQuery 
        ORDER BY $orderBy $orderDir 
        LIMIT $start, $length
    ";
    
    $stmt = $conn->prepare($dataQuery);
    $stmt->execute($params);
    $data = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            'id' => (int)$row['id'],
            'name' => htmlspecialchars($row['name']),
            'phone' => htmlspecialchars($row['phone']),
            'email' => htmlspecialchars($row['email']),
            'age' => (int)$row['age'],
            'gender' => $row['gender'],
            'status' => $row['status'],
            'actions' => generateActionButtons($row['id'])
        ];
    }
    
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int)$totalRecords,
        'recordsFiltered' => (int)$filteredRecords,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

function generateActionButtons($id) {
    return '
        <div class="btn-group btn-group-sm">
            <button class="btn btn-info" onclick="viewPatient(' . $id . ')" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-warning" onclick="editPatient(' . $id . ')" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-danger" onclick="deletePatient(' . $id . ')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    ';
}
?>
