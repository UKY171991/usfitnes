<?php
include 'inc/config.php';
session_start();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $query = "DELETE FROM income_subcategories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success_msg'] = "Income sub-category deleted successfully!";
    } else {
        $_SESSION['error_msg'] = "Error deleting sub-category.";
    }

    mysqli_stmt_close($stmt);
}

header("Location: income-subcategory.php");
exit();
?>
