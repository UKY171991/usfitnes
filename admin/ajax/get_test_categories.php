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
    // Get total categories count
    $totalStmt = $conn->query("SELECT COUNT(*) FROM test_categories");
    $totalCategories = $totalStmt->fetchColumn();
    $totalPages = ceil($totalCategories / $itemsPerPage);

    // Get categories for the current page
    $stmt = $conn->prepare("
        SELECT 
            tc.*, 
            COUNT(t.id) as test_count 
        FROM test_categories tc
        LEFT JOIN tests t ON tc.id = t.category_id
        GROUP BY tc.id
        ORDER BY tc.category_name
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [
        'status' => 'success',
        'categories' => $categories,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalCategories' => $totalCategories
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
