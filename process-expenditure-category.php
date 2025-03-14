<?php
include 'inc/auth.php';
include 'inc/config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_name = trim($_POST['category_name']);

    if (!empty($category_name)) {
        // Prepare the SQL statement
        $query = "INSERT INTO expenditure_categories (category_name) VALUES (?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // Bind parameters and execute
            mysqli_stmt_bind_param($stmt, "s", $category_name);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_msg'] = "Expenditure category added successfully!";
            } else {
                $_SESSION['error_msg'] = "Error: Could not add category.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $_SESSION['error_msg'] = "Error preparing the statement.";
        }
    } else {
        $_SESSION['error_msg'] = "Category name cannot be empty.";
    }

    // Redirect back to the category page
    header("Location: expenditure-category.php");
    exit();
}
?>
