<?php
require_once '../db_connect.php';
session_start();

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in'] || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $test_name = trim($_POST['test_name']);
    $category_id = trim($_POST['category_id']);
    $test_code = trim($_POST['test_code']);
    $parameters = isset($_POST['parameters']) ? implode(',', $_POST['parameters']) : '';
    $reference_range = trim($_POST['reference_range']);
    $price = trim($_POST['price']);

    if (empty($test_name) || empty($category_id) || empty($test_code) || empty($parameters) || empty($price)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO Tests_Catalog (test_name, category_id, test_code, parameters, reference_range, price) 
            VALUES (:test_name, :category_id, :test_code, :parameters, :reference_range, :price)
        ");
        $stmt->execute([
            'test_name' => $test_name,
            'category_id' => $category_id,
            'test_code' => $test_code,
            'parameters' => $parameters,
            'reference_range' => $reference_range,
            'price' => $price
        ]);
        echo json_encode(['success' => true, 'message' => 'Test added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error adding test: ' . $e->getMessage()]);
    }
} elseif ($action === 'edit') {
    $test_id = trim($_POST['test_id']);
    $test_name = trim($_POST['test_name']);
    $category_id = trim($_POST['category_id']);
    $test_code = trim($_POST['test_code']);
    $parameters = isset($_POST['parameters']) ? implode(',', $_POST['parameters']) : '';
    $reference_range = trim($_POST['reference_range']);
    $price = trim($_POST['price']);

    if (empty($test_id) || empty($test_name) || empty($category_id) || empty($test_code) || empty($parameters) || empty($price)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE Tests_Catalog 
            SET test_name = :test_name, category_id = :category_id, test_code = :test_code, 
                parameters = :parameters, reference_range = :reference_range, price = :price
            WHERE test_id = :test_id
        ");
        $stmt->execute([
            'test_id' => $test_id,
            'test_name' => $test_name,
            'category_id' => $category_id,
            'test_code' => $test_code,
            'parameters' => $parameters,
            'reference_range' => $reference_range,
            'price' => $price
        ]);
        echo json_encode(['success' => true, 'message' => 'Test updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating test: ' . $e->getMessage()]);
    }
} elseif ($action === 'delete') {
    $test_id = trim($_POST['test_id']);
    if (empty($test_id)) {
        echo json_encode(['success' => false, 'message' => 'Test ID is required']);
        exit();
    }

    try {
        // Check if the test is in use (e.g., in test requests or reports)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Test_Requests WHERE test_id = :test_id");
        $stmt->execute(['test_id' => $test_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete test: It is associated with one or more test requests']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM Tests_Catalog WHERE test_id = :test_id");
        $stmt->execute(['test_id' => $test_id]);
        echo json_encode(['success' => true, 'message' => 'Test deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error deleting test: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}