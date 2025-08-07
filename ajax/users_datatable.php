<?php
require_once '../config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Check if user is admin
$stmt = $pdo->prepare("SELECT user_type FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
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
    $user_type_filter = $_POST['user_type'] ?? '';
    $date_filter = $_POST['registration_date'] ?? '';

    // Validate order direction
    $order_dir = in_array(strtolower($order_dir), ['asc', 'desc']) ? $order_dir : 'desc';
    
    // Allowed columns for ordering
    $allowed_columns = ['id', 'name', 'email', 'username', 'user_type', 'status', 'last_login', 'created_at'];
    $order_column = in_array($order_column, $allowed_columns) ? $order_column : 'id';

    // Base query
    $base_query = "FROM users WHERE 1=1";
    $params = [];

    // Search functionality
    if (!empty($search_value)) {
        $base_query .= " AND (name LIKE ? OR email LIKE ? OR username LIKE ? OR user_type LIKE ?)";
        $search_param = "%$search_value%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    // Status filter
    if (!empty($status_filter)) {
        $base_query .= " AND status = ?";
        $params[] = $status_filter;
    }
    
    // User type filter
    if (!empty($user_type_filter)) {
        $base_query .= " AND user_type = ?";
        $params[] = $user_type_filter;
    }
    
    // Date filter
    if (!empty($date_filter)) {
        $base_query .= " AND DATE(created_at) = ?";
        $params[] = $date_filter;
    }

    // Get total records (without filters)
    $total_query = "SELECT COUNT(*) as total FROM users";
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
        name,
        email,
        username,
        user_type,
        phone,
        status,
        last_login,
        login_count,
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
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'username' => htmlspecialchars($row['username']),
            'user_type' => $row['user_type'],
            'phone' => htmlspecialchars($row['phone'] ?? ''),
            'status' => $row['status'],
            'last_login' => $row['last_login'],
            'login_count' => (int)($row['login_count'] ?? 0),
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
    error_log("Users DataTable Error: " . $e->getMessage());
    
    echo json_encode([
        'draw' => $draw ?? 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'success' => false,
        'error' => 'Failed to load users data'
    ]);
}
?>