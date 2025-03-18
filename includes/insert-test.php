<?php
session_start();
include('conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = mysqli_real_escape_string($con, $_POST['test_name']);
    $category = mysqli_real_escape_string($con, $_POST['category']);
    $parameters = mysqli_real_escape_string($con, $_POST['parameters']);
    $reference_range = mysqli_real_escape_string($con, $_POST['reference_range']);
    $price = (float)$_POST['price'];

    $query = "INSERT INTO tests (test_name, category, parameters, reference_range, price) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $query);

    mysqli_stmt_bind_param($stmt, 'ssssd', $name, $category, $parameters, $reference_range, $price);

    session_start();
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Test added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add test.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($con);

    header('Location: add-test.php');
    exit();
}
?>
