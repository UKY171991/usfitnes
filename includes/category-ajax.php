<?php
require_once '../db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $category_name = trim($_POST['category_name']);
    if (empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Test_Categories (category_name) VALUES (:category_name)");
        $stmt->execute(['category_name' => $category_name]);
        echo json_encode(['success' => true, 'message' => 'Category added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding category: ' . $e->getMessage()]);
    }
} elseif ($action === 'edit') {
    $category_id = trim($_POST['category_id']);
    $category_name = trim($_POST['category_name']);
    if (empty($category_id) || empty($category_name)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE Test_Categories SET category_name = :category_name WHERE category_id = :category_id");
        $stmt->execute(['category_name' => $category_name, 'category_id' => $category_id]);
        echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $category_id = trim($_POST['category_id']);
    if (empty($category_id)) {
        echo json_encode(['success' => false, 'message' => 'Category ID is required']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Tests_Catalog WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $category_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete category: It is associated with one or more tests']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM Test_Categories WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $category_id]);
        echo json_encode(['success' => true, 'message' => 'Category deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}