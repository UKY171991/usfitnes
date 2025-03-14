<?php
include 'inc/config.php';
session_start();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM income_subcategories WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $subcategory = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $category_id = intval($_POST['category_id']);
    $subcategory_name = trim($_POST['subcategory_name']);

    if (!empty($category_id) && !empty($subcategory_name)) {
        $update_query = "UPDATE income_subcategories SET category_id = ?, subcategory_name = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "isi", $category_id, $subcategory_name, $id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_msg'] = "Income sub-category updated successfully!";
        } else {
            $_SESSION['error_msg'] = "Error updating sub-category.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error_msg'] = "All fields are required.";
    }

    header("Location: income-subcategory.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Edit Income Sub-Category</title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php include 'inc/sidebar.php'; ?>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'inc/topbar.php'; ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <h4 class="text-dark">Edit Income Sub-Category</h4>
        </div>
      </div>

      <!-- Form to Edit Income Sub-Category -->
      <div class="row mt-4">
        <div class="col-md-6 mx-auto">
          <div class="card shadow-lg">
            <div class="card-header bg-gradient-dark text-white d-flex align-items-center">
              <i class="material-symbols-rounded me-2">edit</i>
              <h6 class="mb-0 text-white">Update Sub-Category</h6>
            </div>
            <div class="card-body">
              <?php
              if (isset($_SESSION['success_msg'])) {
                  echo "<div class='alert alert-success'>{$_SESSION['success_msg']}</div>";
                  unset($_SESSION['success_msg']);
              }
              if (isset($_SESSION['error_msg'])) {
                  echo "<div class='alert alert-danger'>{$_SESSION['error_msg']}</div>";
                  unset($_SESSION['error_msg']);
              }
              ?>
              <form action="edit-subcategory.php" method="POST">
                <input type="hidden" name="id" value="<?= $subcategory['id'] ?>">

                <div class="mb-4">
                  <label class="form-label">Select Category</label>
                  <select class="form-control" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    $query = "SELECT * FROM income_categories ORDER BY category_name ASC";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $selected = ($subcategory['category_id'] == $row['id']) ? "selected" : "";
                        echo "<option value='{$row['id']}' $selected>{$row['category_name']}</option>";
                    }
                    ?>
                  </select>
                </div>

                <div class="input-group input-group-outline mb-4">
                  <label class="form-label">Sub-Category Name</label>
                  <input type="text" class="form-control" name="subcategory_name" value="<?= $subcategory['subcategory_name'] ?>" required>
                </div>

                <div class="text-end">
                  <button type="submit" class="btn bg-gradient-dark">
                    <i class="material-symbols-rounded me-1">save</i> Update Sub-Category
                  </button>
                  <a href="income-subcategory.php" class="btn btn-secondary">
                    <i class="material-symbols-rounded me-1">cancel</i> Cancel
                  </a>
                </div>
              </form>
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
