<?php
session_start(); // Must be at the top before any output
include 'inc/auth.php';
include 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Trim and sanitize inputs
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $subcategory_id = intval($_POST['subcategory_id']);
    $actual_amount = floatval($_POST['actual_amount']);
    $received_amount = floatval($_POST['received_amount']);
    $revenue = floatval($_POST['revenue']);
    $balance_amount = $actual_amount - $received_amount;
    $entry_date = trim($_POST['date_of_entry']);

    // Convert dd-mm-yyyy to YYYY-MM-DD for MySQL
    $entry_date = date("Y-m-d", strtotime($entry_date));

    // Ensure required fields are not empty
    if (!empty($name) && !empty($phone) && !empty($category_id) && !empty($subcategory_id) && !empty($actual_amount) && !empty($entry_date) && isset($revenue)) {
        
        // Corrected SQL query (Fixed missing comma)
        $query = "INSERT INTO income (name, phone, description, category_id, subcategory_id, actual_amount, received_amount, balance_amount, entry_date, revenue)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        
        // Corrected Data Types in Bind Param (Fixed incorrect "ss" for revenue)
        mysqli_stmt_bind_param($stmt, "sssiidddds", $name, $phone, $description, $category_id, $subcategory_id, $actual_amount, $received_amount, $balance_amount, $entry_date, $revenue);

        // Execute and check for success
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Income entry added successfully!";
        } else {
            $_SESSION['error_msg'] = "Error adding income entry!";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_msg'] = "All fields are required!";
    }

    // Redirect to view-income.php
    header("Location: view-income.php");
    exit();
}
?>
