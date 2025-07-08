<?php
// Set page title
$page_title = 'Settings';

// Include header and session handling
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            if ($name && $email) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $phone, $_SESSION['user_id']])) {
                    $_SESSION['name'] = $name;
                    $response = ['success' => true, 'message' => 'Profile updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error updating profile'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Name and email are required'];
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if ($current_password && $new_password && $confirm_password) {
                if ($new_password === $confirm_password) {
                    // Verify current password
                    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $user = $stmt->fetch();
                    
                    if ($user && password_verify($current_password, $user['password'])) {
                        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
                            $response = ['success' => true, 'message' => 'Password changed successfully'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error changing password'];
                        }
                    } else {
                        $response = ['success' => false, 'message' => 'Current password is incorrect'];
                    }
                } else {
                    $response = ['success' => false, 'message' => 'New passwords do not match'];
                }
            } else {
                $response = ['success' => false, 'message' => 'All password fields are required'];
            }
            break;
            
        case 'update_system':
            $lab_name = $_POST['lab_name'] ?? '';
            $lab_address = $_POST['lab_address'] ?? '';
            $lab_phone = $_POST['lab_phone'] ?? '';
            $lab_email = $_POST['lab_email'] ?? '';
            
            if ($lab_name) {
                // Store system settings in a simple way (you might want to create a settings table)
                $settings = [
                    'lab_name' => $lab_name,
                    'lab_address' => $lab_address,
                    'lab_phone' => $lab_phone,
                    'lab_email' => $lab_email
                ];
                
                // For now, we'll store in session (in a real app, use a settings table)
                $_SESSION['lab_settings'] = $settings;
                $response = ['success' => true, 'message' => 'System settings updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Lab name is required'];
            }
            break;
    }
    
    if (isset($response)) {
        echo json_encode($response);
        exit;
    }
}

// Get current user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get lab settings (from session for now)
$lab_settings = $_SESSION['lab_settings'] ?? [
    'lab_name' => 'PathLab Pro',
    'lab_address' => '123 Medical Center Drive',
    'lab_phone' => '+1-555-0123',
    'lab_email' => 'info@pathlabpro.com'
];
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-cog mr-2"></i>Settings</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Settings</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <!-- Profile Settings -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-user mr-2"></i>Profile Settings</h3>
            </div>
            <form id="profileForm">
              <div class="card-body">
                <div class="form-group">
                  <label for="name">Full Name *</label>
                  <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($current_user['name']); ?>" required>
                </div>
                <div class="form-group">
                  <label for="email">Email Address *</label>
                  <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($current_user['email']); ?>" required>
                </div>
                <div class="form-group">
                  <label for="phone">Phone Number</label>
                  <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                  <label>User Type</label>
                  <input type="text" class="form-control" value="<?php echo ucfirst(htmlspecialchars($current_user['user_type'])); ?>" readonly>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-2"></i>Update Profile
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-lock mr-2"></i>Change Password</h3>
            </div>
            <form id="passwordForm">
              <div class="card-body">
                <div class="form-group">
                  <label for="current_password">Current Password *</label>
                  <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                  <label for="new_password">New Password *</label>
                  <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                  <small class="form-text text-muted">Password must be at least 6 characters long.</small>
                </div>
                <div class="form-group">
                  <label for="confirm_password">Confirm New Password *</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-warning">
                  <i class="fas fa-key mr-2"></i>Change Password
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php if ($current_user['user_type'] == 'admin'): ?>
      <!-- System Settings (Admin Only) -->
      <div class="row mt-4">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-hospital mr-2"></i>Laboratory Settings</h3>
            </div>
            <form id="systemForm">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="lab_name">Laboratory Name *</label>
                      <input type="text" class="form-control" id="lab_name" name="lab_name" value="<?php echo htmlspecialchars($lab_settings['lab_name']); ?>" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="lab_email">Laboratory Email</label>
                      <input type="email" class="form-control" id="lab_email" name="lab_email" value="<?php echo htmlspecialchars($lab_settings['lab_email']); ?>">
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="lab_phone">Laboratory Phone</label>
                      <input type="text" class="form-control" id="lab_phone" name="lab_phone" value="<?php echo htmlspecialchars($lab_settings['lab_phone']); ?>">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="lab_address">Laboratory Address</label>
                      <textarea class="form-control" id="lab_address" name="lab_address" rows="3"><?php echo htmlspecialchars($lab_settings['lab_address']); ?></textarea>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-save mr-2"></i>Update System Settings
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- System Information -->
      <div class="row mt-4">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>System Information</h3>
            </div>
            <div class="card-body">
              <table class="table table-borderless">
                <tr>
                  <td><strong>System Version:</strong></td>
                  <td>PathLab Pro v1.0.0</td>
                </tr>
                <tr>
                  <td><strong>PHP Version:</strong></td>
                  <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                  <td><strong>Database:</strong></td>
                  <td>MySQL/MariaDB</td>
                </tr>
                <tr>
                  <td><strong>Server Time:</strong></td>
                  <td><?php echo date('Y-m-d H:i:s'); ?></td>
                </tr>
              </table>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-tools mr-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
              <button class="btn btn-info btn-block mb-2" onclick="clearCache()">
                <i class="fas fa-broom mr-2"></i>Clear System Cache
              </button>
              <button class="btn btn-warning btn-block mb-2" onclick="backupDatabase()">
                <i class="fas fa-download mr-2"></i>Backup Database
              </button>
              <button class="btn btn-secondary btn-block mb-2" onclick="viewLogs()">
                <i class="fas fa-file-alt mr-2"></i>View System Logs
              </button>
              <button class="btn btn-primary btn-block" onclick="checkUpdates()">
                <i class="fas fa-sync-alt mr-2"></i>Check for Updates
              </button>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Profile Form Submission
    $('#profileForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'settings.php',
            type: 'POST',
            data: $(this).serialize() + '&action=update_profile',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating profile');
            }
        });
    });

    // Password Form Submission
    $('#passwordForm').submit(function(e) {
        e.preventDefault();
        
        var newPassword = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();
        
        if (newPassword !== confirmPassword) {
            toastr.error('New passwords do not match');
            return;
        }
        
        $.ajax({
            url: 'settings.php',
            type: 'POST',
            data: $(this).serialize() + '&action=change_password',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#passwordForm')[0].reset();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while changing password');
            }
        });
    });

    // System Form Submission
    $('#systemForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'settings.php',
            type: 'POST',
            data: $(this).serialize() + '&action=update_system',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating system settings');
            }
        });
    });

    // Password confirmation validation
    $('#confirm_password').on('keyup', function() {
        var password = $('#new_password').val();
        var confirmPassword = $(this).val();
        
        if (password !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
});

// Quick Action Functions
function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        toastr.info('Cache clearing functionality would be implemented here');
    }
}

function backupDatabase() {
    if (confirm('Are you sure you want to backup the database?')) {
        toastr.info('Database backup functionality would be implemented here');
    }
}

function viewLogs() {
    toastr.info('System logs viewer would be implemented here');
}

function checkUpdates() {
    toastr.info('Update checker would be implemented here');
}
</script>
