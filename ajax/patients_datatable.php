<?php
require_once '../config.php';
require_once '../includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $conn = getDbConnection();
    
    // DataTable parameters
    $draw = (int)($_POST['draw'] ?? 1);
    $start = (int)($_POST['start'] ?? 0);
    $length = (int)($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';
    $orderColumn = (int)($_POST['order'][0]['column'] ?? 0);
    $orderDir = $_POST['order'][0]['dir'] ?? 'desc';
    
    // Column mapping
    $columns = ['id', 'name', 'phone', 'email', 'age', 'gender', 'status'];
    $orderBy = $columns[$orderColumn] ?? 'id';
    
    // Base query
    $baseQuery = "FROM patients WHERE 1=1";
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
