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
            branch_name LIKE :search OR 
            branch_code LIKE :search OR 
            phone LIKE :search OR 
            email LIKE :search OR 
            city LIKE :search OR 
            state LIKE :search OR 
            address LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }

    // Get total branches count
    $totalQuery = "SELECT COUNT(*) FROM branches" . $whereClause;
    $totalStmt = $conn->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $totalStmt->bindValue($key, $value);
    }
    $totalStmt->execute();
    $totalBranches = $totalStmt->fetchColumn();
    $totalPages = ceil($totalBranches / $itemsPerPage);

    // Get branches for the current page
    $query = "
        SELECT * FROM branches 
        " . $whereClause . "
        ORDER BY branch_name
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
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
        'totalBranches' => $totalBranches,
        'searchTerm' => $search
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
