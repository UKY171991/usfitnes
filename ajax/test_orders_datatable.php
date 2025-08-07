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
    $columns = ['to.id', 'patient_name', 'to.test_type', 'doctor_name', 'to.status', 'to.urgency', 'to.created_at'];
    $orderBy = $columns[$orderColumn] ?? 'to.id';
    
    // Base query with joins
    $baseQuery = "
        FROM test_orders to
        LEFT JOIN patients p ON to.patient_id = p.id
        LEFT JOIN doctors d ON to.doctor_id = d.id
        WHERE 1=1
    ";
    $params = [];
    
    // Search functionality
    if (!empty($search)) {
        $baseQuery .= " AND (
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR
            to.test_type LIKE ? OR 
            CONCAT(d.first_name, ' ', d.last_name) LIKE ? OR
            to.status LIKE ?
        )";
        $searchParam = "%$search%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    // Count total records
    $totalRecords = $conn->query("SELECT COUNT(*) FROM test_orders")->fetchColumn();
    
    // Count filtered records
    $filteredQuery = "SELECT COUNT(*) $baseQuery";
    $stmt = $conn->prepare($filteredQuery);
    $stmt->execute($params);
    $filteredRecords = $stmt->fetchColumn();
    
    // Get data
    $dataQuery = "
        SELECT 
            to.id,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            to.test_type,
            CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
            to.status,
            to.urgency,
            to.created_at,
            to.updated_at
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
            'patient_name' => htmlspecialchars($row['patient_name'] ?? 'Unknown Patient'),
            'test_type' => htmlspecialchars($row['test_type']),
            'doctor_name' => htmlspecialchars($row['doctor_name'] ?? 'Unknown Doctor'),
            'status_badge' => generateStatusBadge($row['status']),
            'urgency' => $row['urgency'],
            'created_date' => date('M j, Y', strtotime($row['created_at'])),
            'actions' => generateActionButtons($row['id'], $row['status'])
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

function generateStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'in_progress' => '<span class="badge badge-info">In Progress</span>',
        'completed' => '<span class="badge badge-success">Completed</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge badge-secondary">Unknown</span>';
}

function generateActionButtons($id, $status) {
    $buttons = '
        <div class="btn-group btn-group-sm">
            <button class="btn btn-info" onclick="viewTestOrder(' . $id . ')" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-warning" onclick="editTestOrder(' . $id . ')" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
    ';
    
    if ($status !== 'completed') {
        $buttons .= '
            <button class="btn btn-primary" onclick="updateOrderStatus(' . $id . ')" title="Update Status">
                <i class="fas fa-check"></i>
            </button>
        ';
    }
    
    $buttons .= '
            <button class="btn btn-danger" onclick="deleteTestOrder(' . $id . ')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    ';
    
    return $buttons;
}
?>
