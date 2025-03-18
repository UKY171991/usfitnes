<?php
include('../inc/conn.php');

$query = mysqli_query($con, "SELECT * FROM test_categories ORDER BY id DESC");
echo '<table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';
while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['category_name']}</td>
            <td>
                <button class='btn btn-sm btn-warning edit-btn' data-id='{$row['id']}' data-name='{$row['category_name']}'>Edit</button>
                <button class='btn btn-sm btn-danger delete-btn' data-id='{$row['id']}'>Delete</button>
            </td>
          </tr>";
}
?>
