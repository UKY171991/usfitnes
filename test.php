<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true
]);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    $db = Database::getInstance();
    
    // Fetch all categories for dropdown
    $stmt = $db->query("SELECT category_id, category_name FROM Test_Categories ORDER BY category_name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    error_log("Error fetching categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'inc/head.php'; ?>
    <title>Test Management | Lab Management System</title>
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
        }
        .card-title {
            margin-bottom: 0;
            color: #2c3e50;
            font-size: 1.25rem;
        }
        .table th {
            font-weight: 600;
            color: #495057;
            border-top: none;
        }
        .table td {
            vertical-align: middle;
            color: #6c757d;
        }
        .btn-action {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
            margin-left: 10px;
        }
        .btn-new-category {
            background-color: #3498db;
            border-color: #3498db;
        }
        .btn-new-parameter {
            background-color: #2ecc71;
            border-color: #2ecc71;
        }
        .btn-new-test {
            background-color: #9b59b6;
            border-color: #9b59b6;
        }
        .action-buttons .btn {
            padding: 5px 10px;
            font-size: 14px;
            margin: 0 2px;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .modal-footer {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        .required::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
        .badge-count {
            background-color: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        .edit-btn {
            background-color: #3498db;
            border-color: #3498db;
            color: #fff;
        }
        .delete-btn {
            background-color: #e74c3c;
            border-color: #e74c3c;
            color: #fff;
        }
        .edit-btn:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        .delete-btn:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }
    </style>
</head>
<body class="layout-fixed">
    <div class="wrapper">
        <?php include 'inc/sidebar.php'; ?>
        
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Test Management</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <button type="button" class="btn btn-primary btn-action btn-new-category" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-folder-plus me-2"></i>New Category
                            </button>
                            <button type="button" class="btn btn-success btn-action btn-new-parameter" data-bs-toggle="modal" data-bs-target="#addParameterModal">
                                <i class="fas fa-list-ul me-2"></i>New Parameter
                            </button>
                            <button type="button" class="btn btn-info btn-action btn-new-test" data-bs-toggle="modal" data-bs-target="#addTestModal">
                                <i class="fas fa-vial me-2"></i>New Test
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <!-- Test Categories Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Test Category List</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoriesTableBody">
                                        <!-- Categories will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Test Parameters Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Test Parameter List</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Parameter Name</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="parametersTableBody">
                                        <!-- Parameters will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Tests Card -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Test List</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Test Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="testsTableBody">
                                        <!-- Tests will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label class="form-label required">Category Name</label>
                            <input type="text" class="form-control" name="category_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Parameter Modal -->
    <div class="modal fade" id="addParameterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Parameter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addParameterForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label class="form-label required">Parameter Name</label>
                            <input type="text" class="form-control" name="parameter_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Parameter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Test Modal -->
    <div class="modal fade" id="addTestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTestForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Test Name</label>
                                    <input type="text" class="form-control" name="test_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Test Code</label>
                                    <input type="text" class="form-control" name="test_code" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Category</label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                                                <?php echo htmlspecialchars($category['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label required">Price</label>
                                    <input type="number" class="form-control" name="price" step="0.01" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parameters</label>
                            <div id="parametersList" class="border rounded p-3">
                                <!-- Parameters will be loaded dynamically -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference Range</label>
                            <textarea class="form-control" name="reference_range" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control" name="unit">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Test
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form submissions
        const forms = {
            category: document.getElementById('addCategoryForm'),
            parameter: document.getElementById('addParameterForm'),
            test: document.getElementById('addTestForm')
        };

        // Add submit event listeners
        Object.entries(forms).forEach(([type, form]) => {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                await submitForm(this, type);
            });
        });

        // Load initial data
        loadCategories();
        loadParameters();
        loadTests();
    });

    async function submitForm(form, type) {
        try {
            const formData = new FormData(form);
            formData.append('action', 'add');

            const response = await fetch(`includes/process_${type}.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`${type.charAt(0).toUpperCase() + type.slice(1)} added successfully`);
                location.reload();
            } else {
                alert(result.message || `Failed to add ${type}`);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while processing your request');
        }
    }

    function loadCategories() {
        fetch('includes/fetch_categories.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateCategoriesTable(data.categories);
                } else {
                    showError('categoriesTableBody', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('categoriesTableBody', 'Failed to load categories');
            });
    }

    function loadParameters() {
        fetch('includes/fetch_parameters.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateParametersTable(data.parameters);
                    updateParametersList(data.parameters);
                } else {
                    showError('parametersTableBody', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('parametersTableBody', 'Failed to load parameters');
            });
    }

    function loadTests() {
        fetch('includes/fetch_tests.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTestsTable(data.tests);
                } else {
                    showError('testsTableBody', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('testsTableBody', 'Failed to load tests');
            });
    }

    function updateCategoriesTable(categories) {
        const tbody = document.getElementById('categoriesTableBody');
        tbody.innerHTML = categories.map(category => `
            <tr>
                <td>${category.category_id}</td>
                <td>${category.category_name}</td>
                <td>${formatDate(category.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm edit-btn" onclick="editCategory(${category.category_id}, '${category.category_name}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm delete-btn" onclick="deleteCategory(${category.category_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function updateParametersTable(parameters) {
        const tbody = document.getElementById('parametersTableBody');
        tbody.innerHTML = parameters.map(parameter => `
            <tr>
                <td>${parameter.parameter_id}</td>
                <td>${parameter.parameter_name}</td>
                <td>${formatDate(parameter.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm edit-btn" onclick="editParameter(${parameter.parameter_id}, '${parameter.parameter_name}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm delete-btn" onclick="deleteParameter(${parameter.parameter_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function updateTestsTable(tests) {
        const tbody = document.getElementById('testsTableBody');
        tbody.innerHTML = tests.map(test => `
            <tr>
                <td>${test.test_id}</td>
                <td>${test.test_name}</td>
                <td>${test.category_name}</td>
                <td>${formatPrice(test.price)}</td>
                <td>${formatDate(test.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm edit-btn" onclick="editTest(${test.test_id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm delete-btn" onclick="deleteTest(${test.test_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function updateParametersList(parameters) {
        const container = document.getElementById('parametersList');
        container.innerHTML = parameters.map(parameter => `
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="parameters[]" value="${parameter.parameter_id}" id="param${parameter.parameter_id}">
                <label class="form-check-label" for="param${parameter.parameter_id}">
                    ${parameter.parameter_name}
                </label>
            </div>
        `).join('');
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(price);
    }

    function showError(tableId, message) {
        const tbody = document.getElementById(tableId);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger">
                    ${message}
                </td>
            </tr>
        `;
    }
    </script>
</body>
</html>