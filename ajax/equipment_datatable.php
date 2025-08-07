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
    0 => 'equipment_code',
    1 => 'equipment_name',
    2 => 'equipment_type',
    3 => 'location',
    4 => 'status',
    5 => 'last_maintenance',
    6 => 'actions'
];

$orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'created_at';

try {
    // Base query
    $baseQuery = "FROM equipment WHERE 1=1";
    $params = [];
    
    // Search functionality
    if (!empty($searchValue)) {
        $baseQuery .= " AND (
            equipment_code LIKE ? OR 
            equipment_name LIKE ? OR 
            equipment_type LIKE ? OR 
            manufacturer LIKE ? OR 
            location LIKE ? OR
            model LIKE ?
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
            equipment_code,
            equipment_name,
            equipment_type,
            model,
            manufacturer,
            location,
            status,
            last_maintenance,
            next_maintenance,
            created_at
        $baseQuery
        ORDER BY $orderBy $orderDir
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $length;
    $params[] = $start;
    
    $dataStmt = $pdo->prepare($dataQuery);
    $dataStmt->execute($params);
    $equipment = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($equipment as $item) {
        // Status badge
        $statusClasses = [
            'active' => 'success',
            'inactive' => 'secondary',
            'maintenance' => 'warning',
            'broken' => 'danger'
        ];
        $statusClass = $statusClasses[$item['status']] ?? 'secondary';
        $statusBadge = "<span class='badge badge-$statusClass'>" . ucfirst($item['status']) . "</span>";
        
        // Last maintenance
        $lastMaintenance = $item['last_maintenance'] ? 
            date('M d, Y', strtotime($item['last_maintenance'])) : 
            '<span class="text-muted">Never</span>';
        
        // Check if maintenance is due
        if ($item['next_maintenance'] && strtotime($item['next_maintenance']) <= time()) {
            $lastMaintenance .= '<br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Maintenance Due</small>';
        }
        
        // Actions buttons
        $actions = "
            <div class='btn-group btn-group-sm'>
                <button type='button' class='btn btn-info btn-sm' onclick='openEquipmentModal({$item['id']})' title='Edit'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-success btn-sm' onclick='viewEquipment({$item['id']})' title='View'>
                    <i class='fas fa-eye'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' onclick='deleteEquipment({$item['id']})' title='Delete'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
        
        $data[] = [
            'equipment_code' => htmlspecialchars($item['equipment_code']),
            'equipment_name' => htmlspecialchars($item['equipment_name']) . 
                              ($item['model'] ? '<br><small class="text-muted">' . htmlspecialchars($item['model']) . '</small>' : ''),
            'equipment_type' => htmlspecialchars($item['equipment_type'] ?? ''),
            'location' => htmlspecialchars($item['location'] ?? ''),
            'status' => $statusBadge,
            'last_maintenance' => $lastMaintenance,
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
    error_log("Equipment DataTable Error: " . $e->getMessage());
    
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