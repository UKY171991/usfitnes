<?php
// Set page title
$page_title = 'User Management';

// Include header
include 'includes/header.php';

// Include sidebar with user info
include 'includes/sidebar.php';

// Check if user is admin (this page is admin only)
if($user_type !== 'admin') {
    header('Location: dashboard.php');
    exit();
}
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">User Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">User Management</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Users DataTable -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-users mr-2"></i>System Users</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-user">
                <i class="fas fa-plus"></i> Add User
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="usersTable" class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Sample data -->
                  <tr>
                    <td>John Admin</td>
                    <td>admin@usfitness.com</td>
                    <td><span class="badge badge-danger">Admin</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>2024-01-15 10:30 AM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editUser(1)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(1)" title="Reset Password">
                          <i class="fas fa-key"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(1)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Dr. Sarah Wilson</td>
                    <td>sarah.wilson@usfitness.com</td>
                    <td><span class="badge badge-info">Doctor</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>2024-01-14 2:15 PM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editUser(2)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(2)" title="Reset Password">
                          <i class="fas fa-key"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(2)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Lab Tech Mike</td>
                    <td>mike.tech@usfitness.com</td>
                    <td><span class="badge badge-warning">Lab Tech</span></td>
                    <td><span class="badge badge-secondary">Inactive</span></td>
                    <td>2024-01-10 8:45 AM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editUser(3)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(3)" title="Reset Password">
                          <i class="fas fa-key"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(3)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="modal-add-user" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white"><i class="fas fa-plus mr-2"></i>Add New User</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addUserForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="first_name">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="first_name" name="first_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="last_name">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="last_name" name="last_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="email" name="email" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="user_type">Role <span class="text-danger">*</span></label>
                  <select class="form-control" id="user_type" name="user_type" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="lab_tech">Lab Tech</option>
                    <option value="receptionist">Receptionist</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="password">Password <span class="text-danger">*</span></label>
                  <input type="password" class="form-control" id="password" name="password" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="status">Status</label>
                  <select class="form-control" id="status" name="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveUserBtn">
            <i class="fas fa-save mr-1"></i>Save User
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="modal fade" id="modal-edit-user" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h4 class="modal-title text-white"><i class="fas fa-edit mr-2"></i>Edit User</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editUserForm">
            <input type="hidden" id="edit_user_id" name="id">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_first_name">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_last_name">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_email">Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="edit_email" name="email" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_user_type">Role <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_user_type" name="user_type" required>
                    <option value="">Select Role</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="lab_tech">Lab Tech</option>
                    <option value="receptionist">Receptionist</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="edit_status">Status</label>
              <select class="form-control" id="edit_status" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="updateUserBtn">
            <i class="fas fa-save mr-1"></i>Update User
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Reset Password Modal -->
  <div class="modal fade" id="modal-reset-password" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title text-white"><i class="fas fa-key mr-2"></i>Reset Password</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="resetPasswordForm">
            <input type="hidden" id="reset_user_id" name="id">
            <div class="form-group">
              <label for="new_password">New Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group">
              <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-info" id="resetPasswordBtn">
            <i class="fas fa-key mr-1"></i>Reset Password
          </button>
        </div>
      </div>
    </div>
  </div>

