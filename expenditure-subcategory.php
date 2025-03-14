<?php
// Include configuration and other necessary files
include 'inc/auth.php';
include 'inc/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Add Expenditure Sub-Category</title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <link  href="assets/css/style.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php include 'inc/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'inc/topbar.php'; ?>

    <div class="container-fluid py-4">
      
      <!-- Page Title -->
      <div class="row">
        <div class="col-12">
          <h4 class="text-dark">Add Expenditure Sub-Category</h4>
        </div>
      </div>

      <!-- Form to Add Income Sub-Category -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card shadow-lg">
            <div class="card-header bg-gradient-dark text-white d-flex align-items-center">
              <i class="material-symbols-rounded me-2">category</i>
              <h6 class="mb-0 text-white">Add New Expenditure Sub-Category</h6>
            </div>
            <div class="card-body">
              <?php
              session_start();
              if (isset($_SESSION['success_msg'])) {
                  echo "<div class='alert alert-success'>{$_SESSION['success_msg']}</div>";
                  unset($_SESSION['success_msg']);
              }
              if (isset($_SESSION['error_msg'])) {
                  echo "<div class='alert alert-danger'>{$_SESSION['error_msg']}</div>";
                  unset($_SESSION['error_msg']);
              }
              ?>
              <form action="process-income-subcategory.php" method="POST">
                <div class="mb-4">
                  <label class="form-label">Select Category</label>
                  <select class="form-control border" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    include 'inc/config.php';
                    $query = "SELECT * FROM expenditure_categories ORDER BY category_name ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo "<option value='{$row['id']}'>{$row['category_name']}</option>";
                    }
                    ?>
                  </select>
                </div>
                <div class="input-group input-group-outline mb-4">
                  <label class="form-label">Sub-Category Name</label>
                  <input type="text" class="form-control border" name="subcategory_name" required>
                </div>
                <div class="text-end">
                  <button type="submit" class="btn bg-gradient-dark">
                    <i class="material-symbols-rounded me-1">add</i> Add Sub-Category
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Table to Display Sub-Categories -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-gradient-dark text-white">
              <h6 class="mb-0 text-white">Expenditure Sub-Categories</h6>
            </div>
            <div class="card-body px-3">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Sub-Category</th>
                    <th class="text-center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $query = "SELECT sub.id, cat.category_name, sub.subcategory_name FROM expenditure_subcategories sub
                            INNER JOIN expenditure_categories cat ON sub.category_id = cat.id ORDER BY sub.id DESC";
                  $result = mysqli_query($conn, $query);
                  $count = 1;
                  while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$count}</td>
                            <td>{$row['category_name']}</td>
                            <td>{$row['subcategory_name']}</td>
                            <td class='text-center'>
                              <a href='edit-expenditure-subcategory.php?id={$row['id']}' class='badge badge-sm bg-gradient-success'><i class='fa fa-edit'></i> Edit</a>
                              <a href='delete-expenditure-subcategory.php?id={$row['id']}' class='badge badge-sm bg-gradient-danger' onclick='return confirm(\"Are you sure you want to delete this sub-category?\")'><i class='fa fa-trash'></i> Delete</a>
                            </td>
                          </tr>";
                    $count++;
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>

  <!-- Core JS Files -->
  <script src="assets/js/core/popper.min.js"></script>
  <script src="assets/js/core/bootstrap.min.js"></script>
  <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>

</body>
</html>
