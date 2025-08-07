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
    $order_column = $_POST['order_column'] ?? 'patient_id';
    $order_dir = $_POST['order_dir'] ?? 'desc';
    
    // Get custom filters
    $status_filter = $_POST['status'] ?? '';
    $blood_group_filter = $_POST['blood_group'] ?? '';
    $date_filter = $_POST['registration_date'] ?? '';

    // Validate order direction
    $order_dir = in_array(strtolower($order_dir), ['asc', 'desc']) ? $order_dir : 'desc';
    
    // Allowed columns for ordering
    $allowed_columns = ['patient_id', 'first_name', 'last_name', 'phone', 'blood_group', 'status', 'created_at'];
    $order_column = in_array($order_column, $allowed_columns) ? $order_column : 'patient_id';

    // Base query
    $base_query = "FROM patients WHERE 1=1";
    $params = [];

    // Search functionality
    if (!empty($search_value)) {
        $base_query .= " AND (first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?)";
        $search_param = "%$search_value%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $base_query .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    // Blood group filter
    if (!empty($blood_group_filter)) {
        $base_query .= " AND blood_group = ?";
        $params[] = $blood_group_filter;
    }
    
    // Date filter
    if (!empty($date_filter)) {
        $base_query .= " AND DATE(created_at) = ?";
        $params[] = $date_filter;
    }

    // Get total records (without filters)
    $total_query = "SELECT COUNT(*) as total FROM patients";
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
        patient_id, 
        first_name, 
        last_name, 
        phone, 
        email, 
        date_of_birth, 
        gender, 
        blood_group, 
        address,
        emergency_contact,
        emergency_phone,
        medical_history,
        allergies,
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
            'patient_id' => (int)$row['patient_id'],
            'first_name' => htmlspecialchars($row['first_name']),
            'last_name' => htmlspecialchars($row['last_name']),
            'phone' => htmlspecialchars($row['phone']),
            'email' => htmlspecialchars($row['email'] ?? ''),
            'date_of_birth' => $row['date_of_birth'],
            'gender' => $row['gender'],
            'blood_group' => $row['blood_group'],
            'address' => htmlspecialchars($row['address'] ?? ''),
            'emergency_contact' => htmlspecialchars($row['emergency_contact'] ?? ''),
            'emergency_phone' => htmlspecialchars($row['emergency_phone'] ?? ''),
            'medical_history' => htmlspecialchars($row['medical_history'] ?? ''),
            'allergies' => htmlspecialchars($row['allergies'] ?? ''),
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
    error_log("Patients DataTable Error: " . $e->getMessage());
    
    echo json_encode([
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'success' => false,
        'error' => 'Failed to load patients data'
    ]);
}
?>