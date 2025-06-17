<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$offset = ($page - 1) * $itemsPerPage;

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    // Get total branches count
    $totalStmt = $conn->query("SELECT COUNT(*) FROM branches");
    $totalBranches = $totalStmt->fetchColumn();
    $totalPages = ceil($totalBranches / $itemsPerPage);

    // Get branches for the current page
    $stmt = $conn->prepare("
        SELECT * FROM branches 
        ORDER BY branch_name
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'branches' => $branches,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalBranches' => $totalBranches
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
