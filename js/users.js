// Users Management JavaScript
// AdminLTE3 Template with AJAX Operations

let usersTable;
let usersCrud;
let usersFormHandler;
let resetPasswordFormHandler;

$(document).ready(function() {
    initializeUsersPage();
});

function initializeUsersPage() {
    // Initialize CRUD operations
    usersCrud = new CrudOperations('api/users_api.php', 'User');
    
    // Initialize form handlers
    usersFormHandler = new FormHandler('#userForm', 'api/users_api.php', {
        onSuccess: function(response) {
            $('#userModal').modal('hide');
            usersTable.ajax.reload(null, false);
            showSuccess(response.message);
        }
    });
    
    resetPasswordFormHandler = new FormHandler('#resetPasswordForm', 'api/users_api.php?action=reset_password', {
        onSuccess: function(response) {
            $('#resetPasswordModal').modal('hide');
            showSuccess(response.message);
        }
    });
    
    // Initialize DataTable
    initializeUsersTable();
    
    // Initialize filters
    initializeFilters();
}

function initializeUsersTable() {
    const columns = [
        {
            data: 'id',
            name: 'id',
            title: 'ID',
            width: '80px'
        },
        {
            data: 'name',
            name: 'name',
            title: 'Name',
            render: function(data, type, row) {
                return `<strong>${data}</strong>`;
            }
        },
        {
            data: 'email',
            name: 'email',
            title: 'Email'
        },
        {
            data: 'user_type',
            name: 'user_type',
            title: 'User Type',
            render: function(data, type, row) {
                const typeClass = getUserTypeClass(data);
                return `<span class="badge badge-${typeClass}">${capitalizeFirst(data.replace('_', ' '))}</span>`;
            }
        },
        {
            data: 'status',
            name: 'status',
            title: 'Status',
            render: function(data, type, row) {
                const statusClass = data === 'active' ? 'success' : 'secondary';
                return `<span class="badge badge-${statusClass}">${capitalizeFirst(data)}</span>`;
            }
        },
        {
            data: 'last_login',
            name: 'last_login',
            title: 'Last Login',
            render: function(data, type, row) {
                return data ? formatDateTime(data) : 'Never';
            }
        },
        {
            data: null,
            name: 'actions',
            title: 'Actions',
            orderable: false,
            searchable: false,
            width: '180px',
            render: function(data, type, row) {
                return `
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info btn-action" onclick="viewUser(${row.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-action" onclick="editUser(${row.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-action" onclick="resetPassword(${row.id})" title="Reset Password">
                            <i class="fas fa-key"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-action" onclick="deleteUser(${row.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }
        }
    ];

    usersTable = initializeDataTable('#usersTable', 'ajax/users_datatable.php', columns, {
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            }
        ]
    });
    
    // Store reference globally
    globalDataTable = usersTable;
}

function initializeFilters() {
    $('#statusFilter, #userTypeFilter').on('change', function() {
        applyFilters();
    });
    
    $('#dateFilter').on('change', function() {
        applyFilters();
    });
}

function getCustomFilters() {
    return {
        status: $('#statusFilter').val(),
        user_type: $('#userTypeFilter').val(),
        registration_date: $('#dateFilter').val()
    };
}

function applyFilters() {
    if (usersTable) {
        usersTable.ajax.reload();
    }
}

function clearFilters() {
    $('#statusFilter, #userTypeFilter, #dateFilter').val('').trigger('change');
    applyFilters();
}

// Modal Functions
function showAddUserModal() {
    resetForm('#userForm');
    $('#userId').val('');
    $('#userModal .modal-title').text('Add New User');
    $('#passwordRow input[name="password"]').prop('required', true);
    $('#userModal').modal('show');
}

async function editUser(id) {
    try {
        const user = await usersCrud.getById(id);
        
        // Populate form
        usersFormHandler.populateForm(user);
        
        // Make password optional for edit
        $('#passwordRow input[name="password"]').prop('required', false);
        $('#passwordRow input[name="password"]').attr('placeholder', 'Leave blank to keep current password');
        
        $('#userModal .modal-title').text('Edit User');
        $('#userModal').modal('show');
    } catch (error) {
        showError('Failed to load user data');
    }
}

async function viewUser(id) {
    try {
        showLoader('Loading user details...');
        const user = await usersCrud.getById(id);
        
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">User ID:</th>
                            <td>${user.id}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><strong>${user.name}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>${user.email}</td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td>${user.username}</td>
                        </tr>
                        <tr>
                            <th>User Type:</th>
                            <td><span class="badge badge-${getUserTypeClass(user.user_type)}">${capitalizeFirst(user.user_type.replace('_', ' '))}</span></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>${user.phone || 'N/A'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Status:</th>
                            <td><span class="badge badge-${user.status === 'active' ? 'success' : 'secondary'}">${capitalizeFirst(user.status)}</span></td>
                        </tr>
                        <tr>
                            <th>Last Login:</th>
                            <td>${user.last_login ? formatDateTime(user.last_login) : 'Never'}</td>
                        </tr>
                        <tr>
                            <th>Login Count:</th>
                            <td>${user.login_count || 0}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>${formatDateTime(user.created_at)}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>${formatDateTime(user.updated_at)}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        $('#userDetails').html(detailsHtml);
        $('#viewUserModal').modal('show');
        
    } catch (error) {
        showError('Failed to load user details');
    } finally {
        hideLoader();
    }
}

function resetPassword(id) {
    $('#resetUserId').val(id);
    resetForm('#resetPasswordForm');
    $('#resetPasswordModal').modal('show');
}

async function deleteUser(id) {
    try {
        await usersCrud.delete(id);
        usersTable.ajax.reload(null, false);
    } catch (error) {
        // Error handling is done in CrudOperations class
    }
}

// Export Functions
function exportUsers() {
    const format = 'csv'; // Can be made dynamic
    const filters = getCustomFilters();
    
    AjaxUtils.exportData('api/users_api.php?action=export', format, filters);
}

// Utility Functions
function getUserTypeClass(userType) {
    const typeClasses = {
        'admin': 'danger',
        'doctor': 'info',
        'lab_tech': 'warning',
        'receptionist': 'secondary'
    };
    return typeClasses[userType] || 'secondary';
}

function resetUserForm() {
    resetForm('#userForm');
}

// Form validation for reset password
$('#resetPasswordForm').on('submit', function(e) {
    const newPassword = $('input[name="new_password"]').val();
    const confirmPassword = $('input[name="confirm_password"]').val();
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        showError('Passwords do not match');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        showError('Password must be at least 6 characters long');
        return false;
    }
});

// Global functions for external access
window.showAddUserModal = showAddUserModal;
window.editUser = editUser;
window.viewUser = viewUser;
window.resetPassword = resetPassword;
window.deleteUser = deleteUser;
window.exportUsers = exportUsers;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;