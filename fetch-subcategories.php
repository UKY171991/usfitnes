<?php
include 'inc/config.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);
    
    $query = "SELECT * FROM income_subcategories WHERE category_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    echo "<option value=''>-- Select Sub-Category --</option>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<option value='{$row['id']}'>{$row['subcategory_name']}</option>";
    }
    
    mysqli_stmt_close($stmt);
}
?>
