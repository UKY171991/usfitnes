<?php
require_once 'config.php';
require_once 'includes/adminlte_template.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

renderTemplate('users_ajax', 'Users Management', [
    'page_title' => 'Users Management',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => 'dashboard_new.php'],
        ['name' => 'Users', 'url' => '']
    ]
]);

function getContent() {
    ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Users Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard_new.php">Home</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-2"></i>
                    All Users
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="addUserBtn">
                        <i class="fas fa-plus mr-1"></i>
                        Add User
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add/Edit User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add User</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="userForm">
                <div class="modal-body">
                    <input type="hidden" id="userId" name="id">
                    <input type="hidden" name="action" id="formAction" value="create">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="role">Role *</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Administrator</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="technician">Lab Technician</option>
                                    <option value="receptionist">Receptionist</option>
                                    <option value="manager">Manager</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" id="passwordRow">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="form-text text-muted">Leave empty to keep current password (for updates)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveUserBtn">
                        <i class="fas fa-save mr-1"></i>
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    let usersTable = $('#usersTable').DataTable({
        ajax: {
            url: 'api/users_api.php',
            type: 'GET',
            data: { action: 'list' },
            dataSrc: function(json) {
                if (!json.success) {
                    toastr.error(json.message || 'Failed to load users');
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { 
                data: 'id',
                defaultContent: 'N/A'
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.first_name || ''} ${row.last_name || ''}`.trim() || 'N/A';
                }
            },
            { 
                data: 'username',
                defaultContent: 'N/A'
            },
            { 
                data: 'email',
                defaultContent: 'N/A'
            },
            { 
                data: 'role',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    let displayRole = data || 'N/A';
                    
                    switch(data) {
                        case 'admin':
                            badgeClass = 'danger';
                            displayRole = 'Administrator';
                            break;
                        case 'doctor':
                            badgeClass = 'success';
                            displayRole = 'Doctor';
                            break;
                        case 'technician':
                            badgeClass = 'info';
                            displayRole = 'Lab Technician';
                            break;
                        case 'receptionist':
                            badgeClass = 'warning';
                            displayRole = 'Receptionist';
                            break;
                        case 'manager':
                            badgeClass = 'primary';
                            displayRole = 'Manager';
                            break;
                    }
                    
                    return `<span class="badge badge-${badgeClass}">${displayRole}</span>`;
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    if (data === 'active') badgeClass = 'success';
                    else if (data === 'inactive') badgeClass = 'warning';
                    else if (data === 'suspended') badgeClass = 'danger';
                    
                    return `<span class="badge badge-${badgeClass}">${data || 'N/A'}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm edit-user" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-warning btn-sm reset-password" data-id="${row.id}" title="Reset Password">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-user" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            emptyTable: "No users found",
            loadingRecords: "Loading users...",
            processing: "Processing..."
        }
    });
    
    // Add User Button
    $('#addUserBtn').click(function() {
        resetForm();
        $('#userModalLabel').text('Add User');
        $('#formAction').val('create');
        $('#password').prop('required', true);
        $('#confirmPassword').prop('required', true);
        $('#userModal').modal('show');
    });

    // Edit User
    $(document).on('click', '.edit-user', function() {
        const userId = $(this).data('id');
        editUser(userId);
    });

    // Reset Password
    $(document).on('click', '.reset-password', function() {
        const userId = $(this).data('id');
        resetUserPassword(userId);
    });

    // Delete User
    $(document).on('click', '.delete-user', function() {
        const userId = $(this).data('id');
        deleteUser(userId);
    });

    // Form Submission
    $('#userForm').submit(function(e) {
        e.preventDefault();
        
        // Validate passwords match
        if ($('#password').val() !== $('#confirmPassword').val()) {
            toastr.error('Passwords do not match');
            return;
        }
        
        // Show loading state
        $('#saveUserBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: 'api/users_api.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#userModal').modal('hide');
                    usersTable.ajax.reload(null, false);
                } else {
                    toastr.error(response.message || 'Operation failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                $('#saveUserBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save User');
            }
        });
    });

    function editUser(id) {
        $.ajax({
            url: 'api/users_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    const user = response.data;
                    
                    // Populate form
                    $('#userId').val(user.id);
                    $('#firstName').val(user.first_name);
                    $('#lastName').val(user.last_name);
                    $('#username').val(user.username);
                    $('#email').val(user.email);
                    $('#role').val(user.role);
                    $('#status').val(user.status);
                    $('#phone').val(user.phone);
                    
                    // Clear password fields and make them not required for updates
                    $('#password').val('').prop('required', false);
                    $('#confirmPassword').val('').prop('required', false);
                    
                    $('#userModalLabel').text('Edit User');
                    $('#formAction').val('update');
                    $('#userModal').modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load user data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Failed to load user data');
            }
        });
    }

    function resetUserPassword(id) {
        Swal.fire({
            title: 'Reset Password',
            text: 'Enter new password for this user:',
            input: 'password',
            inputPlaceholder: 'New password',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Reset Password',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a password';
                }
                if (value.length < 6) {
                    return 'Password must be at least 6 characters';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/users_api.php',
                    type: 'POST',
                    data: { 
                        action: 'reset_password', 
                        id: id,
                        new_password: result.value 
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'Password reset failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to reset password');
                    }
                });
            }
        });
    }

    function deleteUser(id) {
        Swal.fire({
            title: 'Delete User',
            text: 'Are you sure you want to delete this user? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/users_api.php',
                    type: 'POST',
                    data: { 
                        action: 'delete', 
                        id: id 
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            usersTable.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Delete failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to delete user');
                    }
                });
            }
        });
    }

    function resetForm() {
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('#formAction').val('create');
        $('#status').val('active');
    }

    // Auto-refresh table every 30 seconds
    setInterval(function() {
        if ($('#userModal').is(':visible') === false) {
            usersTable.ajax.reload(null, false);
        }
    }, 30000);
});
</script>

<?php
    return ob_get_clean();
}
?>
