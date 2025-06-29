<?php
// Set page title and active menu
$page_title = 'User Management';
$active_menu = 'users';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fl                <label for="user_role">User Type <span class="text-danger">*</span></label>
                <select class="form-control" id="user_role" name="role" required>
                  <option value="">Select User Type</option>
                  <option value="admin">Administrator</option>
                  <option value="technician">Technician</option>
                  <option value="receptionist">Receptionist</option>
                  <option value="doctor">Doctor</option>
                  <option value="pathologist">Pathologist</option>
                </select>     <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">User Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Users</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Stats Row -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalUsers">0</h3>
              <p>Total Users</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="activeUsers">0</h3>
              <p>Active Users</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-check"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('active')">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="adminUsers">0</h3>
              <p>Administrators</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-shield"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByType('admin')">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="loginToday">0</h3>
              <p>Logins Today</p>
            </div>
            <div class="icon">
              <i class="fas fa-sign-in-alt"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Main Card -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">System Users</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addUserModal">
                  <i class="fas fa-plus"></i> Add User
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="exportUsers()">
                  <i class="fas fa-download"></i> Export
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Filters -->
              <div class="row mb-3">
                <div class="col-md-3">
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" onclick="loadUsers()">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="typeFilter" onchange="loadUsers()">
                    <option value="">All Types</option>
                    <option value="admin">Administrator</option>
                    <option value="technician">Technician</option>
                    <option value="receptionist">Receptionist</option>
                    <option value="doctor">Doctor</option>
                    <option value="pathologist">Pathologist</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="statusFilter" onchange="loadUsers()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="sortBy" onchange="loadUsers()">
                    <option value="name">Sort by Name</option>
                    <option value="created_at">Sort by Date Added</option>
                    <option value="last_login">Sort by Last Login</option>
                    <option value="type">Sort by Type</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear Filters
                  </button>
                  <button class="btn btn-success btn-sm ml-1" onclick="refreshUsers()">
                    <i class="fas fa-sync"></i> Refresh
                  </button>
                </div>
              </div>

              <!-- Users Table -->
              <div class="table-responsive">
                <table id="usersTable" class="table table-bordered table-striped table-hover">
                  <thead>
                    <tr>
                      <th width="5%">Avatar</th>
                      <th width="20%">Name</th>
                      <th width="15%">Username</th>
                      <th width="15%">Email</th>
                      <th width="10%">Type</th>
                      <th width="10%">Status</th>
                      <th width="10%">Last Login</th>
                      <th width="10%">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="usersTableBody">
                    <!-- Dynamic content will be loaded here -->
                  </tbody>
                </table>
              </div>

              <!-- Loading indicator -->
              <div id="loadingIndicator" class="text-center p-3" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading users...</p>
              </div>

              <!-- Pagination -->
              <div class="row mt-3">
                <div class="col-sm-12 col-md-5">
                  <div id="usersInfo" class="dataTables_info"></div>
                </div>
                <div class="col-sm-12 col-md-7">
                  <nav>
                    <ul class="pagination pagination-sm float-right" id="usersPagination">
                      <!-- Pagination will be loaded here -->
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title" id="addUserModalLabel">
          <i class="fas fa-user-plus"></i> Add New User
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addUserForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="add_full_name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_full_name" name="full_name" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_username" name="username" required>
                <small class="form-text text-muted">Username must be unique</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="add_email" name="email" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_user_type">Role <span class="text-danger">*</span></label>
                <select class="form-control" id="add_user_type" name="user_type" required>
                  <option value="">Select Role</option>
                  <option value="admin">Administrator</option>
                  <option value="technician">Technician</option>
                  <option value="receptionist">Receptionist</option>
                  <option value="doctor">Doctor</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_department">Department</label>
                <input type="text" class="form-control" id="add_department" name="department">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_password">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="add_password" name="password" required minlength="6">
                <small class="form-text text-muted">Minimum 6 characters</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_confirm_password">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="add_confirm_password" name="confirm_password" required minlength="6">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="add_phone">Phone</label>
                <input type="tel" class="form-control" id="add_phone" name="phone">
              </div>
            </div>
          </div>
          <!-- Notes field removed to match backend -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary" id="addUserBtn">
            <i class="fas fa-save"></i> Save User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h4 class="modal-title" id="editUserModalLabel">
          <i class="fas fa-edit"></i> Edit User
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editUserForm">
        <input type="hidden" id="edit_user_id" name="id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="edit_full_name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_username">Username <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_username" name="username" required>
                <small class="form-text text-muted">Username must be unique</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="edit_email" name="email" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_user_type">Role <span class="text-danger">*</span></label>
                <select class="form-control" id="edit_user_type" name="user_type" required>
                  <option value="">Select Role</option>
                  <option value="admin">Administrator</option>
                  <option value="technician">Technician</option>
                  <option value="receptionist">Receptionist</option>
                  <option value="doctor">Doctor</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_department">Department</label>
                <input type="text" class="form-control" id="edit_department" name="department">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                <label for="edit_phone">Phone</label>
                <input type="tel" class="form-control" id="edit_phone" name="phone">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_password">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="edit_password" name="password" minlength="6">
                <small class="form-text text-muted">Minimum 6 characters. Leave blank to keep current password.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_confirm_password">Confirm Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password" minlength="6">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-warning" id="editUserBtn">
            <i class="fas fa-save"></i> Update User
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title" id="viewUserModalLabel">
          <i class="fas fa-eye"></i> View User Details
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewUserContent">
        <!-- Content will be loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h4 class="modal-title text-white" id="deleteUserModalLabel">
          <i class="fas fa-trash"></i> Confirm Delete
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this user?</p>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Warning:</strong> This action cannot be undone.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i class="fas fa-trash"></i> Delete User
        </button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize page
    loadStats();
    loadUsers();
    
    // Search on Enter key
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            loadUsers();
        }
    });
    
    // Auto-refresh every 60 seconds
    setInterval(function() {
        if (!$('.modal').hasClass('show')) {
            loadStats();
        }
    }, 60000);
    
    // Password confirmation validation
    $('#add_confirm_password, #edit_confirm_password').on('input', function() {
        const passwordField = $(this).attr('id').includes('add') ? '#add_password' : '#edit_password';
        const password = $(passwordField).val();
        const confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else if (confirmPassword && password === confirmPassword) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-invalid is-valid');
        }
    });
});

