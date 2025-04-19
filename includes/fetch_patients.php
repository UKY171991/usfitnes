<?php
require_once '../config.php';
require_once '../db_connect.php';

header('Content-Type: application/json');

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check session and role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

// Verify CSRF token
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Restrict to Admin, Doctor, Technician
$allowed_roles = ['Admin', 'Doctor', 'Technician'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    $db = Database::getInstance();

    // Pagination and search variables
    $limit = 10;
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
    $offset = ($page - 1) * $limit;
    $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);

    // Base query
    $query = "SELECT 
                p.*,
                CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
                COALESCE(p.status, 'active') as status
              FROM Patients p 
              LEFT JOIN Users u ON p.user_id = u.user_id
              WHERE 1=1";
    
    $count_query = "SELECT COUNT(*) FROM Patients p WHERE 1=1";
    $params = [];

    // Add branch condition if branch_id is set in session
    if (isset($_SESSION['branch_id'])) {
        $query .= " AND (p.branch_id = :branch_id OR p.branch_id IS NULL)";
        $count_query .= " AND (p.branch_id = :branch_id OR p.branch_id IS NULL)";
        $params['branch_id'] = $_SESSION['branch_id'];
    }

    // Add search condition if search term exists
    if (!empty($search)) {
        $search_condition = " AND (
            CONCAT(p.first_name, ' ', p.last_name) LIKE :search 
            OR p.email LIKE :search 
            OR p.phone LIKE :search
        )";
        $query .= $search_condition;
        $count_query .= $search_condition;
        $params['search'] = "%$search%";
    }

    // Add sorting
    $query .= " ORDER BY p.patient_id DESC";

    // Add pagination
    $query .= " LIMIT :limit OFFSET :offset";

    // Get total count first
    $stmt = $db->query($count_query, $params);
    $total_patients = $stmt->fetchColumn();
    $total_pages = ceil($total_patients / $limit);

    // Add pagination parameters
    $params['limit'] = $limit;
    $params['offset'] = $offset;

    // Execute main query
    $stmt = $db->query($query, $params);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    foreach ($patients as &$patient) {
        $patient['date_of_birth'] = date('Y-m-d', strtotime($patient['date_of_birth']));
        $patient['created_by_name'] = $patient['created_by_name'] ?: 'System';
        $patient['email'] = $patient['email'] ?: '';
        $patient['phone'] = $patient['phone'] ?: '';
        $patient['status'] = $patient['status'] ?: 'active';
    }

    echo json_encode([
        'success' => true,
        'patients' => $patients,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_patients
    ]);

} catch (Exception $e) {
    error_log('Error in fetch_patients.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch patients',
        'message' => defined('ENVIRONMENT') && ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
}