<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Helper function to get role badge color
function getRoleBadgeColor($role) {
    $colors = [
        'master_admin' => 'danger',
        'admin' => 'warning',
        'branch_admin' => 'info',
        'receptionist' => 'success',
        'technician' => 'primary'
    ];
    return $colors[$role] ?? 'secondary';
}

// Helper function to format role display name
function formatRole($role) {
    if (empty($role)) return '-';
    return ucwords(str_replace('_', ' ', $role));
}

// Set base URL for all relative links
$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') . '/';

// Handle delete request
if(isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        header("Location: users.php?success=2");
        exit();
    } catch(PDOException $e) {
        $error = "Error deleting user: " . $e->getMessage();
    }
}

// Handle edit request
if(isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $branch_id = $_POST['branch_id'] ?? null;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $password = $_POST['password'] ?? '';
    if(!empty($name) && !empty($username) && !empty($role)) {
        try {
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, username = ?, email = ?, phone = ?, role = ?, branch_id = ?, status = ?, password = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $username, $email, $phone, $role, $branch_id, $status, $hashed_password, $user_id]);
            } else {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET name = ?, username = ?, email = ?, phone = ?, role = ?, branch_id = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $username, $email, $phone, $role, $branch_id, $status, $user_id]);
            }
            $activity = "User updated: ID $user_id";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            header("Location: users.php?success=3");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Handle add user