// Global variables
let currentPage = 1;
let usersPerPage = 10;
let currentFilters = {
    search: '',
    type: '',
    status: ''
};

// Load statistics
function loadStats() {
    $.ajax({
        url: 'api/users_api.php',
        method: 'GET',
        data: { action: 'get_stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.stats) {
                const stats = response.stats;
                $('#totalUsers').text(stats.total_users || 0);
                $('#activeUsers').text(stats.active_users || 0);
                // Find admin count from users_by_role
                let adminCount = 0;
                if (Array.isArray(stats.users_by_role)) {
                    stats.users_by_role.forEach(function(roleObj) {
                        if (roleObj.user_type === 'admin') adminCount = roleObj.count;
                    });
                }
                $('#adminUsers').text(adminCount);
                $('#loginToday').text(stats.recent_users || 0);
            }
        },
        error: function() {
            console.log('Error loading statistics');
        }
    });
}

// Load users with filters and pagination
function loadUsers(page = 1) {
    currentPage = page;
    
    // Get current filters
    currentFilters.search = $('#searchInput').val().trim();
    currentFilters.type = $('#typeFilter').val();
    currentFilters.status = $('#statusFilter').val();
    
    // Show loading indicator
    $('#loadingIndicator').show();
    $('#usersTableBody').hide();
    
    $.ajax({
        url: 'api/users_api.php',
        method: 'GET',
        data: {
            action: 'get_users'
            // Pagination and filters can be handled here if backend supports
            // page: currentPage,
            // limit: usersPerPage,
            // search: currentFilters.search,
            // type: currentFilters.type,
            // status: currentFilters.status
        },
        dataType: 'json',
        success: function(response) {
            $('#loadingIndicator').hide();
            $('#usersTableBody').show();
            if (response.success && response.users) {
                displayUsers(response.users);
                // Pagination and info update can be handled if backend supports
            } else {
                displayNoUsers(response.message || 'No users found');
            }
        },
        error: function() {
            $('#loadingIndicator').hide();
            $('#usersTableBody').show();
            displayNoUsers('Error loading users');
        }
    });
}

