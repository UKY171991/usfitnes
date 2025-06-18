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
            p.name LIKE :search OR 
            r.id LIKE :search OR 
            t.test_name LIKE :search OR 
            b.branch_name LIKE :search OR
            r.total_amount LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }

    // Get total reports count
    $totalQuery = "
        SELECT COUNT(*) 
        FROM test_reports r 
        JOIN patients p ON r.patient_id = p.id 
        JOIN tests t ON r.test_id = t.id 
        LEFT JOIN branches b ON r.branch_id = b.id
        " . $whereClause;
    
    $totalStmt = $conn->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $totalStmt->bindValue($key, $value);
    }
    $totalStmt->execute();
    $totalReports = $totalStmt->fetchColumn();
    $totalPages = ceil($totalReports / $itemsPerPage);

    // Get reports for the current page
    $query = "
        SELECT 
            r.id,
            r.patient_id,
            r.test_id,
            r.branch_id,
            r.total_amount,
            r.status,
            r.created_at,
            p.name as patient_name,
            p.phone as patient_phone,
            t.test_name,
            b.branch_name
        FROM test_reports r 
        JOIN patients p ON r.patient_id = p.id 
        JOIN tests t ON r.test_id = t.id 
        LEFT JOIN branches b ON r.branch_id = b.id
        " . $whereClause . "
        ORDER BY r.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format the data
    foreach ($reports as &$report) {
        $report['created_formatted'] = date('Y-m-d H:i', strtotime($report['created_at']));
        $report['amount_formatted'] = '₹' . number_format($report['total_amount'], 2);
    }

    $response = [
        'status' => 'success',
        'reports' => $reports,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalReports' => $totalReports,
        'searchTerm' => $search
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
