<?php
require_once '../db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Restrict to Admin, Doctor, Technician
if (!in_array($_SESSION['role'], ['Admin', 'Doctor', 'Technician'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT trs.*, 
                 CONCAT(p.first_name, ' ', p.last_name) AS patient_name, 
                 t.test_name, 
                 CONCAT(s.first_name, ' ', s.last_name, ' (', s.role, ')') AS recorded_by_name 
          FROM Test_Results trs
          JOIN Test_Requests tr ON trs.request_id = tr.request_id
          JOIN Patients p ON tr.patient_id = p.patient_id
          JOIN Tests_Catalog t ON tr.test_id = t.test_id
          JOIN Staff s ON trs.recorded_by = s.staff_id";
$count_query = "SELECT COUNT(*) 
                FROM Test_Results trs
                JOIN Test_Requests tr ON trs.request_id = tr.request_id
                JOIN Patients p ON tr.patient_id = p.patient_id
                JOIN Tests_Catalog t ON tr.test_id = t.test_id
                JOIN Staff s ON trs.recorded_by = s.staff_id";
$params = [];

if (!empty($search)) {
    $query .= " WHERE CONCAT(p.first_name, ' ', p.last_name) LIKE :search OR t.test_name LIKE :search";
    $count_query .= " WHERE CONCAT(p.first_name, ' ', p.last_name) LIKE :search OR t.test_name LIKE :search";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY trs.recorded_at DESC LIMIT :limit OFFSET :offset";

$total_stmt = $pdo->prepare($count_query);
if (!empty($search)) {
    $total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$total_stmt->execute();
$total_results = $total_stmt->fetchColumn();
$total_pages = ceil($total_results / $limit);

$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'results' => $results,
    'total_pages' => $total_pages,
    'current_page' => $page
];

header('Content-Type: application/json');
echo json_encode($response);
exit();