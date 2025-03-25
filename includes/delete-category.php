<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ../login.php");
    exit();
}

// Restrict to Admin
if ($_SESSION['role'] !== 'Admin') {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['category_id'])) {
    $category_id = $_GET['category_id'];

    try {
        // Check if the category is in use
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Tests_Catalog WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $category_id]);
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['error'] = "Cannot delete category: It is associated with one or more tests.";
            header("Location: ../test_categories.php");
            exit();
        }

        // Delete the category
        $stmt = $pdo->prepare("DELETE FROM Test_Categories WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $category_id]);

        $_SESSION['success'] = "Category deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid category ID.";
}

header("Location: ../test_categories.php");
exit();