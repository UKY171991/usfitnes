<?php
error_reporting(E_ALL);
include('../inc/conn.php');

if (isset($_POST['id']) && isset($_POST['category_name'])) {

    $id = (int)$_POST['id'];
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
    	echo "Hello";    exit();
        $stmt = $conn->prepare("UPDATE test_categories SET category_name=? WHERE id=?");

        if ($stmt === false) {
            die("Prepare failed: " . htmlspecialchars($conn->error));
        }

        $stmt->bind_param("si", $category_name, $id);
        $execute = $stmt->execute();

        if (!$execute) {
            die("Execute failed: " . htmlspecialchars($stmt->error));
        }

        $stmt->close();

        echo "success";
    } else {
        die("Category name cannot be empty.");
    }
} else {
    die("Invalid Request: Required parameters missing.");
}
?>
