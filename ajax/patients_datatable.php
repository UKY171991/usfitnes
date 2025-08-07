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
    0 => 'patient_id',
    1 => 'full_name',
    2 => 'phone',
    3 => 'email',
    4 => 'blood_group',
    5 => 'status',
    6 => 'actions'
];

$orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'created_at';
if ($orderBy === 'full_name') {
    $orderBy = 'first_name';
}

try {
    // Base query
    $baseQuery = "FROM patients WHERE 1=1";
    $params = [];
    
    // Search functionality
    if (!empty($searchValue)) {
        $baseQuery .= " AND (
            patient_id LIKE ? OR 
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            phone LIKE ? OR 
            email LIKE ? OR
            blood_group LIKE ?
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
            patient_id,
            first_name,
            last_name,
            CONCAT(first_name, ' ', last_name) as full_name,
            phone,
            email,
            blood_group,
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
    $patients = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($patients as $patient) {
        // Status badge
        $statusClass = $patient['status'] === 'active' ? 'success' : 'secondary';
        $statusBadge = "<span class='badge badge-$statusClass'>" . ucfirst($patient['status']) . "</span>";
        
        // Actions buttons
        $actions = "
            <div class='btn-group btn-group-sm'>
                <button type='button' class='btn btn-info btn-sm' onclick='openPatientModal({$patient['id']})' title='Edit'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-success btn-sm' onclick='viewPatient({$patient['id']})' title='View'>
                    <i class='fas fa-eye'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' onclick='deletePatient({$patient['id']})' title='Delete'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
        
        $data[] = [
            'patient_id' => htmlspecialchars($patient['patient_id']),
            'full_name' => htmlspecialchars($patient['full_name']),
            'phone' => htmlspecialchars($patient['phone'] ?? ''),
            'email' => htmlspecialchars($patient['email'] ?? ''),
            'blood_group' => htmlspecialchars($patient['blood_group'] ?? ''),
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
    error_log("Patients DataTable Error: " . $e->getMessage());
    
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