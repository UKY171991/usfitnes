<?php
require_once '../db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Restrict to Admin only
if ($_SESSION['role'] !== 'Admin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Pagination and search variables
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query
$query = "SELECT * FROM Users";
$count_query = "SELECT COUNT(*) FROM Users";
$params = [];

// Add search filter if provided
if (!empty($search)) {
    $query .= " WHERE username LIKE :search OR CONCAT(first_name, ' ', last_name) LIKE :search OR email LIKE :search";
    $count_query .= " WHERE username LIKE :search OR CONCAT(first_name, ' ', last_name) LIKE :search OR email LIKE :search";
    $params[':search'] = "%$search%";
}

// Add ordering and pagination
$query .= " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";

// Get total number of users (with search filter)
$total_stmt = $pdo->prepare($count_query);
if (!empty($search)) {
    $total_stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$total_stmt->execute();
$total_users = $total_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Fetch users for the current page
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare response
$response = [
    'users' => $users,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'search_term' => $search // For debugging
];

header('Content-Type: application/json');
echo json_encode($response);
exit();