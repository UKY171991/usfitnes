<?php
require_once '../inc/db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

// Pagination variables
$limit = 10; // Rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of users
$total_stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$total_users = $total_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Fetch users for the current page
$stmt = $pdo->prepare("SELECT * FROM Users ORDER BY user_id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare response
$response = [
    'users' => $users,
    'total_pages' => $total_pages,
    'current_page' => $page
];

header('Content-Type: application/json');
echo json_encode($response);
exit();