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

// Fetch all test categories for display
$stmt = $pdo->query("SELECT * FROM Test_Categories ORDER BY category_name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Shiva Pathology Centre | Test Categories</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
                            <h3>Test Category Management</h3>
                        </div>
                        <div class="col-sm-6 text-end">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                                <i class="fas fa-plus"></i> New Category
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
                                                <?php endforeach; ?>
                                            <?php endif; ?>
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