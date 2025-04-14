<?php
require_once '../db_connect.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['action'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$action = $input['action'];

try {
    if ($action === 'add') {
        $branch_name = $input['branch_name'] ?? '';
        $branch_location = $input['branch_location'] ?? '';

        if (empty($branch_name) || empty($branch_location)) {
            echo json_encode(['error' => 'Branch name and location are required']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO branches (branch_name, branch_location) VALUES (:branch_name, :branch_location)");
        $stmt->execute(['branch_name' => $branch_name, 'branch_location' => $branch_location]);

        echo json_encode(['success' => 'Branch added successfully']);
    } elseif ($action === 'edit') {
        $branch_id = $input['branch_id'] ?? null;
        $branch_name = $input['branch_name'] ?? '';
        $branch_location = $input['branch_location'] ?? '';

        if (empty($branch_id) || empty($branch_name) || empty($branch_location)) {
            echo json_encode(['error' => 'Branch ID, name, and location are required']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE branches SET branch_name = :branch_name, branch_location = :branch_location WHERE branch_id = :branch_id");
        $stmt->execute(['branch_name' => $branch_name, 'branch_location' => $branch_location, 'branch_id' => $branch_id]);

        echo json_encode(['success' => 'Branch updated successfully']);
    } elseif ($action === 'delete') {
        $branch_id = $input['branch_id'] ?? null;

        if (empty($branch_id)) {
            echo json_encode(['error' => 'Branch ID is required']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM branches WHERE branch_id = :branch_id");
        $stmt->execute(['branch_id' => $branch_id]);

        echo json_encode(['success' => 'Branch deleted successfully']);
    } else {
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (PDOException $e) {
    error_log("Error managing branches: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to process request']);
}