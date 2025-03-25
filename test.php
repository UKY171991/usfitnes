<?php
require_once 'db_connect.php';

session_start();
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: login.php");
    exit();
}

// Restrict to Admin
if ($_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit();
}

// Fetch categories for the dropdown and category list
$stmt = $pdo->query("SELECT * FROM Test_Categories ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if each category is in use (has associated tests)
$category_usage = [];
foreach ($categories as $category) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Tests_Catalog WHERE category_id = :category_id");
    $stmt->execute(['category_id' => $category['category_id']]);
    $category_usage[$category['category_id']] = $stmt->fetchColumn();
}

// Fetch all tests for display
$tests_stmt = $pdo->query("
    SELECT t.*, c.category_name 
    FROM Tests_Catalog t 
    JOIN Test_Categories c ON t.category_id = c.category_id 
    ORDER BY t.test_name
");
$tests = $tests_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all possible parameters dynamically
$parameters_stmt = $pdo->query("SELECT parameter_name FROM Test_Parameters ORDER BY parameter_name");
$possible_parameters = $parameters_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Shiva Pathology Centre | Test Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <?php include('inc/head.php'); ?>
    <!-- Custom CSS for Select2 -->
    <style>
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            color: white;
            border: 1px solid #006fe6;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: white;
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include('inc/top.php'); ?>
        <?php include('inc/sidebar.php'); ?>
        <main class="app-main">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 mt-2">
                        <div class="col-sm-6">
                            <h3>Test Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                                <i class="fas fa-plus"></i> New Category
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newTestModal">
                                <i class="fas fa-vial"></i> New Test
                            </button>
                        </div>
                    </div>
                </div>
            </section>
            <div class="app-content">
                <div class="container-fluid">
                    <!-- Test Category Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Test Category List</h3></div>
                                <div class="card-body">
                                    <?php
                                    if (isset($_SESSION['success'])) {
                                        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>"
                                            . $_SESSION['success'] .
                                            "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        </div>";
                                        unset($_SESSION['success']);
                                    }

                                    if (isset($_SESSION['error'])) {
                                        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>"
                                            . $_SESSION['error'] .
                                            "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                                        </div>";
                                        unset($_SESSION['error']);
                                    }
                                    ?>

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Category Name</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($categories)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No categories found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($categories as $category): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($category['created_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editCategoryModal<?php echo $category['category_id']; ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <?php if ($category_usage[$category['category_id']] == 0): ?>
                                                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?php echo $category['category_id']; ?>">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-danger" disabled title="Cannot delete: Category is in use">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>

                                                    <!-- Edit Category Modal -->
                                                    <div class="modal fade" id="editCategoryModal<?php echo $category['category_id']; ?>" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="includes/update-category.php" method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label>Category Name</label>
                                                                            <input type="text" class="form-control" name="category_name" value="<?php echo htmlspecialchars($category['category_name']); ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Delete Category Modal -->
                                                    <div class="modal fade" id="deleteCategoryModal<?php echo $category['category_id']; ?>" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Deletion</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete the category "<strong><?php echo htmlspecialchars($category['category_name']); ?></strong>"? This action cannot be undone.
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <a href="includes/delete-category.php?category_id=<?php echo $category['category_id']; ?>" class="btn btn-danger">
                                                                        <i class="fas fa-trash"></i> Delete
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test List Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Test List</h3></div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Test Name</th>
                                                <th>Category</th>
                                                <th>Test Code</th>
                                                <th>Parameters</th>
                                                <th>Reference Range</th>
                                                <th>Price ($)</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tests as $test): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($test['test_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['test_code']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['parameters']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['reference_range']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['price']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editTestModal<?php echo $test['test_id']; ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                    </td>
                                                </tr>

                                                <!-- Edit Test Modal -->
                                                <div class="modal fade" id="editTestModal<?php echo $test['test_id']; ?>" tabindex="-1" aria-labelledby="editTestModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="editTestModalLabel">Edit Test</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="includes/update-test.php" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="test_id" value="<?php echo $test['test_id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label>Test Name</label>
                                                                        <input type="text" class="form-control" name="test_name" value="<?php echo htmlspecialchars($test['test_name']); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Category</label>
                                                                        <select class="form-control" name="category_id" required>
                                                                            <option value="">Select Category</option>
                                                                            <?php foreach ($categories as $category): ?>
                                                                                <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $test['category_id'] ? 'selected' : ''; ?>>
                                                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Test Code</label>
                                                                        <input type="text" class="form-control" name="test_code" value="<?php echo htmlspecialchars($test['test_code']); ?>" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Parameters</label>
                                                                        <select class="form-control select2" name="parameters[]" multiple required>
                                                                            <?php
                                                                            $selected_parameters = explode(',', $test['parameters']);
                                                                            foreach ($possible_parameters as $param): ?>
                                                                                <option value="<?php echo htmlspecialchars($param); ?>" <?php echo in_array($param, $selected_parameters) ? 'selected' : ''; ?>>
                                                                                    <?php echo htmlspecialchars($param); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Reference Range</label>
                                                                        <textarea class="form-control" name="reference_range"><?php echo htmlspecialchars($test['reference_range']); ?></textarea>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label>Price ($)</label>
                                                                        <input type="number" step="0.01" class="form-control" name="price" value="<?php echo htmlspecialchars($test['price']); ?>" required>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer class="app-footer">
            <div class="float-end d-none d-sm-inline">Anything you want</div>
            <strong>Copyright © 2025 <a href="#" class="text-decoration-none">Shiva Pathology Centre</a>.</strong> All rights reserved.
        </footer>
    </div>

    <!-- New Category Modal -->
    <div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="includes/insert-category.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Category Name</label>
                            <input type="text" class="form-control" name="category_name" placeholder="Enter Category Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- New Test Modal -->
    <div class="modal fade" id="newTestModal" tabindex="-1" aria-labelledby="newTestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTestModalLabel">Add New Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="includes/insert-test.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Test Name</label>
                            <input type="text" class="form-control" name="test_name" placeholder="Enter Test Name" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select class="form-control" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Test Code</label>
                            <input type="text" class="form-control" name="test_code" placeholder="Enter Test Code" required>
                        </div>
                        <div class="mb-3">
                            <label>Parameters</label>
                            <select class="form-control select2" name="parameters[]" multiple required>
                                <?php foreach ($possible_parameters as $param): ?>
                                    <option value="<?php echo htmlspecialchars($param); ?>">
                                        <?php echo htmlspecialchars($param); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Reference Range</label>
                            <textarea class="form-control" name="reference_range" placeholder="Reference Ranges"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" class="form-control" name="price" placeholder="Price" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save Test</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('inc/js.php'); ?>
    <script>
        // Initialize Select2 on all elements with class 'select2'
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Select parameters",
                allowClear: true
            });
        });

        const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
        const Default = {
            scrollbarTheme: 'os-theme-light',
            scrollbarAutoHide: 'leave',
            scrollbarClickScroll: true,
        };
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
            if (sidebarWrapper && typeof OverlayScrollbarsGlobal?.OverlayScrollbars !== 'undefined') {
                OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
                    scrollbars: {
                        theme: Default.scrollbarTheme,
                        autoHide: Default.scrollbarAutoHide,
                        clickScroll: Default.scrollbarClickScroll,
                    },
                });
            }
        });
    </script>
</body>
</html>