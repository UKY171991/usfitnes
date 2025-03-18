<?php
include('conn.php');

if(isset($_POST['category_name'])){
    $name = mysqli_real_escape_string($con, $_POST['category_name']);

    $stmt = mysqli_prepare($con, "INSERT INTO test_categories (category_name) VALUES (?)");
    mysqli_stmt_bind_param($stmt, 's', $name);

    if(mysqli_stmt_execute($stmt)){
        echo "success";
    } else {
        echo "error";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);
}
?>
