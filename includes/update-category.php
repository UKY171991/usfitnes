<?php
session_start();
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = trim($_POST['category_id']);
    $category_name = trim($_POST['category_name']);

    if (empty($category_id) || empty($category_name)) {
        $_SESSION['error'] = "All required fields must be filled.";
        header("Location: ../test_categories.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE Test_Categories SET category_name = :category_name WHERE category_id = :category_id");
        $stmt->execute([
            'category_name' => $category_name,
            'category_id' => $category_id
        ]);
        $_SESSION['success'] = "Category updated successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating category: " . $e->getMessage();
    }

    header("Location: ../test_categories.php");
    exit();
}