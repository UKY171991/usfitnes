<?php
require_once '../db_connect.php';

session_start();

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

// Pagination and search variables
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query with JOIN to get creator's name
$query = "SELECT p.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name 
          FROM Patients p 
          LEFT JOIN Users u ON p.user_id = u.user_id";
$count_query = "SELECT COUNT(*) 
                FROM Patients p 
                LEFT JOIN Users u ON p.user_id = u.user_id";
$params = [];

if (!empty($search)) {
    $query .= " WHERE CONCAT(p.first_name, ' ', p.last_name) LIKE :search OR p.email LIKE :search";
    $count_query .= " WHERE CONCAT(p.first_name, ' ', p.last_name) LIKE :search OR p.email LIKE :search";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY p.patient_id DESC LIMIT :limit OFFSET :offset";

// Check database connection
if (!isset($pdo) || !$pdo) {
    error_log('Database connection failed in fetch_patients.php');
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

try {
    // Get total number of patients
    $total_stmt = $pdo->prepare($count_query);
    if (!empty($search)) {
        $total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $total_stmt->execute();
    $total_patients = $total_stmt->fetchColumn();
    $total_pages = ceil($total_patients / $limit);

    // Fetch patients
    $stmt = $pdo->prepare($query);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'patients' => $patients,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'search_term' => $search // For debugging
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} catch (PDOException $e) {
    error_log('Database error in fetch_patients.php: ' . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}
exit();