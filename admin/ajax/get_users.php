<?php
require_once '../../inc/config.php';
require_once '../../inc/db.php';
require_once '../../auth/session-check.php';

checkAdminAccess();

// Helper function to get role badge color
function getRoleBadgeColor($role) {
    $colors = [
        'master_admin' => 'danger',
        'admin' => 'warning',
        'branch_admin' => 'info',
        'receptionist' => 'success',
        'technician' => 'primary'
    ];
    return $colors[$role] ?? 'secondary';
}

// Helper function to format role display name
function formatRole($role) {
    if (empty($role)) return '-';
    return ucwords(str_replace('_', ' ', $role));
}

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
            u.name LIKE :search OR 
            u.username LIKE :search OR 
            u.phone LIKE :search OR 
            u.email LIKE :search OR 
            u.role LIKE :search OR 
            b.branch_name LIKE :search
        )";
        $params[':search'] = '%' . $search . '%';
    }

    // Get total users count
    $totalQuery = "SELECT COUNT(*) FROM users u LEFT JOIN branches b ON u.branch_id = b.id" . $whereClause;
    $totalStmt = $conn->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $totalStmt->bindValue($key, $value);
    }
    $totalStmt->execute();
    $totalUsers = $totalStmt->fetchColumn();
    $totalPages = ceil($totalUsers / $itemsPerPage);

    // Get users for the current page
    $query = "
        SELECT u.*, b.branch_name 
        FROM users u 
        LEFT JOIN branches b ON u.branch_id = b.id 
        " . $whereClause . "
        ORDER BY u.id DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process users data for frontend
    foreach ($users as &$user) {
        $user['role_badge_color'] = getRoleBadgeColor($user['role']);
        $user['role_formatted'] = formatRole($user['role']);
        $user['last_login_formatted'] = $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never';
        $user['created_formatted'] = $user['created_at'] ? date('Y-m-d H:i', strtotime($user['created_at'])) : '-';
    }    $response = [
        'status' => 'success',
        'users' => $users,
        'totalPages' => $totalPages,
        'currentPage' => $page,
        'itemsPerPage' => $itemsPerPage,
        'totalUsers' => $totalUsers,
        'searchTerm' => $search
    ];

} catch (PDOException $e) {
    $response['message'] = "Database Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>
