<?php
/**
 * Results DataTable AJAX Handler
 * Handles server-side processing for results table
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type
header('Content-Type: application/json');

// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'draw' => 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Authentication required'
    ]);
    exit;
}

// Include database configuration
require_once '../api/safe_config.php';

try {
    // Get DataTable parameters
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 25;
    $search_value = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $search_custom = isset($_POST['search_value']) ? $_POST['search_value'] : '';
    $order_column = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
    $order_dir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';
    
    // Custom filters
    $status_filter = isset($_POST['status']) ? $_POST['status'] : '';
    $priority_filter = isset($_POST['priority']) ? $_POST['priority'] : '';
    
    // Use custom search if provided, otherwise use DataTable search
    $final_search = !empty($search_custom) ? $search_custom : $search_value;
    
    // Define column mapping
    $columns = [
        0 => 'r.id',
        1 => 'patient_name',
        2 => 'r.test_type',
        3 => 'r.result_value',
        4 => 'r.status',
        5 => 'r.created_at',
        6 => null // Actions column - not sortable
    ];
    
    // Base query with joins
    $base_query = "
        FROM test_results r
        LEFT JOIN patients p ON r.patient_id = p.id
        LEFT JOIN test_orders o ON r.order_id = o.id
        WHERE r.status != 'deleted'
    ";
    
    // Add search conditions
    $search_conditions = [];
    $search_params = [];
    
    if (!empty($final_search)) {
        $search_conditions[] = "(
            p.first_name LIKE ? OR 
            p.last_name LIKE ? OR
            CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR
            r.test_type LIKE ? OR
            r.result_value LIKE ? OR
            r.comments LIKE ?
        )";
        $search_term = "%{$final_search}%";
        $search_params = array_fill(0, 6, $search_term);
    }
    
    // Add status filter
    if (!empty($status_filter)) {
        $search_conditions[] = "r.status = ?";
        $search_params[] = $status_filter;
    }
    
    // Add priority filter
    if (!empty($priority_filter)) {
        $search_conditions[] = "r.priority = ?";
        $search_params[] = $priority_filter;
    }
    
    // Combine search conditions
    $where_clause = '';
    if (!empty($search_conditions)) {
        $where_clause = ' AND ' . implode(' AND ', $search_conditions);
    }
    
    // Count total records (without filters)
    $total_query = "SELECT COUNT(*) as total FROM test_results r WHERE r.status != 'deleted'";
    $total_stmt = $pdo->prepare($total_query);
    $total_stmt->execute();
    $total_records = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Count filtered records
    $filtered_query = "SELECT COUNT(*) as total " . $base_query . $where_clause;
    $filtered_stmt = $pdo->prepare($filtered_query);
    $filtered_stmt->execute($search_params);
    $filtered_records = $filtered_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Determine order column
    $order_column_name = isset($columns[$order_column]) && $columns[$order_column] ? $columns[$order_column] : 'r.id';
    if ($order_column_name === 'patient_name') {
        $order_column_name = 'CONCAT(p.first_name, " ", p.last_name)';
    }
    
    // Main data query
    $data_query = "
        SELECT 
            r.id,
            r.patient_id,
            r.order_id,
            r.test_type,
            r.result_value,
            r.reference_range,
            r.unit,
            r.status,
            r.priority,
            r.is_critical,
            r.comments,
            r.created_at,
            r.updated_at,
            CONCAT(p.first_name, ' ', p.last_name) as patient_name,
            p.first_name,
            p.last_name,
            o.order_number
        {$base_query}
        {$where_clause}
        ORDER BY {$order_column_name} {$order_dir}
        LIMIT {$start}, {$length}
    ";
    
    $data_stmt = $pdo->prepare($data_query);
    $data_stmt->execute($search_params);
    $results = $data_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTable
    $data = [];
    foreach ($results as $row) {
        // Format the data according to your needs
        $formatted_row = [
            'id' => $row['id'],
            'patient_id' => $row['patient_id'],
            'order_id' => $row['order_id'],
            'test_type' => htmlspecialchars($row['test_type'] ?? ''),
            'result_value' => htmlspecialchars($row['result_value'] ?? ''),
            'reference_range' => htmlspecialchars($row['reference_range'] ?? ''),
            'unit' => htmlspecialchars($row['unit'] ?? ''),
            'status' => $row['status'],
            'priority' => $row['priority'] ?? 'normal',
            'is_critical' => $row['is_critical'],
            'comments' => htmlspecialchars($row['comments'] ?? ''),
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'patient_name' => htmlspecialchars($row['patient_name'] ?? ''),
            'first_name' => htmlspecialchars($row['first_name'] ?? ''),
            'last_name' => htmlspecialchars($row['last_name'] ?? ''),
            'order_number' => htmlspecialchars($row['order_number'] ?? '')
        ];
        
        $data[] = $formatted_row;
    }
    
    // Return JSON response
    $response = [
        'draw' => $draw,
        'recordsTotal' => intval($total_records),
        'recordsFiltered' => intval($filtered_records),
        'data' => $data
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database error in results_datatable.php: " . $e->getMessage());
    echo json_encode([
        'draw' => isset($draw) ? $draw : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    error_log("General error in results_datatable.php: " . $e->getMessage());
    echo json_encode([
        'draw' => isset($draw) ? $draw : 0,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'An error occurred while fetching data'
    ]);
}
?>