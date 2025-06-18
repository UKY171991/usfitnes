<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle delete request
if(isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'] ?? 0;
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Delete associated tests first
        $stmt = $conn->prepare("DELETE FROM tests WHERE category_id = ?");
        $stmt->execute([$category_id]);
        
        // Then delete the category
        $stmt = $conn->prepare("DELETE FROM test_categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        // Log activity
        $activity = "Test category deleted: ID $category_id";
        $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $activity]);
        
        // Commit transaction
        $conn->commit();
        
        header("Location: test-categories.php?success=2");
        exit();
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error = "Error deleting category: " . $e->getMessage();
    }
}

// Handle edit request
if(isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'] ?? 0;
    $name = $_POST['category_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? 0; // Get status value (default to 0 - inactive)
    
    if(!empty($name)) {
        try {
            $stmt = $conn->prepare("
                UPDATE test_categories 
                SET category_name = ?, description = ?, status = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $status, $category_id]);
            
            // Log activity
            $activity = "Test category updated: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-categories.php?success=3");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating category: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a category name";
    }
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_category']) && !isset($_POST['delete_category'])) {
    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? '0'; // Default to '0' (inactive) if not provided

    if(!empty($name)) {
        try {
            $stmt = $conn->prepare("INSERT INTO test_categories (category_name, description, status) VALUES (?, ?, ?)");
            $stmt->execute([$name, $description, $status]);
            
            // Log activity
            $activity = "New test category added: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-categories.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding category: " . $e->getMessage();
        }
    } else {
        $error = "Please enter a category name";
    }
}

// Get all categories with test counts
// $stmt = $conn->query("
//     SELECT 
//         tc.*, 
//         COUNT(t.id) as test_count 
//     FROM test_categories tc
//     LEFT JOIN tests t ON tc.id = t.category_id
//     GROUP BY tc.id
//     ORDER BY tc.category_name
// ");
// $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>
<link rel="stylesheet" href="admin-shared.css">
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#searchInput {
    transition: all 0.3s ease;
}

#searchInput:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    border-color: #80bdff;
}

#clearSearch {
    border-left: 0;
}

