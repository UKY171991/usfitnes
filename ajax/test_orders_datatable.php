<?php
require_once '../config.php';
require_once '../includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    // DataTables server-side processing
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search_value = $_POST['search']['value'] ?? '';
    
    // Order settings
    $order_column_index = intval($_POST['order'][0]['column'] ?? 0);
    $order_direction = $_POST['order'][0]['dir'] ?? 'desc';
    
    // Column mapping for ordering
    $columns = ['to.id', 'patient_name', 'doctor_name', 'to.test_type', 'to.priority', 'to.status', 'to.order_date', 'actions'];
    $order_column = $columns[$order_column_index] ?? 'to.id';
    
    // Validate order direction
    $order_direction = in_array(strtolower($order_direction), ['asc', 'desc']) ? $order_direction : 'desc';
    
    // Base query with search
    $where_clause = "WHERE (to.status != 'deleted' OR to.status IS NULL)";
    $params = [];
    
    if (!empty($search_value)) {
        $where_clause .= " AND (
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR
            CONCAT(d.first_name, ' ', d.last_name) LIKE ? OR
            to.test_type LIKE ? OR
            to.priority LIKE ? OR
            to.status LIKE ? OR
            to.order_date LIKE ?
        )";
        $search_param = "%{$search_value}%";
        $params = array_fill(0, 6, $search_param);
    }
    
    // Count total records
    $total_query = "
        SELECT COUNT(*) as total 
        FROM test_orders to
        LEFT JOIN patients p ON to.patient_id = p.id
        LEFT JOIN doctors d ON to.doctor_id = d.id
        {$where_clause}
    ";
    $stmt = $pdo->prepare($total_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    // Get filtered data with proper ordering
    $data_query = "
        SELECT 
            to.id,
            to.patient_id,
            to.doctor_id,
            CONCAT(COALESCE(p.first_name, ''), ' ', COALESCE(p.last_name, '')) as patient_name,
            CONCAT(COALESCE(d.first_name, ''), ' ', COALESCE(d.last_name, '')) as doctor_name,
            to.test_type,
            to.priority,
            COALESCE(to.status, 'Pending') as status,
            to.order_date,
            to.notes,
            to.created_at
        FROM test_orders to
        LEFT JOIN patients p ON to.patient_id = p.id
        LEFT JOIN doctors d ON to.doctor_id = d.id
        {$where_clause}
        ORDER BY {$order_column} {$order_direction}
        LIMIT {$start}, {$length}
    ";
    
    $stmt = $pdo->prepare($data_query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($orders as $order) {
        $status_class = match(strtolower($order['status'])) {
            'pending' => 'warning',
            'in progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
        $status_badge = "<span class='badge badge-{$status_class}'>{$order['status']}</span>";
        
        $priority_class = match(strtolower($order['priority'])) {
            'normal' => 'secondary',
            'urgent' => 'warning',
            'emergency' => 'danger',
            default => 'secondary'
        };
        $priority_badge = "<span class='badge badge-{$priority_class}'>{$order['priority']}</span>";
        
        $actions = "
            <div class='btn-group btn-group-sm'>
                <button type='button' class='btn btn-info btn-sm' onclick='editTestOrder({$order['id']})' title='Edit'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' data-action='delete' data-id='{$order['id']}' title='Delete'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
        
        // Generate order ID display
        $order_id_display = sprintf('ORD%05d', $order['id']);
        
        $data[] = [
            'order_id' => $order_id_display,
            'patient_name' => htmlspecialchars(trim($order['patient_name']) ?: 'Unknown Patient'),
            'doctor_name' => htmlspecialchars(trim($order['doctor_name']) ?: 'No Doctor'),
            'test_type' => htmlspecialchars($order['test_type'] ?: 'N/A'),
            'priority' => $priority_badge,
            'status' => $status_badge,
            'order_date' => date('M d, Y', strtotime($order['order_date'])),
            'actions' => $actions
        ];
    }
    
    // Return JSON response
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $total_records,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    error_log("Test Orders DataTable error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error occurred'
    ]);
}
?>
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
