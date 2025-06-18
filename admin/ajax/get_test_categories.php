<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? (int)$_GET['itemsPerPage'] : 10;
$offset = ($page - 1) * $itemsPerPage;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    // Build search conditions
    $whereClause = '';
    $params = [];
    
    if (!empty($search)) {
        $whereClause = " WHERE (
            tc.category_name LIKE :search OR 
            tc.description LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }

    // Get total categories count
    $totalQuery = "SELECT COUNT(*) FROM test_categories tc" . $whereClause;
    $totalStmt = $conn->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $totalStmt->bindValue($key, $value);
    }
    $totalStmt->execute();
    $totalCategories = $totalStmt->fetchColumn();
    $totalPages = ceil($totalCategories / $itemsPerPage);

    // Get categories for the current page
    $query = "
        SELECT 
            tc.*, 
            COUNT(t.id) as test_count 
        FROM test_categories tc
        LEFT JOIN tests t ON tc.id = t.category_id
        " . $whereClause . "
        GROUP BY tc.id
        ORDER BY tc.category_name
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
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
        'totalCategories' => $totalCategories,
        'searchTerm' => $search
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
