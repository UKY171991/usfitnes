<?php
session_start();
require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = trim($_POST['category_name']);

    if (empty($category_name)) {
        $_SESSION['error'] = "Category name is required.";
        header("Location: ../test_categories.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO Test_Categories (category_name) VALUES (:category_name)");
        $stmt->execute(['category_name' => $category_name]);
        $_SESSION['success'] = "Category added successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding category: " . $e->getMessage();
    }

    header("Location: ../test_categories.php");
    exit();
}