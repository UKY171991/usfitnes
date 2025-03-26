<?php
include('conn.php');

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

// Fetch all parameters for the dropdown and parameter list
$parameters_stmt = $pdo->query("SELECT * FROM Test_Parameters ORDER BY parameter_name");
$parameters = $parameters_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if each parameter is in use (used in tests)
$parameter_usage = [];
foreach ($parameters as $param) {
    $in_use = false;
    $stmt = $pdo->query("SELECT parameters FROM Tests_Catalog");
    $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tests as $test) {
        $test_params = explode(',', $test['parameters']);
        if (in_array($param['parameter_name'], $test_params)) {
            $in_use = true;
            break;
        }
    }
    $parameter_usage[$param['parameter_id']] = $in_use;
}

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
        .select2-container .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
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
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#newParameterModal">
                                <i class="fas fa-plus"></i> New Parameter
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
                    <!-- Alert Section -->
                    <div id="alert-container"></div>

                    <!-- Test Category Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Test Category List</h3></div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped" id="category-table">
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
                                                    <tr data-id="<?php echo $category['category_id']; ?>">
                                                        <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($category['created_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary edit-category" data-id="<?php echo $category['category_id']; ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <?php if ($category_usage[$category['category_id']] == 0): ?>
                                                                <button class="btn btn-sm btn-danger delete-category" data-id="<?php echo $category['category_id']; ?>">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-danger" disabled title="Cannot delete: Category is in use">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Parameter Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header"><h3 class="card-title">Test Parameter List</h3></div>
                                <div class="card-body">
                                    <table class="table table-bordered table-striped" id="parameter-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Parameter Name</th>
                                                <th>Created At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($parameters)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No parameters found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($parameters as $param): ?>
                                                    <tr data-id="<?php echo $param['parameter_id']; ?>">
                                                        <td><?php echo htmlspecialchars($param['parameter_id']); ?></td>
                                                        <td><?php echo htmlspecialchars($param['parameter_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($param['created_at']); ?></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-primary edit-parameter" data-id="<?php echo $param['parameter_id']; ?>" data-name="<?php echo htmlspecialchars($param['parameter_name']); ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </button>
                                                            <?php if (!$parameter_usage[$param['parameter_id']]): ?>
                                                                <button class="btn btn-sm btn-danger delete-parameter" data-id="<?php echo $param['parameter_id']; ?>">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-danger" disabled title="Cannot delete: Parameter is in use">
                                                                    <i class="fas fa-trash"></i> Delete
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
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
                                    <table class="table table-bordered table-striped" id="test-table">
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
                                                <tr data-id="<?php echo $test['test_id']; ?>">
                                                    <td><?php echo htmlspecialchars($test['test_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['test_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['test_code']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['parameters']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['reference_range']); ?></td>
                                                    <td><?php echo htmlspecialchars($test['price']); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-primary edit-test" data-id="<?php echo $test['test_id']; ?>">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </button>
                                                        <button class="btn btn-sm btn-danger delete-test" data-id="<?php echo $test['test_id']; ?>">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </td>
                                                </tr>
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
                <form id="add-category-form">
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

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-category-form">
                    <div class="modal-body">
                        <input type="hidden" name="category_id">
                        <div class="mb-3">
                            <label>Category Name</label>
                            <input type="text" class="form-control" name="category_name" required>
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
    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the category "<span id="delete-category-name"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-category"><i class="fas fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Parameter Modal -->
    <div class="modal fade" id="newParameterModal" tabindex="-1" aria-labelledby="newParameterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newParameterModalLabel">Add New Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add-parameter-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Parameter Name</label>
                            <input type="text" class="form-control" name="parameter_name" placeholder="Enter Parameter Name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Parameter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Parameter Modal -->
    <div class="modal fade" id="editParameterModal" tabindex="-1" aria-labelledby="editParameterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editParameterModalLabel">Edit Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-parameter-form">
                    <div class="modal-body">
                        <input type="hidden" name="parameter_id">
                        <div class="mb-3">
                            <label>Parameter Name</label>
                            <input type="text" class="form-control" name="parameter_name" required>
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

    <!-- Delete Parameter Modal -->
    <div class="modal fade" id="deleteParameterModal" tabindex="-1" aria-labelledby="deleteParameterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteParameterModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the parameter "<span id="delete-parameter-name"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-parameter"><i class="fas fa-trash"></i> Delete</button>
                </div>
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
                <form id="add-test-form">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Test Name</label>
                            <input type="text" class="form-control" name="test_name" placeholder="Enter Test Name" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select class="form-control select2" name="category_id" id="category-select" required>
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
                            <select class="form-control select2" name="parameters[]" id="parameter-select" multiple required>
                                <?php foreach ($parameters as $param): ?>
                                    <option value="<?php echo htmlspecialchars($param['parameter_name']); ?>">
                                        <?php echo htmlspecialchars($param['parameter_name']); ?>
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

    <!-- Edit Test Modal -->
    <div class="modal fade" id="editTestModal" tabindex="-1" aria-labelledby="editTestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTestModalLabel">Edit Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit-test-form">
                    <div class="modal-body">
                        <input type="hidden" name="test_id">
                        <div class="mb-3">
                            <label>Test Name</label>
                            <input type="text" class="form-control" name="test_name" required>
                        </div>
                        <div class="mb-3">
                            <label>Category</label>
                            <select class="form-control select2" name="category_id" id="edit-category-select" required>
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
                            <input type="text" class="form-control" name="test_code" required>
                        </div>
                        <div class="mb-3">
                            <label>Parameters</label>
                            <select class="form-control select2" name="parameters[]" id="edit-parameter-select" multiple required>
                                <?php foreach ($parameters as $param): ?>
                                    <option value="<?php echo htmlspecialchars($param['parameter_name']); ?>">
                                        <?php echo htmlspecialchars($param['parameter_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Reference Range</label>
                            <textarea class="form-control" name="reference_range"></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Price ($)</label>
                            <input type="number" step="0.01" class="form-control" name="price" required>
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

    <!-- Delete Test Modal -->
    <div class="modal fade" id="deleteTestModal" tabindex="-1" aria-labelledby="deleteTestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTestModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the test "<span id="delete-test-name"></span>"? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-test"><i class="fas fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>

    <?php include('inc/js.php'); ?>
    <script>
        // Function to show alerts
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            $('#alert-container').html(alertHtml);
        }

        // Function to initialize Select2 on a specific element
        function initializeSelect2(element) {
            $(element).select2({
                placeholder: $(element).hasClass('select2-multiple') ? "Select parameters" : "Select an option",
                allowClear: true
            });
        }

        // Initialize Select2 on page load for all select2 elements
        $(document).ready(function() {
            $('.select2').each(function() {
                initializeSelect2(this);
            });
        });

        // Function to update category dropdowns
        function updateCategoryDropdowns() {
            $.ajax({
                url: 'includes/category-ajax.php',
                type: 'POST',
                data: { action: 'fetch' },
                success: function(response) {
                    try {
                        const categories = JSON.parse(response);
                        let options = '<option value="">Select Category</option>';
                        categories.forEach(category => {
                            options += `<option value="${category.category_id}">${category.category_name}</option>`;
                        });

                        // Update both category dropdowns
                        const $categorySelect = $('#category-select');
                        const $editCategorySelect = $('#edit-category-select');

                        // Store current selections
                        const currentCategory = $categorySelect.val();
                        const currentEditCategory = $editCategorySelect.val();

                        // Destroy existing Select2 instances
                        $categorySelect.select2('destroy');
                        $editCategorySelect.select2('destroy');

                        // Update options
                        $categorySelect.html(options);
                        $editCategorySelect.html(options);

                        // Reinitialize Select2
                        initializeSelect2($categorySelect);
                        initializeSelect2($editCategorySelect);

                        // Restore selections
                        $categorySelect.val(currentCategory).trigger('change');
                        $editCategorySelect.val(currentEditCategory).trigger('change');
                    } catch (e) {
                        console.error('Error updating category dropdowns:', e);
                        showAlert('Failed to update category dropdowns', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error updating category dropdowns:', status, error);
                    showAlert('Error fetching categories', 'danger');
                }
            });
        }

        // Function to update parameter dropdowns
        function updateParameterDropdowns() {
            $.ajax({
                url: 'includes/parameter-ajax.php',
                type: 'POST',
                data: { action: 'fetch' },
                success: function(response) {
                    try {
                        const parameters = JSON.parse(response);
                        let options = '';
                        parameters.forEach(param => {
                            options += `<option value="${param.parameter_name}">${param.parameter_name}</option>`;
                        });

                        // Update both parameter dropdowns
                        const $parameterSelect = $('#parameter-select');
                        const $editParameterSelect = $('#edit-parameter-select');

                        // Store current selections
                        const currentParameters = $parameterSelect.val();
                        const currentEditParameters = $editParameterSelect.val();

                        // Destroy existing Select2 instances
                        $parameterSelect.select2('destroy');
                        $editParameterSelect.select2('destroy');

                        // Update options
                        $parameterSelect.html(options);
                        $editParameterSelect.html(options);

                        // Reinitialize Select2
                        $parameterSelect.addClass('select2-multiple');
                        $editParameterSelect.addClass('select2-multiple');
                        initializeSelect2($parameterSelect);
                        initializeSelect2($editParameterSelect);

                        // Restore selections
                        $parameterSelect.val(currentParameters).trigger('change');
                        $editParameterSelect.val(currentEditParameters).trigger('change');
                    } catch (e) {
                        console.error('Error updating parameter dropdowns:', e);
                        showAlert('Failed to update parameter dropdowns', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error updating parameter dropdowns:', status, error);
                    showAlert('Error fetching parameters', 'danger');
                }
            });
        }

        // Test Category AJAX
        $('#add-category-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'includes/category-ajax.php',
                type: 'POST',
                data: $(this).serialize() + '&action=add',
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#newCategoryModal').modal('hide');
                            $('#add-category-form')[0].reset();
                            // Refresh category table
                            const newRow = `
                                <tr data-id="${result.category_id}">
                                    <td>${result.category_id}</td>
                                    <td>${result.category_name}</td>
                                    <td>${new Date().toISOString().slice(0, 19).replace('T', ' ')}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-category" data-id="${result.category_id}" data-name="${result.category_name}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-category" data-id="${result.category_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>`;
                            $('#category-table tbody').prepend(newRow);
                            updateCategoryDropdowns();
                        }
                    } catch (e) {
                        console.error('Error adding category:', e);
                        showAlert('Failed to add category', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error adding category:', status, error);
                    showAlert('Error adding category', 'danger');
                }
            });
        });

        $(document).on('click', '.edit-category', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#editCategoryModal').modal('show');
            $('#edit-category-form [name="category_id"]').val(id);
            $('#edit-category-form [name="category_name"]').val(name);
        });

        $('#edit-category-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'includes/category-ajax.php',
                type: 'POST',
                data: $(this).serialize() + '&action=edit',
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#editCategoryModal').modal('hide');
                            const id = $('#edit-category-form [name="category_id"]').val();
                            const name = $('#edit-category-form [name="category_name"]').val();
                            $(`#category-table tr[data-id="${id}"] td:nth-child(2)`).text(name);
                            updateCategoryDropdowns();
                        }
                    } catch (e) {
                        console.error('Error editing category:', e);
                        showAlert('Failed to edit category', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error editing category:', status, error);
                    showAlert('Error editing category', 'danger');
                }
            });
        });

        $(document).on('click', '.delete-category', function() {
            const id = $(this).data('id');
            const name = $(`#category-table tr[data-id="${id}"] td:nth-child(2)`).text();
            $('#deleteCategoryModal').modal('show');
            $('#delete-category-name').text(name);
            $('#confirm-delete-category').data('id', id);
        });

        $('#confirm-delete-category').on('click', function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'includes/category-ajax.php',
                type: 'POST',
                data: { action: 'delete', category_id: id },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#deleteCategoryModal').modal('hide');
                            $(`#category-table tr[data-id="${id}"]`).remove();
                            updateCategoryDropdowns();
                        }
                    } catch (e) {
                        console.error('Error deleting category:', e);
                        showAlert('Failed to delete category', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error deleting category:', status, error);
                    showAlert('Error deleting category', 'danger');
                }
            });
        });

        // Test Parameter AJAX
        $('#add-parameter-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'includes/parameter-ajax.php',
                type: 'POST',
                data: $(this).serialize() + '&action=add',
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#newParameterModal').modal('hide');
                            $('#add-parameter-form')[0].reset();
                            // Refresh parameter table
                            const newRow = `
                                <tr data-id="${result.parameter_id}">
                                    <td>${result.parameter_id}</td>
                                    <td>${result.parameter_name}</td>
                                    <td>${new Date().toISOString().slice(0, 19).replace('T', ' ')}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-parameter" data-id="${result.parameter_id}" data-name="${result.parameter_name}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-parameter" data-id="${result.parameter_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>`;
                            $('#parameter-table tbody').prepend(newRow);
                            updateParameterDropdowns();
                        }
                    } catch (e) {
                        console.error('Error adding parameter:', e);
                        showAlert('Failed to add parameter', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error adding parameter:', status, error);
                    showAlert('Error adding parameter', 'danger');
                }
            });
        });

        $(document).on('click', '.edit-parameter', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#editParameterModal').modal('show');
            $('#edit-parameter-form [name="parameter_id"]').val(id);
            $('#edit-parameter-form [name="parameter_name"]').val(name);
        });

        $('#edit-parameter-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'includes/parameter-ajax.php',
                type: 'POST',
                data: $(this).serialize() + '&action=edit',
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#editParameterModal').modal('hide');
                            const id = $('#edit-parameter-form [name="parameter_id"]').val();
                            const name = $('#edit-parameter-form [name="parameter_name"]').val();
                            $(`#parameter-table tr[data-id="${id}"] td:nth-child(2)`).text(name);
                            updateParameterDropdowns();
                        }
                    } catch (e) {
                        console.error('Error editing parameter:', e);
                        showAlert('Failed to edit parameter', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error editing parameter:', status, error);
                    showAlert('Error editing parameter', 'danger');
                }
            });
        });

        $(document).on('click', '.delete-parameter', function() {
            const id = $(this).data('id');
            const name = $(`#parameter-table tr[data-id="${id}"] td:nth-child(2)`).text();
            $('#deleteParameterModal').modal('show');
            $('#delete-parameter-name').text(name);
            $('#confirm-delete-parameter').data('id', id);
        });

        $('#confirm-delete-parameter').on('click', function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'includes/parameter-ajax.php',
                type: 'POST',
                data: { action: 'delete', parameter_id: id },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#deleteParameterModal').modal('hide');
                            $(`#parameter-table tr[data-id="${id}"]`).remove();
                            updateParameterDropdowns();
                        }
                    } catch (e) {
                        console.error('Error deleting parameter:', e);
                        showAlert('Failed to delete parameter', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error deleting parameter:', status, error);
                    showAlert('Error deleting parameter', 'danger');
                }
            });
        });

        // Test AJAX
        $('#add-test-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'includes/test-ajax.php',
                type: 'POST',
                data: $(this).serialize() + '&action=add',
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#newTestModal').modal('hide');
                            $('#add-test-form')[0].reset();
                            $('#parameter-select').val(null).trigger('change');
                            $('#category-select').val(null).trigger('change');
                            // Refresh test table
                            const newRow = `
                                <tr data-id="${result.test_id}">
                                    <td>${result.test_id}</td>
                                    <td>${result.test_name}</td>
                                    <td>${$('#category-select option[value="' + result.category_id + '"]').text()}</td>
                                    <td>${result.test_code}</td>
                                    <td>${result.parameters}</td>
                                    <td>${result.reference_range}</td>
                                    <td>${result.price}</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-test" data-id="${result.test_id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-test" data-id="${result.test_id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>`;
                            $('#test-table tbody').prepend(newRow);
                        }
                    } catch (e) {
                        console.error('Error adding test:', e);
                        showAlert('Failed to add test', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error adding test:', status, error);
                    showAlert('Error adding test', 'danger');
                }
            });
        });

        $(document).on('click', '.edit-test', function() {
            const id = $(this).data('id');
            const row = $(`#test-table tr[data-id="${id}"]`);
            const test_name = row.find('td:nth-child(2)').text();
            const category_id = $('#category-select option:contains("' + row.find('td:nth-child(3)').text() + '")').val();
            const test_code = row.find('td:nth-child(4)').text();
            const parameters = row.find('td:nth-child(5)').text().split(',');
            const reference_range = row.find('td:nth-child(6)').text();
            const price = row.find('td:nth-child(7)').text();

            $('#editTestModal').modal('show');
            $('#edit-test-form [name="test_id"]').val(id);
            $('#edit-test-form [name="test_name"]').val(test_name);
            $('#edit-test-form [name="category_id"]').val(category_id).trigger('change');
            $('#edit-test-form [name="test_code"]').val(test_code);
            $('#edit-parameter-select').val(parameters).trigger('change');
            $('#edit-test-form [name="reference_range"]').val(reference_range);
            $('#edit-test-form [name="price"]').val(price);
        });

        $('#edit-test-form').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'includes/test-ajax.php',
                type: 'POST',
                data: $(this).serialize() + '&action=edit',
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#editTestModal').modal('hide');
                            const id = $('#edit-test-form [name="test_id"]').val();
                            const row = $(`#test-table tr[data-id="${id}"]`);
                            row.find('td:nth-child(2)').text(result.test_name);
                            row.find('td:nth-child(3)').text($('#edit-category-select option[value="' + result.category_id + '"]').text());
                            row.find('td:nth-child(4)').text(result.test_code);
                            row.find('td:nth-child(5)').text(result.parameters);
                            row.find('td:nth-child(6)').text(result.reference_range);
                            row.find('td:nth-child(7)').text(result.price);
                        }
                    } catch (e) {
                        console.error('Error editing test:', e);
                        showAlert('Failed to edit test', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error editing test:', status, error);
                    showAlert('Error editing test', 'danger');
                }
            });
        });

        $(document).on('click', '.delete-test', function() {
            const id = $(this).data('id');
            const name = $(`#test-table tr[data-id="${id}"] td:nth-child(2)`).text();
            $('#deleteTestModal').modal('show');
            $('#delete-test-name').text(name);
            $('#confirm-delete-test').data('id', id);
        });

        $('#confirm-delete-test').on('click', function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'includes/test-ajax.php',
                type: 'POST',
                data: { action: 'delete', test_id: id },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        showAlert(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            $('#deleteTestModal').modal('hide');
                            $(`#test-table tr[data-id="${id}"]`).remove();
                        }
                    } catch (e) {
                        console.error('Error deleting test:', e);
                        showAlert('Failed to delete test', 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error deleting test:', status, error);
                    showAlert('Error deleting test', 'danger');
                }
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