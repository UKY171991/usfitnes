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
    // Get total tests count
    $totalStmt = $conn->query("SELECT COUNT(*) FROM tests");
    $totalTests = $totalStmt->fetchColumn();
    $totalPages = ceil($totalTests / $itemsPerPage);

    // Get tests for the current page
    $stmt = $conn->prepare("
        SELECT t.*, c.category_name as category_name 
        FROM tests t 
        LEFT JOIN test_categories c ON t.category_id = c.id 
        ORDER BY t.test_name
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'tests' => $tests,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalTests' => $totalTests
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
