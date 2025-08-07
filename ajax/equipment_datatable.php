<?php
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get DataTable parameters
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search_value = $_POST['search_value'] ?? '';
    $order_column = $_POST['order_column'] ?? 'id';
    $order_dir = $_POST['order_dir'] ?? 'desc';
    
    // Get custom filters
    $status_filter = $_POST['status'] ?? '';
    $category_filter = $_POST['category'] ?? '';
    $manufacturer_filter = $_POST['manufacturer'] ?? '';

    // Validate order direction
    $order_dir = in_array(strtolower($order_dir), ['asc', 'desc']) ? $order_dir : 'desc';
    
    // Allowed columns for ordering
    $allowed_columns = ['id', 'equipment_code', 'equipment_name', 'category', 'manufacturer', 'location', 'status', 'created_at'];
    $order_column = in_array($order_column, $allowed_columns) ? $order_column : 'id';

    // Base query
    $base_query = "FROM equipment WHERE 1=1";
    $params = [];

    // Search functionality
    if (!empty($search_value)) {
        $base_query .= " AND (equipment_code LIKE ? OR equipment_name LIKE ? OR category LIKE ? OR manufacturer LIKE ? OR location LIKE ?)";
        $search_param = "%$search_value%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $base_query .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    // Category filter
    if (!empty($category_filter)) {
        $base_query .= " AND category = ?";
        $params[] = $category_filter;
    }
    
    // Manufacturer filter
    if (!empty($manufacturer_filter)) {
        $base_query .= " AND manufacturer LIKE ?";
        $params[] = "%$manufacturer_filter%";
    }

    // Get total records (without filters)
    $total_query = "SELECT COUNT(*) as total FROM equipment";
    $stmt = $pdo->prepare($total_query);
    $stmt->execute();
    $total_records = $stmt->fetch()['total'];

    // Get filtered records count
    $filtered_query = "SELECT COUNT(*) as total $base_query";
    $stmt = $pdo->prepare($filtered_query);
    $stmt->execute($params);
    $filtered_records = $stmt->fetch()['total'];

    // Get data
    $data_query = "SELECT 
        id,
        equipment_code, 
        equipment_name, 
        equipment_type,
        model,
        serial_number,
        manufacturer,
        category,
        location,
        purchase_date,
        warranty_expiry,
        cost,
        maintenance_schedule,
        last_maintenance,
        next_maintenance,
        description,
        status, 
        created_at,
        updated_at
        $base_query 
        ORDER BY $order_column $order_dir 
        LIMIT $start, $length";
        
    $stmt = $pdo->prepare($data_query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format data for DataTable
    $formatted_data = [];
    foreach ($data as $row) {
        $formatted_data[] = [
            'id' => (int)$row['id'],
            'equipment_code' => htmlspecialchars($row['equipment_code']),
            'equipment_name' => htmlspecialchars($row['equipment_name']),
            'equipment_type' => htmlspecialchars($row['equipment_type'] ?? ''),
            'model' => htmlspecialchars($row['model'] ?? ''),
            'serial_number' => htmlspecialchars($row['serial_number'] ?? ''),
            'manufacturer' => htmlspecialchars($row['manufacturer'] ?? ''),
            'category' => htmlspecialchars($row['category'] ?? ''),
            'location' => htmlspecialchars($row['location'] ?? ''),
            'purchase_date' => $row['purchase_date'],
            'warranty_expiry' => $row['warranty_expiry'],
            'cost' => $row['cost'],
            'maintenance_schedule' => $row['maintenance_schedule'],
            'last_maintenance' => $row['last_maintenance'],
            'next_maintenance' => $row['next_maintenance'],
            'description' => htmlspecialchars($row['description'] ?? ''),
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }

    // Return JSON response
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int)$total_records,
        'recordsFiltered' => (int)$filtered_records,
        'data' => $formatted_data,
        'success' => true
    ]);

} catch (Exception $e) {
    error_log("Equipment DataTable Error: " . $e->getMessage());
    
    echo json_encode([
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'success' => false,
        'error' => 'Failed to load equipment data'
    ]);
}
?>