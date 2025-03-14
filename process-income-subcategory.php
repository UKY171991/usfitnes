<?php
include 'inc/auth.php';
include 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = intval($_POST['category_id']);
    $subcategory_name = trim($_POST['subcategory_name']);

    if (!empty($category_id) && !empty($subcategory_name)) {
        $query = "INSERT INTO income_subcategories (category_id, subcategory_name) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "is", $category_id, $subcategory_name);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Income sub-category added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error: Could not add sub-category.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_msg'] = "All fields are required.";
    }

    header("Location: income-subcategory.php");
    exit();
}
?>
