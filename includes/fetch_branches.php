<?php
require_once '../db_connect.php';

header('Content-Type: application/json');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

try {
    if (!empty($search)) {
        $stmt = $pdo->prepare("SELECT branch_id, branch_name, branch_location FROM branches WHERE branch_name LIKE :search OR branch_location LIKE :search ORDER BY branch_name");
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        $stmt->execute();
    } else {
        $stmt = $pdo->query("SELECT branch_id, branch_name, branch_location FROM branches ORDER BY branch_name");
    }

    $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['branches' => $branches]);
} catch (PDOException $e) {
    error_log("Error fetching branches: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to fetch branches', 'details' => $e->getMessage()]);
}