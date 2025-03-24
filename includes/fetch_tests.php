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

$query = "SELECT * FROM Tests_Catalog";
$count_query = "SELECT COUNT(*) FROM Tests_Catalog";
$params = [];

if (!empty($search)) {
    $query .= " WHERE test_name LIKE :search OR test_code LIKE :search";
    $count_query .= " WHERE test_name LIKE :search OR test_code LIKE :search";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY test_id DESC LIMIT :limit OFFSET :offset";

$total_stmt = $pdo->prepare($count_query);
if (!empty($search)) {
    $total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$total_stmt->execute();
$total_tests = $total_stmt->fetchColumn();
$total_pages = ceil($total_tests / $limit);

$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'tests' => $tests,
    'total_pages' => $total_pages,
    'current_page' => $page
];

header('Content-Type: application/json');
echo json_encode($response);
exit();