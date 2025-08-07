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
    $columns = ['id', 'name', 'model', 'category', 'serial_number', 'status', 'maintenance_status', 'actions'];
    $order_column = $columns[$order_column_index] ?? 'id';
    
    // Validate order direction
    $order_direction = in_array(strtolower($order_direction), ['asc', 'desc']) ? $order_direction : 'desc';
    
    // Base query with search
    $where_clause = "WHERE (status != 'deleted' OR status IS NULL)";
    $params = [];
    
    if (!empty($search_value)) {
        $where_clause .= " AND (
            name LIKE ? OR
            model LIKE ? OR
            category LIKE ? OR
            serial_number LIKE ? OR
            manufacturer LIKE ? OR
            status LIKE ?
        )";
        $search_param = "%{$search_value}%";
        $params = array_fill(0, 6, $search_param);
    }
    
    // Count total records
    $total_query = "SELECT COUNT(*) as total FROM equipment {$where_clause}";
    $stmt = $pdo->prepare($total_query);
    $stmt->execute($params);
    $total_records = $stmt->fetch()['total'];
    
    // Get filtered data with proper ordering
    $data_query = "
        SELECT 
            id,
            name,
            model,
            category,
            serial_number,
            manufacturer,
            purchase_date,
            warranty_expiry,
            last_maintenance,
            next_maintenance,
            COALESCE(status, 'Active') as status,
            description,
            created_at
        FROM equipment 
        {$where_clause}
        ORDER BY {$order_column} {$order_direction}
        LIMIT {$start}, {$length}
    ";
    
    $stmt = $pdo->prepare($data_query);
    $stmt->execute($params);
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($equipment as $item) {
        $status_class = match(strtolower($item['status'])) {
            'active' => 'success',
            'maintenance' => 'warning',
            'inactive' => 'secondary',
            default => 'secondary'
        };
        $status_badge = "<span class='badge badge-{$status_class}'>{$item['status']}</span>";
        
        // Check maintenance status
        $maintenance_status = 'N/A';
        $maintenance_class = 'secondary';
        
        if ($item['next_maintenance']) {
            $next_maintenance = new DateTime($item['next_maintenance']);
            $today = new DateTime();
            $days_until = $today->diff($next_maintenance)->days;
            
            if ($next_maintenance < $today) {
                $maintenance_status = 'Overdue';
                $maintenance_class = 'danger';
            } elseif ($days_until <= 30) {
                $maintenance_status = 'Due Soon';
                $maintenance_class = 'warning';
            } else {
                $maintenance_status = 'Scheduled';
                $maintenance_class = 'success';
            }
        }
        
        $maintenance_badge = "<span class='badge badge-{$maintenance_class}'>{$maintenance_status}</span>";
        
        $actions = "
            <div class='btn-group btn-group-sm'>
                <button type='button' class='btn btn-info btn-sm' onclick='editEquipment({$item['id']})' title='Edit'>
                    <i class='fas fa-edit'></i>
                </button>
                <button type='button' class='btn btn-danger btn-sm' data-action='delete' data-id='{$item['id']}' title='Delete'>
                    <i class='fas fa-trash'></i>
                </button>
            </div>
        ";
        
        $data[] = [
            'id' => $item['id'],
            'name' => htmlspecialchars($item['name']),
            'model' => htmlspecialchars($item['model'] ?: 'N/A'),
            'category' => htmlspecialchars($item['category'] ?: 'N/A'),
            'serial_number' => htmlspecialchars($item['serial_number'] ?: 'N/A'),
            'status' => $status_badge,
            'maintenance_status' => $maintenance_badge,
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
    error_log("Equipment DataTable error: " . $e->getMessage());
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
