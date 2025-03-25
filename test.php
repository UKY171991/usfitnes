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

// Fetch categories for the dropdown
$stmt = $pdo->query("SELECT * FROM Test_Categories ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all tests for display
$tests_stmt = $pdo->query("
    SELECT t.*, c.category_name 
    FROM Tests_Catalog t 
    JOIN Test_Categories c ON t.category_id = c.category_id 
    ORDER BY t.test_name
");
$tests = $tests_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>AdminLTE 4 | Test Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="title" content="AdminLTE 4 | Test Management" />
    <meta name="author" content="ColorlibHQ" />
    <meta name="description" content="AdminLTE is a Free Bootstrap 5 Admin Dashboard, 30 example pages using Vanilla JS." />
    <meta name="keywords" content="bootstrap 5, bootstrap, bootstrap 5 admin dashboard, bootstrap 5 dashboard, bootstrap 5 charts, bootstrap 5 calendar, bootstrap 5 datepicker, bootstrap 5 tables, bootstrap 5 datatable, vanilla js datatable, colorlibhq, colorlibhq dashboard, colorlibhq admin dashboard" />
    <?php include('inc/head.php'); ?>
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
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Test List</h3></div>
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
                                                                        <textarea class="form-control" name="parameters" required><?php echo htmlspecialchars($test['parameters']); ?></textarea>
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
            <strong>Copyright © 2014-2024 <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.</strong> All rights reserved.
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
                            <textarea class="form-control" name="parameters" placeholder="Enter Parameters (comma separated)" required></textarea>
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