.input-group .btn {
    z-index: 2;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
}
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Test Categories</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="fas fa-plus"></i> Add New Category
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Category added successfully!</div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Test Categories Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Test Categories List</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search categories...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Category Details</th>
                        <th>Tests</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="categories-table-body">
                    <?php /* if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-flask fa-2x mb-2"></i>
                                    <p>No test categories found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $sr_no = 1; // Initialize serial number ?>
                        <?php foreach($categories as $category): 
                            $db_status = $category['status']; // Assuming this is 0 or 1 from DB
                            $status_string = ($db_status == 1) ? 'active' : 'inactive';
                            $test_count = $category['test_count'] ?? 0; // Default to 0 if missing
                        ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $sr_no++; // Display and increment serial number ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($category['category_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($category['description'] ?: 'No description'); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $test_count; ?> Tests
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo ($db_status == 1) ? 'success' : 'danger'; ?>">
                                        <?php echo ($db_status == 1) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-category" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewCategoryModal"
                                                data-id="<?php echo $category['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                                data-status="<?php echo $status_string; ?>"
                                                data-test-count="<?php echo $test_count; ?>"
                                                title="View Category">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary edit-category" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#categoryModal"
                                                data-id="<?php echo $category['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                data-description="<?php echo htmlspecialchars($category['description']); ?>"
                                                data-status="<?php echo $status_string; ?>"
                                                title="Edit Category">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this category? All tests in this category will also be deleted.')"
                                           title="Delete Category">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; */ ?>
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" id="pagination-controls">
                <!-- Pagination controls will be inserted here by JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<!-- View Category Modal -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Category Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Category Name</dt>
                            <dd class="col-sm-8" id="view-category-name">-</dd>
                            
                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8" id="view-category-description">-</dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8" id="view-category-status">-</dd>
                            
                            <dt class="col-sm-4">Tests Count</dt>
                            <dd class="col-sm-8" id="view-category-test-count">-</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
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
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="add_status" class="form-label">Status</label>
                        <select class="form-select" id="add_status" name="status">
                            <option value="1">Active</option>
                            <option value="0" selected>Inactive</option> <!-- Default to Inactive -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="edit_category" value="1">
                <input type="hidden" name="category_id" id="category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const categoryEditModalEl = document.getElementById('categoryModal');
    const categoryViewModalEl = document.getElementById('viewCategoryModal');
    
    // Initialize Bootstrap Modals
    const categoryEditModal = new bootstrap.Modal(categoryEditModalEl);
    const categoryViewModal = new bootstrap.Modal(categoryViewModalEl); // Assuming view modal exists

    let currentPage = 1;
    const itemsPerPage = 10; // Or get from a select input
    let searchTerm = '';
    let searchTimeout = null;

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchTerm = this.value.trim();
            currentPage = 1; // Reset to first page when searching
            fetchCategories(currentPage);
        }, 300); // Debounce search by 300ms
    });

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchTerm = '';
        currentPage = 1;
        fetchCategories(currentPage);
    });

    function fetchCategories(page) {
        const searchParam = searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '';
        fetch(`ajax/get_test_categories.php?page=${page}&itemsPerPage=${itemsPerPage}${searchParam}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderTable(data.categories, (page - 1) * itemsPerPage);
                    renderPagination(data.totalPages, parseInt(data.currentPage));
                    currentPage = parseInt(data.currentPage);
                    // Re-attach event listeners for view/edit buttons if they are dynamically added
                    attachActionListeners(); 
                } else {
                    console.error('Error fetching categories:', data.message);
                    document.getElementById('categories-table-body').innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error loading categories: ${data.message}</p></div></td></tr>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                document.getElementById('categories-table-body').innerHTML = `<tr><td colspan="5" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Could not connect to server.</p></div></td></tr>`;
            });
    }

    function renderTable(categories, offset) {
        const tbody = document.getElementById('categories-table-body');
        tbody.innerHTML = ''; // Clear existing rows
        if (categories.length === 0) {
            const message = searchTerm ? 
                `<div class="text-muted"><i class="fas fa-search fa-2x mb-2"></i><p>No categories found matching "${searchTerm}"</p><p class="small">Try adjusting your search terms</p></div>` :
                '<div class="text-muted"><i class="fas fa-flask fa-2x mb-2"></i><p>No test categories found</p></div>';
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4">${message}</td></tr>`;
            return;
        }

        categories.forEach((category, index) => {
            const sr_no = offset + index + 1;
            const status_string = (category.status == 1) ? 'active' : 'inactive';
            const test_count = category.test_count || 0;
            const row = `
                <tr>
                    <td><span class="badge bg-secondary">${sr_no}</span></td>
                    <td>
                        <div class="fw-bold">${escapeHTML(category.category_name)}</div>
                        <small class="text-muted">${escapeHTML(category.description || 'No description')}</small>
                    </td>
                    <td><span class="badge bg-info">${test_count} Tests</span></td>
                    <td>
                        <span class="badge bg-${(category.status == 1) ? 'success' : 'danger'}">
                            ${(category.status == 1) ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info view-category" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewCategoryModal"
                                    data-id="${category.id}"
                                    data-name="${escapeHTML(category.category_name)}"
                                    data-description="${escapeHTML(category.description)}"
                                    data-status="${status_string}"
                                    data-test-count="${test_count}"
                                    title="View Category">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary edit-category" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#categoryModal"
                                    data-id="${category.id}"
                                    data-name="${escapeHTML(category.category_name)}"
                                    data-description="${escapeHTML(category.description)}"
                                    data-status="${status_string}"
                                    title="Edit Category">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="test-categories.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category? All tests in this category will also be deleted.')">
                                <input type="hidden" name="delete_category" value="1">
                                <input type="hidden" name="category_id" value="${category.id}">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Category">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function renderPagination(totalPages, currentPage) {
        const paginationControls = document.getElementById('pagination-controls');
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        const prevA = document.createElement('a');
        prevA.className = 'page-link';
        prevA.href = '#';
        prevA.textContent = 'Previous';
        prevA.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) fetchCategories(currentPage - 1);
        });
        prevLi.appendChild(prevA);
        paginationControls.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                fetchCategories(i);
            });
            li.appendChild(a);
            paginationControls.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        const nextA = document.createElement('a');
        nextA.className = 'page-link';
        nextA.href = '#';
        nextA.textContent = 'Next';
        nextA.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) fetchCategories(currentPage + 1);
        });
        nextLi.appendChild(nextA);
        paginationControls.appendChild(nextLi);
    }
    
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>\"\'`]/g, function (match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '`': '&#x60;'
            }[match];
        });
    }

    function attachActionListeners() {
        // Re-attach view category button clicks
        document.querySelectorAll('.view-category').forEach(button => {
            // Remove existing listener to prevent multiple attachments if any
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                document.getElementById('view-category-name').textContent = this.dataset.name || '-';
                document.getElementById('view-category-description').textContent = this.dataset.description || '-';
                document.getElementById('view-category-status').innerHTML = `
                    <span class="badge bg-${this.dataset.status == 'active' ? 'success' : 'danger'}">
                        ${this.dataset.status == 'active' ? 'Active' : 'Inactive'}
                    </span>
                `;
                document.getElementById('view-category-test-count').innerHTML = `
                    <span class="badge bg-info">${this.dataset.testCount || 0} Tests</span>
                `;
                categoryViewModal.show(); 
            });
        });

        // Re-attach edit category button clicks
        document.querySelectorAll('.edit-category').forEach(button => {
            // Remove existing listener
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const modalTitle = categoryEditModalEl.querySelector('.modal-title');
                const categoryIdInput = categoryEditModalEl.querySelector('#category_id');
                // Ensure you are selecting the correct category_name input for edit modal
                const categoryNameInput = categoryEditModalEl.querySelector('form[method="POST"] #category_name'); 
                const descriptionTextarea = categoryEditModalEl.querySelector('form[method="POST"] #description');
                const statusSelect = categoryEditModalEl.querySelector('form[method="POST"] #status');

                modalTitle.textContent = 'Edit Category';
                
                categoryIdInput.value = this.dataset.id || '';
                if(categoryNameInput) categoryNameInput.value = this.dataset.name || '';
                if(descriptionTextarea) descriptionTextarea.value = this.dataset.description || '';
                
                const statusValue = (this.dataset.status === 'active') ? '1' : '0';
                if(statusSelect) statusSelect.value = statusValue;
                
                categoryEditModal.show();
            });
        });
    }

    // Initial fetch
    fetchCategories(currentPage);

    // Handle view category button clicks (Initial setup, will be re-attached in fetchCategories)
    // ... (keep existing .view-category and .edit-category listeners as they are, 
    //      but ensure they are either general enough or re-attached after AJAX updates)
    // It's better to call attachActionListeners after the first render too.
    // attachActionListeners(); // Call after initial table render if categories were pre-loaded by PHP

    // Handle edit category button clicks (Initial setup)
    // ... (similar to view-category)
});
</script>

<?php include '../inc/footer.php'; ?>