// Display users in table
function displayUsers(users) {
    let html = '';
    
    if (users.length === 0) {
        displayNoUsers('No users found matching your criteria');
        return;
    }
    
    users.forEach(function(user) {
        // Status badge
        let statusBadge = '';
        switch(user.status) {
            case 'active':
                statusBadge = '<span class="badge badge-success">Active</span>';
                break;
            case 'inactive':
                statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                break;
            case 'suspended':
                statusBadge = '<span class="badge badge-danger">Suspended</span>';
                break;
            default:
                statusBadge = '<span class="badge badge-secondary">' + user.status + '</span>';
        }
          // Type badge
        let typeBadge = '';
        switch(user.type) {
            case 'admin':
                typeBadge = '<span class="badge badge-danger">Admin</span>';
                break;
            case 'technician':
                typeBadge = '<span class="badge badge-info">Technician</span>';
                break;
            case 'receptionist':
                typeBadge = '<span class="badge badge-warning">Receptionist</span>';
                break;
            case 'doctor':
                typeBadge = '<span class="badge badge-primary">Doctor</span>';
                break;
            case 'pathologist':
                typeBadge = '<span class="badge badge-success">Pathologist</span>';
                break;
            default:
                typeBadge = '<span class="badge badge-secondary">' + user.type + '</span>';
        }
        
        // User avatar
        const userInitial = (user.first_name && user.last_name) 
            ? (user.first_name.charAt(0) + user.last_name.charAt(0)).toUpperCase()
            : user.username.charAt(0).toUpperCase();
        
        html += `
            <tr>
                <td class="text-center">
                    <img src="https://via.placeholder.com/40x40/2c5aa0/ffffff?text=${userInitial}" 
                         class="img-circle" alt="User Avatar" width="40" height="40">
                </td>
                <td>
                    <strong>${user.first_name || ''} ${user.last_name || ''}</strong>
                    <br><small class="text-muted">${user.username}</small>
                </td>
                <td>${user.username}</td>
                <td>${user.email || 'N/A'}</td>
                <td>${typeBadge}</td>
                <td>${statusBadge}</td>
                <td>
                    <small class="text-muted">${formatDate(user.last_login)}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewUser(${user.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editUser(${user.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteUser(${user.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $('#usersTableBody').html(html);
}

// Display sample users for demonstration
function displaySampleUsers() {
    const sampleUsers = [
        {
            id: 1,
            first_name: 'John',
            last_name: 'Doe',
            username: 'admin',
            email: 'admin@pathlab.com',
            type: 'admin',
            status: 'active',
            last_login: '2024-06-19 10:30:00'
        },
        {
            id: 2,
            first_name: 'Jane',
            last_name: 'Smith',
            username: 'tech01',
            email: 'jane@pathlab.com',
            type: 'technician',
            status: 'active',
            last_login: '2024-06-19 09:15:00'
        },
        {
            id: 3,
            first_name: 'Mike',
            last_name: 'Johnson',
            username: 'reception',
            email: 'mike@pathlab.com',
            type: 'receptionist',
            status: 'active',
            last_login: '2024-06-18 16:45:00'
        }
    ];
    
    displayUsers(sampleUsers);
    
    // Update pagination info for sample data
    updateUsersInfo({
        page: 1,
        pages: 1,
        limit: 10,
        total: 3
    });
}

// Display no users message
function displayNoUsers(message) {
    $('#usersTableBody').html(`
        <tr>
            <td colspan="8" class="text-center text-muted py-4">
                <i class="fas fa-users fa-2x mb-2"></i><br>
                ${message}
            </td>
        </tr>
    `);
    $('#usersPagination').empty();
    $('#usersInfo').text('Showing 0 to 0 of 0 entries');
}

// Display pagination
function displayPagination(pagination) {
    let html = '';
    
    if (pagination.pages > 1) {
        // Previous button
        if (pagination.page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadUsers(${pagination.page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.pages, pagination.page + 2);
        
        if (startPage > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadUsers(1)">1</a>
            </li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const active = i === pagination.page ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadUsers(${i})">${i}</a>
            </li>`;
        }
        
        if (endPage < pagination.pages) {
            if (endPage < pagination.pages - 1) {
                html += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadUsers(${pagination.pages})">${pagination.pages}</a>
            </li>`;
        }
        
        // Next button
        if (pagination.page < pagination.pages) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadUsers(${pagination.page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        }
    }
    
    $('#usersPagination').html(html);
}

// Update users info
function updateUsersInfo(pagination) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    $('#usersInfo').text(`Showing ${start} to ${end} of ${pagination.total} entries`);
}

// Filter functions
function filterByStatus(status) {
    $('#statusFilter').val(status);
    loadUsers(1);
}

function filterByType(type) {
    $('#typeFilter').val(type);
    loadUsers(1);
}

function clearFilters() {
    $('#searchInput').val('');
    $('#typeFilter').val('');
    $('#statusFilter').val('');
    loadUsers(1);
}

function refreshUsers() {
    loadStats();
    loadUsers(currentPage);
    showToaster('success', 'Users refreshed successfully');
}

// Add user form submission
$('#addUserForm').submit(function(e) {
    e.preventDefault();
    
    // Validate passwords match
    const password = $('#add_password').val();
    const confirmPassword = $('#add_confirm_password').val();
    
    if (password !== confirmPassword) {
        showToaster('danger', 'Passwords do not match');
        return;
    }
    
    const submitBtn = $('#addUserBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    
    $.ajax({
        url: 'api/users_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=create_user',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addUserModal').modal('hide');
                $('#addUserForm')[0].reset();
                loadUsers(currentPage);
                loadStats();
                showToaster('success', 'User added successfully!');
            } else {
                showToaster('danger', 'Error adding user: ' + response.message);
            }
        },
        error: function() {
            showToaster('danger', 'Error adding user. Please try again.');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// Edit user
function editUser(id) {
    // Fetch user data and populate modal
    $.ajax({
        url: 'api/users_api.php',
        method: 'GET',
        data: { action: 'get_user', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.user) {
                const user = response.user;
                $('#edit_user_id').val(user.id);
                $('#edit_full_name').val(user.full_name);
                $('#edit_username').val(user.username);
                $('#edit_email').val(user.email);
                $('#edit_user_type').val(user.user_type);
                $('#edit_phone').val(user.phone);
                $('#edit_department').val(user.department);
                $('#edit_status').val(user.status);
                $('#editUserModal').modal('show');
            } else {
                showToaster('danger', 'Error loading user data.');
            }
        },
        error: function() {
            showToaster('danger', 'Error loading user data.');
        }
    });
}

// Handle edit user form submit
$('#editUserForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize() + '&action=update_user';
    const submitBtn = $('#editUserBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
    $.ajax({
        url: 'api/users_api.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editUserModal').modal('hide');
                loadUsers(currentPage);
                loadStats();
                showToaster('success', 'User updated successfully!');
            } else {
                showToaster('danger', 'Error updating user: ' + (response.message || response.error));
            }
        },
        error: function() {
            showToaster('danger', 'Error updating user. Please try again.');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// View user details
function viewUser(id) {
    // Implementation for viewing user details
    showToaster('info', 'View functionality will be available soon.');
}

// Delete confirmation
let userToDelete = null;

function confirmDeleteUser(id) {
    userToDelete = id;
    $('#deleteUserModal').modal('show');
}

$('#confirmDeleteBtn').click(function() {
    if (userToDelete) {
        deleteUser(userToDelete);
        userToDelete = null;
    }
});

// Delete user
function deleteUser(id) {
    $.ajax({
        url: 'api/users_api.php',
        method: 'POST',
        data: { action: 'delete_user', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToaster('success', 'User deleted successfully!');
                loadUsers(currentPage);
                loadStats();
            } else {
                showToaster('danger', 'Error deleting user: ' + (response.message || response.error));
            }
        },
        error: function() {
            showToaster('danger', 'Error deleting user. Please try again.');
        },
        complete: function() {
            $('#deleteUserModal').modal('hide');
        }
    });
}

// Export users
function exportUsers() {
    showToaster('info', 'Export feature will be available soon.');
}

// Utility functions
function formatDate(dateStr) {
    if (!dateStr) return 'Never';
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
