<?php
include('conn.php');

$query = mysqli_query($con, "SELECT * FROM test_categories ORDER BY id DESC");
echo '<table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
            </tr>
        </thead>
        <tbody>';
while ($row = mysqli_fetch_assoc($query)) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['category_name']}</td>
          </tr>";
}
?>
