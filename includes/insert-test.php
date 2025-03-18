<?php
include('../inc/conn.php');

if(isset($_POST['category_name'])){
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO test_categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
}
?>
