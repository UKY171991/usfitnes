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
    0 => 'order_number',
    1 => 'patient_name',
    2 => 'doctor_name',
    3 => 'test_count',
    4 => 'status',
    5 => 'priority',
    6 => 'order_date',
    7 => 'actions'
];

$orderBy = isset($columns[$orderColumn]) ? $columns[$orderColumn] : 'to.created_at';
if ($orderBy === 'patient_name') {
    $orderBy = 'p.first_name';
} elseif ($orderBy === 'doctor_name') {
    $orderBy = 'd.name';
}

try {
    // Base query
    $baseQuery = "FROM test_orders to
                  LEFT JOIN patients p ON to.patient_id = p.id
                  LEFT JOIN doctors d ON to.doctor_id = d.id
                  WHERE 1=1";
    $params = [];
    
    // Search functionality
    if (!empty($searchValue)) {
        $baseQuery .= " AND (
            to.order_number LIKE ? OR 
            p.first_name LIKE ? OR 
            p.last_name LIKE ? OR 
            d.name LIKE ? OR
            to.status LIKE ? OR
            to.priority LIKE ?
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
            to.id,
            to.order_number,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            p.patient_id,
            d.name as doctor_name,
            to.status,
            to.priority,
            to.total_amount,
            to.order_date,
            to.created_at,
            (SELECT COUNT(*) FROM test_order_items WHERE test_order_id = to.id) as test_count
        $baseQuery
        ORDER BY $orderBy $orderDir
        LIMIT ? OFFSET ?
    ";
    
    $params[] = $length;
    $params[] = $start;
    
    $dataStmt = $pdo->prepare($dataQuery);
    $dataStmt->execute($params);
    $orders = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($orders as $order) {
        // Status badge
        $statusClasses = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger'
        ];
        $statusClass = $statusClasses[$order['status']] ?? 'secondary';
        $statusBadge = "<span class='badge badge-$statusClass'>" . ucfirst($order['status']) . "</span>";
        
        // Priority badge
        $priorityClasses = [
            'normal' => 'secondary',
            'high' => 'warning',
            'urgent' => 'danger'
        ];
        $priorityClass = $priorityClasses[$order['priority']] ?? 'secondary';
        $priorityBadge = "<span class='badge badge-$priorityClass'>" . ucfirst($order['priority']) . "</span>";
        
        // Actions buttons
        $actions = "
            <div class='btn-group btn-group-sm' role='group'>
                <button type='button' class='btn btn-success btn-sm' onclick='viewTestOrder({$order['id']})' title='View' data-toggle='tooltip'>
                    <i class='fas fa-eye'></i>
                </button>
        ";
        
        if ($order['status'] !== 'cancelled' && $order['status'] !== 'completed') {
            $actions .= "
                <button type='button' class='btn btn-info btn-sm' onclick='editTestOrder({$order['id']})' title='Edit' data-toggle='tooltip'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' onclick='deleteTestOrder({$order['id']})' title='Cancel' data-toggle='tooltip'>
                    <i class='fas fa-times'></i>
                </button>
            ";
        }
        
        $actions .= "</div>";
        
        $data[] = [
            'order_number' => htmlspecialchars($order['order_number']),
            'patient_name' => htmlspecialchars($order['patient_name']) . 
                            '<br><small class="text-muted">' . htmlspecialchars($order['patient_id']) . '</small>',
            'doctor_name' => htmlspecialchars($order['doctor_name'] ?? 'Not assigned'),
            'test_count' => $order['test_count'] . ' test' . ($order['test_count'] != 1 ? 's' : ''),
            'status' => $statusBadge,
            'priority' => $priorityBadge,
            'order_date' => date('M d, Y H:i', strtotime($order['order_date'])),
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
    error_log("Test Orders DataTable Error: " . $e->getMessage());
    
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