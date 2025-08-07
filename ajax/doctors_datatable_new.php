<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // DataTables server-side processing
    $draw = intval($_POST['draw']);
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search_value = $_POST['search']['value'];
    
    // Order parameters
    $order_column = $_POST['order'][0]['column'];
    $order_dir = $_POST['order'][0]['dir'];
    
    // Filter parameters
    $status_filter = $_POST['status_filter'] ?? '';
    $specialization_filter = $_POST['specialization_filter'] ?? '';
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    
    // Column mapping for ordering
    $columns = [
        0 => 'id',
        1 => 'CONCAT(first_name, " ", last_name)',
        2 => 'phone',
        3 => 'specialization',
        4 => 'license_number',
        5 => 'hospital_affiliation',
        6 => 'status',
        7 => 'created_date'
    ];
    
    $order_column_name = $columns[$order_column] ?? 'id';
    
    // Base query
    $query = "SELECT 
        d.id,
        d.first_name,
        d.last_name,
        CONCAT(d.first_name, ' ', d.last_name) as full_name,
        d.phone,
        d.email,
        d.specialization,
        d.license_number,
        d.hospital_affiliation,
        d.notes,
        d.status,
        d.created_date,
        d.updated_date,
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT CASE WHEN DATE(o.created_date) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN o.id END) as monthly_orders,
        COUNT(DISTINCT CASE WHEN DATE(o.created_date) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN o.id END) as weekly_orders
    FROM doctors d
    LEFT JOIN test_orders o ON d.id = o.doctor_id
    WHERE 1=1";
    
    $params = [];
    $param_types = '';
    
    // Apply filters
    if (!empty($status_filter)) {
        $query .= " AND d.status = ?";
        $params[] = $status_filter;
        $param_types .= 's';
    }
    
    if (!empty($specialization_filter)) {
        $query .= " AND d.specialization LIKE ?";
        $params[] = "%{$specialization_filter}%";
        $param_types .= 's';
    }
    
    if (!empty($date_from)) {
        $query .= " AND DATE(d.created_date) >= ?";
        $params[] = $date_from;
        $param_types .= 's';
    }
    
    if (!empty($date_to)) {
        $query .= " AND DATE(d.created_date) <= ?";
        $params[] = $date_to;
        $param_types .= 's';
    }
    
    // Apply search
    if (!empty($search_value)) {
        $query .= " AND (
            d.first_name LIKE ? OR
            d.last_name LIKE ? OR
            d.phone LIKE ? OR
            d.email LIKE ? OR
            d.specialization LIKE ? OR
            d.license_number LIKE ? OR
            d.hospital_affiliation LIKE ?
        )";
        $search_param = "%{$search_value}%";
        for ($i = 0; $i < 7; $i++) {
            $params[] = $search_param;
            $param_types .= 's';
        }
    }
    
    // Group by doctor
    $query .= " GROUP BY d.id";
    
    // Get total count before applying limits
    $count_query = "SELECT COUNT(DISTINCT d.id) as total FROM doctors d WHERE 1=1";
    $count_params = [];
    $count_param_types = '';
    
    // Apply same filters to count query
    if (!empty($status_filter)) {
        $count_query .= " AND d.status = ?";
        $count_params[] = $status_filter;
        $count_param_types .= 's';
    }
    
    if (!empty($specialization_filter)) {
        $count_query .= " AND d.specialization LIKE ?";
        $count_params[] = "%{$specialization_filter}%";
        $count_param_types .= 's';
    }
    
    if (!empty($date_from)) {
        $count_query .= " AND DATE(d.created_date) >= ?";
        $count_params[] = $date_from;
        $count_param_types .= 's';
    }
    
    if (!empty($date_to)) {
        $count_query .= " AND DATE(d.created_date) <= ?";
        $count_params[] = $date_to;
        $count_param_types .= 's';
    }
    
    if (!empty($search_value)) {
        $count_query .= " AND (
            d.first_name LIKE ? OR
            d.last_name LIKE ? OR
            d.phone LIKE ? OR
            d.email LIKE ? OR
            d.specialization LIKE ? OR
            d.license_number LIKE ? OR
            d.hospital_affiliation LIKE ?
        )";
        $search_param = "%{$search_value}%";
        for ($i = 0; $i < 7; $i++) {
            $count_params[] = $search_param;
            $count_param_types .= 's';
        }
    }
    
    // Execute count query
    $count_stmt = $conn->prepare($count_query);
    if (!empty($count_params)) {
        $count_stmt->bind_param($count_param_types, ...$count_params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $filtered_records = $count_result->fetch_assoc()['total'];
    
    // Get total records count
    $total_stmt = $conn->prepare("SELECT COUNT(*) as total FROM doctors");
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_records = $total_result->fetch_assoc()['total'];
    
    // Add ordering and pagination to main query
    $query .= " ORDER BY {$order_column_name} {$order_dir}";
    $query .= " LIMIT ?, ?";
    $params[] = $start;
    $params[] = $length;
    $param_types .= 'ii';
    
    // Execute main query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'id' => $row['id'],
            'full_name' => $row['full_name'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'phone' => $row['phone'],
            'email' => $row['email'] ?? '',
            'specialization' => $row['specialization'],
            'license_number' => $row['license_number'],
            'hospital_affiliation' => $row['hospital_affiliation'] ?? '',
            'notes' => $row['notes'] ?? '',
            'status' => $row['status'],
            'created_date' => $row['created_date'],
            'updated_date' => $row['updated_date'],
            'total_orders' => $row['total_orders'],
            'monthly_orders' => $row['monthly_orders'],
            'weekly_orders' => $row['weekly_orders']
        ];
    }
    
    // Prepare response
    $response = [
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $filtered_records,
        'data' => $data
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log('Doctors DataTable Error: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