if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['name']) && isset($_POST['role'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $branch_id = $_POST['branch_id'] ?? null;
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    if(!empty($username) && !empty($password) && !empty($name) && !empty($role)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("
                INSERT INTO users (username, password, name, email, phone, role, branch_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$username, $hashed_password, $name, $email, $phone, $role, $branch_id, $status]);
            $activity = "New user added: $username";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            header("Location: users.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Get all users with branch names (commenting out for AJAX pagination)
// $users = $conn->query("
//     SELECT u.*, b.branch_name 
//     FROM users u 
//     LEFT JOIN branches b ON u.branch_id = b.id 
//     ORDER BY u.id DESC
// ")->fetchAll(PDO::FETCH_ASSOC);

// Get all branches for dropdown
$branches = $conn->query("SELECT id, branch_name as name FROM branches ORDER BY branch_name")->fetchAll(PDO::FETCH_ASSOC);

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
    <h1 class="h2">Manage Users</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus"></i> Add New User
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
            switch($_GET['success']) {
                case 1:
                    echo "User added successfully!";
                    break;
                case 2:
                    echo "User deleted successfully!";
                    break;
                case 3:
                    echo "User updated successfully!";
                    break;
            }
        ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Users List</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
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
                        <th>Name</th>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="users-table-body">
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="master_admin">Master Admin</option>
                                    <option value="admin">Admin</option>
                                    <option value="branch_admin">Branch Admin</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="technician">Technician</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="branch_id" class="form-label">Branch</label>
                                <select class="form-control" id="branch_id" name="branch_id">
                                    <option value="">Select Branch</option>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?php echo $branch['id']; ?>">
                                            <?php echo htmlspecialchars($branch['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="edit_user" value="1">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="edit_username" name="username" required readonly>
                                <div class="form-text">Username cannot be changed</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                                <div class="invalid-feedback">Please enter full name</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                                <div class="invalid-feedback">Please enter a valid email</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_role" class="form-label">Role *</label>
                                <select class="form-control" id="edit_role" name="role" required>
                                    <option value="master_admin">Master Admin</option>
                                    <option value="admin">Admin</option>
                                    <option value="branch_admin">Branch Admin</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="technician">Technician</option>
                                </select>
                                <div class="invalid-feedback">Please select a role</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_branch_id" class="form-label">Branch</label>
                                <select class="form-control" id="edit_branch_id" name="branch_id">
                                    <option value="">Select Branch</option>
                                    <?php foreach($branches as $branch): ?>
                                        <option value="<?php echo $branch['id']; ?>">
                                            <?php echo htmlspecialchars($branch['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
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

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Basic Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8" id="view-user-name">-</dd>
                                    
                                    <dt class="col-sm-4">Username</dt>
                                    <dd class="col-sm-8" id="view-user-username">-</dd>
                                    
                                    <dt class="col-sm-4">Role</dt>
                                    <dd class="col-sm-8" id="view-user-role">-</dd>
                                    
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="view-user-status">-</dd>
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
                                    <dd class="col-sm-8" id="view-user-phone">-</dd>
                                    
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8" id="view-user-email">-</dd>
                                    
                                    <dt class="col-sm-4">Branch</dt>
                                    <dd class="col-sm-8" id="view-user-branch">-</dd>
                                    
                                    <dt class="col-sm-4">Last Login</dt>
                                    <dd class="col-sm-8" id="view-user-last-login">-</dd>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 1;
    const itemsPerPage = 10;
    let searchTerm = '';
    let searchTimeout = null;    // Form validation
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
            fetchUsers(currentPage);
        }, 300); // Debounce search by 300ms
    });

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        searchTerm = '';
        currentPage = 1;
        fetchUsers(currentPage);
    });

    function fetchUsers(page) {
        const searchParam = searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '';
        fetch(`ajax/get_users.php?page=${page}&itemsPerPage=${itemsPerPage}${searchParam}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderTable(data.users, (page - 1) * itemsPerPage);
                    renderPagination(data.totalPages, parseInt(data.currentPage));
                    currentPage = parseInt(data.currentPage);
                    attachActionListeners();
                } else {
                    console.error('Error fetching users:', data.message);
                    document.getElementById('users-table-body').innerHTML = `<tr><td colspan="11" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error loading users: ${data.message}</p></div></td></tr>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                document.getElementById('users-table-body').innerHTML = `<tr><td colspan="11" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Could not connect to server.</p></div></td></tr>`;
            });
    }    function renderTable(users, offset) {
        const tbody = document.getElementById('users-table-body');
        tbody.innerHTML = '';
        if (users.length === 0) {
            const message = searchTerm ? 
                `<div class="text-muted"><i class="fas fa-search fa-2x mb-2"></i><p>No users found matching "${searchTerm}"</p><p class="small">Try adjusting your search terms</p></div>` :
                '<div class="text-muted"><i class="fas fa-users fa-2x mb-2"></i><p>No users found</p></div>';
            tbody.innerHTML = `<tr><td colspan="11" class="text-center py-4">${message}</td></tr>`;
            return;
        }

        users.forEach((user, index) => {
            const sr_no = offset + index + 1;
            const statusBadge = user.status ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            const roleBadge = `<span class="badge bg-${user.role_badge_color}">${user.role_formatted}</span>`;
            
            const row = `
                <tr>
                    <td>${sr_no}</td>
                    <td>${highlightSearchTerm(escapeHTML(user.name), searchTerm)}</td>
                    <td>${escapeHTML(user.username)}</td>
                    <td>${escapeHTML(user.phone)}</td>
                    <td>${escapeHTML(user.email)}</td>
                    <td>${roleBadge}</td>
                    <td>${escapeHTML(user.branch_name || '-')}</td>
                    <td>${user.last_login_formatted}</td>
                    <td>${statusBadge}</td>
                    <td>${user.created_formatted}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info view-user" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewUserModal"
                                    data-id="${user.id}"
                                    data-name="${escapeHTML(user.name)}"
                                    data-username="${escapeHTML(user.username)}"
                                    data-phone="${escapeHTML(user.phone)}"
                                    data-email="${escapeHTML(user.email)}"
                                    data-role="${escapeHTML(user.role)}"
                                    data-branch="${escapeHTML(user.branch_name)}"
                                    data-status="${user.status}"
                                    data-last-login="${user.last_login || ''}"
                                    data-created-at="${user.created_at || ''}"
                                    title="View User">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary edit-user" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editUserModal"
                                    data-id="${user.id}"
                                    data-name="${escapeHTML(user.name)}"
                                    data-username="${escapeHTML(user.username)}"
                                    data-phone="${escapeHTML(user.phone)}"
                                    data-email="${escapeHTML(user.email)}"
                                    data-role="${escapeHTML(user.role)}"
                                    data-branch="${user.branch_id || ''}"
                                    data-status="${user.status}"
                                    data-created-at="${user.created_at || ''}"
                                    title="Edit User">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="users.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                <input type="hidden" name="delete_user" value="1">
                                <input type="hidden" name="user_id" value="${user.id}">
                                <button type="submit" class="btn btn-outline-danger" title="Delete User">
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
            if (currentPage > 1) fetchUsers(currentPage - 1);
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
                fetchUsers(i);
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
            if (currentPage < totalPages) fetchUsers(currentPage + 1);
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
        // Handle view user button clicks
        document.querySelectorAll('.view-user').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                document.getElementById('view-user-name').textContent = this.dataset.name || '-';
                document.getElementById('view-user-username').textContent = this.dataset.username || '-';
                document.getElementById('view-user-role').innerHTML = `
                    <span class="badge bg-${getRoleBadgeColor(this.dataset.role)}">
                        ${formatRole(this.dataset.role)}
                    </span>
                `;
                document.getElementById('view-user-status').innerHTML = `
                    <span class="badge bg-${this.dataset.status == 1 ? 'success' : 'danger'}">
                        ${this.dataset.status == 1 ? 'Active' : 'Inactive'}
                    </span>
                `;
                document.getElementById('view-user-phone').textContent = this.dataset.phone || '-';
                document.getElementById('view-user-email').textContent = this.dataset.email || '-';
                document.getElementById('view-user-branch').textContent = this.dataset.branch || '-';
                
                const lastLoginDate = this.dataset.lastLogin;
                if (lastLoginDate && lastLoginDate !== '0000-00-00 00:00:00' && lastLoginDate !== 'Never') {
                    const date = new Date(lastLoginDate);
                    const formattedDate = date.getFullYear() + '-' +
                                      ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                                      ('0' + date.getDate()).slice(-2) + ' ' +
                                      ('0' + date.getHours()).slice(-2) + ':' +
                                      ('0' + date.getMinutes()).slice(-2);
                    document.getElementById('view-user-last-login').textContent = formattedDate;
                } else {
                    document.getElementById('view-user-last-login').textContent = 'Never';
                }
            });
        });

        // Handle edit user button clicks
        document.querySelectorAll('.edit-user').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const modal = document.getElementById('editUserModal');
                modal.querySelector('.modal-title').textContent = 'Edit User';
                
                document.getElementById('edit_user_id').value = this.dataset.id;
                document.getElementById('edit_username').value = this.dataset.username;
                document.getElementById('edit_name').value = this.dataset.name;
                document.getElementById('edit_email').value = this.dataset.email || '';
                document.getElementById('edit_phone').value = this.dataset.phone || '';
                document.getElementById('edit_role').value = this.dataset.role;
                document.getElementById('edit_branch_id').value = this.dataset.branch || '';
                document.getElementById('edit_status').value = this.dataset.status;
                
                document.getElementById('edit_password').value = '';
                
                const editModal = new bootstrap.Modal(modal);
                editModal.show();
            });
        });
    }

    // Helper functions for role handling
    function getRoleBadgeColor(role) {
        return {
            'master_admin': 'danger',
            'admin': 'warning',
            'branch_admin': 'info',
            'receptionist': 'success',
            'technician': 'primary'
        }[role] || 'secondary';
    }

    function formatRole(role) {
        if (!role) return '-';
        return role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    // Initial fetch
    fetchUsers(currentPage);
});
</script>

<?php include '../inc/footer.php'; ?>