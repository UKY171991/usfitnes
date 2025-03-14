<?php
include 'inc/auth.php';
include 'inc/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $query = "DELETE FROM expenditure_categories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_msg'] = "Income category deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting category.";
    }

    mysqli_stmt_close($stmt);
}

header("Location: expenditure-category.php");
exit();
?>
