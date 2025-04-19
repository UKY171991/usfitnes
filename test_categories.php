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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'inc/head.php'; ?>
    <title>Test Categories | Lab Management System</title>
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 1.5rem;
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
        .btn-add-category {
            border-radius: 20px;
            padding: 8px 20px;
            font-weight: 500;
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
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }
        .delete-btn {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
        }
        .edit-btn:hover {
            background-color: #ffb300;
            border-color: #ffb300;
        }
        .delete-btn:hover {
            background-color: #c82333;
            border-color: #c82333;
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
                            <h1>Test Category Management</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            <button type="button" class="btn btn-primary btn-add-category float-end" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus me-2"></i>Add New Category
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Tests Count</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoriesTableBody">
                                        <!-- Table content will be loaded dynamically -->
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

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editCategoryForm">
                    <div class="modal-body">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="category_id" id="editCategoryId">
                        <div class="mb-3">
                            <label class="form-label required">Category Name</label>
                            <input type="text" class="form-control" name="category_name" id="editCategoryName" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize form submission
        const addCategoryForm = document.getElementById('addCategoryForm');
        const editCategoryForm = document.getElementById('editCategoryForm');

        addCategoryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitForm(this, 'add');
        });

        editCategoryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitForm(this, 'edit');
        });

        // Load categories on page load
        loadCategories();
    });

    async function submitForm(form, action) {
        try {
            const formData = new FormData(form);
            formData.append('action', action);

            const response = await fetch('includes/process_category.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(action === 'add' ? 'Category added successfully' : 'Category updated successfully');
                location.reload();
            } else {
                alert(result.message || `Failed to ${action} category`);
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
                    showError(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load categories');
            });
    }

    function updateCategoriesTable(categories) {
        const tbody = document.getElementById('categoriesTableBody');
        tbody.innerHTML = categories.map(category => `
            <tr>
                <td>${category.category_id}</td>
                <td>${category.category_name}</td>
                <td><span class="badge-count">${category.tests_count}</span></td>
                <td>${formatDate(category.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-sm edit-btn" onclick="editCategory(${category.category_id}, '${category.category_name}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${category.tests_count === 0 ? `
                            <button class="btn btn-sm delete-btn" onclick="deleteCategory(${category.category_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function editCategory(id, name) {
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
    }

    async function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('category_id', id);
            formData.append('csrf_token', '<?php echo $_SESSION["csrf_token"]; ?>');

            const response = await fetch('includes/process_category.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Category deleted successfully');
                location.reload();
            } else {
                alert(result.message || 'Failed to delete category');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while deleting the category');
        }
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function showError(message) {
        const tbody = document.getElementById('categoriesTableBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center text-danger">
                    ${message}
                </td>
            </tr>
        `;
    }
    </script>
</body>
</html>