<?php
// Additional scripts specific to the users page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    var usersTable = $('#usersTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false
    });
    
    // Handle Add User form submission
    $('#saveUserBtn').on('click', function() {
        var formData = {
            first_name: $('#first_name').val(),
            last_name: $('#last_name').val(),
            email: $('#email').val(),
            user_type: $('#user_type').val(),
            password: $('#password').val(),
            status: $('#status').val()
        };
        
        // Basic validation
        if (!formData.first_name || !formData.last_name || !formData.email || !formData.user_type || !formData.password) {
            toastr.error('Please fill in all required fields.');
            return;
        }

        // Email validation
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(formData.email)) {
            toastr.error('Please enter a valid email address.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            var roleBadge = '';
            switch(formData.user_type) {
                case 'admin': roleBadge = '<span class="badge badge-danger">Admin</span>'; break;
                case 'doctor': roleBadge = '<span class="badge badge-info">Doctor</span>'; break;
                case 'lab_tech': roleBadge = '<span class="badge badge-warning">Lab Tech</span>'; break;
                case 'receptionist': roleBadge = '<span class="badge badge-secondary">Receptionist</span>'; break;
            }
            
            var statusBadge = formData.status === 'active' ? 
                '<span class="badge badge-success">Active</span>' : 
                '<span class="badge badge-secondary">Inactive</span>';
            
            // Add new row to table
            var newRow = [
                formData.first_name + ' ' + formData.last_name,
                formData.email,
                roleBadge,
                statusBadge,
                'Never logged in',
                '<div class="btn-group" role="group">' +
                    '<button type="button" class="btn btn-warning btn-sm" onclick="editUser(' + (usersTable.rows().count() + 1) + ')" title="Edit">' +
                        '<i class="fas fa-edit"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-secondary btn-sm" onclick="resetPassword(' + (usersTable.rows().count() + 1) + ')" title="Reset Password">' +
                        '<i class="fas fa-key"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteUser(' + (usersTable.rows().count() + 1) + ')" title="Delete">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</div>'
            ];
            
            usersTable.row.add(newRow).draw();
            
            toastr.success('User added successfully!');
            $('#modal-add-user').modal('hide');
            $('#addUserForm')[0].reset();
        }, 500);
    });

    // Handle Update User form submission
    $('#updateUserBtn').on('click', function() {
        var formData = {
            id: $('#edit_user_id').val(),
            first_name: $('#edit_first_name').val(),
            last_name: $('#edit_last_name').val(),
            email: $('#edit_email').val(),
            user_type: $('#edit_user_type').val(),
            status: $('#edit_status').val()
        };
        
        // Basic validation
        if (!formData.first_name || !formData.last_name || !formData.email || !formData.user_type) {
            toastr.error('Please fill in all required fields.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            toastr.success('User updated successfully!');
            $('#modal-edit-user').modal('hide');
            $('#editUserForm')[0].reset();
            // In real implementation, refresh the table data
        }, 500);
    });

    // Handle Reset Password form submission
    $('#resetPasswordBtn').on('click', function() {
        var newPassword = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();
        
        // Basic validation
        if (!newPassword || !confirmPassword) {
            toastr.error('Please fill in all password fields.');
            return;
        }

        if (newPassword !== confirmPassword) {
            toastr.error('Passwords do not match.');
            return;
        }

        if (newPassword.length < 6) {
            toastr.error('Password must be at least 6 characters long.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            toastr.success('Password reset successfully!');
            $('#modal-reset-password').modal('hide');
            $('#resetPasswordForm')[0].reset();
        }, 500);
    });

    // Reset forms when modals are hidden
    $('#modal-add-user').on('hidden.bs.modal', function () {
        $('#addUserForm')[0].reset();
    });
    
    $('#modal-edit-user').on('hidden.bs.modal', function () {
        $('#editUserForm')[0].reset();
    });
    
    $('#modal-reset-password').on('hidden.bs.modal', function () {
        $('#resetPasswordForm')[0].reset();
    });
});

// Edit user function
function editUser(id) {
    // Get the row data
    var table = $('#usersTable').DataTable();
    var row = table.row(function(idx, data, node) {
        return $(node).find('button[onclick="editUser(' + id + ')"]').length > 0;
    });
    
    if (row.length) {
        var data = row.data();
        
        // Extract data from the row and populate edit form
        var fullName = data[0].split(' ');
        $('#edit_user_id').val(id);
        $('#edit_first_name').val(fullName[0] || '');
        $('#edit_last_name').val(fullName.slice(1).join(' ') || '');
        $('#edit_email').val(data[1]);
        
        // Extract role from badge
        var roleText = $(data[2]).text().toLowerCase().replace(' ', '_');
        $('#edit_user_type').val(roleText);
        
        // Extract status from badge
        var statusText = $(data[3]).text().toLowerCase();
        $('#edit_status').val(statusText);
        
        $('#modal-edit-user').modal('show');
    }
}

// Reset password function
function resetPassword(id) {
    $('#reset_user_id').val(id);
    $('#modal-reset-password').modal('show');
}

// Delete user function
function deleteUser(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            // Find and remove the row
            var table = $('#usersTable').DataTable();
            var row = table.row(function(idx, data, node) {
                return $(node).find('button[onclick="deleteUser(' + id + ')"]').length > 0;
            });
            
            if (row.length) {
                row.remove().draw();
                toastr.success('User deleted successfully!');
            }
        }, 300);
    }
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
