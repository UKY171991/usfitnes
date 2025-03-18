<?php
include('../inc/conn.php');

if (isset($_POST['id'], $_POST['category_name'])) {
    
    $id = (int) $_POST['id'];
    $category_name = trim($_POST['category_name']);
    echo "Hello"; die;

    if (empty($category_name)) {
        die("Error: Category name is empty.");
    }

    $stmt = $conn->prepare("UPDATE test_categories SET category_name=? WHERE id=?");

    if (!$stmt) {
        die("Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("si", $category_name, $id);
    if (!$stmt->execute()) {
        die("Execute Error: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo "Category updated successfully.";
    } else {
        echo "Error: No rows updated. Possibly incorrect ID or unchanged data.";
    }

    $stmt->close();
?>
