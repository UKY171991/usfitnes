<?php
include('../inc/conn.php');

if(isset($_POST['id'])){
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM test_categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
?>
