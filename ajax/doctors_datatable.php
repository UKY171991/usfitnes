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
    $columns = ['id', 'full_name', 'specialization', 'phone', 'email', 'license_number', 'status', 'actions'];
    $order_column = $columns[$order_column_index] ?? 'id';
    
    // Validate order direction
    $order_direction = in_array(strtolower($order_direction), ['asc', 'desc']) ? $order_direction : 'desc';
    
    // Base query with search
    $where_clause = "WHERE (status != 'deleted' OR status IS NULL)";
    $params = [];
    
    if (!empty($search_value)) {
        $where_clause .= " AND (
            CONCAT(first_name, ' ', last_name) LIKE ? OR
            specialization LIKE ? OR
            phone LIKE ? OR
            email LIKE ? OR
            license_number LIKE ? OR
            status LIKE ?
        )";
        $search_param = "%{$search_value}%";
        $params = array_fill(0, 6, $search_param);
    }
    
    // Count total records
    $total_query = "SELECT COUNT(*) as total FROM doctors {$where_clause}";
    $stmt = $pdo->prepare($total_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    // Get filtered data with proper ordering
    $data_query = "
        SELECT 
            id,
            first_name,
            last_name,
            CONCAT(first_name, ' ', last_name) as full_name,
            specialization,
            phone,
            email,
            license_number,
            qualification,
            COALESCE(status, 'Active') as status,
            created_at
        FROM doctors 
        {$where_clause}
        ORDER BY {$order_column} {$order_direction}
        LIMIT {$start}, {$length}
    ";
    
    $stmt = $pdo->prepare($data_query);
    $stmt->execute($params);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($doctors as $doctor) {
        $status_class = strtolower($doctor['status']) === 'active' ? 'success' : 'secondary';
        $status_badge = "<span class='badge badge-{$status_class}'>{$doctor['status']}</span>";
        
        $actions = "
            <div class='btn-group btn-group-sm'>
                <button type='button' class='btn btn-info btn-sm' onclick='editDoctor({$doctor['id']})' title='Edit'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' onclick='deleteDoctor({$doctor['id']})' title='Delete'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
        
        $data[] = [
            'id' => $doctor['id'],
            'full_name' => htmlspecialchars($doctor['full_name']),
            'specialization' => htmlspecialchars($doctor['specialization'] ?: 'Not specified'),
            'phone' => htmlspecialchars($doctor['phone'] ?: ''),
            'email' => htmlspecialchars($doctor['email'] ?: ''),
            'license_number' => htmlspecialchars($doctor['license_number'] ?: ''),
            'status' => $status_badge,
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
    error_log("Doctors DataTable error: " . $e->getMessage());
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
