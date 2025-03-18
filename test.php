<!-- Test Management Module (AdminLTE 4 Template) -->

<?php include('conn.php'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Management | AdminLTE 4</title>
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
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTestModal">
                            <i class="fas fa-plus"></i> Add Test
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <div class="app-content">
            <div class="container-fluid">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Tests List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Test Name</th>
                                <th>Category</th>
                                <th>Parameters</th>
                                <th>Reference Range</th>
                                <th>Price ($)</th>
                                <th>Edit</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $tests = mysqli_query($con, "SELECT * FROM tests ORDER BY id DESC");
                            while($test = mysqli_fetch_assoc($tests)) {
                                echo "<tr>
                                    <td>{$test['id']}</td>
                                    <td>{$test['test_name']}</td>
                                    <td>{$test['category']}</td>
                                    <td>{$test['parameters']}</td>
                                    <td>{$test['reference_range']}</td>
                                    <td>{$test['price']}</td>
                                    <td><button class='btn btn-sm btn-warning'>Edit</button></td>
                                </tr>";
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include('inc/footer.php'); ?>
</div>

<!-- Add Test Modal -->
<div class="modal fade" id="newTestModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="insert_test.php" method="POST">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">Add New Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control" name="test_name" placeholder="Test Name" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <select class="form-control" name="category" required>
                                <option value="">Select Category</option>
                                <option>Blood Test</option>
                                <option>Urine Test</option>
                                <option>Imaging Test</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-2">
                            <textarea class="form-control" name="parameters" placeholder="Parameters (comma separated)" required></textarea>
                        </div>
                        <div class="col-md-12 mb-2">
                            <textarea class="form-control" name="reference_range" placeholder="Reference Range"></textarea>
                        </div>
                        <div class="col-md-6 mb-2">
                            <input type="number" step="0.01" class="form-control" name="price" placeholder="Price" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('inc/js.php'); ?>
</body>
</html>
