<?php
require_once '../config.php';
require_once '../db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
    'gc_maxlifetime' => SESSION_LIFETIME
]);

// Check session
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Restrict to Admin, Doctor, Technician
if (!in_array($_SESSION['role'], ['Admin', 'Doctor', 'Technician'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

try {
    $db = Database::getInstance();

    // Pagination and search variables
    $limit = 10;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    // Base query with JOIN to get creator's name
    $query = "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name 
              FROM patients p 
              LEFT JOIN users u ON p.created_by = u.user_id";
    $count_query = "SELECT COUNT(*) 
                    FROM patients p 
                    LEFT JOIN users u ON p.created_by = u.user_id";
    $params = [];

    // Add branch condition
    $query .= " WHERE p.branch_id = :branch_id";
    $count_query .= " WHERE p.branch_id = :branch_id";
    $params['branch_id'] = $_SESSION['branch_id'];

    // Add search condition if search term exists
    if (!empty($search)) {
        $query .= " AND (CONCAT(p.first_name, ' ', p.last_name) LIKE :search OR p.email LIKE :search)";
        $count_query .= " AND (CONCAT(p.first_name, ' ', p.last_name) LIKE :search OR p.email LIKE :search)";
        $params['search'] = "%$search%";
    }

    $query .= " ORDER BY p.patient_id DESC LIMIT :limit OFFSET :offset";

    // Get total number of patients
    $stmt = $db->query($count_query, $params);
    $total_patients = $stmt->fetchColumn();
    $total_pages = ceil($total_patients / $limit);

    // Add pagination parameters
    $params['limit'] = $limit;
    $params['offset'] = $offset;

    // Fetch patients
    $stmt = $db->query($query, $params);
    $patients = $stmt->fetchAll();

    // Format dates and ensure all fields are present
    foreach ($patients as &$patient) {
        $patient['date_of_birth'] = date('Y-m-d', strtotime($patient['date_of_birth']));
        $patient['created_by_name'] = $patient['created_by_name'] ?: 'Unknown';
        $patient['email'] = $patient['email'] ?: '';
        $patient['phone'] = $patient['phone'] ?: '';
    }

    // Prepare response
    $response = [
        'patients' => $patients,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'total_records' => $total_patients
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    error_log('Error in fetch_patients.php: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'error' => 'Failed to fetch patients',
        'message' => ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
}