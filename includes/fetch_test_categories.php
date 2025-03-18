<?php
include('../inc/conn.php');

$sql = "SELECT * FROM test_categories ORDER BY id DESC";
$result = $conn->query($sql);

if ($result === false) {
    die("Query Error: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo '<table class="table table-bordered">';
    echo '<thead><tr><th>#</th><th>Category Name</th><th>Created At</th><th>Action</th></tr></thead><tbody>';
    $i = 1;
    while($row = $result->fetch_assoc()){
        echo "<tr>
            <td>".$i++."</td>
            <td>".$row['category_name']."</td>
            <td>".$row['created_at']."</td>
            <td>
              <button class='btn btn-sm btn-warning edit-btn' data-id='{$row['id']}' data-name='{$row['category_name']}'>Edit</button>
              <button class='btn btn-sm btn-danger delete-btn' data-id='{$row['id']}'>Delete</button>
            </td>
        </tr>";
    }
    echo '</tbody></table>';
} else {
    echo '<p>No categories found.</p>';
}
