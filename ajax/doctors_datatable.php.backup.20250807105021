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
    $order_column = $_POST['order_column'] ?? 'doctor_id';
    $order_dir = $_POST['order_dir'] ?? 'desc';
    
    // Get custom filters
    $status_filter = $_POST['status'] ?? '';
    $specialization_filter = $_POST['specialization'] ?? '';
    $hospital_filter = $_POST['hospital'] ?? '';

    // Validate order direction
    $order_dir = in_array(strtolower($order_dir), ['asc', 'desc']) ? $order_dir : 'desc';
    
    // Allowed columns for ordering
    $allowed_columns = ['doctor_id', 'name', 'specialization', 'phone', 'hospital', 'status', 'created_at'];
    $order_column = in_array($order_column, $allowed_columns) ? $order_column : 'doctor_id';

    // Base query
    $base_query = "FROM doctors WHERE 1=1";
    $params = [];

    // Search functionality
    if (!empty($search_value)) {
        $base_query .= " AND (name LIKE ? OR specialization LIKE ? OR phone LIKE ? OR email LIKE ? OR hospital LIKE ?)";
        $search_param = "%$search_value%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $base_query .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    // Specialization filter
    if (!empty($specialization_filter)) {
        $base_query .= " AND specialization = ?";
        $params[] = $specialization_filter;
    }
    
    // Hospital filter
    if (!empty($hospital_filter)) {
        $base_query .= " AND hospital LIKE ?";
        $params[] = "%$hospital_filter%";
    }

    // Get total records (without filters)
    $total_query = "SELECT COUNT(*) as total FROM doctors";
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
        doctor_id, 
        name, 
        specialization, 
        phone, 
        email, 
        license_number,
        hospital,
        address,
        notes,
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
            'doctor_id' => (int)$row['doctor_id'],
            'name' => htmlspecialchars($row['name']),
            'specialization' => htmlspecialchars($row['specialization']),
            'phone' => htmlspecialchars($row['phone']),
            'email' => htmlspecialchars($row['email'] ?? ''),
            'license_number' => htmlspecialchars($row['license_number'] ?? ''),
            'hospital' => htmlspecialchars($row['hospital'] ?? ''),
            'address' => htmlspecialchars($row['address'] ?? ''),
            'notes' => htmlspecialchars($row['notes'] ?? ''),
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
    error_log("Doctors DataTable Error: " . $e->getMessage());
    
    echo json_encode([
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'success' => false,
        'error' => 'Failed to load doctors data'
    ]);
}
?>