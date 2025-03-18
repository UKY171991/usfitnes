<?php

include('../inc/conn.php');

if(isset($_POST['id'], $_POST['category_name'])){
    $id = $_POST['id'];
    $name = $_POST['category_name'];

    $stmt = $conn->prepare("UPDATE test_categories SET category_name=? WHERE id=?");
    $stmt->bind_param("s", $name);
    //$stmt->bind_param("si", $name, $id);
    $stmt->execute();
}
?>
