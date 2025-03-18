<?php
include('../inc/conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Sanitize and fetch data from form
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $age = (int)$_POST['age'];
    $gender = mysqli_real_escape_string($con, $_POST['gender']);
    $contact = mysqli_real_escape_string($con, $_POST['contact']);
    $referring_doctor = !empty($_POST['referring_doctor']) ? mysqli_real_escape_string($con, $_POST['referring_doctor']) : NULL;
    $registration_date = $_POST['registration_date'];
    $added_by = mysqli_real_escape_string($con, $_POST['added_by']);

    // Prepare SQL Insert
    $query = "INSERT INTO patients (name, age, gender, contact, referring_doctor, registration_date, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Use prepared statements for security
    $stmt = mysqli_prepare($con, $query);

    // Bind parameters
    mysqli_stmt_bind_param($stmt, 'sisssss', $name, $age, $gender, $contact, $referring_doctor, $registration_date, $added_by);

    // Execute and check for success
    if(mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Patient added successfully.";
    } else {
        $_SESSION['error'] = "Failed to add patient.";
    }

    // Close the statement
    mysqli_stmt_close($stmt);
    
    // Close the connection
    mysqli_close($con);

    // Redirect back to patient list page
    header('Location: ../add-patitent.php');
    exit();
}
?>
