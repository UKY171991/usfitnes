<?php
session_start();
include 'inc/auth.php';
include 'inc/config.php';

// Handle Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['subcategory_id']) || empty($_POST['subcategory_id'])) {
        $_SESSION['error_msg'] = "Invalid request.";
        header("Location: expenditure-subcategory.php");
        exit();
    }

    $subcategory_id = intval($_POST['subcategory_id']);
    $category_id = intval($_POST['category_id']);
    $subcategory_name = trim($_POST['subcategory_name']);

    // Check if subcategory name is empty
    if (empty($subcategory_name)) {
        $_SESSION['error_msg'] = "Subcategory name cannot be empty.";
        header("Location: edit-expenditure-subcategory.php?id=" . $subcategory_id);
        exit();
    }

    // Update subcategory
    $update_query = "UPDATE expenditure_subcategories SET category_id = ?, subcategory_name = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("isi", $category_id, $subcategory_name, $subcategory_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_msg'] = "Subcategory updated successfully.";
        header("Location: expenditure-subcategory.php");
    } else {
        $_SESSION['error_msg'] = "Update failed: " . $conn->error;
        header("Location: edit-expenditure-subcategory.php?id=" . $subcategory_id);
    }
    
    $stmt->close();
    exit();
}

// Handle Edit Form Display
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_msg'] = "Invalid request.";
    header("Location: expenditure-subcategory.php");
    exit();
}

$subcategory_id = intval($_GET['id']);

// Fetch subcategory details
$query = "SELECT * FROM expenditure_subcategories WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $subcategory_id);
$stmt->execute();
$result = $stmt->get_result();
$subcategory = $result->fetch_assoc();

if (!$subcategory) {
    $_SESSION['error_msg'] = "Subcategory not found.";
    header("Location: expenditure-subcategory.php");
    exit();
}

$stmt->close();

// Fetch categories
$category_query = "SELECT * FROM expenditure_categories ORDER BY category_name ASC";
$categories = $conn->query($category_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Expenditure Subcategory</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
    <?php include 'inc/sidebar.php'; ?>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <?php include 'inc/topbar.php'; ?>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <h4 class="text-dark">Edit Expenditure Subcategory</h4>
                </div>
            </div>

            <div class="row mt-4 d-flex justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg">
                        <div class="card-header bg-gradient-dark text-white">
                            <h6 class="mb-0 text-white">Update Subcategory</h6>
                        </div>
                        <div class="card-body">
                            <form action="edit-expenditure-subcategory.php" method="POST">
                                <input type="hidden" name="subcategory_id" value="<?= $subcategory['id']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-control border" name="category_id" required>
                                        <?php while ($row = $categories->fetch_assoc()) { ?>
                                            <option value="<?= $row['id']; ?>" <?= ($row['id'] == $subcategory['category_id']) ? 'selected' : ''; ?>>
                                                <?= $row['category_name']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Subcategory Name</label>
                                    <input type="text" class="form-control border" name="subcategory_name" value="<?= $subcategory['subcategory_name']; ?>" required>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn bg-gradient-dark">Update</button>
                                    <a href="expenditure-subcategories.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/core/popper.min.js"></script>
    <script src="assets/js/core/bootstrap.min.js"></script>
    <script src="assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>
</html>
