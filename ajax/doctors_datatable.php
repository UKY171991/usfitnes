<?php
require_once '../config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// DataTables server-side processing
$draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 25;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$orderColumn = isset($_POST['order'][0]['column']) ? (int)$_POST['order'][0]['column'] : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';

// Column mapping
$columns = [
    0 => 'doctor_id',
    1 => 'name',
    2 => 'specialization',
    3 => 'phone',
    4 => 'email',
    5 => 'status',
    6 => 'actions'
];

$orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'created_at';

try {
    // Base query
    $baseQuery = "FROM doctors WHERE 1=1";
    $params = [];
    
    // Search functionality
    if (!empty($searchValue)) {
        $baseQuery .= " AND (
            doctor_id LIKE ? OR 
            name LIKE ? OR 
            specialization LIKE ? OR 
            phone LIKE ? OR 
            email LIKE ? OR
            hospital LIKE ?
        )";
        $searchTerm = "%$searchValue%";
        $params = array_fill(0, 6, $searchTerm);
    }
    
    // Get total records count
    $totalQuery = "SELECT COUNT(*) as total $baseQuery";
    $totalStmt = $pdo->prepare($totalQuery);
    $totalStmt->execute($params);
    $totalRecords = $totalStmt->fetch()['total'];
    
    // Get filtered records count (same as total if no search)
    $filteredRecords = $totalRecords;
    
    // Get actual data
    $dataQuery = "
        SELECT 
            id,
            doctor_id,
            name,
            specialization,
            phone,
            email,
            hospital,
            status,
            created_at
        $baseQuery
        ORDER BY $orderBy $orderDir
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $length;
    $params[] = $start;
    
    $dataStmt = $pdo->prepare($dataQuery);
    $dataStmt->execute($params);
    $doctors = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($doctors as $doctor) {
        // Status badge
        $statusClass = $doctor['status'] === 'active' ? 'success' : 'secondary';
        $statusBadge = "<span class='badge badge-$statusClass'>" . ucfirst($doctor['status'] ?? 'active') . "</span>";
        
        // Actions buttons
        $actions = "
            <div class='btn-group btn-group-sm' role='group'>
                <button type='button' class='btn btn-info btn-sm' onclick='editDoctor({$doctor['id']})' title='Edit' data-toggle='tooltip'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-success btn-sm' onclick='viewDoctor({$doctor['id']})' title='View' data-toggle='tooltip'>
                    <i class='fas fa-eye'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' onclick='deleteDoctor({$doctor['id']})' title='Delete' data-toggle='tooltip'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
        
        $data[] = [
            'doctor_id' => htmlspecialchars($doctor['doctor_id'] ?? ''),
            'name' => htmlspecialchars($doctor['name']),
            'specialization' => htmlspecialchars($doctor['specialization']),
            'phone' => htmlspecialchars($doctor['phone'] ?? ''),
            'email' => htmlspecialchars($doctor['email'] ?? ''),
            'status' => $statusBadge,
            'actions' => $actions
        ];
    }
    
    // Response for DataTables
    $response = [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Doctors DataTable Error: " . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error occurred'
    ]);
}
?>