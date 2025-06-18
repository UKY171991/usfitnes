<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

$success_msg = '';
$error_msg = '';

// Function to generate unique branch code
function generateBranchCode($conn) {
    // Get the last branch code number
    $stmt = $conn->query("SELECT branch_code FROM branches WHERE branch_code LIKE 'BR%' ORDER BY id DESC LIMIT 1");
    $lastCode = $stmt->fetchColumn();
    
    if ($lastCode) {
        // Extract the number from the last code and increment it
        $number = intval(substr($lastCode, 2)) + 1;
    } else {
        // Start with 1 if no existing codes
        $number = 1;
    }
    
    // Generate new code with leading zeros (e.g., BR001, BR002)
    return 'BR' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

// Handle form submission for adding/editing branch
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch_id = $_POST['branch_id'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? '1'; // Default to active if not provided

    if (empty($name)) {
        $error_msg = "Branch name is required";
    } else {
        try {
            if (empty($branch_id)) {
                // Generate new branch code for new branches
                $branch_code = generateBranchCode($conn);
                // Status for new branches is taken from the form (defaults to '1' - Active)
                $stmt = $conn->prepare("INSERT INTO branches (branch_code, branch_name, address, city, state, pincode, phone, email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$branch_code, $name, $address, $city, $state, $pincode, $phone, $email, $status]);
                $success_msg = "Branch added successfully";
            } else {
                // Update existing branch - don't update branch_code
                $stmt = $conn->prepare("UPDATE branches SET branch_name = ?, address = ?, city = ?, state = ?, pincode = ?, phone = ?, email = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $address, $city, $state, $pincode, $phone, $email, $status, $branch_id]);
                $success_msg = "Branch updated successfully";
            }

            // Log activity
            $activity = empty($branch_id) ? "Added new branch: $name" : "Updated branch: $name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);

        } catch (PDOException $e) {
            $error_msg = "Error: " . $e->getMessage();
        }
    }
}

// Handle branch deletion
if (isset($_POST['delete_branch'])) {
    try {
        $branch_id = $_POST['branch_id'];
        
        // Check if branch has any associated users
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE branch_id = ?");
        $stmt->execute([$branch_id]);
        $user_count = $stmt->fetchColumn();

        if ($user_count > 0) {
            $error_msg = "Cannot delete branch: There are users associated with this branch";
        } else {
            // Get branch name for activity log
            $stmt = $conn->prepare("SELECT branch_name FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);
            $branch_name = $stmt->fetchColumn();

            // Delete the branch
            $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->execute([$branch_id]);

            // Log activity
            $activity = "Deleted branch: $branch_name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);

            $success_msg = "Branch deleted successfully";
        }
    } catch (PDOException $e) {
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Fetch all branches (commenting out for AJAX pagination)
// try {
//     $branches = $conn->query("SELECT * FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);
// } catch (PDOException $e) {
//     $error_msg = "Error fetching branches: " . $e->getMessage();
//     $branches = [];
// }

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
    <h1 class="h2">Manage Branches</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#branchModal">
        <i class="fas fa-plus"></i> Add New Branch
    </button>
</div>

<?php if ($success_msg): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error_msg; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Branches Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Branches List</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search branches...">
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
                        <th>Sr. No.</th>
                        <th>Branch Code</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="branches-table-body">
                    <!-- Table rows will be loaded via AJAX -->
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

<!-- View Branch Modal -->
<div class="modal fade" id="viewBranchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Branch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Basic Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Branch Code</dt>
                                    <dd class="col-sm-8" id="view-branch-code">-</dd>
                                    
                                    <dt class="col-sm-4">Branch Name</dt>
                                    <dd class="col-sm-8" id="view-branch-name">-</dd>
                                    
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="view-branch-status">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Contact Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Phone</dt>
                                    <dd class="col-sm-8" id="view-branch-phone">-</dd>
                                    
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8" id="view-branch-email">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Location Details</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-2">Address</dt>
                                    <dd class="col-sm-10" id="view-branch-address">-</dd>
                                    
                                    <dt class="col-sm-2">City</dt>
                                    <dd class="col-sm-4" id="view-branch-city">-</dd>
                                    
                                    <dt class="col-sm-2">State</dt>
                                    <dd class="col-sm-4" id="view-branch-state">-</dd>
                                    
                                    <dt class="col-sm-2">Pincode</dt>
                                    <dd class="col-sm-4" id="view-branch-pincode">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Branch Modal -->
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="branchModalLabel">Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="branch_id" id="branch_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Branch Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required maxlength="100">
                            <div class="invalid-feedback">Please enter branch name</div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required maxlength="15">
                            <div class="invalid-feedback">Please enter phone number</div>
                        </div>
                        <div class="col-md-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control" id="state" name="state" maxlength="100">
                        </div>
                        <div class="col-md-4">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control" id="pincode" name="pincode" maxlength="10">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" maxlength="100">
                            <div class="invalid-feedback">Please enter a valid email address</div>
                        </div>
                        <div class="col-md-12">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Branch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    const itemsPerPage = 10;
    let searchTerm = '';
    let searchTimeout = null;

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            searchTerm = this.value.trim();
            currentPage = 1; // Reset to first page when searching
            fetchBranches(currentPage);
        }, 300); // Debounce search by 300ms
    });

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchTerm = '';
        currentPage = 1;
        fetchBranches(currentPage);
    });

    function fetchBranches(page) {
        const searchParam = searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '';
        fetch(`ajax/get_branches.php?page=${page}&itemsPerPage=${itemsPerPage}${searchParam}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderTable(data.branches, (page - 1) * itemsPerPage);
                    renderPagination(data.totalPages, parseInt(data.currentPage));
                    currentPage = parseInt(data.currentPage);
                    attachActionListeners();
                } else {
                    console.error('Error fetching branches:', data.message);
                    document.getElementById('branches-table-body').innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error loading branches: ${data.message}</p></div></td></tr>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                document.getElementById('branches-table-body').innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Could not connect to server.</p></div></td></tr>`;
            });
    }    function renderTable(branches, offset) {
        const tbody = document.getElementById('branches-table-body');
        tbody.innerHTML = '';
        if (branches.length === 0) {
            const message = searchTerm ? 
                `<div class="text-muted"><i class="fas fa-search fa-2x mb-2"></i><p>No branches found matching "${searchTerm}"</p><p class="small">Try adjusting your search terms</p></div>` :
                '<div class="text-muted"><i class="fas fa-hospital fa-2x mb-2"></i><p>No branches found</p></div>';
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">${message}</td></tr>`;
            return;
        }        branches.forEach((branch, index) => {
            const sr_no = offset + index + 1;
            const statusBadge = branch.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            
            // Use highlighting for search results
            const branchCode = highlightSearchTerm(branch.branch_code, searchTerm);
            const branchName = highlightSearchTerm(branch.branch_name, searchTerm);
            const phone = highlightSearchTerm(branch.phone, searchTerm);
            const email = highlightSearchTerm(branch.email, searchTerm);
            const city = highlightSearchTerm(branch.city, searchTerm);
            const state = highlightSearchTerm(branch.state, searchTerm);
            const pincode = highlightSearchTerm(branch.pincode, searchTerm);
            
            const location = `${city}, ${state}, ${pincode}`;
            const contact = `${phone}<br>${email}`;
            
            const row = `
                <tr>
                    <td>${sr_no}</td>
                    <td>${branchCode}</td>
                    <td>${branchName}</td>
                    <td>${contact}</td>
                    <td>${location}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info view-branch" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewBranchModal"
                                    data-id="${branch.id}"
                                    data-code="${escapeHTML(branch.branch_code)}"
                                    data-name="${escapeHTML(branch.branch_name)}"
                                    data-address="${escapeHTML(branch.address)}"
                                    data-city="${escapeHTML(branch.city)}"
                                    data-state="${escapeHTML(branch.state)}"
                                    data-pincode="${escapeHTML(branch.pincode)}"
                                    data-phone="${escapeHTML(branch.phone)}"
                                    data-email="${escapeHTML(branch.email)}"
                                    data-status="${branch.status}"
                                    title="View Branch">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary edit-branch" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#branchModal"
                                    data-id="${branch.id}"
                                    data-code="${escapeHTML(branch.branch_code)}"
                                    data-name="${escapeHTML(branch.branch_name)}"
                                    data-address="${escapeHTML(branch.address)}"
                                    data-city="${escapeHTML(branch.city)}"
                                    data-state="${escapeHTML(branch.state)}"
                                    data-pincode="${escapeHTML(branch.pincode)}"
                                    data-phone="${escapeHTML(branch.phone)}"
                                    data-email="${escapeHTML(branch.email)}"
                                    data-status="${branch.status}"
                                    title="Edit Branch">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="branches.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this branch?')">
                                <input type="hidden" name="delete_branch" value="1">
                                <input type="hidden" name="branch_id" value="${branch.id}">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Branch">
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
            if (currentPage > 1) fetchBranches(currentPage - 1);
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
                fetchBranches(i);
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
            if (currentPage < totalPages) fetchBranches(currentPage + 1);
        });
        nextLi.appendChild(nextA);
        paginationControls.appendChild(nextLi);
    }

    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>\"'`]/g, function (match) {
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

    function highlightSearchTerm(text, searchTerm) {
        if (!searchTerm || searchTerm.length < 2) return escapeHTML(text);
        
        const escapedText = escapeHTML(text);
        const escapedSearchTerm = escapeHTML(searchTerm);
        const regex = new RegExp(`(${escapedSearchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escapedText.replace(regex, '<span class="search-highlight">$1</span>');
    }

    function attachActionListeners() {
        // Handle view branch button clicks
        document.querySelectorAll('.view-branch').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                document.getElementById('view-branch-code').textContent = this.dataset.code || '-';
                document.getElementById('view-branch-name').textContent = this.dataset.name || '-';
                document.getElementById('view-branch-status').innerHTML = `
                    <span class="badge bg-${this.dataset.status == 1 ? 'success' : 'danger'}">
                        ${this.dataset.status == 1 ? 'Active' : 'Inactive'}
                    </span>
                `;
                document.getElementById('view-branch-phone').textContent = this.dataset.phone || '-';
                document.getElementById('view-branch-email').textContent = this.dataset.email || '-';
                document.getElementById('view-branch-address').textContent = this.dataset.address || '-';
                document.getElementById('view-branch-city').textContent = this.dataset.city || '-';
                document.getElementById('view-branch-state').textContent = this.dataset.state || '-';
                document.getElementById('view-branch-pincode').textContent = this.dataset.pincode || '-';
            });
        });

        // Handle edit branch button clicks
        document.querySelectorAll('.edit-branch').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const modal = document.getElementById('branchModal');
                modal.querySelector('.modal-title').textContent = 'Edit Branch';
                modal.querySelector('#branch_id').value = this.dataset.id;
                
                modal.querySelector('#name').value = this.dataset.name;
                modal.querySelector('#address').value = this.dataset.address;
                modal.querySelector('#city').value = this.dataset.city;
                modal.querySelector('#state').value = this.dataset.state;
                modal.querySelector('#pincode').value = this.dataset.pincode;
                modal.querySelector('#phone').value = this.dataset.phone;
                modal.querySelector('#email').value = this.dataset.email;
                modal.querySelector('#status').value = this.dataset.status;
            });
        });
    }

    // Initial fetch
    fetchBranches(currentPage);

    // Reset form when adding new branch
    document.querySelector('[data-bs-target="#branchModal"]').addEventListener('click', function() {
        const modal = document.getElementById('branchModal');
        modal.querySelector('.modal-title').textContent = 'Add New Branch';
        modal.querySelector('form').reset();
        modal.querySelector('#branch_id').value = '';
        modal.querySelector('form').classList.remove('was-validated');
    });
});
</script>

<?php include '../inc/footer.php'; ?>